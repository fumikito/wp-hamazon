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
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."wp-load.php";
require_once(ABSPATH . 'wp-admin/includes/admin.php');


// iframe用の諸々を呼び出し
wp_iframe('_wp_hamazon_search_form');

/**
 * 検索用のiframeを書き出し
 * 
 * @global array $hamazon_settings
 * @global WP_Hamazon $wp_hamazon_parser
 * @global WP_Hamazon_Admin $hamazon_admin
 */
function _wp_hamazon_search_form(){
	global $hamazon_settings, $wp_hamazon_parser, $hamazon_admin;
	// 権限チェック
	$error = false;
	if(!current_user_can('edit_posts')){
		$error = true;
		$message = 'このページにアクセスする権限がありません';
	}elseif(empty($hamazon_settings['associatesid']) || empty($hamazon_settings['accessKey']) || empty($hamazon_settings['secretKey'])){
		$error = true;
		$message = 'アソシエイトID、アクセスID、シークレットアクセスIDが正しく入力されていません。';
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
	
	//OKなのでフォームを出力
	$nonce = wp_create_nonce('amazon_search');
	//検索フォームを出力
	?>
		<form method="get" class="hamazon-search-form search-amazon" action="<?php echo plugin_dir_url(__FILE__); ?>amazon.php">
			<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />
			<p style="display: inline;"><a id="searchpagetop">Amazon 検索</a></p>&nbsp;
			<select name="SearchIndex">
				<?php foreach($wp_hamazon_parser->searchIndex as $k => $v): ?>
				<option value="<?php echo $k; ?>"<?php if((isset($_GET['SearchIndex']) && $_GET['SearchIndex'] == $k) || (!isset($_GET['SearchIndex']) && $k == 'Books')) echo ' selected="selected"'; ?>>
					<?php echo $v; ?>
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
	if(isset($_GET['keyword'], $_GET['_wpnonce']) && !empty( $_GET['keyword']) && wp_verify_nonce($_GET['_wpnonce'], 'amazon_search')){
		echo '<div id="amazon-search-result">';
		$keyword = (string) $_GET['keyword'];
		$searchindex = !empty( $_GET['SearchIndex'] ) ? $_GET['SearchIndex'] : 'Blended';
		$result = $wp_hamazon_parser->search_with($keyword, $PageNum, $searchindex);

		if(is_wp_error($result) ){
			// Amazon function was returned false, so AWS is down
			echo '<div class="error"><p>検索結果を取得できませんでした。amazonのサーバでエラーが起こっているかもしれません。</p></div>';
		}else{
			// Amazon function returned XML data
			if($result->Items->Request->Errors){
				printf('<div class="error"><p>%s</p></div>', $result->Items->Request->Errors->Error->Message);
			}else{
				// results were found, so display the products
				$totalresults = $result->Items->TotalResults;
				$totalpages =  $result->Items->TotalPages;

				if( $totalresults == 0 ){ // no result was found
					printf('<div class="error"><p>「%s」の検索結果が見つかりませんでした。</p></div>', esc_html($keyword));
				} else {
					// Pagenation
					if( $totalpages > 1 ) {
						$pagination = '<div class="wp-hamazon-pagination">'.paginate_links(array(
							'base' => sprintf('%samazon.php?SearchIndex=%s&keyword=%s&_wpnonce=%s',
											plugin_dir_url(__FILE__), $searchindex, esc_attr($keyword), $nonce).'%_%',
							'format' => '&page=%#%',
							'total' => $totalpages,
							'current' => $PageNum
						)).'</div>';
					}else{
						$pagination = '';
					}
					// results were found
					$length = count($result->Items->Item);
					?>
						<div class="result-desc clearfix">
							<h1>「<?php echo esc_html($keyword); ?>」の検索結果: <?php echo number_format((string)$totalresults); ?>件</h1>
							<?php echo $pagination; ?>
						</div><!-- //.result-desc -->
						<table class="wp-hamazon-product-table">
					<?php
					for($i = 0; $i < $length; $i++) {
						$item = $result->Items->Item[$i];
						$smallimage = $wp_hamazon_parser->get_image_src($item,'small');
						$atts = $wp_hamazon_parser->get_atts($item);
						?>
							<tr class="amazon">
								<th>
									<img src="<?php echo $smallimage; ?>" border="0" alt="" /><br />
									<a class="button" href="<?php echo $item->DetailPageURL; ?>" target="_blank">Amazonで見る</a>
								</th>
								<td>
									<strong><?php echo $atts['Title']; ?></strong><br />
									価格：<em class="price"><?php
										if($item->OfferSummary->LowestNewPrice->FormattedPrice){
											echo esc_html((string)$item->OfferSummary->LowestNewPrice->FormattedPrice);
										}else{
											echo 'N/A';
										}
									?></em><br />
<?php
										foreach(array('Actor', 'Artist', 'Author', 'Creator', 'Director', 'Manufacturer') as $key){
											if(isset($atts[$key])){
												echo $wp_hamazon_parser->atts_to_string($key).": ".$atts[$key]."<br />";
											}
										}
									?>
									<label>コード: <input type="text" size="40" value="[tmkm-amazon]<?php echo $item->ASIN; ?>[/tmkm-amazon]" onclick="this.select();" /></label>
									<br />
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
		}
		echo '</div>';
	}
}
