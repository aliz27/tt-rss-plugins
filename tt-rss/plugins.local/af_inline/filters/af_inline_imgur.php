<?php
class Af_Inline_Imgur extends Af_InlineFilter {
	function supported() {
		return array("Imgur");
	}

	function process(&$article, &$entry, &$doc, &$found, &$inline, $debug) {
		if (strpos($entry->getAttribute("href"), "imgur.com/") !== FALSE) {
			$url = parse_url($entry->getAttribute("href"));
			$url = $url["scheme"]."://".$url["host"].$url["path"];

			if (preg_match("/imgur\.com\/([A-Z0-9]+)/i", $url, $matches)) {
				_debug("(imgur) link ".$url, $debug);

				$resp = $inline->imgurLookup($matches[1], "image", $debug);
				$url = $resp["data"]["link"];
			}

			if (preg_match("/imgur\.com\/r\/([A-Z0-9]+)\/([A-Z0-9]+)[\/]?$/i", $url, $matches)) {
				_debug("(imgur) subreddit link ".$url, $debug);

				$resp = $inline->imgurLookup("/r/".$matches[1]."/".$matches[2], "gallery", $debug);
				$url = $resp["data"]["link"];
			}

			if (preg_match("/\.(jpg|jpeg|png|webp)$/i", $url)) {
				_debug("(imgur) image ".$url, $debug);
				$inline->handle_as_image($doc, $entry, $url);
				array_push($article["tags"], "image");
				$found = true;
				return true;
			}

			if (preg_match("/([A-Z0-9]+)\.(gif|gifv|mp4|webm)$/i", $url, $matches) ) {
				_debug("(imgur) animated?: ".$url, $debug);

				$resp = $inline->imgurLookup($matches[1],"image",$debug);

				if ($resp["data"]["mp4"]) {
					_debug("(imgur) gif(v) mp4: ".$resp["data"]["mp4"], $debug);
					$inline->handle_as_video($doc, $entry, $resp["data"]["mp4"], $resp["data"]["link"], $debug);
					array_push($article["tags"], "video");
					$found = true;
					return true;
				} else {
					_debug("(imgur) gif(v) gif: ".$resp["data"]["link"], $debug);
					$inline->handle_as_image($doc, $entry, $resp["data"]["link"]);
					array_push($article["tags"], "video");
					$found = true;
					return true;
				}
			}

			if (preg_match("/imgur.com\/(a|gallery)\/([A-Z0-9]+)/i", $url, $matches)) {
				_debug("(imgur) album/gallery ".$url, $debug);

				if ($matches[1] == "a") { $lookup = "album"; } else { $lookup = "gallery"; }

				$resp = $inline->imgurLookup($matches[2],$lookup,$debug);

				if ($resp["data"]["images"]) { $images = $resp["data"]["images"]; } else { $images[0]["link"] = $resp["data"]["link"]; } 

				foreach ($images as $image) {
					_debug("(imgur) album/gallery link: ".$image["link"], $debug);

					if (preg_match("/([A-Z0-9]+)\.gif[v]?$/i", $image["link"], $matches) ) {
						_debug("(imgur) gif(v): ".$url, $debug);

						$resp_gif = $inline->imgurLookup($matches[1],"image",$debug);

						if ($resp_gif["data"]["mp4"]) {
							_debug("(imgur) gif(v) mp4: ".$resp_gif["data"]["mp4"], $debug);
							$inline->handle_as_video($doc, $entry, $resp_gif["data"]["mp4"], $resp_gif["data"]["link"], $debug);
							array_push($article["tags"], "video");
							$found = true;
							return true;
						} else {
							_debug("(imgur) gif(v) gif: ".$resp_gif["data"]["link"], $debug);
							$inline->handle_as_image($doc, $entry, $resp_gif["data"]["link"]);
							array_push($article["tags"], "video");
							$found = true;
							return true;
						}
					}

					if (preg_match("/\.(jpg|jpeg|png)$/i", $image["link"])) {
						_debug("(imgur) album/gallery image ".$image["link"], $debug);
						$inline->handle_as_image($doc, $entry, $image["link"]);
						array_push($article["tags"], "image");
						$found = true;
						return true;
					}
				}
			}
		}
		return false;
	}
}
