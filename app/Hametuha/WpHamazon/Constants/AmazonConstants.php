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
     * Get search index
     *
     * @param string $search_index
     * @return string
     */
	public static function index_label( $search_index ) {
	    switch ( $search_index ) {
            case 'eBooks':
                return __( 'eBook', 'hamazon' );
                break;
            case 'Pharmacy':
                return __( 'Pharmacy', 'hamazon' );
                break;
            case 'Health and Beauty':
                return __( 'Health & Beauty', 'hamazon' );
                break;
            case 'TV Series Episode Video on Demand':
                return __( 'On Demand Video', 'hamazon' );
                break;
            default:
	            $convered_index = str_replace( ' ', '', $search_index );
                $filter = [
                    'Baby Product' => 'Baby',
                    'Book' => 'Books',
                    'CE' => 'Electronics',
                    'Home Theater' => 'Electronics',
                    'Home' => 'HomeImprovement',
                    'Movie' => 'Video',
                    'OfficeProduct' => 'OfficeProducts',
                    'DigitalMusicAlbum' => 'Music',
                    'Hobby' => 'Hobbies',
                    'Sports' => 'SportingGoods',
                    'Toy' => 'Toys',
                    'VHS' => 'Video',
                    'Watch' => 'Watches',
                ];
                if ( isset( $filter[ $convered_index ] ) ) {
                    $search_index = $filter[ $convered_index ];
                }
	            $index = self::get_search_index();
	            return isset( $index[ $convered_index ] ) ? $index[ $convered_index ] : __( $search_index, 'hamazon' ) ;
                break;
        }
    }

	/**
	 * Search Index values for AWS
	 *
	 * @return array
	 */
	public static function get_search_index() {
	    static $search_index =  null;
	    if (is_null($search_index) ){
	        $search_index = [
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
                'ForeignBooks'       => __( 'Foreign Books', 'hamazon' ),
                'GiftCards'          => __( 'Gift Cards', 'hamazon' ),
                'Grocery'            => __( 'Grocery', 'hamazon' ),
                'HealthPersonalCare' => __( 'Health Personal Care', 'hamazon' ),
                'Hobbies'            => __( 'Hobbies', 'hamazon' ),
                'HomeImprovement'    => __( 'Home Improvement', 'hamazon' ),
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
	    return $search_index;
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
			 * hamazon_default_timeout
			 *
			 * Filter for timeout
			 * @param array $param
			 * @param string $service
			 */
			$default_time_out = apply_filters( 'hamazon_default_timeout', 10, $param, 'amazon' );
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
			'items' => [],
		];
		foreach ( $result->Items->Item as $item ) {
			$atts = self::get_attributes( $item );
			$image = self::get_image_src( $item, 'medium' );
			$price = 'N/A';
			if ( isset( $item->OfferSummary->LowestNewPrice ) ) {
				$price = (string) $item->OfferSummary->LowestNewPrice->FormattedPrice;
			} elseif ( isset( $item->OfferSummary->LowestUsedPrice ) ) {
				$price = (string) $item->OfferSummary->LowestUsedPrice->FormattedPrice;
			}
			$data = [
				'title' => (string) $atts['Title'],
                'category' => self::index_label( isset( $atts['ProductGroup'] ) ? $atts['ProductGroup'] : '' ),
				'asin' => (string) $item->ASIN,
				'price' => $price,
				'attributes' => $atts,
				'image' => $image,
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
				$url = (string) $item->LargeImage->URL ?: hamazon_no_image() ;
				break;
			case 'medium':
				$url = (string) $item->MediumImage->URL ?: hamazon_no_image();
				break;
			case 'small':
				$url = (string) $item->SmallImage->URL ?: hamazon_no_image();
				break;
			default:
				$url = hamazon_no_image();
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
	public static function get_attributes( $item ) {
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
	 * Get item from ASIN code.
	 *
	 * @param string $asin
	 * @return \SimpleXMLElement|\WP_Error
	 */
	public static function get_item_by_asin( $asin ) {
		$param = [
			'Operation' => 'ItemLookup',
			'IdType' => 'ASIN',
			'ItemId' => (string) $asin,
			'ResponseGroup' => 'Medium,Offers,Images,Reviews'
		];
		$id = "asin_{$asin}";
		return self::send_request($param, $id);
	}

	/**
	 * Detect if string is ASIN
	 *
	 * @param $asin
	 * @return bool
	 */
	private static function is_asin( $asin ) {
		return (boolean) preg_match( '/^[0-9a-zA-Z]{10,13}$/', trim( $asin ) );
	}


	/**
	 * Create HTML Source With Asin
	 *
	 * @since 3.0.0 May return WP_Error
	 * @param string $asin
	 * @param array $extra_atts
	 * @return string|\WP_Error
	 */
	public static function format_amazon( $asin, $extra_atts = [] ) {
		try {
			if ( self::is_asin( $asin ) ) {
				// Old format like [tmkm-amazon]000000000[/tmkm-amazon]
				$content = $extra_atts[ 'description' ];
			} elseif ( self::is_asin( $extra_atts[ 'asin' ] ) ) {
				// New format
				$content = $asin;
				$asin = $extra_atts[ 'asin' ];
			} else {
				throw new \Exception( __( 'ASIN format is wrong.', 'hamazon' ), 400 );
			}

			$result = self::get_item_by_asin( $asin );

			if ( is_wp_error( $result ) ) {
				return $result;
			} else {
				// Amazon function returned XML data
				$status = $result->Items->Request->IsValid;
				if ( $status == 'False' ) {
					throw new \Exception( __( 'Request for Amazon is invalid.', 'hamazon' ), 400 );
				} else {
					// results were found, so display the products
					$item = $result->Items->Item[ 0 ];
					$attributes = self::get_attributes( $item );
					$goods_image = self::get_image_src( $item, 'large' );

					$url = esc_url( $item->DetailPageURL );

					$title = $attributes[ 'Title' ];
					$product_group = sprintf( '<small>%s</small>', self::index_label( $attributes[ 'ProductGroup' ] ) );
					$price = isset( $attributes[ 'ListPrice' ] ) ? $attributes[ 'ListPrice' ][ 'FormattedPrice' ] : false;
					$desc = $price ? sprintf( "<p class=\"price\"><span class=\"label\">%s</span><em>{$price}</em></p>", __( 'Price', 'hamazon' ) ) : '';
					$filter = [
						'author' => [ 'Author', 'Director', 'Actor', 'Artist', 'Creator' ],
						'publisher' => [ 'Publisher', 'Manufacturer', 'Label', 'Brand', 'Studio' ],
						'Date' => [ 'PublicationDate' ],
						'allowable' => [ 'Binding', 'NumberOfPages', 'ISBN', 'Feature' ]
					];
					foreach($filter as $f => $values){
						foreach($values as $val){
							if(isset($attributes[$val]) && ( $label = self::atts_to_string( $val ) )  ){
								$key = self::atts_to_string($val);
								$value = esc_html(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $attributes[$val]) ? mysql2date(get_option('date_format'), $attributes[$val]) : $attributes[$val]);
								$desc .= "<p><span class=\"label\">{$key}</span><em>{$value}</em></p>";
								if( false === array_search( $f, [ 'allowable', 'author' ] ) ) {
									break;
								}
							}
						}
					}
					if ( get_option( 'hamazon_show_review', false ) && 'true' === (string) $item->CustomerReviews->HasReviews ) {
						$review = sprintf( '<p class="review"><iframe src="%s"></iframe></p>', $item->CustomerReviews->IFrameURL );
					} else {
						$review = '';
					}
					if ( !empty( $content ) ) {
						$desc .= sprintf( '<p class="additional-description">%s</p>', $content );
					}
					$tag = <<<EOS
<div class="tmkm-amazon-view wp-hamazon-amazon">
<p class="tmkm-amazon-img"><a href="{$url}" target="_blank"><img src="{$goods_image}" border="0" alt="{$title}" /></a></p>
<p class="tmkm-amazon-title"><a href="{$url}" target="_blank">{$title}{$product_group}</a></p>
{$desc}{$review}
<p class="vendor"><a href="https://affiliate.amazon.co.jp/gp/advertising/api/detail/main.html">Supported by amazon Product Advertising API</a></p>
</div>
EOS;
					/**
					 * wp_hamazon_amazon
					 *
					 * Filter output of amazon
					 *
					 * @param string $html
					 * @param \SimpleXMLElement $item
					 * @param array $extra_atts
					 * @param string $content
					 * @return string
					 */
					return apply_filters( 'wp_hamazon_amazon', $tag, $item, $extra_atts, $content );
				}
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}
	}


	/**
	 * Translate Attribute
	 *
	 * @param string $key
	 * @return string
	 */
	public static function atts_to_string($key){
		$attributes = [
			'Actor' => __( 'Actor', 'hamazon' ),
			'Artist' => __( 'Artist', 'hamazon' ),
			'Author' => __( 'Author', 'hamazon' ),
			'Binding' => __( 'Category', 'hamazon' ),
			'Brand' => __( 'Brand', 'hamazon' ),
			'Creator' => __( 'Creator', 'hamazon' ),
			'Director' => __( 'Director', 'hamazon' ),
			'ISBN' => 'ISBN',
			'Label' => __( 'Label', 'hamazon' ),
			'Manufacturer' => __( 'Actor', 'hamazon' ),
			'NumberOfPages' => _x( 'No. of Pages', 'item_attributes', 'hamazon' ),
			'PublicationDate' => __( 'Published', 'hamazon' ),
			'Publisher' => _x( 'Publisher', 'item_attributes', 'hamazon' ),
			'Studio' => __( 'Studio', 'hamazon' ),
		];
		return isset( $attributes[ $key ] ) ? $attributes[ $key ] : '';
	}

}
