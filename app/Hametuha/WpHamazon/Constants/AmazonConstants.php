<?php

namespace Hametuha\WpHamazon\Constants;

use Hametuha\WpHamazon\Service\Amazon;

/**
 * Amazon constants holder
 * @package hamazon
 */
class AmazonConstants {

	/**
	 * Service Name
	 * @var string
	 */
	const SERVICE = "AWSECommerceService";

	/**
	 * Version
	 * @var string
	 */
	const VERSION = '2013-08-01';

	/**
	 * Get locale endpoint.
	 *
	 * @param string $locale
	 *
	 * @return string
	 */
	public static function get_locale_endpoint( $locale ) {
		$locales = [
			'US' => 'http://ecs.amazonaws.com/onca/xml',
			'UK' => 'http://ecs.amazonaws.co.uk/onca/xml',
			'DE' => 'http://ecs.amazonaws.de/onca/xml',
			'JP' => 'http://ecs.amazonaws.jp/onca/xml',
			'FR' => 'http://ecs.amazonaws.fr/onca/xml',
			'CA' => 'http://ecs.amazonaws.ca/onca/xml',
		];
		if ( ! isset( $locales[ $locale ] ) ) {
			$locale = 'JP';
		}

		return $locales[ $locale ];
	}

	/**
	 * Search Index values for AWS
	 *
	 * @return array
	 */
	public static function get_search_index() {
		return [
			'All'                => __( 'All', 'hamazon' ),
			'Apparel'            => __( 'Apparel', 'hamazon' ),
			'Appliances'         => __( 'Appliances', 'hamazon' ),
			'Automotive'         => __( 'Automotive', 'hamazon' ),
			'Baby'               => __( 'Baby', 'hamazon' ),
			'Beauty'             => __( 'Beauty', 'hamazon' ),
			'Blended'            => __( 'Blended', 'hamazon' ),
			'Books'              => __( 'Books', 'hamazon' ),
			'Classical'          => __( 'Classical', 'hamazon' ),
			'CreditCards'        => __( 'CreditCards', 'hamazon' ),
			'DVD'                => __( 'DVD', 'hamazon' ),
			'Electronics'        => __( 'Electronics', 'hamazon' ),
			'ForeignBooks'       => __( 'ForeignBooks', 'hamazon' ),
			'GiftCards'          => __( 'GiftCards', 'hamazon' ),
			'Grocery'            => __( 'Grocery', 'hamazon' ),
			'HealthPersonalCare' => __( 'HealthPersonalCare', 'hamazon' ),
			'Hobbies'            => __( 'Hobbies', 'hamazon' ),
			'HomeImprovement'    => __( 'HomeImprovement', 'hamazon' ),
			'Industrial'         => __( 'Industrial', 'hamazon' ),
			'Jewelry'            => __( 'Jewelry', 'hamazon' ),
			'KindleStore'        => __( 'Kindle Store', 'hamazon' ),
			'Kitchen'            => __( 'Kitchen', 'hamazon' ),
			'Marketplace'        => __( 'Marketplace', 'hamazon' ),
			'MobileApps'         => __( 'MobileApps', 'hamazon' ),
			'MP3Downloads'       => __( 'MP3Downloads', 'hamazon' ),
			'Music'              => __( 'Music', 'hamazon' ),
			'MusicalInstruments' => __( 'Musical Instruments', 'hamazon' ),
			'OfficeProducts'     => __( 'Office Products', 'hamazon' ),
			'PCHardware'         => __( 'PC Hardware', 'hamazon' ),
			'PetSupplies'        => __( 'Pet Supplies', 'hamazon' ),
			'Shoes'              => __( 'Shoes', 'hamazon' ),
			'Software'           => __( 'Software', 'hamazon' ),
			'SportingGoods'      => __( 'Sporting Goods', 'hamazon' ),
			'Toys'               => __( 'Toys', 'hamazon' ),
			'Video'              => __( 'Video', 'hamazon' ),
			'VideoDownload'      => __( 'Video Download', 'hamazon' ),
			'VideoGames'         => __( 'Video Games', 'hamazon' ),
			'Watches'            => __( 'Watches', 'hamazon' ),
		];
	}


	/**
	 * Send Request and get XML Object
	 *
	 * @param array $param
	 * @param string|bool $cash_id
	 * @param int $cash_time
	 *
	 * @return \WP_Error|\SimpleXMLElement
	 */
	public static function send_request( array $param, $cash_id = false, $cash_time = 86400 ) {
		// Build URL and Check it.
		$url = self::build_url( $param );
		if ( is_wp_error( $url ) ) {
			return $url;
		}
		//Cash Request if required.
		$transient = false;
		if ( $cash_id ) {
			$transient = get_transient( $cash_id );
		}
		if ( $transient !== false ) {
			return simplexml_load_string( $transient );
		} else {
			// Make Request
			/**
			 * hamazon_timeout_amazon
			 *
			 * Filter for timeout
			 */
			$default_time_out = apply_filters( 'hamazon_timeout_amazon', 10, $param );
			$response = wp_remote_get( $url, [
				'timeout' => $default_time_out,
			] );
			if ( is_wp_error( $response ) ) {
				return $response;
			}
			$data = $response['body'];
			$xml = simplexml_load_string( $data );
			foreach ( $xml->Error as $error ) {
				return new \WP_Error( (string) $error->Code, (string) $error->Message );
			}
			if ( $cash_id && $data ) {
				set_transient( $cash_id, $data, $cash_time );
			}
			return $xml;
		}
	}


