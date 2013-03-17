<?php


/**
 * リンクシェアとコミュニケーションを取るツール
 * 
 * @since 2.0
 */
class WP_Hamazon_Linkshare{
	
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
	
	public function __construct() {
		add_shortcode('hamazon_linkshare', array($this, 'do_shortcode'));
	}
	
	/**
	 * 登録したトークンを返す
	 * @global array $hamazon_settings
	 * @return string
	 */
	public function get_token(){
		global $hamazon_settings;
		return $hamazon_settings['linkshare_token'];
	}
	
	/**
	 * トークンが有効か否か
	 * @return boolean
	 */
	public function is_valid(){
		$token = $this->get_token();
		return !empty($token);
	}
	
	/**
	 * リンクシェアの提携企業リストを取得する
	 * @return array
	 */
	public function get_company_list(){
		$cash_key = 'linkshare_company_list';
		$liftime = 1800;
		$list = get_transient($cash_key);
		if(false === $list){
			$list = array();
			$result = $this->get_request(self::COMPANY_LIST, array('token' => $this->get_token()));
			if(isset($result->midlist)){
				foreach($result->midlist->merchant as $merchant){
					$list[(string)$merchant->mid] = (string)$merchant->merchantname;
				}
			}
			set_transient($cash_key, $list, $liftime);
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
	public function do_shortcode($atts){
		extract(shortcode_atts(array(
			'title' => '',
			'url' => '',
			'src' => '',
			'price' => 0,
			'cat' => ''
		), $atts));
		$price = number_format($price);
		$cat = implode(' &gt; ', explode(',', $cat));
		$template = <<<EOS
<div class="tmkm-amazon-view wp-hamazon-linkshare">
	<p class="tmkm-amazon-title"><a href="{$url}" target="_blank">{$title}</a></p>
	<p class="tmkm-amazon-img"><a href="{$url}" target="_blank"><img src="{$src}" border="0" alt="{$title}" /></a></p>
	<p>価格: <em>&yen;{$price}</em></p>
	<p>カテゴリー: {$cat}</p>
	<hr class="tmkm-amazon-clear" />
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
	public function get_short_code($item){
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
		return sprintf('[hamazon_linkshare title="%s" url="%s" src="%s" price="%s" cat="%s" /]',
				$title, $url, $src, $price, implode(',', $cat));
	}
}