<?php

/**
 * 商品出力・検索を抽象化するコントローラー
 * 
 * @since 2.2
 */
class WP_Hamazon_Controller{
	
	/**
	 * Page slug on admin panel
	 * @var string
	 */
	public $slug = 'wp-hamazon';
	
	
	/**
	 * Message Container
	 * @var array
	 */
	private $message = array('error' => array(), 'normal' => array());
	
	
	
	/**
	 * バージョン番号
	 * 
	 * @var string
	 */
	public $version = '';
	
	
	/**
	 * オプションの初期値
	 * @var array
	 */
	private $default_options = array(
		'associatesid' => '',
		'accessKey' => '',
		'secretKey' => '',
		'rakuten_app_id' => '',
		'rakuten_affiliate_id' => '',
		'linkshare_token' => '',
		'post_types' => array('post'),
		'load_css' => true
	);
	
	
	/**
	 * 実装されているサービスの名称リスト
	 * @var string
	 */
	private $services = array('amazon', 'linkshare');
	
	
	
	/**
	 * @var WP_Hamazon_Service_Amazon
	 */
	public $amazon = null;
	
	
	
	public $rakuten = null;
	
	
	
	/**
	 * @var WP_Hamazon_Service_Linkshare
	 */
	public $linkshare = null;
	
	
	
	/**
	 * コンストラクタ
	 * @global array $hamazon_settings
	 * @param string $version
	 */
	public function __construct($version) {
		global $hamazon_settings;
		
		// バージョン番号を設定
		$this->version = $version;
		
		// オプションの初期設定を行う
		$hamazon_settings = wp_parse_args(get_option('wp_tmkm_admin_options', array()), $this->default_options);
		$hamazon_settings['version'] = $this->version;
		
		// サービスを初期化
		require_once dirname(__FILE__).'/wp-hamazon-service.php';
		foreach($this->services as $service){
			$class_name = 'WP_Hamazon_Service_'.ucfirst($service);
			$path = dirname(__FILE__).'/wp-hamazon-service-'.$service.'.php';
			if(file_exists($path)){
				require_once $path;
				if(class_exists($class_name)){
					$this->{$service} = new $class_name();
				}
			}
		}
		
		// 設定画面を追加
		add_action('admin_menu', array($this, 'admin_menu'));
		
		// Action Hook for admin_init
		add_action('admin_init', array($this, 'admin_init'));
		
		// Admin enqueue style
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_script'));
		
		// 管理画面用メッセージの追加
		add_action('admin_notices', array($this, 'admin_notices'));
		
		// 検索用iframeを出力するアクション
		add_action('wp_hamazon_iframe', array($this, 'iframe'));
		
		// 公開画面用CSSを読み込む
		add_action('wp_enqueue_scripts', array($this, 'enqueue_script'));
	}
	
	
	
	/**
	 * Add Options Page
	 */
	public function admin_menu() {
		// Add a new menu under Options:
		add_options_page(
			'WP Hamazon（アフィリエイト）設定',
			'アフィリエイト設定',
			'manage_options',
			$this->slug,
			array($this, 'options_page')
		);
	}
	
	/**
	 * Admin init hook.
	 */
	public function admin_init(){
		// 設定をアップデート
		if(isset($_REQUEST['page'], $_REQUEST['action']) && $_REQUEST['page'] == $this->slug && $_REQUEST['action'] == 'save_options'){
			$this->update_option();
		}
	}
	
	
	
	/**
	 * 管理画面にメッセージを表示する
	 * 
	 */
	public function admin_notices(){
		if(!empty($this->message['normal'])){
			?>
				<div class="updated">
					<?php foreach($this->message['normal'] as $message): ?>
						<p><?php echo $message; ?></p>
					<?php endforeach; ?>
				</div>
			<?php
		}
		if(!empty($this->message['error'])){
			?>
				<div class="error">
					<?php foreach($this->message['error'] as $message): ?>
						<p><?php echo $message; ?></p>
					<?php endforeach; ?>
				</div>
			<?php
		}
	}
	
	
	
	/**
	 * 設定を保存する
	 * @global array $hamazon_settings
	 */
	public function update_option(){
		global $hamazon_settings;
		//Save options.
		if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'hamazon_setting') && current_user_can('manage_options')){
			foreach($this->default_options as $key => $val){
				if(isset($_REQUEST[$key])){
					switch($key){
						case 'post_types':
							$hamazon_settings[$key] = is_array($_POST[$key]) ? $_POST[$key] : array('post');
							break;
						case 'load_css':
							$hamazon_settings[$key] = (boolean)$_POST[$key];
							break;
						default:
							$hamazon_settings[$key] = (string)$_POST[$key];
							break;
					}
				}
			}
			update_option('wp_tmkm_admin_options', $hamazon_settings);
			do_action('wp_hamazon_update_options');
			$this->message['normal'][] = '<strong>設定を保存しました。</strong>'; 
		}
	}
	
	
	
	/**
	 * 管理画面のテンプレートを読み込む
	 * 
	 * @global array $hamazon_settings
	 */
	public function options_page(){
		global $hamazon_settings;
		require_once dirname(dirname(__FILE__)).'/templates/admin.php';
	}
	
	
	
	/**
	 * CSSを読み込む
	 * @global array $hamazon_settings
	 */
	public function enqueue_script(){
		global $hamazon_settings;
		if(!is_admin() && $hamazon_settings['load_css']){
			if(file_exists(get_stylesheet_directory().'/tmkm-amazon.css')){
				$css_url = get_stylesheet_directory_uri().'/tmkm-amazon.css';
			}else{
				$css_url = plugin_dir_url(dirname(__FILE__)).'assets/css/hamazon.css';
			}
			$args = apply_filters('wp_hamazon_css_args', array(
				'handle' => 'wp-hamazon',
				'src' => $css_url,
				'deps' => array(),
				'version' => $this->version,
				'media' => 'all'
			));
			if(is_array($args)){
				wp_enqueue_style($args['handle'], $args['src'], $args['deps'], $args['version'], $args['media']);
			}
		}
	}
	
	
	
	/**
	 * 管理画面用にCSSを読み込む
	 * @global array $hamazon_settings
	 */
	public function enqueue_admin_script(){
		wp_enqueue_style('wp-hamazon-admin', plugin_dir_url(dirname(__FILE__)).'assets/css/hamazon-search.css', array(), $this->version);
	}
	
	
	
	/**
	 * iframeを出力する
	 */
	public function iframe(){
		$path = explode('.', basename($_SERVER['SCRIPT_FILENAME']));
		$service = $path[0];
		if(false === array_search($service, $this->services)){
			wp_die('不正なアクセスです。', get_status_header_desc(403), array(
				'status_code' => 403,
				'back_link' => true
			));
		}
		if(!$this->{$service}->is_valid()){
			wp_die('このアフィリエイトサービスは有効化されていません。', get_status_header_desc(503), array(
				'status_code' => 503,
				'back_link' => true
			));
		}
		if(!apply_filters('wp_hamazon_affiliate_available', current_user_can('edit_posts'), $service)){
			wp_die('あなたのアカウントにはアフィリエイトコードを取得する権限がありません。', get_status_header_desc(403), array(
				'status_code' => 403,
				'back_link' => true
			));
		}
		wp_iframe(array($this->{$service}, 'show_iframe'));
	}
}