<?php
class Af_Inline_VReddIt extends Af_InlineFilter {
	function supported() {
		return array("v.redd.it");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug) {
		if (strpos($entry->getAttribute("href"), "v.redd.it/") !== FALSE) {
			$req = $article["link"].".json";
                        $resp = json_decode($inline->apiCall($req), true);
			$inline->handle_as_video($doc, $entry, $resp[0]["data"]["children"][0]["data"]["media"]["reddit_video"]["fallback_url"], $resp[0]["data"]["children"][0]["data"]["preview"]["images"][0]["source"]["url"], $debug);
			array_push($article["tags"], "video");
                        $found = true;
			return true;
		}
		return false;
	}
}
