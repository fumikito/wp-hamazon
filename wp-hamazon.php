<?php
/*
Plugin Name: wp-hamazon
Plugin URI: https://github.com/fumikito/WP-Hamazon
Description: ともかめさん作のtmkm-amazon後継プラグインです。ASIN を指定して Amazon から個別商品の情報を取出します。BOOKS, DVD, CD は詳細情報を取り出せます。
Author: Takahashi_Fumiki
Version: 1.0
Author URI: http://hametuha.co.jp
*/
if( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'wp-tmkm-amazon.php' ) {
	die();
}

/**
 * @var array Default Options
 */
$_hamazon_settings_default = array(
	'version' => '1.0',
	'associatesid' => '',
	'accessKey' => '',
	'secretKey' => '',
	'windowtarget' => 'self',
	'goodsimgsize' => 'small',
	'layout_type' => '0',
	'post_types' => array('post'),
	'load_css' => true
);

/**
 * Global Setting for tmkm Amazon
 * @var array
 */
$hamazon_settings = get_option('wp_tmkm_admin_options', $_hamazon_settings_default);
foreach($_hamazon_settings_default as $key => $val){
	if(!isset($hamazon_settings[$key])){
		$hamazon_settings[$key] = $val;
	}
}



if(!function_exists('tmkm_amazon_view')) {
	/**
	 * Echo HTML strign with asin code.
	 * @global WpTmkmAmazonView $wpTmkmAmazonView
	 * @param string $asin ASIN code
	 * @deprecated
	 * @return void
	 */
	function tmkm_amazon_view($asin) {
		global $hamazon_list;
		$hamazon_list->amazon_view($asin);
	}
}


require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'wp-hamazon-parser.php';
$wp_hamazon_parser = new WP_Hamazon($hamazon_settings['accessKey'], $hamazon_settings['secretKey'], $hamazon_settings['associatesid']);
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'wp-hamazon-list.php';
$hamazon_list =  new WP_Hamazon_List();
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'wp-hamazon-admin.php';
$hamazon_admin =  new WP_Hamazon_Admin();