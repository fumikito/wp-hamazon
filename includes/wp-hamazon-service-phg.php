<?php

/**
 * 楽天Webサービスとコミュニケーションを取るツール
 * 
 * @since 2.0
 */
class WP_Hamazon_Service_Phg extends WP_Hamazon_Service implements WP_Hamazon_Service_Required
{
	
	/**
	 * 検索タイトル
	 * @var string
	 */
	public $title = 'PHG iTunes アフィリエイト';
	
	
	
	/**
	 * アイコンファイル名
	 * @var string
	 */
	protected $icon = 'apple.png';

	

	/**
	 * トークン
	 * @var string
	 */
	private $token = '';
	

	
	/**
	 * PHG's endpoint
	 * @see http://www.apple.com/itunes/affiliates/resources/documentation/itunes-store-web-service-search-api.html
	 */
	const SEARCH_API = 'https://itunes.apple.com/search';

	/**
	 * PHG's endpoint
	 * @see http://www.apple.com/itunes/affiliates/resources/documentation/itunes-store-web-service-search-api.html
	 */
	const LOOKUP_API = 'https://itunes.apple.com/lookup';

	
	/**
	 * ページング
	 */
	const PER_PAGE = 100;
	
	
	
	/**
	 * オプションを設定する
	 */
	public function set_option() {
		$this->token = $this->get_option('phg_id');
	}
	
	
	
	/**
	 * 有効か否か
	 * @return boolean
	 */
	public function is_valid() {
		return !empty($this->token);
	}
	
	
	
	/**
	 * ジャンルを配列にして返す
	 * @return array
	 */
	public function get_genre(){
		return array(
			'movie' => '映画',
			'podcast' => 'Podcast',
			'music' => 'ミュージック',
			'musicVideo' => 'ミュージックビデオ',
			'audiobook' => 'オーディオブック',
			'software' => 'iOSアプリ',
			'macSoftware' => 'Macアプリ',
			'ebook' => '電子書籍',
		);
	}

	/**
	 * Get kind label
	 *
	 * @param Object $item
	 * @return string
	 */
	private function get_kind_label($item){
		switch($item->kind){
			case 'ebook':
				return '電子書籍';
				break;
			case 'song':
			case 'musicvideo':
			case 'music':
				return 'ミュージック';
				break;
			case 'music-video':
				return 'ミュージックビデオ';
				break;
			case 'software':
				return 'iOSアプリ';
				break;
			case 'iPadSoftware':
				return 'iPadアプリ';
				break;
			case 'mac-software':
				return 'Macアプリ';
				break;
			case 'podcast':
				return 'ポッドキャスト';
				break;
			default:
				return '映画';
				break;
		}
	}

	/**
	 * Returns artist name label
	 *
	 * @param Object $item
	 * @return string
	 */
	private function get_label($item){
		switch($item->kind){
			case 'ebook':
				return '作者';
				break;
			case 'song':
			case 'music':
			case 'music-video':
				return 'アーティスト';
				break;
			case 'software':
			case 'iPadSoftware':
			case 'mac-software':
				return '開発者';
				break;
			default:
				return '制作';
				break;
		}
	}
	
	/**
	 * 商品を検索する
	 * @param string $keyword
	 * @param int $media
	 * @return array
	 */
	public function search($keyword, $media, $country){
		$request = array(
			'media' => $media,
			'limit' => self::PER_PAGE,
			'country' => $country,
			'term' => $this->sanitize_term($keyword),
			'lang' => 'ja_jp',
			'explicit' => 'No',
		);
		return $this->make_request(self::SEARCH_API, $request);
	}

	/**
	 * Sanitize term string
	 *
	 * @param string $term
	 * @return string
	 */
	private function sanitize_term($term){
		return preg_replace('/\s+/u', '+', str_replace('　', ' ', $term));
	}

