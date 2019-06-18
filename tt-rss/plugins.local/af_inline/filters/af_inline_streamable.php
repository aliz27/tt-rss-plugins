<?php
class Af_Inline_Streamable extends Af_InlineFilter {
	function supported() {
		return array("Streamable");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug) {
		if (preg_match("/streamable.com\/([A-Z0-9]+)$/i", $entry->getAttribute("href"), $matches)) {

			$vid_id = $matches[1];

			_debug("Handling as streamable: $vid_id", $debug);

			$iframe = $doc->createElement("iframe");
			$iframe->setAttribute("class", "streamable-embed");
			$iframe->setAttribute("width", "1280");
			$iframe->setAttribute("height", "720");
			$iframe->setAttribute("src", "https://www.youtube.com/embed/$vid_id");
			$iframe->setAttribute("allowfullscreen", "1");
			$iframe->setAttribute("frameborder", "0");
			$iframe->setAttribute("scrolling", "no");

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
