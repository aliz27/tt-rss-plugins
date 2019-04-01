<?php
class Af_Inline_Youtube extends Af_InlineFilter {
	function supported() {
		return array("Vimeo");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug) {
		$matches = array();
		if (preg_match("/vimeo\.com\/([\d]+)/", $entry->getAttribute("href"), $matches) {

			$vid_id = $matches[1];

			_debug("Handling as vimeo: $vid_id", $debug);

			$iframe = $doc->createElement("iframe");
			$iframe->setAttribute("width", "640");
			$iframe->setAttribute("height", "360");
			$iframe->setAttribute("src", "https://player.viemo.com/video/$vid_id");
			$iframe->setAttribute("frameborder", "0");

			$br = $doc->createElement('br');
			$entry->parentNode->insertBefore($iframe, $entry);
			$entry->parentNode->insertBefore($br, $entry);
			array_push($article["tags"], "video");
			$found = true;
			return true;
		}
		return false;
	}
}
