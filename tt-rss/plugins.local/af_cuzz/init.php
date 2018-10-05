<?php
class Af_Cuzz extends Plugin {

	private $host;
	private $filters = array();

	function about() {
		return array(1.0,
			"Inlines content from cuzz.cazooka.se. Based on af_comics, af_redditimgur and af_unburn.",
			"aliz");
	}

	function flags() {
		return array("needs_curl" => true);
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

				if (is_subclass_of($filter, "Af_CuzzFilter")) {
					array_push($this->filters, $filter);
					array_push($names, $filter_name);
				}
			}
		}
	}

	function hook_prefs_tab($args) {
		if ($args != "prefFeeds") return;

		print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__('Hosting sites supported by af_cuzz')."\">";

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
		if (strpos($article["link"], "cuzz.cazooka.se/open.php") !== FALSE) {
			$page = fetch_file_contents([ "url" => $article["link"], "followlocation" => false ]);

			$pageDoc = new DOMDocument();

                        if (@$pageDoc->loadHTML($page)) {
                                $pageXPath = new DOMXPath($pageDoc);

                                $scripts = $pageXPath->query("//script");

                                foreach ($scripts as $script) {
                                        if (preg_match('/window.location = \'(.*)\'/', $script->nodeValue, $matches)) {
                                                _debug("Found new url: ".$matches[1], $debug);
                                                $article["link"] = $matches[1];
                                        }
                                }
                        }

			foreach ($this->filters as $f) {
				if ($f->process($article))
					break;
			}
		}

		return $article;
	}

	function api_version() {
		return 2;
	}
}
