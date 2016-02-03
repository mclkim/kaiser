<?php
class ServiceLocator {
	protected static $container;
	public static function setContainer(\Pimple $container) {
		static::$container = $container;
	}
	public static function get($id) {
		return static::$container [$id];
	}
}