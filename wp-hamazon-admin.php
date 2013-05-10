<?php
/**
 * Controller for Admin Panel
 */
class WP_Hamazon_Admin {
	
	/**
	 * Page slug on admin panel
	 * @var string
	 */
	var $slug  = 'wp-hamazon';
	
	/**
	 * Message Container
	 * @var array
	 */
	var $message = array('error' => array(), 'normal' => array());
	
	/**
	 * Constructor
	 * @return void
	 */
	function __construct(){
		// Add Options Page
		add_action('admin_menu', array($this, 'add_menu'));
		// Action Hook for admin_init
		add_action('admin_init', array($this, 'admin_init'));
		// Admin enqueue style
		add_action('admin_enqueue_scripts', array($this, 'enqueue_script'));
	}

	/**
	 * Add Options Page
	 * @global string $tmkm_amazon_php 
	 */
	function add_menu() {
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
	function admin_init(){
		add_action( 'media_buttons', array( $this, 'media_button' ), 1000 );
	}
	
	/**
	 * echo media button
	 * @global array $hamaozn_setings
	 */
	function media_button(){
		global $hamazon_settings;
		if(false !== array_search(get_post_type(), $hamazon_settings['post_types'])){
			printf('<a href="%s" id="add_amazon" class="thickbox" title="Amazon商品検索"><img src="%s" alt="Amazon商品検索" width="16" height="16" /></a>', 
					plugin_dir_url(__FILE__)."endpoint/amazon.php?TB_iframe=true",
					esc_url( plugin_dir_url( __FILE__ ).'assets/img/amazon.png'));
			if(!empty($hamazon_settings['linkshare_token'])){
				printf('<a href="%s" id="add_linkshare" class="thickbox" title="リンクシェア商品検索"><img src="%s" alt="リンクシェア商品検索" width="16" height="16" /></a>', 
					plugin_dir_url(__FILE__)."endpoint/linkshare.php?TB_iframe=true",
					esc_url( plugin_dir_url( __FILE__ ).'assets/img/rakuten.gif' ));
			}
		}
	}
	
	/**
	 * Create Admin Panel
	 * 
	 * @global array $hamazon_settings 
	 * @return void
	 */
	function options_page() {
		global $hamazon_settings;
		//Save options.
		if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'hamazon_setting') && current_user_can('manage_options')){
			$hamazon_settings = array(
				'associatesid' => htmlspecialchars($_POST['associatesid']),
				'accessKey' => (string)$_POST['accessKey'],
				'secretKey' => (string)$_POST['secretKey'],
				'rakuten_app_id' => (string)$_POST['rakuten_app_id'],
				'rakuten_affiliate_id' => (string)$_POST['rakuten_affiliate_id'],
				'linkshare_token' => (string)$_POST['linkshare_token'],
				'post_types' => (isset($_POST['post_types']) && is_array($_POST['post_types'])) ? $_POST['post_types'] : array(),
				'load_css' => (boolean)$_POST['load_css']
			);
			update_option('wp_tmkm_admin_options', $hamazon_settings);
			$this->message['normal'][] = '<strong>設定を保存しました。</strong>'; 
		}
		
		//Check if all option is saved.
		if(empty($hamazon_settings['associatesid'])){
			$this->message['error'][] = 'アソシエイトIDが入力されていません。<a href="https://affiliate.amazon.co.jp/" target="_blank">Amazonアソシエイト</a>でアソシエイトIDを取得してください。';
		}
		if(empty($hamazon_settings['accessKey']) || empty($hamazon_settings['secretKey'])){
			$this->message['error'][] = 'アクセスIDまたはシークレットアクセスIDが入力されていません。<a href="http://aws.amazon.com/jp/" target="_blank">Amazon Web Service</a>に登録し、アクセス認証情報を取得してください。';
		}
		
		add_action('admin_notice', array($this, 'show_message'));

		?>
			<div class="wrap" id="footnote-options">
			<h2>Wp Hamazon （アフィリエイト）設定</h2>
			<?php do_action('admin_notice'); ?>
			
			
			
			<form method="post">
				<?php wp_nonce_field('hamazon_setting'); ?>
				<input type="hidden" name="action" value="save_options" />
				
				
				<h3>全般</h3>
				<table class="form-table">
					<tr>
						<th><label for="post_types">ボタンを表示する投稿タイプ</label></th>
						<td>
							<?php foreach(get_post_types('', 'object') as $post_type): if(false === array_search($post_type->name, array('revision', 'nav_menu_item', 'page', 'attachment', 'lwp_notification')) && $post_type->public): ?>
								<label>
									<input type="checkbox" name="post_types[]" value="<?php echo $post_type->name; ?>" <?php if(false !== array_search($post_type->name, $hamazon_settings['post_types'])) echo 'checked="checked" '; ?>/>
									<?php echo $post_type->labels->name; ?>
								</label>&nbsp;
							<?php endif; endforeach; ?>
						</td>
					</tr>
					<tr>
						<th>CSSの読み込み</th>
						<td>
							<label><input type="radio" name="load_css" value="1" <?php if($hamazon_settings['load_css']) echo ' checked="checked"'; ?>/> 読み込む</label><br />
							<label><input type="radio" name="load_css" value="0" <?php if(!$hamazon_settings['load_css']) echo ' checked="checked"'; ?>/> 読み込まない</label>
							<p class="description">
								オリジナルのCSSを読み込みたい場合はテーマフォルダ内にtmkm-amazon.cssを配置してください。存在しない場合はデフォルトのものを読み込みます。「読み込まない」を選択した場合は何も読み込みません。
							</p>
						</td>
					</tr>
				</table>
				<p>&nbsp;</p>
				<h3>Amazon</h3>
					
