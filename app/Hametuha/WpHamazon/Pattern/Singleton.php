<?php

namespace Hametuha\WpHamazon\Pattern;

/**
 * Singleton extension
 *
 * @package WpHamazon
 * @since 3.0.0
 */
abstract class Singleton {

	private static $instances = [];

	/**
	 * Singleton constructor.
	 */
	protected function __construct() {
	}

	/**
	 * Get instance
	 *
	 * @return static
	 */
	public static function get_instance() {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name();
		}
		return self::$instances[ $class_name ];
	}

}
