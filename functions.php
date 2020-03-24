<?php

if ( !function_exists( 'tmkm_amazon_view' ) ) {
	/**
	 * Echo Product link HTML with asin code.
	 *
	 * This function exists only for the backward compatibility.
	 *
	 * @param string $asin ASIN code
	 * @deprecated Since 2.0
	 */
	function tmkm_amazon_view( $asin ) {
		hamazon_asin_link( $asin );
	}
}

/**
 * Echo Product link HTML with asin code.
 *
 * @package hamazon
 * @since 2.3.1
 * @param string $asin ASIN code.
 * @param string $content Default empty string.
 * @return string
 */
function hamazon_asin_link( $asin, $content = '' ) {
	return \Hametuha\WpHamazon\Constants\AmazonConstants::format_amazon( $content, [ 'asin' => $asin ] );
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
function hamazon_no_image() {
	$default = hamazon_asset_url( 'img/hamazon-no-image.svg' );
	return apply_filters( 'hamazon_default_no_image', $default );
}

/**
 * Load template.
 *
 * @since 5.0.0
 * @param string $template Template name.
 * @param string $suffix   Suffix same as {get_template_part}
 * @param array  $attrs    Attributes.
 * @param bool   $echo     Default false. If set to true, echo strings
 * @return string
 */
function hamazon_template( $template, $suffix = '', $attrs = [], $echo = false ) {
	$dirs = [ __DIR__ . '/template-parts' ];
	$dirs[] = get_template_directory() . '/template-parts/hamazon';
	if ( get_template_directory() !== get_stylesheet_directory() ) {
		$dirs[] = get_stylesheet_directory() . '/template-parts/hamazon';
	}
	$files = [ $template ];
	if ( $suffix ) {
		$files[] = $template . '-' . $suffix;
	}
	$files = array_map( function( $file ) {
		return ltrim( $file, '/' ) . '.php';
	}, $files);
	$path_candidate = '';
	foreach ( $files as $file ) {
		foreach ( $dirs as $dir ) {
			$path = $dir . '/' . $file;
			if ( file_exists( $path ) ) {
				$path_candidate = $path;
			}
		}
	}
	$filtered = apply_filters( 'hamazon_template_path', $path_candidate, $template, $suffix, $attrs, $echo );
	if ( ( $filtered !== $path_candidate ) && file_exists( $filtered ) ) {
		$path_candidate = $filtered;
	}
	$lambda = function() use ( $path_candidate, $attrs ) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
		extract( $attrs );
		include $path_candidate;
	};
	ob_start();
	$lambda();
	$content = ob_get_contents();
	if ( $echo ) {
		ob_end_flush();
	} else {
		ob_end_clean();
	}
	return implode( "", array_filter( array_map( 'trim', explode( "\n", $content ) ) ) );
}
