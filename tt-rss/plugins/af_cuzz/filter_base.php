<?php
abstract class Af_CuzzFilter {
	public abstract function supported();
	public abstract function process(&$article);
}