	/**
	 * Get specific object
	 *
	 * @param string $media
	 * @param int $id
	 * @return Object|WP_Error
	 */
	public function lookup($media, $id){
		$request = array(
			'media' => $media,
			'id' => $id,
			'lang' => 'ja_jp',
			'country' => 'JP',
		);
		$result = $this->make_request(self::LOOKUP_API, $request);
		if(is_wp_error($result)){
			return $result;
		}
		if($result->resultCount == 0){
			return new WP_Error(404, '該当する商品が見つかりませんでした');
		}
		return $result->results[0];
	}
	
	/**
	 * リクエストを行い、JSONを返す
	 * @param string $endpoint
	 * @param array $args
	 * @return \WP_Error|Object
	 */
	private function make_request($endpoint, $args = array()){
		$queries = array();
		foreach($args as $key => $val){
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
		$this->short_codes = array('phg');
	}

	
	
	/**
	 * ショートコードを返す
	 * @param string $item_code JSONオブジェクト
	 * @return string
	 */
	public function get_shortcode($item){
		return sprintf('[phg media="%s" id="%s"][/phg]', $item->kind, $item->trackId);
	}


	/**
	 * アフィリエイトリンクを返す
	 *
	 * @see http://www.apple.com/itunes/affiliates/resources/documentation/basic-affiliate-link-guidelines-for-the-phg-network-jp.html
	 * @param Object $item
	 * @return string
	 */
	public function affiliate_url($item){
		$glue = false !== strpos($item->trackViewUrl, '?') ? '&' : '?';
		return $item->trackViewUrl .$glue . 'at='.$this->token.'&ct='.(is_admin() ? 'wphamazon-admin' : 'wphamazon');
	}

	/**
	 * Export shortcode
	 *
	 * @param array $atts
	 * @param string $content
	 * @return mixed|string|void
	 */
	public function shortcode_phg($atts, $content = ''){
		$atts = shortcode_atts(array(
			'media' => 'all',
			'id' => false,
		), $atts);
		if(!$atts['id']){
			return '';
		}
		$product = get_transient('phg_'.$atts['media'].'_'.$atts['id']);
		if(false === $product){
			$product = $this->lookup($atts['media'], $atts['id']);
			if(is_wp_error($product) ){
				return $this->error_message();
			}else{
				set_transient('phg_'.$atts['media'].'_'.$atts['id'], $product, 60*60*24);
			}
		}
		if(isset($product->formattedPrice)){
			$price = $product->formattedPrice;
		}elseif(isset($product->trackPrice)){
			if(0 == $product->trackPrice){
				$price = '無料';
			}else{
				$price = number_format($product->trackPrice).' '.$product->currency;
			}
		}else{
			$price = '-';
		}
		if(isset($product->genres) && is_array($product->genres)){
			$genres = array();
			foreach($product->genres as $g){
				$genres[] = $g;
			}
			$genres = implode(', ', $genres);
		}elseif(isset($product->primaryGenreName)){
			$genres = $product->primaryGenreName;
		}else{
			$genres = '-';
		}
		$src = plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg.png";
		for($i = 512; $i >= 60; $i--){
			$prop = 'artworkUrl'.$i;
			if(isset($product->{$prop})){
				$src = $product->{$prop};
				break;
			}
		}
		$url = $this->affiliate_url($product);
		$desc = empty($content) ? '' : sprintf('<p class="additional-description">%s</p>', $content);
		$label = $this->get_label($product);
		$date = date(get_option('date_format'), strtotime($product->releaseDate));
		$icon_src = plugin_dir_url(dirname(__FILE__)).'assets/img/appstore.png';
		$kind = $this->get_kind_label($product);
		$out = <<<EOS
<div class="tmkm-amazon-view wp-hamazon-phg">
	<p class="tmkm-amazon-img"><a href="{$url}" target="_blank"><img src="{$src}" border="0" alt="{$product->trackName}" /></a></p>
	<p class="tmkm-amazon-title"><a href="{$url}" target="_blank">{$product->trackName}</a></p>
	<p class="kind"><span class="label">種別</span><em>{$kind}</em></p>
	<p class="artist"><span class="label">{$label}</span><em>{$product->artistName}</em></p>
	<p class="price"><span class="label">価格</span><em>{$price}</em></p>
	<p class="release"><span class="label">リリース日</span><em>{$date}</em></p>
	<p class="genre"><span class="label">ジャンル</span><em>{$genres}</em></p>
	<p><a href="{$url}"><img src="{$icon_src}" alt="AppStoreでダウンロード" width="135" height="40" /></a></p>{$desc}
	<p class="vendor"><a href="http://www.apple.com/itunes/affiliates/resources/">Supported by Performance Horizon Group</a></p>
</div>
EOS;
		$item_code = apply_filters('wp_hamazon_phg', $out, $product, $url);
		return $item_code;
	}
	
	/**
	 * 検索フォームを表示する
	 */
	public function show_form() {
		$genres = $this->get_genre();
		?>
		<form method="get" class="hamazon-search-form search-phg" action="<?php echo plugin_dir_url(dirname(__FILE__)); ?>/endpoint/phg.php">
			<?php wp_nonce_field('phg_nonce', '_wpnonce', false); ?>
			<p style="display: inline;"><a id="searchpagetop"><?php echo esc_html($this->title); ?></a></p>&nbsp;
			<select name="media">
				<option value="all"<?php selected(!isset($_REQUEST['media']) || $_REQUEST['media'] == 'all'); ?>>すべてのジャンル</option>
				<?php if(!empty($genres)): ?>
					<?php foreach($genres as $key => $value): ?>
					<option value="<?php echo $key; ?>"<?php if((isset($_GET['media']) && $_GET['media'] == $key)) echo ' selected="selected"'; ?>>
						<?php echo esc_html($value); ?>
					</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
			<select name="country">
				<?php foreach(array(
					'JP' => '日本',
					'US' => '合衆国'
				) as $key => $val):?>
				<option value="<?php echo $key ?>"<?php selected(isset($_REQUEST['country']) && $key == $_REQUEST['country']); ?>><?php echo $val ?></option>
				<?php endforeach; ?>
			</select>
			<input type="text" size="20" maxlength="50" value="<?php if(isset($_GET['term'])) echo esc_attr($_GET['term']); ?>" name="term" />&nbsp;
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
		$media = (isset($_GET['media'])) ? strval($_GET['media']) : 'all';
		if(isset($_GET['term'], $_GET['_wpnonce']) && !empty($_GET['term']) && wp_verify_nonce($_GET['_wpnonce'], 'phg_nonce')){

			echo '<div id="amazon-search-result">';
			$keyword = (string) $_GET['term'];
			$result = $this->search($keyword, $media, $_REQUEST['country']);
			if(is_wp_error($result)){
				printf('<div class="error"><p>%s</p></div>', $result->get_error_message());
			}else{
				if($result->resultCount == 0){
					printf('<div class="error"><p>「%s」の検索結果が見つかりませんでした。</p></div>', esc_html($keyword));
				}else{
					$pagination = '';
					$counter = 0;
					?>
						<div class="result-desc clearfix">
							<h1>「<?php echo esc_html($keyword); ?>」の検索結果: <?php echo number_format((string)$result->resultCount); ?>件</h1>
							<?php echo $pagination; ?>
						</div><!-- //.result-desc -->
						
						<table class="wp-hamazon-product-table">
							<?php foreach($result->results as $item): $counter++; ?>
							<?php
								if($item->artworkUrl100){
									$src = $item->artworkUrl100;
								}elseif($item->artworkUrl60){
									$src = $item->artworkUrl60;
								}else{
									$src = plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg.png";
								}
							?>
							<tr class="amazon">
								<th>
									<em>No. <?php echo number_format(  $counter); ?></em><br />
									<img src="<?php echo esc_attr($src); ?>" border="0" alt="" /><br />
									<a class="button" href="<?php echo $this->affiliate_url($item); ?>" target="_blank">iTunesで見る</a>
								</th>
								<td>
									<strong><?php echo esc_html($item->trackName); ?></strong><br />
									価格：<em class="price"><?php echo $item->formattedPrice; ?> <?php  ?></em><br />
									<label>コード: <input type="text" class="hamazon-target" size="40" value="<?php echo esc_attr($this->get_shortcode($item)); ?>" onclick="this.select();" /></label>
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