				<table class="form-table">
					<tr>
						<th><label for="associatesid">あなたのアソシエイト ID</label></th>
						<td>
							<input type="text" class="regular-text" id="associatesid" name="associatesid" value="<?php echo esc_attr($hamazon_settings['associatesid']); ?>" />
						</td>
					</tr>
					<tr>
						<th><label for="associatesid">あなたのAWS アクセス ID</label></th>
						<td>
							<input type="text" class="regular-text" id="accessKey" name="accessKey" value="<?php echo $hamazon_settings['accessKey']; ?>" />
						</td>
					</tr>
					<tr>
						<th><label for="associatesid">あなたのAWS シークレットアクセス ID</label></th>
						<td>
							<input type="text" class="regular-text" id="secretKey" name="secretKey" value="<?php echo $hamazon_settings['secretKey']; ?>" />
						</td>
					</tr>
				</table>
				<p>&nbsp;</p>
				<h3>楽天</h3>
				<table class="form-table">
					<tr>
						<th><label for="rakuten_app_id">アプリID / デベロッパ—ID</label></th>
						<td>
							<input type="text" class="regular-text" id="rakuten_app_id" name="rakuten_app_id" value="<?php echo $hamazon_settings['rakuten_app_id']; ?>" />
						</td>
					</tr>
					<tr>
						<th><label for="rakuten_affiliate_id">アフィリエイトID</label></th>
						<td>
							<input type="text" class="regular-text" id="rakuten_affiliate_id" name="rakuten_affiliate_id" value="<?php echo $hamazon_settings['rakuten_affiliate_id']; ?>" />
						</td>
					</tr>
				</table>
				<p>&nbsp;</p>
				<h3>リンクシェア</h3>
				<table class="form-table">
					<tr>
						<th><label for="linkshare_token">サイトアカウントのトークン</label></th>
						<td>
							<input type="text" class="regular-text" name="linkshare_token" id="linkshare_token" value="<?php echo esc_attr($hamazon_settings['linkshare_token']) ?>" />
							<p class="description">
								リンクシェアのトークンは<a href="http://cli.linksynergy.com/cli/publisher/links/webServices.php" target="_blank">こちら</a>で取得してください。
							</p>
						</td>
					</tr>
				</table>
				<p>&nbsp;</p>
				<h3>表示のカスタマイズ</h3>
				<p class="description">
					フィルターフックを使ってショートコードの出力をカスタマイズしてください。
					テーマファイルのfunctions.phpに次のように書くとカスタマイズできます。<br />
					<code><?php echo get_stylesheet_directory(); ?>/functions.php</code>
				</p>
				<pre class="wp-hamazon-pre"><?php
					$html = <<<EOS
//フィルターフックを登録
add_filter('wp_hamazon_amazon', '_my_amazon_tag', 10, 2);
   
/**
 * ショートコードをカスタマイズする関数
 * 
 * @param string \$html 商品リンクのHTMLタグです
 * @param SimpleXMLElement \$item 商品情報です。サービスによって異なります
 * @return string HTMLタグを返します
 */
function _my_amazon_tag(\$html, \$item){
	//ここで好きなHTMLを作成します。
	\$my_html = '<div id="my_amazon"><a href="'.strval(\$item->DetailPageURL).'">'.strval(\$item->ItemAttributes->Title).'</a></div>';
	return \$my_html;
}
EOS;
					echo esc_html($html);
				?></pre>
				<table class="form-table">
					<tr>
						<th>Amazonのフィルター名</th>
						<td>
							<code>wp_hamazon_amazon</code><br />
							$itemはSimpleXMLElementオブジェクトのインスタンスです。かなり多くの情報が入っています。
						</td>
					</tr>
					<tr>
						<th>リンクシェアのフィルター名</th>
						<td>
							<code>wp_hamazon_linkshare</code><br />
							$itemは連想配列です。
						</td>
					</tr>
				</table>
					
				
				<?php submit_button('設定を保存する')?>
			</form>
			</div>
		<?php
	}
	
	/**
	 * Echo Message on admin panel
	 */
	function show_message(){
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
	 * 検索ページにCSSを読み込む
	 * @global array $hamazon_settings
	 */
	function enqueue_script(){
		global $hamazon_settings;
		wp_enqueue_style('wp-hamazon-admin', plugin_dir_url(__FILE__).'assets/css/hamazon-search.css', array(), $hamazon_settings['version']);
	}
}