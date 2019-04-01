<?php
class Af_Cuzz_Youtube extends Af_CuzzFilter {
	function supported() {
		return array("Vimeo");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug) {
		$matches = array();
		if (preg_match("/vimeo\.com\/([\d]+)/", $entry->getAttribute("href"), $matches) {

			$vid_id = $matches[1];

			_debug("Handling as vimeo: $vid_id", $debug);

			$article["content"] = "<iframe width=\"640\" height=\"360\" src=\"https://player.viemo.com/video/$vid_id\" allowfullscreen frameborder=\"0\"></iframe>";

			return true;
		}
		return false;
	}
}
