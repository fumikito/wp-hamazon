<?php

/******************************************************************************
 * THIS FILE IS CALLED ONLY.
 *****************************************************************************/
require_once dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."wp-load.php";

//If user has appropriate authenticity
if(!current_user_can('edit_posts')){
	die();
}

$nonce = wp_create_nonce('amazon_search');

/* @var array $hamazon_settings */
/* @var WpTmkmAmazonAdmin $wpTmkmAmazonAdmin */
/* @var WpTmkmAmazonParser $wpTmkmAmazonParser */

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>wp-hamazon( WordPress Plugin ) Amazon Search</title>
<link rel="stylesheet" href="hamazon-search.css" type="text/css" />
<script type="text/javascript" src="<?php echo trailingslashit(home_url())."/".$wp_scripts->registered['jquery']->src; ?>"></script>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		var height = $('body').height() + parseInt($('body').css('padding-top')) + parseInt($('body').css('padding-bottom'));
		$("#tmkm-iframe", top.document).height(height);
	});
})(jQuery);
</script>
</head>
<body>

<form method="GET">
<p style="display: inline;"><a id="searchpagetop">Amazon 検索</a></p>&nbsp;
<select name="SearchIndex">
	<?php foreach($wp_hamazon_parser->searchIndex as $k => $v): ?>
	<option value="<?php echo $k; ?>"<?php if(isset($_GET['SearchIndex']) && $_GET['SearchIndex'] == $k) echo ' selected="selected"'; ?>>
		<?php echo $v; ?>
	</option>
	<?php endforeach; ?>
</select>
<input type="text" size="20" maxlength="50" value="<?php if(isset($_GET['keyword'])) echo esc_attr($_GET['keyword']); ?>" name="keyword" />&nbsp;
<input type="submit" style="cursor:pointer;" value="検索" />
<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />
</form><?php
/******************************************************************************
 * Check Setting
 *****************************************************************************/
if(empty($hamazon_settings['associatesid']) || empty($hamazon_settings['accessKey']) || empty($hamazon_settings['secretKey'])):
	global $user_level;
?>
	<div class="error">
		<p>
			アソシエイトID、アクセスID、シークレットアクセスIDが正しく入力されていません。
			<?php if($user_level >= 8): ?>
			<a target="_top" href="<?php echo admin_url('options-general.php?page='.$hamazon_admin->slug); ?>">設定ページ</a>より入力してください。
			<?php else: ?>
			管理者に報告してください。
			<?php endif; ?>
		</p>
	</div>	
<?php
else:
/******************************************************************************
 * Ready for communicate with Amazon
 *****************************************************************************/
if( isset( $_GET['page'] ) ){
	$PageNum = (int) $_GET['page'];
	if( 0==$PageNum ){
		$PageNum = 1;
	}
}else{
	$PageNum = 1;
}

//Start Searching
if(isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'amazon_search')){
	if( isset($_GET['keyword'] ) && !empty( $_GET['keyword'] ) ){
		$keyword = (string) $_GET['keyword'];
		$searchindex = !empty( $_GET['SearchIndex'] ) ? $_GET['SearchIndex'] : 'Blended';
		$result = $wp_hamazon_parser->search_with($keyword, $PageNum, $searchindex);
		$display_keyword = esc_html($keyword);

		if(is_wp_error($result) ){  // Amazon function was returned false, so AWS is down
			?>
			<p>アマゾンのサーバでエラーが起こっているかもしれません。一度ページを再読み込みしてみてください。</p>
			<?php
		}else{ // Amazon function returned XML data
			if($result->Items->Request->Errors){
				echo "<p>{$result->Items->Request->Errors->Error->Message}</p>";
			}else{
				// results were found, so display the products
				$totalresults = $result->Items->TotalResults;
				$totalpages =  $result->Items->TotalPages;

				if( $totalresults == 0 ){ // no result was found
					echo '<h1>「' . $display_keyword . '」の検索結果が見つかりませんでした。</h1>' . "\n";
				} else {
					// Pagenation
					if( $totalpages > 1 ) {
						$prevpage = $PageNum - 1;
						$nextpage = $PageNum + 1;
						$prevlink = '<li><a href="?SearchIndex=' .$searchindex. '&keyword=' .esc_attr($keyword). '&_wpnonce='.$nonce . '&page=' .$prevpage. '" class="pagenation">前のページ</a></li>' . "\n";
						$nextlink = '<li><a href="?SearchIndex=' .$searchindex. '&keyword=' .esc_attr($keyword). '&_wpnonce='.$nonce . '&page=' .$nextpage. '" class="pagenation">次のページ</a></li>' . "\n"; 
						if( $PageNum == 1 ) {
							$prevlink = '';
						} elseif( $PageNum == $totalpages ) {
							$nextlink = '';
						}
						$pagelink = '<ul class="wp-tmkm-amazon-search-guide">' . $prevlink . '<li> &laquo;　' . $PageNum . ' / ' . $totalpages . 'ページ　&raquo;</li>' . $nextlink . '</ul>' . "\n";
					}
					echo $pagelink;
					// results were found
					$length = count($result->Items->Item);
					echo '<h1>「' .$display_keyword. '」の検索結果： ' . $PageNum. '/' .$totalpages . ' ページの ' .$length . ' 件を表示しています</h1>' . "\n"
						 .'<p>ご希望の商品のコードをコピーしてください。投稿編集画面に貼り付けてください。</p>';
					for($i = 0; $i < $length; $i++) {
						$item = $result->Items->Item[$i];
						$smallimage = $wp_hamazon_parser->get_image_src($item,'small');
						$atts = $wp_hamazon_parser->get_atts($item);
						?>
							<div id="amazon-search-result">
								<h2><?php echo $i + 1; ?></h2>
								<p>
								<img src="<?php echo $smallimage; ?>" border="0" alt="" />
								<strong><?php echo $atts['Title']; ?></strong><br />
								<label>コード: <input type="text" size="50" value="[tmkm-amazon]<?php echo $item->ASIN; ?>[/tmkm-amazon]" onclick="this.select();" /></label>
								<br />
								<?php
									foreach(array('Actor', 'Artist', 'Author', 'Creator', 'Director', 'Manufacturer') as $key){
										if(isset($atts[$key])){
											echo $wp_hamazon_parser->atts_to_string($key).": ".$atts[$key]."<br />";
										}
									}
								?>
								<a href="<?php echo $item->DetailPageURL; ?>" target="_blank">Amazon で詳細をみる</a>
								</p>
								<br style="clear:both;" />
							</div>
						<?php
					}
					echo $pagelink;
				}
			}
		}
		echo '<p><a href="#searchpagetop">↑ このページの TOP へ</a></p>' . "\n";

	} else {

		echo "<p>キーワードが指定されていません</p>" . "\n";
		echo '<a href="wp-tmkm-amazon-search.php">Back To Search</p>' . "\n";

	}


}

endif;
?>
</body></html>