	/**
	 * Return request url to AWS REST Service
	 *
	 * @param array $params Request params.
	 *
	 * @return string|\WP_Error
	 */
	public static function build_url( $params ) {
		$service = Amazon::get_instance();
		//Add Default query
		$params['Service']        = self::SERVICE;
		$params['AWSAccessKeyId'] = $service->get_option( 'accessKey' );
		$params['AssociateTag']   = $service->get_option( 'associatesid' );
		$params['Version']        = self::VERSION;
		$params['Timestamp']      = self::get_timestamp( false );
		//Sort Key by byte order
		ksort( $params );
		//Make Query String
		$query_string = '';
		foreach ( $params as $k => $v ) {
			$query_string .= '&' . self::urlencode( $k ) . '=' . self::urlencode( $v );
		}
		$query_string = substr( $query_string, 1 );
		// Get endpoint
		$url = self::get_locale_endpoint( $service->get_option( 'locale' ) );
		//Create Signature
		$url_components = parse_url( $url );
		$string_to_sign = "GET\n{$url_components['host']}\n{$url_components['path']}\n{$query_string}";
		$signature      = self::get_signature( $string_to_sign, $service->get_option( 'secretKey' ) );
		if ( is_wp_error( $signature ) ) {
			return $signature;
		} else {
			return $url . "?" . $query_string . "&Signature=" . self::urlencode( base64_encode( $signature ) );
		}
	}


	/**
	 * Encode URL according to RFC 3986
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	protected static function urlencode( $str ) {
		return str_replace( '%7E', '~', rawurlencode( $str ) );
	}


	/**
	 * Get signature for AWS
	 *
	 * @param string $string_to_sign
	 * @param string $secret_access_key
	 *
	 * @return \WP_Error|string
	 */
	protected static function get_signature( $string_to_sign, $secret_access_key ) {
		if ( function_exists( 'hash_hmac' ) ) {
			return hash_hmac( 'sha256', $string_to_sign, $secret_access_key, true );
		} elseif ( function_exists( 'mhash' ) ) {
			return mhash( MHASH_SHA256, $string_to_sign, $secret_access_key );
		} else {
			return new \WP_Error( 'error', __( 'hash_hmac or mhash functions are required. Please contact to server admin.', 'hamazon' ) );
		}
	}

	/**
	 * Returns timestamp.
	 *
	 * @param boolean $with_suffix if set to true, return with suffix for query string. Default true.
	 *
	 * @return string
	 */
	protected static function get_timestamp( $with_suffix = true ) {
		$timestamp = gmdate( 'Y-m-d\TH:i:s\Z' );
		if ( $with_suffix ) {
			$timestamp = 'Timestamp=' . $timestamp;
		}

		return $timestamp;
	}


	/**
	 * Search item with string.
	 *
	 * @param string $query
	 * @param int $page
	 * @param string $index
	 *
	 * @return \WP_Error|array
	 */
	public static function search_with( $query, $page = 1, $index = 'ALL' ) {
		$param = array(
			'Operation'     => 'ItemSearch',
			'SearchIndex'   => (string) $index,
			'Keywords'      => (string) $query,
			'ItemPage'      => $page,
			'ResponseGroup' => 'Offers,Images,Small'
		);
		$result = self::send_request( $param );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$response = [
			'total_page' => (int) $result->Items->TotalPages,
			'total_result' => (int) $result->Items->TotalResults,
			'items' => [

			],
		];
		foreach ( $result->Items->Item as $item ) {
			$atts = self::get_attributes( $item );
			$small_image = self::get_image_src( $item, 'small' );
			$price = 'N/A';
			if ( isset( $item->OfferSummary->LowestNewPrice ) ) {
				$price = (string) $item->OfferSummary->LowestNewPrice->FormattedPrice;
			} elseif ( isset( $item->OfferSummary->LowestUsedPrice ) ) {
				$price = (string) $item->OfferSummary->LowestUsedPrice->FormattedPrice;
			}
			$data = [
				'title' => (string) $atts['Title'],
				'asing' => (string) $item->ASIN,
				'price' => $price,
				'attributes' => $atts,
				'image' => $small_image,
				'url' => (string) $item->DetailPageURL,
			];
			$response['items'][] = $data;
		}
		return $response;
	}

	/**
	 * Grab image URL.
	 *
	 * @param \SimpleXMLElement $item
	 * @param string $size
	 *
	 * @return string
	 */
	protected static function get_image_src( $item, $size = 'small' ) {
		switch ( $size ) {
			case 'large':
				$url = (string) $item->LargeImage->URL ?: '' ;
				break;
			case 'medium':
				$url = (string) $item->MediumImage->URL ?: '';
				break;
			case 'small':
				$url = (string) $item->SmallImage->URL ?: '';
				break;
			default:
				$url = '';
				break;
		}
		return $url;
	}

