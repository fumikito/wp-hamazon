<?php

/**
 * 楽天Webサービスとコミュニケーションを取るツール
 * 
 * @since 2.0
 */
class WP_Hamazon_Service_Dmm extends WP_Hamazon_Service implements WP_Hamazon_Service_Required
{
	
	/**
	 * 検索タイトル
	 * @var string
	 */
	public $title = 'DMM アフィエイト';
	
	
	
	/**
	 * アイコンファイル名
	 * @var string
	 */
	protected $icon = 'dmm.png';

	

	/**
	 * アプリケーションID
	 * @var string
	 */
	private $api_id = '';
	
	
	
	/**
	 * アフィリエイトID
	 * @var string 
	 */
	private $affiliate_id = '';
	
	
	
	/**
	 * DMMアフィリエイトのエンドポイント
	 * @see https://affiliate.dmm.com/api/reference/com/all/
	 */
	const SEARCH_API = 'http://affiliate-api.dmm.com/';
	

	
	/**
	 * ページング
	 */
	const PER_PAGE = 30;
	
	
	
	/**
	 * オプションを設定する
	 */
	public function set_option() {
		$this->api_id = $this->get_option('dmm_api_id');
		$this->affiliate_id = $this->get_option('dmm_affiliate_id');
	}
	
	
	
	/**
	 * 有効か否か
	 * @return boolean
	 */
	public function is_valid() {
		return !empty($this->api_id) && !empty($this->affiliate_id);
	}
	
	
	
	/**
	 * ジャンルを配列にして返す
	 * @param int $parent ジャンルID
	 * @return array
	 */
	public function get_genre($parent = 0){
		$genre = get_transient('rakuten_genre_list_'.intval($parent));
		if(false === $genre){
			$genre = $this->make_request(self::GENRE_API, array(
				'genreId' => intval($parent)
			));
			if(is_wp_error($genre)){
				$genre = array();
			}else{
				$genre = $genre->children;
				set_transient('rakuten_genre_list_'.intval($parent), $genre, 60 * 60 * 1);
			}
		}
		return $genre;
	}
	
	
	
	/**
	 * 商品を検索する
	 * @param string $keyword
	 * @param int $genre_id
	 * @param int $page
	 * @return array
	 */
	public function search($keyword, $genre_id, $page, $item_code = false){
		$request = array(
			'genreId' => $genre_id,
			'hits' => self::PER_PAGE,
			'page' => $page
		);
		if(!empty($keyword)){
			$request['keyword'] = $keyword;
		}
		if($item_code){
			$request['itemCode'] = $item_code;
		}
		return $this->make_request(self::SEARCH_API, $request);
	}
	
	
	
	/**
	 * リクエストを行い、JSONを返す
	 * @param string $endpoint
	 * @param array $args
	 * @return \WP_Error|Object
	 */
	private function make_request($endpoint, $args = array()){
		$params = array_merge(array(
			'applicationId' => $this->app_id,
			'affiliateId' => $this->affiliate_id,
			'format' => 'json'
		), $args);
		$queries = array();
		foreach($params as $key => $val){
			$queries[] = rawurlencode($key).'='.rawurlencode($val);
		}
		$url = $endpoint.'?'.implode('&', $queries);
		// Make Request
		$timeout = 30;
		$context = stream_context_create(array(
			'http' => array(
				'timeout' => $timeout,
			),
		));
		$data = @file_get_contents($url, false, $context);
		if(!$data || is_null(($json = json_decode($data)))){
			return new WP_Error('error', 'リクエストがタイムアウトしました。');
		}else{
			return $json;
		}
	}
	
	
	
	/**
	 * ショートコードを登録
	 */
	public function set_shortcode() {
		$this->short_codes = array('dmm');
	}

	
	
	/**
	 * ショートコードを返す
	 * @param string $item_code 商品コード
	 * @return string
	 */
	public function get_shortcode($item_code){
		return sprintf('[dmm id="%s"][/dmm]', $item_code);
	}
	
	
	
	
	public function shortcode_rakuten($atts, $content = ''){

		$atts = shortcode_atts(array(
			'id' => '',
			'description' => '',
		), $atts);

		if($this->is_id($content)){
			// Old format [rakuten]id[/rakuten]
			$takuten_id = $content;
			$description = $atts['description'];
		}elseif($this->is_id($atts['id'])){
			$rakuten_id = $atts['id'];
			$description = !empty($content) ? $content : $atts['description'];
		}else{
			return '';
		}

		$item_code = '';
		if(!empty($rakuten_id)){
			$product = get_transient($rakuten_id);
			if(false === $product){
				$item = $this->search('', 0, 1, $rakuten_id);
				if(is_wp_error($item) || $item->count < 1){
					return '<p class="message error">商品情報を取得できませんでした。</p>';
				}else{
					$product = $item->Items[0]->Item;
					set_transient($rakuten_id, $product, 60*60*24);
				}
			}
			$price = number_format($product->itemPrice);
			$src = '1' == (string)$product->imageFlag
					? $product->mediumImageUrls[0]->imageUrl
					: plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg.png";

			$catch = nl2br(mb_substr($product->itemCaption, 0, 140, 'utf-8').'&hellip;');
			$desc = !empty($description) ? sprintf('<p class="additional-description">%s</p>', $description) : '';
			$out = <<<EOS
<div class="tmkm-amazon-view wp-hamazon-rakuten">
	<p class="tmkm-amazon-img"><a href="{$product->affiliateUrl}" target="_blank"><img src="{$src}" border="0" alt="{$product->itemName}" /></a></p>
	<p class="tmkm-amazon-title"><a href="{$product->affiliateUrl}" target="_blank">{$product->itemName}</a></p>
	<p class="shop"><span class="label">ショップ名</span><a href="{$product->shopUrl}"><em>{$product->shopName}</em></a></p>
	<p class="price"><span class="label">価格</span><em>&yen;{$price}</em></p>
	<p class="review-average"><span class="label">レビュー</span><em>平均{$product->reviewAverage}点</em></p>
	<p class="description">{$catch}</p>{$desc}
	<p class="vendor"><a href="http://webservice.rakuten.co.jp/">Supported by 楽天ウェブサービス</a></p>
</div>
EOS;
			$item_code = apply_filters('wp_hamazon_rakuten', $out, $product);
		}
		return $item_code;
	}

