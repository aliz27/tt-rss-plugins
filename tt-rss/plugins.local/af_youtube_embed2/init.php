<?php
class Af_Youtube_Embed2 extends Plugin {
	private $host;

	function about() {
		return array(2.0,
			"Embed videos in Youtube RSS feeds",
			"fox (aliz)");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_RENDER_ENCLOSURE, $this);
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	function hook_render_enclosure($entry, $hide_images) {

		$matches = array();

		if (preg_match("/\/\/www\.youtube\.com\/v\/([\w-]+)/", $entry["url"], $matches) ||
			preg_match("/\/\/www\.youtube\.com\/watch?v=([\w-]+)/", $entry["url"], $matches) ||
			preg_match("/\/\/youtu.be\/([\w-]+)/", $entry["url"], $matches)) {

			$vid_id = $matches[1];

			return "<iframe class=\"youtube-player\"
				type=\"text/html\" width=\"640\" height=\"385\"
				src=\"https://www.youtube.com/embed/$vid_id\"
				allowfullscreen frameborder=\"0\"></iframe><br />".nl2br($entry["title"]);

		}
	}

	function api_version() {
		return 2;
	}

}
