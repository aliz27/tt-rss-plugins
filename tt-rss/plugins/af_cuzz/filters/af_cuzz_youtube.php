<?php
class Af_Cuzz_Youtube extends Af_CuzzFilter {

	function supported() {
		return array("Youtube");
	}

	function process(&$article) {
		$matches = array();
		if (preg_match("/youtube\.com\/v\/([\w-]+)/", $article["link"], $matches) ||
			preg_match("/youtube\.com\/.*?[\&\?]v=([\w-]+)/", $article["link"], $matches) ||
			preg_match("/youtube\.com\/watch\?v=([\w-]+)/", $article["link"], $matches) ||
			preg_match("/\/\/youtu.be\/([\w-]+)/", $article["link"], $matches)) {
				$vid_id = $matches[1];
				_debug("Handling as youtube: $vid_id", $debug);

				$article["content"] = "<iframe class=\"youtube-player\"
                	                type=\"text/html\" width=\"640\" height=\"385\"
                        	        src=\"https://www.youtube.com/embed/$vid_id\"
                                	allowfullscreen frameborder=\"0\"></iframe>";

				return true;
		}
		return false;
	}
}
