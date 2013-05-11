<?php

if(basename($_SERVER['SCRIPT_FILENAME']) == 'admin.php'){
	die();
}
/* @var $this WP_Hamazon_Controller */
?>
<div class="wrap" id="footnote-options">
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
					<input type="text" class="regular-text" id="accessKey" name="accessKey" value="<?php echo esc_attr($hamazon_settings['accessKey']); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="associatesid">あなたのAWS シークレットアクセス ID</label></th>
				<td>
					<input type="text" class="regular-text" id="secretKey" name="secretKey" value="<?php echo esc_attr($hamazon_settings['secretKey']); ?>" />
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>
		<h3>楽天</h3>
		<table class="form-table">
			<tr>
				<th><label for="rakuten_app_id">アプリID / デベロッパ—ID</label></th>
				<td>
					<input type="text" class="regular-text" id="rakuten_app_id" name="rakuten_app_id" value="<?php echo esc_attr($hamazon_settings['rakuten_app_id']); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="rakuten_affiliate_id">アフィリエイトID</label></th>
				<td>
					<input type="text" class="regular-text" id="rakuten_affiliate_id" name="rakuten_affiliate_id" value="<?php echo esc_attr($hamazon_settings['rakuten_affiliate_id']); ?>" />
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