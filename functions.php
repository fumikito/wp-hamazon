<?php

if(!function_exists('tmkm_amazon_view')) {
	/**
     * Echo Product link HTML with asin code.
     *
     * This function exists only for the backward compatibility.
	 * 
	 * @param string $asin ASIN code
	 * @deprecated Since 2.0
	 */
	function tmkm_amazon_view($asin) {
        hamazon_asin_link($asin);
	}


}

/**
 * Echo Product link HTML with asin code.
 *
 * @package hamazon
 * @param string $asin ASIN code
 */
function hamazon_asin_link($asin){
    $instance = WP_Hamazon_Controller::get_instance();
    if( $instance->amazon ){
        echo $instance->amazon->format_amazon($asin);
    }
}

/**
 * Get root directory
 *
 * @return string
 */
function hamazon_root_dir() {
	return __DIR__;
}

/**
 * Get asset URL
 *
 * @param string $path
 *
 * @return string
 */
function hamazon_asset_url( $path ) {
	return plugin_dir_url( __FILE__ ) . 'assets/' . ltrim( $path, '/' );
}

/**
 * Default no image URL.
 *
 * @return string
 */
function hamazon_no_image(){
	$default = hamazon_asset_url( 'img/hamazon-no-image.svg' );
	return apply_filters( 'hamazon_default_no_image', $default );
}