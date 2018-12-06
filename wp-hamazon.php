<?php
/*
 * Plugin Name: Hamazon
 * Plugin URI: https://wordpress.org/plugins/wp-hamazon/
 * Description: An affiliate plugin specialized for amazon. Forked from tmkm-amazon.
 * Author: Gianism.info
 * Author URI: https://gianism.info
 * Version: 4.0.2
 * PHP Version: 5.4.0
 * Text Domain: hamazon
 * Domain Path: /language/
 * License: GPL3 or Later
*/

// Do not load directly
defined( 'ABSPATH' ) or die();

// Register initialization hook
add_action( 'plugins_loaded', 'hamazon_init' );

/**
 * Get plugin information
 *
 * @param string $key
 *
 * @return null
 */
function hamazon_info( $key ) {
	static $version = null;
	if ( is_null( $version ) ) {
		$version = get_file_data( __FILE__, array(
			'version' => 'Version',
			'php'     => 'PHP Version',
		) );
	}
	return isset( $version[$key] ) ? $version[ $key ] : null;
}

/**
 * Initialization
 *
 * @package hamazon
 * @since 2.0.0
 */
function hamazon_init() {
	// Load translations.
	load_plugin_textdomain( 'hamazon', false, basename( dirname( __FILE__ ) ) . '/language' );
	// Check PHP version
	if ( version_compare( phpversion(), hamazon_info( 'php' ), '<' ) ) {
		add_action( 'admin_notices', 'hamazon_warnings' );
		return;
	}
	// Load global functions
	require_once dirname( __FILE__ ) . '/functions.php';
	// Bootstrap
	require_once __DIR__ . '/vendor/autoload.php';
	call_user_func( array( 'Hametuha\\WpHamazon\\BootStrap', 'get_instance' ) );

}

/**
 * Notice for PHP version
 *
 * @internal
 * @package hamazon
 * @since 3.0.0
 */
function hamazon_warnings() {
	printf(
		'<div class="error"><p>%s</p></div>',
		sprintf(
			esc_html__( 'Hamazon requires PHP %1$s and over, but your version is %2$s.', 'hamazon' ),
			hamazon_info( 'php' ),
			phpversion()
		)
	);
}
