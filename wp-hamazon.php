<?php
/*
Plugin Name: wp-hamazon
Plugin URI: https://github.com/fumikito/WP-Hamazon
Description: ともかめさん作のtmkm-amazon後継プラグインです。ASIN を指定して Amazon から個別商品の情報を取出します。BOOKS, DVD, CD は詳細情報を取り出せます。
Author: Takahashi_Fumiki
Version: 2.1
Author URI: http://hametuha.co.jp
*/
if( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'wp-hamazon.php' ) {
	die();
}

/**
 * グローバル設定オプション
 * @var array
 */
global $hamazon_settings;

// メインコントローラーを読み込む
require_once dirname(__FILE__).'/includes/wp-hamazon-controller.php';
new WP_Hamazon_Controller('2.1');

// グローバル関数が記載されたファイルを読み込む
require_once dirname(__FILE__).'/functions.php';