	/**
	 * Get item attributes
	 *
	 * @param \SimpleXMLElement $item
	 *
	 * @return array
	 */
	public static function get_attributes( \SimpleXMLElement $item ) {
		if ( $item->ItemAttributes ) {
			return self::parse_object( $item->ItemAttributes );
		} else {
			return array();
		}
	}

	/**
	 * Parse object to array
	 *
	 * @param \SimpleXMLElement|array $object
	 *
	 * @return array
	 */
	protected static function parse_object( $object ) {
		$vars = array();
		foreach ( get_object_vars( $object ) as $key => $val ) {
			if ( is_object( $val ) ) {
				$vars[ $key ] = self::parse_object( $val );
			} elseif ( is_array( $val ) ) {
				$vars[ $key ] = implode( ', ', $val );
			} else {
				$vars[ $key ] = $val;
			}
		}
		return $vars;
	}




	/**
	 * Display
	 *
	 */
	public static function format_search_result() {
		// Get pagination
		if ( isset( $_GET['page'] ) ) {
			$page_num = max( 1, (int) $_GET['page'] );
		} else {
			$page_num = 1;
		}
		//Start Searching
		if ( isset( $_GET['keyword'], $_GET['_wpnonce'] ) && ! empty( $_GET['keyword'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'amazon_search' ) ) {
			echo '<div id="amazon-search-result">';
			$keyword     = (string) $_GET['keyword'];
			$searchindex = ! empty( $_GET['SearchIndex'] ) ? $_GET['SearchIndex'] : 'Blended';
			$result      = $this->search_with( $keyword, $page_num, $searchindex );

			if ( is_wp_error( $result ) ) {
				// Amazon function was returned false, so AWS is down
				echo '<div class="error"><p>検索結果を取得できませんでした。amazonのサーバでエラーが起こっているかもしれません。</p></div>';
			} else {
				// Amazon function returned XML data
				if ( $result->Items->Request->Errors ) {
					printf( '<div class="error"><p>%s</p></div>', $result->Items->Request->Errors->Error->Message );
				} else {
					// results were found, so display the products
					$total_results = $result->Items->TotalResults;
					$total_pages   = $result->Items->TotalPages;
					$per_page      = $searchindex == 'Blended' ? 3 : 10;

					if ( $total_results == 0 ) { // no result was found
						printf( '<div class="error"><p>「%s」の検索結果が見つかりませんでした。</p></div>', esc_html( $keyword ) );
					} else {
						// Pagenation
						if ( $total_pages > 1 ) {
							$pagination = $this->paginate( $total_pages, $page_num, 1, array(
								'SearchIndex' => $searchindex,
								'keyword'     => $keyword,
								'_wpnonce'    => wp_create_nonce( 'amazon_search' ),
							) );
						} else {
							$pagination = '';
						}
						// results were found
						$length = count( $result->Items->Item );
						?>
						<div class="result-desc clearfix">
							<h1>「<?php echo esc_html( $keyword ); ?>
								」の検索結果: <?php echo number_format( (string) $total_results ); ?>件</h1>
							<?php echo $pagination; ?>
						</div><!-- //.result-desc -->
						<table class="wp-hamazon-product-table">
							<?php
							for ( $i = 0; $i < $length; $i ++ ) {
								$item       = $result->Items->Item[ $i ];
								$smallimage = $this->get_image_src( $item, 'small' );
								$atts       = $this->get_atts( $item );
								?>
								<tr class="amazon">
									<th>
										<?php if ( $searchindex !== 'Blended' ): ?>
											<em>No. <?php echo number_format( ( $page_num - 1 ) * $per_page + $i + 1 ); ?></em>
											<br/>
										<?php endif; ?>
										<img src="<?php echo $smallimage; ?>" border="0" alt=""/><br/>
										<a class="button" href="<?php echo $item->DetailPageURL; ?>" target="_blank">Amazonで見る</a>
									</th>
									<td>
										<strong><?php echo $atts['Title']; ?></strong><br/>
										価格：<em class="price"><?php
											if ( $item->OfferSummary->LowestNewPrice->FormattedPrice ) {
												echo esc_html( (string) $item->OfferSummary->LowestNewPrice->FormattedPrice );
											} else {
												echo 'N/A';
											}
											?></em><br/>
										<?php
										foreach (
											array(
												'Actor',
												'Artist',
												'Author',
												'Creator',
												'Director',
												'Manufacturer'
											) as $key
										) {
											if ( isset( $atts[ $key ] ) ) {
												echo $this->atts_to_string( $key ) . ": " . $atts[ $key ] . "<br />";
											}
										}
										?>
										<label>コード: <input type="text" class="hamazon-target" size="40"
														   value="[tmkm-amazon asin='<?php echo $item->ASIN; ?>'][/tmkm-amazon]"
														   onclick="this.select();"/></label>
										<a class="button-primary hamazon-insert" data-target=".hamazon-target" href="#">挿入</a>
										<br/>
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
}
