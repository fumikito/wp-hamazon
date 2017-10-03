<?php

namespace Hametuha\WpHamazon;


use Hametuha\WpHamazon\Pattern\AbstractService;
use Hametuha\WpHamazon\Pattern\Singleton;

/**
 * Bootstrap file
 *
 * @package Hametuha\WpHamazon
 */
class BootStrap extends Singleton {

	/**
	 * Singleton constructor.
	 */
	protected function __construct() {
		// Setup options API
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		// Register general setting
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		// Register assets
		add_action( 'init', [ $this, 'register_assets' ] );
		// Add Action links on plugin lists.
		add_filter( 'plugin_action_links', [ $this, 'plugin_page_link' ], 500, 2 );
		// Load public CSS
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );
		// Scan all services and enables
		$dir = __DIR__ . '/Service';
		$name_space_root = 'Hametuha\\WpHamazon\\Service\\';
		$has_service = false;
		foreach ( scandir( $dir ) as $file ) {
			if ( preg_match( '#^([^._].*)\.php$#u', $file, $matches ) ) {
				$class_name = $name_space_root . $matches[1];
				if ( class_exists( $class_name ) ) {
					/** @var AbstractService $class_name */
					$service = $class_name::get_instance();
					if ( $service->is_valid() ) {
						$has_service = true;
					}
				}
			}
		}
		if ( $has_service ) {
			add_action( 'media_buttons', [ $this, 'action_media_buttons' ] );
		}
	}

	/**
	 * Resister menu
	 */
	public function admin_menu() {
		// Add a new menu under Options:
		add_options_page(
			__( 'Hamazon Affiliate Setting', 'hamazon' ),
			__( 'Affiliate Setting', 'hamazon' ),
			'manage_options',
			'wp-hamazon',
			[ $this, 'options_page' ]
		);
		add_action( 'admin_enqueue_scripts', function( $page ) {
			if ( 'settings_page_wp-hamazon' == $page ) {
				wp_enqueue_style( 'hamazon-admin' );
			}
		} );
	}

	/**
	 * Render option screen
	 */
	public function options_page() {
		include hamazon_root_dir() . '/templates/admin.php';
	}

	/**
	 * Add setting fields
	 */
	public function register_settings() {
		add_settings_section(
			'hamazon_setting_general',
			__( 'General Setting', 'hamazon' ),
			function() {
			},
			'wp-hamazon'
		);

		// Post types
		add_settings_field(
			'hamazon_post_types',
			__( 'Available Post Types', 'hamazon' ),
			function() {
				$post_types = get_post_types( [ 'public' => true ], OBJECT );
				/**
				 * hamazon_valid_post_types
				 *
				 * @param array $post_types Post type objects array.
				 */
				$post_types = apply_filters( 'hamazon_valid_post_types', $post_types );
				foreach ( $post_types as $post_type ) {
					printf(
						'<label class="hamazon-inline-block"><input type="checkbox" name="hamazon_post_types[]" value="%s" %s/> %s</label>',
						esc_attr( $post_type->name ),
						checked( false !== array_search( $post_type->name, get_option( 'hamazon_post_types', [] ) ), true, false ),
						esc_html( $post_type->label )
					);
				}
			},
			'wp-hamazon',
			'hamazon_setting_general'
		);
		register_setting( 'wp-hamazon', 'hamazon_post_types' );

		// Load CSS
		add_settings_field(
			'hamazon_load_css',
			__( 'Load CSS', 'hamazon' ),
			function() {
				foreach ( [
					'1' => __( 'Load CSS', 'hamazon' ),
					''  => __( 'No CSS', 'hamazon' ),
				] as $value => $label ) {
					printf(
						'<label class="hamazon-inline-block"><input type="radio" name="hamazon_load_css" value="%s" %s/> %s</label>',
						esc_attr( $value ),
						checked( get_option( 'hamazon_load_css', 1 ), $value, false ),
						esc_html( $label )
					);
				}
				?>
				<p class="description">
					<?php esc_html_e( 'If you need original CSS, put "tmkm-amazon.css" in your theme folder. It will override default CSS.', 'hamazon' ) ?>
					<?php printf( esc_html__( 'If you choose "%s", anything will be loaded.', 'hamazon' ), esc_html__( 'No CSS', 'hamazon' ) ) ?>
				</p>
				<?php
			},
			'wp-hamazon',
			'hamazon_setting_general'
		);
		register_setting( 'wp-hamazon', 'hamazon_load_css' );
	}

	/**
	 * Register assets
	 */
	public function register_assets() {
		wp_register_style( 'hamazon-admin', hamazon_asset_url( '/css/hamazon-admin.css' ), [], hamazon_info( 'version' ) );
	}

	/**
	 * Add link on plugin list
	 *
	 * @param array $links
	 * @param string $file
	 *
	 * @return array
	 */
	public function plugin_page_link( $links, $file ) {
		if ( false !== strpos( $file, 'hamazon' ) ) {
			array_push( $links, '<a href="https://github.com/fumikito/wp-hamazon" target="_blank">Github</a>' );
			array_unshift( $links, sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'options-general.php?page=wp-hamazon' ),
				__( 'Setting', 'hamazon' )
			) );

		}

		return $links;
	}

	/**
	 * CSSを読み込む
	 * @global array $hamazon_settings
	 */
	public function enqueue_script() {
		global $hamazon_settings;
		if ( ! is_admin() && $hamazon_settings['load_css'] ) {
			if ( file_exists( get_stylesheet_directory() . '/tmkm-amazon.css' ) ) {
				$css_url = get_stylesheet_directory_uri() . '/tmkm-amazon.css';
			} else {
				$css_url = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/hamazon.css';
			}
			/**
			 * wp_hamazon_css_args
			 *
			 * Filter for WP Hamazon's CSS
			 *
			 * @param array $args handle, src, deps, version, media
			 * @return array
			 */
			$args = apply_filters( 'wp_hamazon_css_args', array(
				'handle'  => 'wp-hamazon',
				'src'     => $css_url,
				'deps'    => array(),
				'version' => $this->version,
				'media'   => 'all',
			) );
			if ( is_array( $args ) ) {
				wp_enqueue_style( $args['handle'], $args['src'], $args['deps'], $args['version'], $args['media'] );
			}
		}
	}

	/**
	 * Show media buttons
	 *
	 * @param string $editor_id
	 */
	public function action_media_buttons( $editor_id ) {
		if ( ! is_admin() ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		$post_types = get_option( 'hamazon_post_types', [ 'post' ] );
		if ( false === array_search( $screen->post_type, $post_types ) ) {
			return;
		}
		printf(
			'<button type="button" class="button hamazon-insert-button" data-editor="%s"><img width="26" height="26" class="hamazon-editor-button" src="%s" alt="%s" /></button>',
			esc_attr( $editor_id ),
			hamazon_asset_url( 'img/button-icon.png' ),
			esc_html__( 'Add Hamazon', 'hamazon' )
		);
	}

}
