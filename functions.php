<?php

if(!function_exists('tmkm_amazon_view')) {
	/**
	 * Echo HTML strign with asin code.
	 * 
	 * @global WpTmkmAmazonView $wpTmkmAmazonView
	 * @param string $asin ASIN code
	 * @deprecated
	 * @return void
	 */
	function tmkm_amazon_view($asin) {
		global $shortcode_tags;
		if(isset($shortcode_tags['tmkm-amazon']) && is_callable($shortcode_tags['tmkm-amazon'])){
			echo call_user_func($callback, array(), $asin);
		}
	}
}