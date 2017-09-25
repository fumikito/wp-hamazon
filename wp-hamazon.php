<?php
/*
Plugin Name: wp-hamazon
Plugin URI: https://github.com/fumikito/WP-Hamazon
Description: ともかめさん作のtmkm-amazon後継プラグインです。投稿にアフィリエイトの商品リンクを出力できます。対応しているアフィリエイトサービスはいまのところ Amazon 楽天 リンクシェア PHG(iTunesアフィリエイト) DMMです。
Author: Takahashi_Fumiki
Version: 2.4.0
Author URI: http://takahashifumiki.com
*/

// Do not load directly
defined( 'ABSPATH' ) or die();

/**
 * Global setting option
 * @var array
 */
global $hamazon_settings;

// Register intialization hook
add_action( 'plugins_loaded', '_wp_hamazon_init' );

/**
 * Initialization
 *
 * @ignore
 */
function _wp_hamazon_init() {
	// Load main controller
	require_once dirname( __FILE__ ) . '/includes/wp-hamazon-controller.php';

	// Make Bootstrap
	WP_Hamazon_Controller::get_instance( '2.4.0' );

	// Load global functions
	require_once dirname( __FILE__ ) . '/functions.php';
}

