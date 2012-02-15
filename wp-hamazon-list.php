<?php

/**
 * ASINから商品を出力するコントローラー
 * 
 */
class WP_Hamazon_List {

	/**
	 * Constructor
	 */
	function __construct() {
		add_shortcode('tmkm-amazon-list', array ($this, 'tmkm_amazon_list'));
		add_shortcode('tmkm-amazon', array($this, 'replace_strings'));
		add_action('wp_print_styles', array($this, 'enqueue_style'));
	}
	
	/**
	 * 記事本文中のショートコードを個別商品表示 HTML に置き換える
	 * 
	 * @param $content
	 * @return $transformedstring
	 */
	function replace_strings($atts, $content = null) {
		return $this->format_amazon($content);
	}
	
	/**
	 * PHP 関数として Amazon の個別商品 HTML を呼び出す
	 * 
	 * @param $asin ( ASIN )
	 * @return echo $display ( HTML )
	 */
	function amazon_view( $asin ) {
		$display = $this->format_amazon( $asin);
		echo $display;
	}
	
	/**
	 * Create HTML Source With Asin
	 * @global array $hamazon_settings
	 * @global string $tmkm_plugin_directory
	 * @global WP_Hamazon $wp_hamazon_parser
	 * @param string $asin
	 * @return string 
	 */
	function format_amazon($asin) {
		global $hamazon_settings, $tmkm_plugin_directory, $wp_hamazon_parser;
		$output = '';
        switch( $hamazon_settings['windowtarget']) {
        	case 'newwin': $windowtarget = ' target="_blank"'; break;
        	case 'self': $windowtarget = '';
        }
		
		$result = $wp_hamazon_parser->get_itme_by_asin($asin);

		if(is_wp_error($result)){ 
			//// Amazon function was returned false, so AWS is down
			$output = '<p class="message error">アマゾンのサーバでエラーが起こっているかもしれません。一度ページを再読み込みしてみてください。</p>';
		}else{
			// Amazon function returned XML data
			$status = $result->Items->Request->IsValid;
			if( $status == 'False' ){
				// Request is invalid
				$output = '<p>与えられたリクエストが正しくありません</p>';
			}else{
				// results were found, so display the products
				$item = $result->Items->Item[0];
				$atts = $wp_hamazon_parser->get_atts($item);
				$smallimage = $wp_hamazon_parser->get_image_src($item, 'small');
				$mediumimage = $wp_hamazon_parser->get_image_src($item, 'medium');

				$url = $item->DetailPageURL;
				$Title = $atts['Title'];
				$ProductGroup = isset($wp_hamazon_parser->searchIndex[$atts['ProductGroup']]) ? $wp_hamazon_parser->searchIndex[$atts['ProductGroup']]: '不明' ;
				if(isset($atts['ProductGroup']) && $atts['ProductGroup'] == 'Book'){
					$ProductGroup = '書籍';
				}
				$ProductGroup = " <small>[{$ProductGroup}]</small>";
				$price = $atts['ListPrice']['FormattedPrice'];
				

				if( $hamazon_settings['layout_type'] != '3' ) {
			        switch( $hamazon_settings['goodsimgsize'] ) {
			        	case 'small': $goodsimage = $smallimage; break;
			        	case 'medium': $goodsimage = $mediumimage; break;
			        }
				}
				$desc = $price ? "<p><em>価格: </em>{$price}</p>" : '';
				$filter = array(
					'author' => array('Author', 'Director', 'Actor', 'Artist', 'Creator'),
					'publisher' => array('Publisher', 'Studio', 'Label', 'Brand', 'Manufacturer'),
					'Date' => array('PublicationDate'),
					'allowable' => array('Binding', 'NumberOfPages', 'ISBN', 'Feature')
				);
				foreach($filter as $filter => $vals){
					foreach($vals as $val){
						if(isset($atts[$val])){
							$key = $wp_hamazon_parser->atts_to_string($val);
							$desc .= "<p><em>{$key}: </em>{$atts[$val]}</p>";
							if($filter != 'allowable' && $filter != 'author'){
								break;
							}
						}
					}
				}
				$output = <<<EOS
<div class="tmkm-amazon-view">
<p class="tmkm-amazon-title"><a href="{$url}"{$windowtarget}>{$Title}{$ProductGroup}</a></p>
<p><a href="{$url}"{$windowtarget}><img src="{$mediumimage}" border="0" alt="{$Title}" /></a></p>
{$desc}
<hr class="tmkm-amazon-clear" />
</div>
EOS;
			}
		}
		return $output;
	}

	/**
	 * Returns Amazon List
	 * @global wpdb $wpdb
	 * @global array $hamazon_settings
	 * @param array $attr
	 * @return string 
	 */
	function tmkm_amazon_list( $attr ) {
		
		global $wpdb, $hamazon_settings;

		extract( shortcode_atts( array(
			'orderby' 	=> 'post_id',
			'order'		=> 'asc',
		), $attr ));

		$orderby = strval( $orderby );
		$order = strtoupper(strval( $order ));

		$output = '';

		switch( $orderby ) {
			case 'post_id': $ordersql = "ID " . $order; break;
			case 'post_title': $ordersql = "post_title " . $order; break;
			case 'modified_date': $ordersql = "post_modified " . $order; break;
			default: $ordersql = "post_date " . $order; break;
		}

	    $sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS
				ID, post_title, post_date, post_content
			FROM
				{$wpdb->posts}
			WHERE
				post_status = 'publish' AND
				post_content LIKE '%[tmkm-amazon]%'
			ORDER BY
				$ordersql
EOS;

	    $PostRetainAsin = $wpdb->get_results($sql);
	    $postcount = $wpdb->get_var('SELECT FOUND_ROWS()');
	    $perpage = get_option("posts_per_page");
	    if( $PostRetainAsin ) {
			$heredoc = '';
			$books = array();
	    	foreach( $PostRetainAsin as $asinlist ) {
				$matches = array();
				if(!preg_match_all("/\[tmkm-amazon\]([0-9]+)\[\/tmkm-amazon\]/", $asinlist->post_content, $matches)){
					continue;
				}
	    		$permalink = get_permalink($asinlist->ID);
				$date = mysql2date(get_option('date_format'), $asinlist->post_date, false);
				$asins = $matches[1];
	    		foreach( $asins as $asin ) {
	    			$display = $this->format_amazon( $asin);
					$tag = '<p class="tmkm-amazon-clear"><em><em></p>';
					$books[] = <<<EOS
					<dt><a href="{$permalink}">{$asinlist->post_title}</a><br /><small>（投稿日: {$date}）</small></dt>
					<dd>{$display}</dd>
EOS;
	    		}
	    	}
			$heredoc .= '<dl>';
			foreach($books as $book){
				$heredoc .= $book;
			}
			$heredoc .= '</dl>';
	    } else {
	    	$heredoc = "<p>まだブログで書籍が紹介されていません。</p>\n";
	    }

		$output .= $heredoc;
		return $output;

	}

	/**
	 * CSSを読み込む
	 * @global array $hamazon_settings 
	 */
	public function enqueue_style(){
		global $hamazon_settings;
		if(!is_admin() && $hamazon_settings['load_css']){
			if(file_exists(TEMPLATEPATH.'/tmkm-amazon.css')){
				wp_enqueue_style('wp-hamazon', get_template_directory_uri().'/tmkm-amazon.css', array(), $hamazon_settings['version']);
			}else{
				wp_enqueue_style('wp-hamazon', plugin_dir_url(__FILE__).'hamazon.css', array(), $hamazon_settings['version']);
			}
		}
	}
}