<?php

if(basename($_SERVER['SCRIPT_FILENAME']) == 'admin.php'){
	die();
}
/* @var $this WP_Hamazon_Controller */
?>
<div class="wrap" id="footnote-options">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Wp Hamazon （アフィリエイト）設定</h2>

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
		<?php foreach($this->services as $service): ?>
			<p>&nbsp;</p>
			<h3><?php echo esc_html($this->{$service}->title); ?></h3>
			<table class="form-table">
				<tr>
					<th>ステータス</th>
					<td>
						<?php if($this->{$service}->is_valid()): ?>
							<strong style="color: green;">有効：</strong>
						<?php else: ?>
							<strong style="color: red;">無効：</strong>
						<?php endif; ?>
						<span class="description">
							<?php
								switch($service){
									case 'amazon':
										$link = 'https://affiliate.amazon.co.jp/gp/advertising/api/detail/main.html';
										break;
									case 'rakuten':
										$link = 'http://webservice.rakuten.co.jp';
										break;
									case 'linkshare':
										$link = 'http://www.linkshare.ne.jp';
										break;
									case 'phg':
										$link = 'http://www.apple.com/jp/itunes/affiliates/';
										break;
									case 'dmm':
										$link = 'https://affiliate.dmm.com/account/index/';
										break;
								}
								printf('認証に必要な情報は<a href="%s">こちら</a>で登録の上、入手してください。', $link);
							?>
						</span>
					</td>
				</tr>
				<?php
					switch($service){
						case 'amazon':
							$input = array(
								'associatesid' => 'あなたのアソシエイト ID',
								'accessKey' => 'あなたのAWS アクセス ID',
								'secretKey' => 'あなたのAWS シークレットアクセス ID',
								'show_review' => 'レビューの表示',
							);
							break;
						case 'rakuten':
							$input = array(
								'rakuten_app_id' => 'アプリID / デベロッパ—ID',
								'rakuten_affiliate_id' => 'アフィリエイトID',
							);
							break;
						case 'linkshare':
							$input = array(
								'linkshare_token' => 'サイトアカウントのトークン',
							);
							break;
						case 'phg':
							$input = array(
								'phg_id' => 'PHG アフィリエイト・トークン:'
							);
							break;
						case 'dmm':
							$input = array(
								'dmm_affiliate_id' => 'アフィリエイトID',
								'dmm_api_id' => 'API ID',
							);
							break;
					}
					foreach($input as $name => $label):
				?>
				<tr>
					<th><label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label></th>
					<td>
						<?php switch($name): case 'show_review'?>
							<select name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>">
								<option value="0"<?php selected($hamazon_settings[$name] == false) ?>>オフ</option>
								<option value="1"<?php selected($hamazon_settings[$name] == true) ?>>オン</option>
							</select>
						<?php break; default: ?>
							<input type="text" class="regular-text" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($hamazon_settings[$name]); ?>" />
						<?php endswitch; ?>
					</td>
				</tr>
					<?php endforeach; ?>
			</table>
		<?php endforeach; ?>
		<?php submit_button('設定を保存する')?>
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
	// ここで好きなHTMLを作成します。
	\$my_html = '<div id="my_amazon"><a href="'.strval(\$item->DetailPageURL).'">'.strval(\$item->ItemAttributes->Title).'</a></div>';
	// はじめてカスタマイズするときは\$itemがなんなのかわからないと思います。
	// 一度var_dumpして確認してみると構造がよく理解できます。
	var_dump(\$item); // 本番環境ではこの行を使わないでください
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
				<th>楽天のフィルター名</th>
				<td>
					<code>wp_hamazon_rakuten</code><br />
					$itemはオブジェクトです。
				</td>
			</tr>
			<tr>
				<th>リンクシェアのフィルター名</th>
				<td>
					<code>wp_hamazon_linkshare</code><br />
					$itemは連想配列です。
				</td>
			</tr>
			<tr>
				<th>PHGのフィルター名</th>
				<td>
					<code>wp_hamazon_phg</code><br />
					$itemはオブジェクト、第3引数に$urlを取ります。
				</td>
			</tr>
			<tr>
				<th>DMMのフィルター名</th>
				<td>
					<code>wp_hamazon_dmm</code><br />
					$itemは連想配列です。
				</td>
			</tr>
		</table>



	</form>
</div>