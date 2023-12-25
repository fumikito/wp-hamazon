<?php

namespace Hametuha\WpHamazon;


use Hametuha\WpHamazon\Pattern\Singleton;

/**
 * Add Block Editor.
 *
 * @package hamazon
 * @property BootStrap $hamazon
 */
class BlockEditor extends Singleton {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	/**
	 * Register blocks.
	 */
	public function register_blocks() {
		// If no service available, skip.
		if ( ! $this->hamazon->service_instances ) {
			return;
		}

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		// Register variables.
		wp_localize_script( 'hamazon-block', 'HamazonBlock', [
			'title' => __( 'Affiliate', 'hamazon' ),
			'services'        => $this->hamazon->service_data_for_script(),
			'attributes'      => $this->get_attributes(),
			'shortCodes'      => $this->get_types(),
		] );
		// Block
		register_block_type( 'hamazon/single', [
			'editor_style'  => 'hamazon-block',
			'editor_script' => 'hamazon-block',
			'attributes'      => $this->get_attributes(),
			'render_callback' => function( $attributes, $content = '' ) {
				$attributes = wp_parse_args( $attributes, [
					'type' => '',
				] );
				try {
					if ( empty( $attributes['type'] ) ) {
						throw new \Exception( __( 'No data is set.', 'hamazon' ) );
					} elseif ( ! in_array( $attributes['type'], $this->get_types() ) ) {
						throw new \Exception( __( 'No affiliate service available.', 'hamazon' ) );
					}
					if ( 'amazon' === $attributes['type'] ) {
						$attributes['asin'] = $attributes['id'];
					}
					$contents = $content ? strip_tags( $content ) : implode( ' ', (array) $attributes['content'] );
					$instance = $this->hamazon->service_instances[ $attributes['type'] ];
					$key      = '';
					foreach ( $this->get_types() as $short_code => $type ) {
						if ( $type === $attributes['type'] ) {
							$key = $short_code;
						}
					}
					if ( ! $key ) {
						throw new \Exception( __( 'Failed to get proper contents.', 'hamazon' ) );
					}
					$result =  $instance->short_code_callback( $key, $attributes, $contents );
					if ( is_wp_error( $result ) ) {
						throw new \Exception( $result->get_error_message() );
					}
					return $result;
				} catch ( \Exception $e ) {
					return sprintf(
						'<p class="hamazon-block-no-content"><!-- %s -->%s</p>',
						esc_html( $e->getMessage() ),
						esc_html__( 'Sorry, but this link is temporary unavailable. Please try again later.', 'hamazon' )
					);
				}
			},
		] );
	}

	/**
	 * Get short code
	 *
	 * @param array $attributes
	 * @param string $content
	 * @return string
	 */
	public function render_callback( $attributes = [], $content = '' ) {
		return 'string';
	}

	/**
	 * Get available short codes.
	 *
	 * @return array
	 */
	protected function get_types() {
		$types = [];
		foreach ( $this->hamazon->service_instances as $service ) {
			foreach ( $service->short_code_setting() as $short_code => $setting ) {
				$types[ $short_code ] = $service->name;
			}
		}
		return $types;
	}

	/**
	 * Get attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		$attributes = [
			'type' => [
				'type'    => 'string',
				'default' => '',
			],
			'content' => [
				'type'   => 'array',
			],
		];
		foreach ( $this->hamazon->service_instances as $name => $service ) {
			foreach ( $service->short_code_setting() as $short_code => $settings ) {
				foreach ( $settings as $setting ) {
					$key = $setting['attr'];
					if ( 'asin' === $key ) {
						$key = 'id';
					}
					if ( ! isset( $attributes[ $key ] ) ) {
						$attributes[ $key ] = [
							'type'    => 'string',
							'default' => '',
						];
					}
				}
			}
		}
		return $attributes;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'hamazon':
				return BootStrap::get_instance();
				break;
			default:
				return null;
				break;
		}

	}
}
