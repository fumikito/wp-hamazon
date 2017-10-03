<?php

namespace Hametuha\WpHamazon\Pattern;

/**
 * Abstract class for Service
 *
 * @package hamazon
 */
abstract class AbstractService extends Singleton {

	/**
	 * Service name in alphanumeric format
	 *
	 * @var string
	 */
	public $name = 'service';

	/**
	 * Used for title
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * メディアボタンアイコンのファイル名
	 *
	 * この名称のファイルがプラグインディレクトリ内
	 * /assets/img内に保存されていなければなりません。
	 *
	 * @var string
	 */
	protected $icon = 'yen.png';

	/**
	 * ショートコードストリング
	 *
	 * ここに登録されたショートコードはshortcode_[ショートコード名]というメソッドが
	 * 存在する場合、登録されます。
	 * 継承するクラスは必ず同名のメソッドを持っていなければなりません。
	 *
	 * @var array ショートコード名からなる配列
	 */
	protected $short_codes = [];


	/**
	 * Returns array
	 *
	 * @return array format is [ 'key' => 'foo', 'default' => 'var', 'label' => 'hoge', 'description' => '', 'type' => 'text', 'options' => [] ]
	 */
	abstract protected function get_option_names();

	/**
	 * Get description
	 *
	 * @return string;
	 */
	protected function get_service_description() {
		return '';
	}

	/**
	 * Constructor
	 */
	protected function __construct(){
		// Register Option screen
		add_action( 'admin_init', [ $this, 'register_options' ], 11 );
		// Register REST API
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	/**
	 * Register options
	 */
	public function register_options() {
		$section = "hamazon_setting_{$this->name}";
		add_settings_section(
			$section,
			$this->title,
			function() {
				if ( $desc = $this->get_service_description() ) {
					printf( '<p class="descirptoin">%s</p>', wp_kses_post( $desc ) );
				}
			},
			'wp-hamazon'
		);
		foreach ( $this->get_option_names() as $option ) {
			$option = wp_parse_args( $option, [
				'type'    => 'text',
				'label'   => '',
				'default' => '',
				'description' => '',
				'options' => [],
			] );
			$option_name = "hamazon_{$option['key']}";
			add_settings_field(
				$option_name,
				isset( $option['label'] ) ? $option['label'] : ucfirst( $option['label'] ),
				function() use ( $option, $option_name ) {
					$current_value = get_option( $option_name, $option['default'] );
					switch ( $option['type']) {
						case 'radio':
							foreach ( $option['options'] as $label => $value ) {
								printf(
									'<label class="hamazon-inline-block"><input type="radio" name="%s" value="%s" %s/> %s</label>',
									esc_attr( $option_name ),
									esc_attr( $value ),
									checked( $current_value, $value, false ),
									esc_html( $label )
								);
							}
							break;
						default:
							printf(
								'<input class="regular-text" type="%1$s" name="%2$s" id="%2$s" value="%3$s" />',
								esc_attr( $option['type'] ),
								esc_attr( $option_name ),
								esc_attr( $current_value )
							);
							break;
					}
					if ( $option['description'] ) {
						printf( '<p class="description">%s</p>', wp_kses_post( $option['descriptoin'] ) );
					}
				},
				'wp-hamazon',
				$section
			);
			register_setting( 'wp-hamazon', $option_name );
		}
	}



	/**
	 * ショートコード名をメソッドにして返す
	 *
	 * @param string $code
	 * @return string
	 */
	protected function convert_code_to_method($code){
		return 'shortcode_'.str_replace('-', '_', strtolower($code));
	}



	/**
	 * Get specified option value
	 *
	 * @global array $hamazon_settings
	 * @param string $key
	 * @return string
	 */
	public function get_option( $key ) {
		foreach ( $this->get_option_names() as $option ) {
			if ( $key !== $option['key'] ) {
				continue;
			}
			$key = "hamazon_{$option['key']}";
			$default = isset( $option['default'] ) ? $option['default'] : '';
			return get_option( $key, $default );
		}
	}



	/**
	 * 管理画面で実行される関数
	 */
	public function admin_init(){
		add_action('media_buttons', array($this, 'media_buttons'), 1000);
	}



	/**
	 * メディアボタン出力のショートハンド
	 *
	 * @param string $editor_id
	 */
	public function media_buttons($editor_id){
		global $hamazon_settings;
		$cap = ($this->is_valid() && (false !== array_search(get_post_type(), $hamazon_settings['post_types'])));
		if(apply_filters('wp_hamazon_show_media_button', $cap, $this->name, $editor_id)){
			printf('<a href="%1$s" id="%2$s" class="thickbox add-hamazon button" title="%3$s"><img src="%4$s" alt="%3$s" /></a>',
				esc_url(plugin_dir_url(dirname(__FILE__)).'endpoint/'.$this->name.'.php?TB_iframe=true&amp;width=400&amp;height=350'),
				'add_'.$this->name,
				esc_attr($this->title),
				esc_url( plugin_dir_url( dirname(__FILE__) ).'assets/img/'.$this->icon ));
		}
	}

	/**
	 * Returns error message
	 *
	 * @param string $message
	 * @return string
	 */
	protected function error_message($message = ''){
		if(empty($message)){
			$message = '商品情報を取得できませんでした';
		}
		return sprintf('<p class="hamazon-message message error">%s</p>', $message);
	}

	/**
	 * Register REST endpoint
	 */
	public function rest_api_init() {
		if ( $this->is_valid() ) {
			register_rest_route( 'hamazon/v3/', $this->name, [
				'method' => 'GET',
				'callback' => [ $this, 'handle_rest_request' ],
				'args' => $this->get_rest_arguments(),
				'permission_callback' => null,
			] );
		}
	}

	/**
	 * Get rest arguments.
	 *
	 * @return array
	 */
	abstract public function get_rest_arguments();

	/**
	 * Request Handler
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	abstract public function handle_rest_request( \WP_REST_Request $request );


	/**
	 * Returns is this service is valid
	 *
	 * @return bool
	 */
	abstract public function is_valid();

	/**
	 * Register short codes
	 *
	 * @return void
	 */
//	abstract public function register_short_code();

	/**
	 * @return mixed
	 */
	//public function show_form();

	/**
	 * @return mixed
	 */
	//public function show_results();

}