<?php
class Af_Inline_Image extends Af_InlineFilter {
	function supported() {
		return array("images");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug) {
		if (preg_match("/\.(jpg|jpeg|gif|png)$/i", $entry->getAttribute("href")) ||
			preg_match("/i.reddituploads.com/i", $entry->getAttribute("href")) ||
			preg_match("/i.redditmedia.com/i", $entry->getAttribute("href"))) {
			_debug("Handling as a image", $debug);
			$inline->handle_as_image($doc, $entry, $entry->getAttribute("href"));
			array_push($article["tags"], "image");
			$found = true;
			return true;
		}

		return false;
	}
}
