<?php
abstract class Af_InlineFilter {
	public abstract function supported();
	public abstract function process(&$article, &$entry, &$doc, &$found, &$inline, $debug);
}