	/**
	 * Detect if given string is id
	 * @param string $id
	 * @return bool
	 */
	private function is_id($id){
		return (boolean)preg_match('/^[0-9a-zA-Z]+:[0-9a-zA-Z]+$/', $id);
	}
	
	/**
	 * 検索フォームを表示する
	 */
	public function show_form() {
		$genres = $this->get_genre();
		?>
		<form method="get" class="hamazon-search-form search-rakuten" action="<?php echo plugin_dir_url(dirname(__FILE__)); ?>/endpoint/dmm.php">
			<?php wp_nonce_field('rakuten_nonce'); ?>
			<p style="display: inline;"><a id="searchpagetop"><?php echo esc_html($this->title); ?></a></p>&nbsp;
			<select name="genreId">
				<option value="0"<?php if(!isset($_REQUEST['genreId']) || $_REQUEST['genreId'] == '0') ?>>すべてのジャンル</option>
				<?php if(!empty($genres)): ?>
					<?php foreach($genres as $genre): ?>
					<option value="<?php echo $genre->child->genreId; ?>"<?php if((isset($_GET['genreId']) && $_GET['genreId'] == $genre->child->genreId)) echo ' selected="selected"'; ?>>
						<?php echo esc_html($genre->child->genreName); ?>
					</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
			<input type="text" size="20" maxlength="50" value="<?php if(isset($_GET['keyword'])) echo esc_attr($_GET['keyword']); ?>" name="keyword" />&nbsp;
			<input class="button-primary" type="submit" style="cursor:pointer;" value="検索" />
		</form>
		<?php
	}
	
	
	/**
	 * 検索フォームを表示する
	 */
	public function show_results() {
		// Get pagination
		if( isset( $_GET['page'] ) ){
			$page_num = max(1, (int) $_GET['page']);
		}else{
			$page_num = 1;
		}
		// ジャンルIDを取得
		$genreId = (isset($_GET['genreId'])) ? intval($_GET['genreId']) : 0;
		if(isset($_GET['keyword'], $_GET['_wpnonce']) && !empty($_GET['keyword']) && wp_verify_nonce($_GET['_wpnonce'], 'rakuten_nonce')){
			echo '<div id="amazon-search-result">';
			$keyword = (string) $_GET['keyword'];
			$result = $this->search($keyword, $genreId, $page_num);
			if(is_wp_error($result)){
				echo '<div class="error"><p>検索結果を取得できませんでした。楽天のサーバに障害が起きているかもしれません。</p></div>';
			}else{
				$total_results = $result->count;
				$total_pages = $result->pageCount;
				if($total_pages == 0){
					printf('<div class="error"><p>「%s」の検索結果が見つかりませんでした。</p></div>', esc_html($keyword));
				}else{
					if($total_pages > 1){
						$pagination = $this->paginate($total_pages, $page_num, self::PER_PAGE, array(
							'genreId' => $genreId,
							'keyword' => $keyword,
							'_wpnonce' => wp_create_nonce('rakuten_nonce'),
						));
					}else{
						$pagination = '';
					}
					$counter = 0;
					?>
						<div class="result-desc clearfix">
							<h1>「<?php echo esc_html($keyword); ?>」の検索結果: <?php echo number_format((string)$total_results); ?>件</h1>
							<?php echo $pagination; ?>
						</div><!-- //.result-desc -->
						
						<table class="wp-hamazon-product-table">
							<?php foreach($result->Items as $item): $counter++; ?>
							<?php
								$src = $item->Item->imageFlag
										? $item->Item->mediumImageUrls[0]->imageUrl
										: plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg.png";
							?>
							<tr class="amazon">
								<th>
									<em>No. <?php echo number_format( ($page_num - 1) * self::PER_PAGE + $counter); ?></em><br />
									<img src="<?php echo esc_attr($src); ?>" border="0" alt="" /><br />
									<a class="button" href="<?php echo strval($item->Item->affiliateUrl); ?>" target="_blank">ストアで見る</a>
								</th>
								<td>
									<strong><?php echo esc_html($item->Item->itemName); ?></strong><br />
									価格：<em class="price">&yen;<?php echo number_format(strval($item->Item->itemPrice)); ?></em><br />
									ショップ：<?php printf('<a href="%s">%s</a>', $item->Item->shopUrl, strval($item->Item->shopName)); ?><br />
									レビュー： <?php echo $item->Item->reviewAverage; ?><br />
									<label>コード: <input class="hamazon-target" type="text" size="40" value="<?php echo esc_attr($this->get_shortcode($item->Item->itemCode)); ?>" onclick="this.select();" /></label>
									<a class="button-primary hamazon-insert" data-target=".hamazon-target" href="#">挿入</a><br />
									<span class="description">ショートコードを投稿本文に貼り付けてください</span>
								</td>
							</tr>
							<?php endforeach; ?>
						</table><!-- .wp-hamazon-product-table -->
						
						<div class="result-desc clearfix">
							<?php echo $pagination; ?>
						</div><!-- //.result-desc -->
					<?php 
				}
			}
			echo '</div>';
		}
	}	
}