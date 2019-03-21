<?php
class Af_Inline_Gfycat extends Af_InlineFilter {
	function supported() {
		return array("Gfycat");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug = false) {
		if (preg_match("/gfycat.com\/([A-Z0-9]+)$/i", $entry->getAttribute("href"), $matches)) {
			_debug("Found gfycat.com ". $entry->getAttribute("href"), $debug);
//			$req = "https://gfycat.com/cajax/get/".$matches[1];
			$resp = $inline->gfycatLookup($matches[1], $debug);
			if ($resp["gfyItem"]) {
				$inline->handle_as_video($doc, $entry, $resp["gfyItem"]["mp4Url"], $resp["gfyItem"]["posterUrl"], $debug);
				array_push($article["tags"], "video");
				$found = true;
				return true;
			}
		}
		return false;
	}
}
