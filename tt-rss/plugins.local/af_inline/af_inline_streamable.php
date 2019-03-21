<?php
class Af_Inline_Streamable extends Af_InlineFilter {
	function supported() {
		return array("Streamable");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug) {
		if (preg_match("/streamable.com\/([A-Z0-9]+)$/i", $entry->getAttribute("href"), $matches)) {
                        _debug("Found streamable.com ". $entry->getAttribute("href"), $debug);
                        $req = "https://api.streamable.com/videos/".$matches[1];
                        $resp = json_decode(fetch_file_contents($req, false, $context), true);

                        $inline->handle_as_video($doc, $entry, $resp["files"]["mp4"]["url"], $resp["files"]["thumbnail_url"], $debug);
			array_push($article["tags"], "video");
                        $found = "true";
			return true;
		}
		return false;
	}
}
