<?php
class Af_Inline_OgImage extends Af_InlineFilter {
	function supported() {
		return array("og:image");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug) {
		if (preg_match("/imgflip.com\//i", $entry->getAttribute("href"))) {
			_debug("Looking for og:image", $debug);
			$page = fetch_file_contents($entry->getAttribute("href"));

                        $pageDoc = new DOMDocument();

                        if (@$pageDoc->loadHTML($page)) {
                                $pageXPath = new DOMXPath($pageDoc);
                                $images = $pageXPath->query("//meta[@property='og:image']");

                                foreach ($images as $image) {
					_debug("Found og:image: ".$image->getAttribute("content"), $debug);
					$inline->handle_as_image($doc, $entry, $image->getAttribute("content"));
					array_push($article["tags"], "image");
					$found = true;
					return true;
                                }
                        }

		}

		return false;
	}
}
