<?php


/**
 * リンクシェアとコミュニケーションを取るツール
 * 
 * @since 2.0
 */
class WP_Hamazon_Service_Linkshare extends WP_Hamazon_Service implements WP_Hamazon_Service_Required
{
	
	/**
	 * 参加企業の一覧を取得するインターフェース
	 */
	const COMPANY_LIST = 'http://findadvertisers.linksynergy.com/merchantsearch';
	
	
	
	/**
	 * 検索用エンドポイント
	 */
	const SEARCH_LIST = 'http://productsearch.linksynergy.com/productsearch';
	
	
	
	/**
	 * 1ページあたりの商品数
	 */
	const PER_PAGE = 30;
	
	
	
	/**
	 * アフィリエイトトークン
	 * @var string
	 */
	private $token = '';
	
	
	
	/**
	 * 商品検索ページで表示されるタイトル
	 * @var string
	 */
	public $title = '楽天リンクシェア商品検索';
	
	
	
	/**
	 * アイコンファイル名
	 * @var string
	 */
	protected $icon = 'linkshare.gif';
	
	
	
	/**
	 * オプションを設定する
	 * @global array $hamazon_settings
	 */
	public function set_option() {
		global $hamazon_settings;
		$this->token = $hamazon_settings['linkshare_token'];
	}
	
	
	
	/**
	 * ショートコードを設定
	 */
	public function set_shortcode() {
		$this->short_codes = array('hamazon_linkshare');
	}
	
	/**
	 * 登録したトークンを返す
	 * @global array $hamazon_settings
	 * @return string
	 */
	public function get_token(){
		return $this->token;
	}
	
	/**
	 * トークンが有効か否か
	 * @return boolean
	 */
	public function is_valid(){
		return !empty($this->token);
	}
	
	/**
	 * リンクシェアの提携企業リストを取得する
	 * @return array
	 */
	public function get_company_list($cash_update = true){
		//規定値
		$cash_key = 'linkshare_company_list';
		$life_time_key = 'linkshare_cash_lifetime';
		$liftime = 1800;
		//保存された値を取得
		$list = get_option($cash_key, array());
		$saved = get_option($life_time_key, 0);
		if(current_time('timestamp') > $saved + $liftime && $cash_update){
			$list = array();
			$result = $this->get_request(self::COMPANY_LIST, array('token' => $this->get_token()));
			if(is_wp_error($result)){
				return $result;
			}elseif(isset($result->Errors)){
				return new WP_Error('error', strval($result->Errors->ErrorText));
			}elseif(isset($result->midlist)){
				foreach($result->midlist->merchant as $merchant){
					$list[(string)$merchant->mid] = (string)$merchant->merchantname;
				}
				//値を保存
				update_option($cash_key, $list);
				update_option($life_time_key, current_time('timestamp'));
			}
		}
		return $list;
	}
	
	/**
	 * 検索結果を取得する
	 * @param string $keyword
	 * @param string $mid
	 * @param type $page
	 * @return \WP_Error
	 */
	public function search($keyword, $mid, $page){
		$result = $this->get_request(self::SEARCH_LIST, array(
			'token' => $this->get_token(),
			'mid' => intval($mid),
			'keyword' => $keyword,
			'max' => self::PER_PAGE,
			'pagenumber' => $page
		));
		return $result;
	}
	
	/**
	 * リクエストを送信する
	 * @param string $endpoint
	 * @param string $args
	 * @return \WP_Error|SimpleXMLElement
	 */
	private function get_request($endpoint, $args = array()){
		if(!empty($args)){
			$endpoint .= '?';
		}
		$query_strings = array();
		foreach($args as $key => $val){
			$query_strings[] = $key.'='.rawurlencode($val);
		}
		if(!empty($query_strings)){
			$endpoint .= implode('&', $query_strings);
		}
		// Make Request
		$timeout = 30;
		$context = stream_context_create(array(
			'http' => array(
				'timeout' => $timeout,
			),
		));
		$data = @file_get_contents($endpoint, false, $context);
		if(!$data){
			return new WP_Error('error', 'リクエストがタイムアウトしました。');
		}else{
			return simplexml_load_string($data);
		}
	}
	
