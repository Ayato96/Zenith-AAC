<?php namespace Zenith\Setting;

use Illuminate\Filesystem\Filesystem;

abstract class Parser implements ParserInterface {
	public static function read($path) {
		$fs = new Filesystem();
		if ($fs->exists($path))
			return $fs->get($path);
		return null;
	}

	public static function zenithify($content) {
		$parsed = static::parse($content);
		$sanitized = static::sanitize($parsed);
		static::push($sanitized);
	}
}
