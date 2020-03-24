<?php
/**
 * Function test
 *
 * @package tssfw
 */

/**
 * Sample test case.
 */
class Hamazon_Basic_Test extends WP_UnitTestCase {

	/**
	 * A single example test
	 *
	 */
	function test_functions() {
		// Check domain exists.
		$result = hamazon_asin_link( '11111111111' );
		$this->assertWPError( $result );
		// Check asset function
		$url = hamazon_asset_url( '/css/hamazon.css' );
		$this->assertEquals( 1, preg_match( '#^https?://#', $url ) );
		// Check image
		$src = hamazon_no_image();
		$this->assertEquals( 1, preg_match( '#^https?://#', $src ) );
	}

	/**
	 * Load template
	 */
	function test_template() {
		add_filter( 'hamazon_template_path', function( $path ) {
			return dirname( __DIR__ ) . '/tests/template.php';
		} );
		$template_result = hamazon_template( 'test' );
		$this->assertEquals( '<p>Test</p>', trim( $template_result ) );
	}

}
