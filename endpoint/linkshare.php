<?php
if ( ! defined('WP_ADMIN') )
	define('WP_ADMIN', true);

if ( ! defined('WP_NETWORK_ADMIN') )
	define('WP_NETWORK_ADMIN', false);

if ( ! defined('WP_USER_ADMIN') )
	define('WP_USER_ADMIN', false);

if ( ! WP_NETWORK_ADMIN && ! WP_USER_ADMIN ) {
	define('WP_BLOG_ADMIN', true);
}

//DEBUG
define('WP_DEBUG', true);

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."wp-load.php";
require_once(ABSPATH . 'wp-admin/includes/admin.php');


// iframe用の諸々を呼び出し
wp_iframe('_wp_hamazon_search_form');

/**
 * 検索用のiframeを書き出し
 * 
 * @global array $hamazon_settings
 * @global WP_Hamazon_Linkshare $wp_hamazon_linkshare
 * @global WP_Hamazon_Admin $hamazon_admin
 */
function _wp_hamazon_search_form(){
	global $hamazon_settings, $wp_hamazon_linkshare, $hamazon_admin;
	// 権限チェック
	$error = false;
	if(!current_user_can('edit_posts')){
		$error = true;
		$message = 'このページにアクセスする権限がありません';
	}elseif(!$wp_hamazon_linkshare->is_valid()){
		$error = true;
		$message = 'リンクシェアトークンが正しく入力されていません。';
		if(current_user_can('manage_options')){
			$message .= sprintf('<a target="_top" href="%s">設定ページ</a>より入力してください。', admin_url('options-general.php?page='.$hamazon_admin->slug));
		}else{
			$message .= '管理者に報告してください。'; 
		}
	}
	if($error){
		printf('<div class="error"><p>%s</p></div>', $message);
		return;
	}
	//提携企業リストを取得
	$companies = $wp_hamazon_linkshare->get_company_list();
	if(empty($companies)){
		$message = <<<EOS
提携企業のリストを取得できませんでした。
トークンが正しいか、提携企業が少なくとも一つ登録されているかご確認ください。
なお、リンクシェア管理画面上での変更がWordPressに反映されるにはは少なくとも1時間かかります。
EOS;
		printf('<div class="error"><p>%s</p></div>', nl2br($message));
		return;
	}
	//OKなのでフォームを出力
	$nonce = wp_create_nonce('linkshare_nonce');
	?>
		<form method="get" class="hamazon-search-form search-amazon" action="<?php echo plugin_dir_url(__FILE__); ?>linkshare.php">
			<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />
			<p style="display: inline;"><a id="searchpagetop">リンクシェア 検索</a></p>&nbsp;
			<select name="mid">
				<?php foreach($companies as $mid => $name): ?>
				<option value="<?php echo $mid; ?>"<?php if((isset($_GET['mid']) && $_GET['mid'] == $mid)) echo ' selected="selected"'; ?>>
					<?php echo esc_html($name); ?>
				</option>
				<?php endforeach; ?>
			</select>
			<input type="text" size="20" maxlength="50" value="<?php if(isset($_GET['keyword'])) echo esc_attr($_GET['keyword']); ?>" name="keyword" />&nbsp;
			<input class="button-primary" type="submit" style="cursor:pointer;" value="検索" />
		</form>
	<?php
	/******************************************************************************
	 * Ready for communicate with Amazon
	 *****************************************************************************/
	
	// Get pagination
	if( isset( $_GET['page'] ) ){
		$PageNum = max(1, (int) $_GET['page']);
	}else{
		$PageNum = 1;
	}
	//Start Searching
	if(isset($_GET['keyword'], $_GET['mid'], $_GET['_wpnonce']) && !empty( $_GET['keyword']) && wp_verify_nonce($_GET['_wpnonce'], 'linkshare_nonce')){
		echo '<div id="amazon-search-result">';
		$keyword = (string) $_GET['keyword'];
		$result = $wp_hamazon_linkshare->search($keyword, $_GET['mid'], $PageNum);
		if(is_wp_error($result) ){
			// Amazon function was returned false, so AWS is down
			echo '<div class="error"><p>検索結果を取得できませんでした。リンクシェアのサーバに障害が起きているかもしれません。また、楽天市場のアフィリエイト検索はうまく動きません。</p></div>';
		}else{
			// results were found, so display the products
			$totalresults = intval($result->TotalMatches);
			$totalpages =  intval($result->TotalPages);

			if( $totalresults == 0 ){ // no result was found
				printf('<div class="error"><p>「%s」の検索結果が見つかりませんでした。</p></div>', esc_html($keyword));
			} else {
				// Pagenation
				if( $totalpages > 1 ) {
					$pagination = '<div class="wp-hamazon-pagination">'.paginate_links(array(
						'base' => sprintf('%slinkshare.php?token=%s&keyword=%s&mid=%d&_wpnonce=%s',
										plugin_dir_url(__FILE__), $wp_hamazon_linkshare->get_token(),
										rawurlencode($keyword), intval($_REQUEST['mid']), $nonce).'%_%',
						'format' => '&page=%#%',
						'total' => $totalpages,
						'current' => $PageNum
					)).'</div>';
				}else{
					$pagination = '';
				}
				// results were found
				$length = count($result->item);
				?>
					<div class="result-desc clearfix">
						<h1>「<?php echo esc_html($keyword); ?>」の検索結果: <?php echo number_format((string)$totalresults); ?>件</h1>
						<?php echo $pagination; ?>
					</div><!-- //.result-desc -->
					<table class="wp-hamazon-product-table">
				<?php
				foreach($result->item as $item){
					?>
						<tr class="amazon">
							<th>
								<img src="<?php echo strval($item->imageurl); ?>" border="0" alt="" /><br />
								<a class="button" href="<?php echo strval($item->linkurl); ?>" target="_blank">ストアで見る</a>
							</th>
							<td>
								<strong><?php echo strval($item->productname); ?></strong><br />
								価格：<em class="price">&yen;<?php echo number_format(strval($item->price)); ?></em><br />
								ストア：<?php echo strval($item->merchantname); ?><br />
								カテゴリー： <?php echo strval($item->category->primary); ?> &gt; <?php echo strval($item->category->secondary); ?><br />
								<textarea rows="3" onclick="this.select();"><?php echo ($wp_hamazon_linkshare->get_short_code($item)); ?></textarea><br />
								<span class="description">ショートコードを投稿本文に貼り付けてください</span>
							</td>
						</tr>
					<?php
				}
				?>
					</table>
					<div class="result-desc clearfix">
						<?php echo $pagination; ?>
					</div><!-- //.result-desc -->
				<?php 
			}
		}
		echo '</div>';
	}
}
