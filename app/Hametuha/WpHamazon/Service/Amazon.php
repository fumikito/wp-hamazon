<?php

namespace Hametuha\WpHamazon\Service;


use Hametuha\WpHamazon\Constants\AmazonLocales;
use Hametuha\WpHamazon\Pattern\AbstractService;
use Hametuha\WpHamazon\Constants\AmazonConstants;

/**
 * Amazon class
 * @package hamazon
 */
class Amazon extends AbstractService {

	public $name = 'amazon';

	public $title = 'Amazon';

	/**
	 * Get default options
	 *
	 * @return array
	 */
	protected function get_option_names() {
		return [
			[
				'key' => 'associatesid',
				'label' => __( 'Associate ID', 'hamazon' ),
			],
			[
				'key'   => 'accessKey',
				'label' => __( 'Access Key', 'hamazon' ),
			],
			[
				'key'   => 'secretKey',
				'label' => __( 'Secret Key', 'hamazon' ),
			],
			[
				'key' => 'locale',
				'label' => __( 'Locale', 'hamazon' ),
				'default' => 'JP',
				'type' => 'radio',
				'options' => AmazonLocales::get_locale_labels(),
			],
		];
	}

	/**
	 * Detect if this service is valid
	 *
	 * @return bool
	 */
	public function is_valid() {
		return $this->get_option( 'associatesid' ) && $this->get_option( 'accessKey' ) && $this->get_option( 'secretKey' );
	}


    /**
     * Filter data passed to react.
     *
     * @param $data
     * @return array|\stdClass
     */
    protected function filter_data( $data ) {
        $constants = AmazonConstants::get_search_index();
        $orders    = AmazonLocales::get_sort_orders();
        return [
            'options' => array_map( function( $key, $value ) {
                return [
                    'key' => $key,
                    'label' => $value,
                ];
            }, array_keys( $constants ), array_values( $constants ) ),
            'orders' => array_map( function( $key, $value ) {
            	return [
            		'value' => $key,
		            'label' => $value,
	            ];
            }, array_keys( $orders ), array_values( $orders ) ),
        ];
    }

	/**
	 * Get rest arguments.
	 *
	 * @return array
	 */
	public function get_rest_arguments() {
		return [
			'query' => [
				'required' => true,
				'description' => __( 'Search keyword.', 'hamazon' ),
				'validate_callback' => function( $var ) {
					return ! empty( $var );
				},
			],
			'page' => [
				'default' => 1,
				'description' => __( 'Specified page.', 'hamazon' ),
				'validate_callback' => function( $var ) {
					return is_numeric( $var ) && ( 0 < $var );
				},
			],
			'index' => [
				'default' => 'All',
				'required' => true,
				'description' => __( 'Search category.', 'hamazon' ),
				'validation_callback' => function( $var ) {
					$indexed = AmazonConstants::get_search_index();
					return isset( $indexed[ $var ] );
				}
			],
			'order' => [
				'default'  => 'Relevance',
				'required' => true,
				'description' => 'Order of search results',
				'validation_callback' => function( $var ) {
					$orders = AmazonLocales::get_sort_orders();
					return isset( $orders[ $var ] );
				},
			]
		];
	}

	/**
	 * Handle Amazon search request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_rest_request( \WP_REST_Request $request ) {
		$response = AmazonConstants::search_with( $request['query'], $request['page'], $request['index'], $request['order'] );
		if ( is_wp_error( $response ) ) {
			return $response;
		} else {
			return new \WP_REST_Response( $response );
		}
	}

	/**
	 * Get setting
	 *
	 * @return array
	 */
	public function short_code_setting() {
		return [
			'tmkm-amazon' => [
				[
					'label' => 'ASIN',
					'type'  => 'text',
					'attr'  => 'asin',
				],
			],
		];
	}

	protected function get_service_description() {
		return sprintf( __( 'Display link via Amazon Advertising API. You can get credentials from <a href="%s" target="_blank" rel="noopener noreferrer">Associate Central</a>.', 'hamazon' ), 'https://affiliate.amazon.co.jp/assoc_credentials/home' );
	}


	/**
	 * Get short code
	 *
	 * @param string $short_code
	 * @param array $attributes
	 * @param string $content
	 * @return string
	 */
	public function short_code_callback( $short_code, array $attributes = [], $content = '' ){
		switch ( $short_code ) {
			case 'tmkm-amazon':
				return AmazonConstants::format_amazon( $content, $attributes );
				break;
			default:
				return '';
				break;
		}
	}

}
