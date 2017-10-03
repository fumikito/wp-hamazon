<?php

namespace Hametuha\WpHamazon\Pattern;

/**
 * hamazon
 *
 * @package hamazon
 */
class WpApi extends Singleton {

	protected $service_name = '';

	/**
	 * Get route endpoint
	 *
	 * @param string $service_name
	 */
	public function register_api( $service_name ) {
		$this->service_name = $service_name;
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	/**
	 * Register rest endpoints
	 *
	 * @throws \Exception If no handler is set, throws error.
	 */
	public function rest_api_init() {
		$register = [];
		foreach ( [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTION' ] as $method ) {
			$method_name = strtolower( "handle_{$method}" );
			if ( ! method_exists( $this, $method_name ) ) {
				continue;
			}
			$register[] = [
				'methods' => $method,
				'callback' => [ $this, $method_name ],
				'args'     => $this->get_arguments( $method ),
				'permission_callback' => [ $this, 'permission_callback' ],
			];
		}
		if ( $register ) {
			register_rest_route( 'hamazon/v3', $this->service_name, $register );
		}
	}

	/**
	 * Parse permission
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		return [];
	}
}
