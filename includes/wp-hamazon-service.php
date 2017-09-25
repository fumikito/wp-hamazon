<?php

/**
 * アフィリエイトサービスAPIを利用するための抽象クラス
 * 
 * 各アフィリエイトサービスはこのクラスを継承し、
 * なおかつWP_Hamazon_Service_Requiredをインターフェースとして持つ必要があります。
 * 
 * @since 2.2
 */
abstract class WP_Hamazon_Service{
	
	
	
	/**
	 * サービス名称
	 * 
	 * サービス名はクラス名末尾を小文字にしたものです。
	 * コンストラクタで登録されます。
	 * 
	 * @var string
	 */
	public $name = 'service';
	
	
	
	/**
	 * サービス名称タイトル
	 * 
	 * 商品検索ページ等で表示される名称です。
	 * 
	 * @var string
	 */
	public $title = 'デフォルト';
	
	
	
	
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
	protected $short_codes = array();
	
	
	
	/**
	 * コンストラクタ
	 */
	public function __construct(){
		//サービス名設定
        $name = explode('_', get_class($this));
		$this->name = strtolower($name[count($name) - 1]);
		//オプション設定
		if(method_exists($this, 'set_option')){
			$this->set_option();
			add_action('wp_hamazon_update_options', array($this, 'set_option'));
		}
		if(method_exists($this, 'set_shortcode')){
			$this->set_shortcode();
		}
		//ショートコードを登録する
		if(!empty($this->short_codes)){
			foreach($this->short_codes as $code){
				if(method_exists($this, $this->convert_code_to_method($code))){
					add_shortcode($code, array($this, $this->convert_code_to_method($code)));
				}
			}
		}
		//管理画面用のメソッドを登録
		add_action('admin_init', array($this, 'admin_init'));
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
	 * 指定されたオプションの値を返す
	 * 
	 * @global array $hamazon_settings
	 * @param string $key
	 * @return string
	 */
	protected function get_option($key){
		global $hamazon_settings;
		if(isset($hamazon_settings[$key])){
			return (string)$hamazon_settings[$key];
		}else{
			return '';
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
	 * iframeの中身を出力する
	 */
	public function show_iframe(){
		$this->show_form();
		$this->show_results();
	}
	
	/**
	 * 検索結果画面でページネーションを出力する
	 * 
	 * @param int $total
	 * @param int $current_page
	 * @param int $per_page
	 * @param array $args
	 * @return string
	 */
	protected function paginate($total, $current_page, $per_page, $args = array()){
		if($total < $per_page){
			return '';
		}else{
			$base_url = plugin_dir_url(dirname(__FILE__)).'endpoint/'.$this->name.'.php';
			$queries = array();
			foreach($args as $key => $val){
				$queries[] = rawurlencode($key).'='.rawurlencode($val);
			}
			if(!empty($queries)){
				$base_url .= '?'.implode('&', $queries);
			}
			return '<div class="wp-hamazon-pagination">'.paginate_links(array(
						'base' => $base_url.'%_%',
						'format' => '&page=%#%',
						'total' => ceil($total / $per_page),
						'current' => $current_page
					)).'</div>';
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
}


/**
 * サービスクラスが持たなければいけないインターフェース
 * 
 * @since 2.2
 */
interface WP_Hamazon_Service_Required{
	public function is_valid();
	public function set_option();
	public function set_shortcode();
	public function show_form();
	public function show_results();
}