	/**
	 * 楽天用のDOM要素を出力する
	 * 
	 * @param SimpleXMLElement $item
	 * @return string
	 */
	public function shortcode_hamazon_linkshare($atts, $content = ''){
		extract(shortcode_atts(array(
			'title' => '',
			'url' => '',
			'src' => '',
			'price' => 0,
			'cat' => ''
		), $atts));
		$price = number_format($price);
		$cat = implode(' &gt; ', explode(',', $cat));
		$desc = empty($content) ? '' : sprintf('<p class="additional-description">%s</p>', $content);
		$template = <<<EOS
<div class="tmkm-amazon-view wp-hamazon-linkshare">
	<p class="tmkm-amazon-img"><a href="{$url}" target="_blank"><img src="{$src}" border="0" alt="{$title}" /></a></p>
	<p class="tmkm-amazon-title"><a href="{$url}" target="_blank">{$title}</a></p>
	<p class="price"><span class="label">価格</span><em>&yen;{$price}</em></p>
	<p><span class="label">カテゴリー</span><em>{$cat}</em></p>{$desc}
	<p class="vendor"><a href="http://www.linkshare.ne.jp">Supported by リンクシェア</a></p>
</div>
EOS;
		return apply_filters('wp_hamazon_linkshare', $template, $atts);
	}
	
	/**
	 * リンクシェア用のショートコードを返す
	 * 
	 * @param SimpleXMLElement $item
	 * @return string
	 */
	public function get_shortcode($item){
		$title = strval($item->productname);
		$url = strval($item->linkurl);
		$src = strval($item->imageurl);
		$price = strval($item->price);
		$cat = array();
		if($item->category->primary){
			$cat[] = strval($item->category->primary);
		}
		if($item->category->secondary){
			$cat[] = strval($item->category->secondary);
		}
		return sprintf('[hamazon_linkshare title="%s" url="%s" src="%s" price="%s" cat="%s"][/hamazon_linkshare]',
			$title, $url, $src, $price, implode(',', $cat));
	}
	
	

	/**
	 * 検索フォームを表示する
	 */
	public function show_form() {
		$companies = $this->get_company_list();
		if(is_wp_error($companies)){
			wp_die($companies->get_error_message(), get_status_header_desc(500), array(
				'response' => 500,
				'back_link' => false
			));
		}
		?>
		<form method="get" class="hamazon-search-form search-linkshare" action="<?php echo plugin_dir_url(dirname(__FILE__)); ?>/endpoint/linkshare.php">
			<?php wp_nonce_field('linkshare_nonce'); ?>
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
	}
	
	
	
	/**
	 * 検索結果を表示する
	 */
	public function show_results() {
		// Get pagination
		if( isset( $_GET['page'] ) ){
			$page_num = max(1, (int) $_GET['page']);
		}else{
			$page_num = 1;
		}
		//Start Searching
		if(isset($_GET['keyword'], $_GET['mid'], $_GET['_wpnonce']) && !empty( $_GET['keyword']) && wp_verify_nonce($_GET['_wpnonce'], 'linkshare_nonce')){
			echo '<div id="amazon-search-result">';
			$keyword = (string) $_GET['keyword'];
			$result = $this->search($keyword, $_GET['mid'], $page_num);
			if(is_wp_error($result) ){
				// Amazon function was returned false, so AWS is down
				echo '<div class="error"><p>検索結果を取得できませんでした。リンクシェアのサーバに障害が起きているかもしれません。また、楽天市場のアフィリエイト検索はうまく動きません。</p></div>';
			}else{
				// results were found, so display the products
				$total_results = intval($result->TotalMatches);
				$total_pages =  intval($result->TotalPages);

				if( $total_results == 0 ){ // no result was found
					printf('<div class="error"><p>「%s」の検索結果が見つかりませんでした。</p></div>', esc_html($keyword));
				} else {
					// Pagenation
					if( $total_pages > 1 ) {
						$pagination = $this->paginate($total_results, $page_num, self::PER_PAGE, array(
							'token' => $this->get_token(),
							'mid' => intval($_REQUEST['mid']),
							'keyword' => $keyword,
							'_wpnonce' => wp_create_nonce('linkshare_nonce'),
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
							<?php foreach($result->item as $item): $counter++; ?>
							<tr class="amazon">
								<th>
									<em>No. <?php echo number_format( ($page_num - 1) * self::PER_PAGE + $counter); ?></em><br />
									<img src="<?php echo strval($item->imageurl); ?>" border="0" alt="" /><br />
									<a class="button" href="<?php echo strval($item->linkurl); ?>" target="_blank">ストアで見る</a>
								</th>
								<td>
									<strong><?php echo strval($item->productname); ?></strong><br />
									価格：<em class="price">&yen;<?php echo number_format(strval($item->price)); ?></em><br />
									ストア：<?php echo strval($item->merchantname); ?><br />
									カテゴリー： <?php echo strval($item->category->primary); ?> &gt; <?php echo strval($item->category->secondary); ?><br />
									<textarea rows="3" class="hamazon-target" onclick="this.select();"><?php echo ($this->get_shortcode($item)); ?></textarea><br />
									<a class="button-primary hamazon-insert" data-target=".hamazon-target" href="#">挿入</a>
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