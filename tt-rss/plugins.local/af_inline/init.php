<?php
class Af_Inline extends Plugin {

	private $host;
	private $filters = array();

	function about() {
		return array(1.0,
			"Inlines content from reddit. Based on af_comics, af_redditimgur and af_unburn.",
			"aliz");
	}

	public function handle_as_image($doc, $entry, $image) {
		$img = $doc->createElement('img');
		$img->setAttribute("src", $image);
		$paragraph = $doc->createElement('p');
		$paragraph->appendChild($img);

		$entry->parentNode->insertBefore($paragraph, $entry);
	}

	public function handle_as_video($doc, $entry, $source_stream, $poster_url = false, $debug = false) {
		_debug("handle_as_video: $source_stream", $debug);

		$video = $doc->createElement('video');
		$video->setAttribute("autoplay", "1");
		$video->setAttribute("controls", "1");
		$video->setAttribute("preload", "auto");
		$video->setAttribute("loop", "1");

		if ($poster_url) $video->setAttribute("poster", $poster_url);

		$source = $doc->createElement('source');
		$source->setAttribute("src", $source_stream);
		if (strpos($source_stream, "m3u8") !== FALSE) {
			$source->setAttribute("type", "application/x-mpegURL");
		} else {
			$source->setAttribute("type", "video/mp4");
		}

		$video->appendChild($source);

		$paragraph = $doc->createElement('p');
		$paragraph->appendChild($video);

		$entry->parentNode->insertBefore($paragraph, $entry);

		$img = $doc->createElement('img');
		$img->setAttribute("src",
			"data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D");

		$entry->parentNode->insertBefore($img, $entry);
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
		$host->add_hook($host::HOOK_PREFS_TAB, $this);

		require_once __DIR__ . "/filter_base.php";

		$filters = array_merge(glob(__DIR__ . "/filters.local/*.php"), glob(__DIR__ . "/filters/*.php"));
		$names = [];

		foreach ($filters as $file) {
			$filter_name = preg_replace("/\..*$/", "", basename($file));

			if (array_search($filter_name, $names) === FALSE) {
				if (!class_exists($filter_name)) {
					require_once $file;
				}

				array_push($names, $filter_name);

				$filter = new $filter_name();

				if (is_subclass_of($filter, "Af_InlineFilter")) {
					array_push($this->filters, $filter);
					array_push($names, $filter_name);
				}
			}
		}
	}

	function hook_prefs_tab($args) {
		if ($args != "prefFeeds") return;

		print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__('Hosting sites supported by af_inline')."\">";

		print "<p>" . __("The following hosting sites are currently supported:") . "</p>";

		$inlines = array();

		foreach ($this->filters as $f) {
			foreach ($f->supported() as $inline) {
				array_push($inlines, $inline);
			}
		}

		asort($inlines);

		print "<ul class=\"browseFeedList\" style=\"border-width : 1px\">";
		foreach ($inlines as $inline) {
			print "<li>$inline</li>";
		}
		print "</ul>";

		print "<p>".__('Drop any updated filters into <code>filters.local</code> in plugin directory.')."</p>";

		print "</div>";
	}

	function hook_article_filter($article) {
		if (strpos($article["link"], "reddit.com/r/") !== FALSE) {
			$doc = new DOMDocument();
			@$doc->loadHTML($article["content"]);
			$xpath = new DOMXPath($doc);

			if ($this->host->get($this, "enable_content_dupcheck")) {
				debug("dupecheck", $debug);
				$content_link = $xpath->query("(//a[text()='[link]'])")->item(0);

				if ($content_link) {
					_debug("Found link for dupecheck: ".$content_link->getAttribute("href"), $debug);

					$content_href = db_escape_string($content_link->getAttribute("href"));
					$entry_guid = db_escape_string($article["guid_hashed"]);
					$owner_uid = $article["owner_uid"];

					if (DB_TYPE == "pgsql") {
						$interval_qpart = "date_entered < NOW() - INTERVAL '1 day'";
					} else {
						$interval_qpart = "date_entered < DATE_SUB(NOW(), INTERVAL 1 DAY)";
					}

					// $interval_qpart AND

					$result = db_query("SELECT COUNT(id) AS cid
						FROM ttrss_entries, ttrss_user_entries WHERE
							ref_id = id AND
							guid != '$entry_guid' AND
							owner_uid = '$owner_uid' AND
							content LIKE '%href=\"$content_href\">[link]%'");
					if ($result) {
						$num_found = db_fetch_result($result, 0, "cid");
						file_put_contents("/tmp/debug.log","Debug dupecheck, found : $num_found for $content_href\n", FILE_APPEND);
						if ($num_found > 0) {
							file_put_contents("/tmp/debug.log","Force catchup\n", FILE_APPEND);
							_debug("marking as read (dupecheck)", $debug);
							$article["force_catchup"] = true;
						}
					}
				}
			}

			$found = false;
			$entry = $xpath->query("(//a[text()='[link]'])")->item(0);

			if (!$entry) { return; }
			_debug("processing href: " . $entry->getAttribute("href"), $debug);

			foreach ($this->filters as $f) {
				if ($f->process($article, $entry, $doc, $found, $this, $debug))
					break;
			}

//			$found = $this->inline_stuff($article, $doc, $xpath, $debug);

			$node = $doc->getElementsByTagName('body')->item(0);

//			$owner_uid = $article["owner_uid"];
//			$labels = $this->get_all_labels_filter_format($owner_uid);

			if ($found == "video") {
				array_push($article["tags"], "video");
			}

			if ($found) {
				array_push($article["tags"], "inlined");
			} else {
				array_push($article["tags"], "notInlined");
			}

			if ($found) {
				$table = $doc->getElementsByTagName('td')->item(0);
				if ($table)
					$table->parentNode->removeChild($table);
			}

			if ($node && $found) {
				$article["content"] = $doc->saveHTML($node);
			}

			return $article;
		}
	}

	function api_version() {
		return 2;
	}
}
