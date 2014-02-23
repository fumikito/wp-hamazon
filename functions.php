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
 * @param string $asin ASIN code
 */
function hamazon_asin_link($asin){
    $instance = WP_Hamazon_Controller::get_instance();
    if( $instance->amazon ){
        echo $instance->amazon->format_amazon($asin);
    }
}
