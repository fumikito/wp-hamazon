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
		//Add Options Page
		add_action('admin_menu', array($this, 'add_menu'));
		//Action Hook for admin_init
		add_action('admin_init', array($this, 'admin_init'));
	}

	/**
	 * Add Options Page
	 * @global string $tmkm_amazon_php 
	 */
	function add_menu() {
		// Add a new menu under Options:
		add_options_page(
			'WP Hamazon',
			'WP Hamazon',
			8,
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
			$url = plugin_dir_url(__FILE__)."wp-hamazon-search.php?TB_iframe=true";
			echo '<a href="'.$url.'" id="add_video" class="thickbox" title="Amazon商品検索"><img src="' . esc_url( plugin_dir_url( __FILE__ ).'/amazon.png' ) . '" alt="Amazon商品検索" width="16" height="16" /></a>';
		}
	}
	
	/**
	 * Create Admin Panel
	 * @global type $hamazon_settings 
	 * @return void
	 */
	function options_page() {
		global $hamazon_settings;

		//Save options.
		if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'hamazon_setting')){
			$hamazon_settings = array(
				'associatesid' => htmlspecialchars($_POST['associatesid']),
				'accessKey' => (string)$_POST['accessKey'],
				'secretKey' => (string)$_POST['secretKey'],
				'windowtarget' => (string)$_POST['windowtarget'],
				'goodsimgsize' => (string)$_POST['goodsimgsize'],
				'layout_type' => intval($_POST['layout_type']),
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

		switch( $hamazon_settings['windowtarget']) {
			case 'newwin':$newwindow = ' checked';$selfwindow = '';break;
			case 'self':$newwindow = '';$selfwindow = ' checked';break;
		}

		switch( $hamazon_settings['goodsimgsize'] ) {
			case 'medium': $m_goodssize = ' checked'; $s_goodssize = ''; break;
			case 'small': $m_goodssize = ''; $s_goodssize = ' checked'; break;
		}

		switch( $hamazon_settings['layout_type'] ) {
			case 0: $default_layout  = ' checked';$medium_layout = '';  $simple_layout = ''; $noimage_layout = ''; break;
			case 1: $default_layout = ''; $medium_layout = ' checked'; $simple_layout = ''; $noimage_layout = ''; break;
			case 2: $default_layout  = ''; $medium_layout = ''; $simple_layout = ' checked'; $noimage_layout = ''; break;
			case 3: $default_layout  = ''; $medium_layout = ''; $simple_layout = ''; $noimage_layout = ' checked'; break;
		}

		?>
			<div class="wrap" id="footnote-options">
			<h2>Wp Hamazon プラグイン設定</h2>
			<?php do_action('admin_notice'); ?>
			<form method="post">
				<?php wp_nonce_field('hamazon_setting'); ?>
				<input type="hidden" name="action" value="save_options" />
				<table class="form-table">
					<tr>
						<th><label for="associatesid">あなたのアソシエイト ID</label></th>
						<td>
							<input type="text" class="regular-text" id="associatesid" name="associatesid" value="<?php echo $hamazon_settings['associatesid']; ?>" />
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
						<th>商品リンクの動作</th>
						<td>
							<label>
								<input type="radio" name="windowtarget" value="self"<?php echo $selfwindow; ?> />
								&nbsp;同じウィンドウ（ target 指定なし ）
							</label><br />
							<label>
								<input type="radio" name="windowtarget" value="newwin"<?php echo $newwindow; ?> />
								&nbsp;新規ウィンドウ（ target="_blank" ）
							</label>
						</td>
					</tr>
					<tr>
						<th>商品詳細の表示スタイル</th>
						<td>
							<label>
								<input type="radio" name="layout_type" value="0"<?php echo $default_layout; ?> />
								&nbsp;画像、タイトル、出版社、発売時期、著者、価格、本のタイプ、ページ数、ISBN（ 初期設定。本以外はこれに準ずる項目 ）
							</label><br />
							<label>
								<input type="radio" name="layout_type" value="1"<?php echo $medium_layout; ?> />
								&nbsp;画像、タイトル、出版社、著者、発売時期（ 初期設定から価格情報とコード情報を省略 ）<br />
							</label><br />
							<label>
								<input type="radio" name="layout_type" value="2"<?php echo $simple_layout; ?> />
								&nbsp;画像とタイトルのみ<br />
							</label><br />
							<label>
								<input type="radio" name="layout_type" value="3"<?php echo $noimage_layout; ?> />
								&nbsp;タイトルのみ
							</label>
						</td>
					</tr>
					<tr>
						<th>商品画像サイズ</th>
						<td>
							<label>
								<input type="radio" name="goodsimgsize" value="small"<?php echo $s_goodssize; ?> />
								&nbsp;小サイズ（ 初期設定 ）
							</label><br />
							<label>
								<input type="radio" name="goodsimgsize" value="medium"<?php echo $m_goodssize ; ?> />
								&nbsp;中サイズ
							</label>
						</td>
					</tr>
					<tr>
						<th>CSSの読み込み</th>
						<td>
							<label><input type="radio" name="load_css" value="1" <?php if($hamazon_settings['load_css']) echo ' checked="checked"'; ?>/>読み込む</label>&nbsp;
							<label><input type="radio" name="load_css" value="0" <?php if(!$hamazon_settings['load_css']) echo ' checked="checked"'; ?>/>読み込まない</label>
							<p class="description">
								オリジナルのCSSを読み込みたい場合はテーマフォルダ内にtmkm-amazon.cssを配置してください。存在しない場合はデフォルトのものを読み込みます。「読み込まない」を選択した場合は何も読み込みません。
							</p>
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
}