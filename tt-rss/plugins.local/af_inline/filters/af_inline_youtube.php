<?php
class Af_Inline_VReddIt extends Af_InlineFilter {
	function supported() {
		return array("Youtube");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug) {
		$matches = array();
		if (preg_match("/youtube\.com\/v\/([\w-]+)/", $entry->getAttribute("href"), $matches) ||
			preg_match("/youtube\.com\/.*?[\&\?]v=([\w-]+)/", $entry->getAttribute("href"), $matches) ||
			preg_match("/youtube\.com\/watch\?v=([\w-]+)/", $entry->getAttribute("href"), $matches) ||
			preg_match("/\/\/youtu.be\/([\w-]+)/", $entry->getAttribute("href"), $matches)) {

			$vid_id = $matches[1];

			_debug("Handling as youtube: $vid_id", $debug);

			$iframe = $doc->createElement("iframe");
			$iframe->setAttribute("class", "youtube-player");
			$iframe->setAttribute("type", "text/html");
			$iframe->setAttribute("width", "640");
			$iframe->setAttribute("height", "385");
			$iframe->setAttribute("src", "https://www.youtube.com/embed/$vid_id");
			$iframe->setAttribute("allowfullscreen", "1");
			$iframe->setAttribute("frameborder", "0");

			$br = $doc->createElement('br');
			$entry->parentNode->insertBefore($iframe, $entry);
			$entry->parentNode->insertBefore($br, $entry);

			$found = true;
			return true;
		}
		return false;
	}
}
