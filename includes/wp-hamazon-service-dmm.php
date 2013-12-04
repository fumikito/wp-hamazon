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
	public $title = 'DMM アフィリエイト';
	
	
	
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
	const PER_PAGE = 20;
	
	
	
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
	public function get_genre(){
		return array(
			'lod' => array(
				'akb48' => 'AKB48',
				'ske48' => 'SKE48',
				'nmb48' => 'NMB48',
				'hkt48' => 'HKT48',
			),
			'digital' => array(
				'bandai' => 'バンダイch',
				'anime' => 'アニメ',
				'video' => 'バラエティ',
				'idol' => 'アイドル',
				'cinema' => '映画・ドラマ',
				'fight' => '格闘技',
			),
			'a_digital' => array(
				'videoa' => 'アダルトビデオ',
				'videoc' => '素人',
				'nikkatsu' => '成人映画',
				'photo' => '電子写真集',
				'anime' => 'アニメ',
			),
			'monthly' => array(
				'toei' => '東映',
				'animate' => 'アニメ',
				'idol' => 'アイドル',
				'cinepara' => 'シネマパラダイス',
				'dgc' => 'ギャルコレ',
				'fleague' => 'Fリーグ',
			),
			'a_monthly' => array(
				'shirouto' => '素人ガールズコレクション',
				'nikkatsu' => 'ピンク映画',
				'paradisetv' => 'パラダイステレビ',
				'animech' => 'アダルトアニメ',
				'dream' => 'ドリーム',
				'avstation' => 'AVステーション',
				'playgirl' => 'プレイガール',
				'alice' => 'アリス',
				'crystal' => 'クリスタル',
				'hmp' => 'h.m.p',
				'waap' => 'Waap',
				'momotarobb' => '桃太郎BB',
				'moodyz' => 'MOODYZ',
				'prestige' => 'プレステージ',
				'jukujo' => '熟女チャンネル',
				'sod' => 'ソフト・オン・デマンド',
				'mania' => 'マニア',
				's1' => 'エスワン ナンバーワンスタイル',
				'kmp' => 'KMP',
			),
			'digital_book' => array(
				'comic' => 'コミック',
				'novel' => '小説',
				'magazine' => '雑誌',
				'photo' => '写真集',
				'audio' => 'オーディオブック',
				'movie' => '動画',
			),
			'pcsoft' => array(
				'pcgame' => 'PCゲーム',
				'pcsoft' => 'ソフトウェア',
			),
			'mono' => array(
				'dvd' => 'DVD',
				'cd' => 'CD',
				'book' => '本・コミック',
				'game' => 'ゲーム',
				'hobby' => 'ホビー',
			),
			'a_mono' => array(
				'dvd' => 'DVD',
				'goods' => '大人のおもちゃ',
				'anime' => 'アニメ',
				'pcgame' => '美少女ゲーム',
				'book' => 'ブック',
				'doujin' => '同人',
			),
			'rental' => array(
				'rental_dvd' => '月額DVDレンタル',
				'ppr_dvd' => '単品DVDレンタル',
				'rental_cd' => '月額CDレンタル',
				'ppr_cd' => '単品CDレンタル',
				'comic' => 'コミック',
			),
			'a_rental' => array(
				'rental_dvd' => '月額DVDレンタル',
				'ppr_dvd' => '単品DVDレンタル',
			),
			'nandemo' => array(
				'fashion_ladies' => 'レディース',
				'fashion_mens' => 'メンズ',
				'rental_iroiro' => 'いろいろレンタル',
			),
			'a_ppm' => array(
				'video' => 'ビデオ',
				'videoc' => '素人',
			),
			'a_pcgame' => array(
				'pcgame' => '美少女ゲーム'
			),
			'a_doujin' => array(
				'doujin' => '同人',
			),
			'a_book' => array(
				'book' => '電子コミック'
			),
		);
	}

	/**
	 * Returns sevice name
	 *
	 * @param string $key
	 * @return string
	 */
	private function get_label($key){
		switch(str_replace('a_', '', $key)){
			case 'lod':
				$string = 'AKB48グループ';
				break;
			case 'mono':
				$string = '通販';
				break;
			case 'rental':
				$string = 'CD/DVDレンタル';
				break;
			case 'digital_book':
				$string = '電子書籍';
				break;
			case 'nandemo':
				$string = 'いろいろレンタル';
				break;
			case 'pcsoft':
				$string = 'PCソフト';
				break;
			case 'ppm':
				$string = '1円動画';
				break;
			case 'pcgame':
				$string = '美少女ゲーム';
				break;
			case 'doujin':
				$string = '同人';
				break;
			case 'book':
				$string = '電子コミック';
				break;
			case 'monthly':
				$string = '月額動画';
				break;
			case 'digital':
				$string = '動画';
				break;
		}
		if(false !== strpos($key, 'a_')){
			$string .= '（アダルト）';
		}
		return $string;
	}
	
	/**
	 * 商品を検索する
	 * @param string $keyword
	 * @param int $genre_id
	 * @param int $page
	 * @return array
	 */
	public function search($keyword, $service, $floor, $page = 1, $args = array()){
		$offset = max(0, ($page - 1)) * 20;
		$request = array(
			'floor' => $floor,
			'offset' => ($offset + 1),
			'keyword' => mb_convert_encoding($keyword, 'EUC-JP', 'UTF-8'),
		);
		if(!empty($args)){
			$request = array_merge($request, $args);
		}
		return $this->make_request(self::SEARCH_API, $service, $request);
	}
	

	/**
	 * リクエストを行い、XMLを返す
	 *
	 * @param string $endpoint
	 * @param string $service
	 * @param array $args
	 * @return \WP_Error|Object
	 */
	private function make_request($endpoint, $service, $args = array()){
		$params = array_merge(array(
			'api_id' => $this->api_id,
			'affiliate_id' => $this->affiliate_id,
			'operation' => 'ItemList',
			'version' => '2.00',
			'timestamp' => current_time('mysql'),
			'hits' => 20,
			'sort' => 'rank',
		), $args);
		if(false !== strpos($service, 'a_')){
			$params['site'] = 'DMM.co.jp';
			$params['service'] = str_replace('a_', '', $service);
		}else{
			$params['site'] = 'DMM.com';
			$params['service'] = $service;
		}
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
		try{
			$data = @file_get_contents($url, false, $context);
			if(!$data){
				throw new Exception('リクエストがタイムアウトしました。');
			}
			$xml = new SimpleXMLElement($data);
			return $xml;
		}catch (Exception $e){
			return new WP_Error(500, $e->getMessage());
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
	public function get_shortcode($id, $service, $floor){
		return sprintf('[dmm service="%s" floor="%s" id="%s"][/dmm]', $service, $floor, $id);
	}


	/**
	 * Do shortcode
	 *
	 * @param $atts
	 * @param string $content
	 * @return mixed|string|void
	 */
	public function shortcode_dmm($atts, $content = ''){

		$atts = shortcode_atts(array(
			'service' => '',
			'floor' => '',
			'id' => '',
		), $atts);

		$genres = $this->get_genre();
		// check if service exists
		if(!isset($genres[$atts['service']], $genres[$atts['service']][$atts['floor']])){
			return '';
		}

		// Get data
		$item_code = '';
		if(!empty($atts['id'])){
			$key = sprintf('%s_%s_%s', $atts['service'], $atts['floor'], $atts['id']);
			$product = get_transient($key);
			if(false === $product){
				$item = $this->search($atts['id'], $atts['service'], $atts['floor'], 1,  array('hits' => 1));
				if(is_wp_error($item) || '1' != (string)$item->result->result_count){
					return $this->error_message();
				}else{
					$product = $item->result->items->item;
					set_transient($key, $product->asXML(), 60*60*24);
				}
			}else{
				$product = new SimpleXMLElement($product);
			}
			$src = (string) $product->imageURL->large;
			if(!$src){
				$src = plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg.png";
			}
			$price = strval($product->prices->price);
			if( is_numeric($price) ){
				$price = '&yen;'.number_format($price);
			}
			$maker = strval($product->iteminfo->label->name);
			if(empty($maker)){
				$maker = strval($product->iteminfo->maker->name);
			}
			$keywords = array();
			foreach($product->iteminfo->keyword as $k){
				$keywords[] = (string)$k->name;
			}
			if(empty($keywords)){
				$keywords = '';
			}else{
				$keywords = sprintf('<p class="keywords"><span class="label">キーワード</span><em>%s</em></p>', implode(', ', $keywords));
			}
			$desc = !empty($content) ? sprintf('<p class="additional-description">%s</p>', $content) : '';
			$out = <<<EOS
<div class="tmkm-amazon-view wp-hamazon-rakuten">
	<p class="tmkm-amazon-img"><a href="{$product->affiliateURL}" target="_blank"><img src="{$src}" border="0" alt="{$product->title}" /></a></p>
	<p class="tmkm-amazon-title"><a href="{$product->affiliateURL}" target="_blank">{$product->title}</a></p>
	<p class="category"><span class="label">カテゴリ</span><em>{$product->category_name}</em></p>
	<p class="shop"><span class="label">制作</span><em>{$maker}</em></p>
	<p class="price"><span class="label">価格</span><em>{$price}</em></p>
	{$keywords}{$desc}
	<p class="vendor"><a href="https://affiliate.dmm.com/">Supported by DMMアフィリエイト</a></p>
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
		$current_service = isset($_REQUEST['service']) ? $_REQUEST['service'] : '';
		?>
		<form method="get" class="hamazon-search-form search-dmm" action="<?php echo plugin_dir_url(dirname(__FILE__)); ?>/endpoint/dmm.php">
			<?php wp_nonce_field('dmm_nonce', '_wpnonce', false); ?>
			<p style="display: inline;"><a id="searchpagetop"><?php echo esc_html($this->title); ?></a></p>&nbsp;
			<select name="service">
				<option value="">ジャンルを選択</option>
				<?php foreach($genres as $key => $val): ?>
				<option value="<?php echo $key; ?>"<?php selected($current_service == $key); ?>><?php echo $this->get_label($key); ?></option>
				<?php endforeach; ?>
			</select>
			<?php foreach($genres as $key => $value): ?>
				<select name="floor[<?php echo $key; ?>]"<?php if($current_service == $key) echo ' class="active"';; ?>>
					<?php foreach( $value as $k => $v): ?>
					<option value="<?php echo $k; ?>"><?php echo esc_html($v); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endforeach; ?>
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
		if(isset($_GET['keyword'], $_GET['_wpnonce']) && !empty($_GET['keyword']) && wp_verify_nonce($_GET['_wpnonce'], 'dmm_nonce')){
			echo '<div id="amazon-search-result">';
			$keyword = (string) $_GET['keyword'];
			$service = (string) $_GET['service'];
			$floor = (string) $_GET['floor'][$service];
			$result = $this->search($keyword, $service, $floor, $page_num);
			if(is_wp_error($result)){
				echo '<div class="error"><p>検索結果を取得できませんでした。楽天のサーバに障害が起きているかもしれません。</p></div>';
			}else{
				$total_results = (int)$result->result->total_count;
				if($total_results < 1){
					printf('<div class="error"><p>「%s」の検索結果が見つかりませんでした。</p></div>', esc_html($keyword));
				}else{
					$total_pages = floor($total_results / 20);
					if($total_pages > 1){
						$pagination = $this->paginate($total_results, $page_num, self::PER_PAGE, array(
							'service' => $service,
							"floor[$service]" => $floor,
							'keyword' => $keyword,
							'_wpnonce' => wp_create_nonce('dmm_nonce'),
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
							<?php foreach($result->result->items->item as $item): $counter++; ?>
							<?php
//								var_dump($item);continue;
								$src = (string) $item->imageURL->list;
								if(!$src){
									$src = plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg.png";
								}
								$price = strval($item->prices->price);
								if( is_numeric($price) ){
									$price = '&yen;'.number_format($price);
								}
								$maker = strval($item->iteminfo->label->name);
								if(empty($maker)){
									$maker = strval($item->iteminfo->maker->name);
								}
							?>
							<tr class="amazon">
								<th>
									<em>No. <?php echo number_format( ($page_num - 1) * self::PER_PAGE + $counter); ?></em><br />
									<img src="<?php echo esc_attr($src); ?>" border="0" alt="" /><br />
									<a class="button" href="<?php echo strval($item->affiliateURL); ?>" target="_blank">ストアで見る</a>
								</th>
								<td>
									<strong><?php echo esc_html($item->title); ?></strong><br />
									価格：<em class="price"><?php echo $price; ?></em><br />
									制作：<?php echo $maker; ?><br />
									発売日: <?php echo mysql2date(get_option('date_format'), $item->date); ?><br />
									カテゴリ： <?php echo (string)$item->category_name; ?><br />
									<label>コード: <input class="hamazon-target" type="text" size="40" value="<?php echo esc_attr($this->get_shortcode((string)$item->product_id, $service, $floor)); ?>" onclick="this.select();" /></label>
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