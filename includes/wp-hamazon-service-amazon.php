<?php
/**
 * Communicator with Amazon
 * @since 1.0
 * @package wordpress
 */
class WP_Hamazon_Service_Amazon extends WP_Hamazon_Service implements WP_Hamazon_Service_Required{
	
	
	/**
	 * 検索ページで表示されるタイトル
	 * @var string
	 */
	public $title = 'Amazonアソシエイト商品検索';
	
	
	/**
	 * アイコンファイル名
	 * @var string
	 */
	protected $icon = 'amazon-bk.png';


	/**
	 * Service Name
	 * @var string
	 */
	const SERVICE = "AWSECommerceService";
	
	
	
	/**
	 * API version
	 * @see http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/
	 * @var string
	 */
	const VERSION = "2011-08-01";



	/**
	 * Asocciate ID
	 * @var string
	 */
	private $AssociatesID = '';



	/**
	 * Developper Token (Access ID)
	 * @var string
	 */
	private $DevToken = '';



	/**
	 * Securiry Access ID
	 * @var string
	 */
	private $SAK = '';


	/**
	 * Whether to show review
	 * @var bool
	 */
	private $show_review = false;


	/**
	 * URL of AWS endpoint
	 * @var string
	 */
	var $endpoint = '';



	/**
	 * Mime Type of response
	 * @var string
	 */
	var $mime = 'text/xml';



	/**
	 * Search Index values for AWS
	 * @var array
	 */
	public $searchIndex = array(
		'Blended' => 'すべての商品',
		'Books' => '和書',
		'KindleStore' => 'Kindleストア',
		'ForeignBooks' => '洋書',
		'Electronics' => '家電',
		'OfficeProducts' => 'オフィス用品',
		'Software' => 'ソフトウェア',
		'VideoGames' => 'ビデオゲーム',
		'DVD' => 'DVD',
		'Video' => 'ビデオ',
		'Music' => 'ミュージック',
		'MP3Downloads' => 'MP3ダウンロード',
		'MusicalInstruments' => '楽器',
		'Classical' => 'クラシック',
		'Apparel' => 'アパレル',
		'Shoes' => '靴',
		'Jewelry' => '宝石',
		'Beauty' => '美容',
		'HealthPersonalCare' => 'ヘルスケア',
		'Grocery' => '日用品',
		'Baby' => '赤ちゃん用品',
		'HomeImprovement' => '家庭用品',
		'Kitchen' => '台所用品',
		'SportingGoods' => 'スポーツ用品',
		'Automotive' => 'カー用品',
		'Hobbies' => 'ホビー',
		'Toys' => 'おもちゃ',
		'Marketplace' => 'マーケットプレイス'
	);



	/**
	 * Constructor
	 * @param string $devToken
	 * @param string $sak
	 * @param string $associatesid
	 * @param string $locale Default JP
	 */
	public function set_option() {
		$this->DevToken = $this->get_option('accessKey');
		$this->AssociatesID = $this->get_option('associatesid');
		$this->SAK = $this->get_option('secretKey');
		$this->show_review = $this->get_option('show_review');
		$this->setLocale('JP');
	}



	/**
	 * 有効か否かを返す
	 * @return boolean
	 */
	public function is_valid() {
		return (!empty($this->SAK) && !empty($this->DevToken) && !empty($this->AssociatesID));
	}



	/**
	 * Set up locale for amazon.
	 * @param string $locale JP, US, UK, DE, FR, CA
	 * @return WP_Error|true
	 */
	function setLocale($locale){
        $urls = array(
            'US' => 'http://ecs.amazonaws.com/onca/xml',
            'UK' => 'http://ecs.amazonaws.co.uk/onca/xml',
            'DE' => 'http://ecs.amazonaws.de/onca/xml',
            'JP' => 'http://ecs.amazonaws.jp/onca/xml',
            'FR' => 'http://ecs.amazonaws.fr/onca/xml',
            'CA' => 'http://ecs.amazonaws.ca/onca/xml',
        );
        $locale = strtoupper($locale);
        if (!isset($urls[$locale])) {
            return new WP_Error('', 'Amazonはそのロケールに対応していません。');
        }else{
			$this->endpoint = $urls[$locale];
			return true;
		}
    }



	/**
	 * Send Request and get XML Object
	 * @param array $param
	 * @param string $cash_id
	 * @param int $cash_time
	 * @return WP_Error|SimpleXMLElement
	 */
	function send_request($param, $cash_id = false, $cash_time = 86400){
		// Build URL and Check it.
		$url = $this->build_url($param);
		if(is_wp_error($url)){
			return $url;
		}
		//Cash Request if required.
		$transient = false;
		if($cash_id){
			$transient = get_transient($cash_id);
		}
		if($transient !== false){
			$data = $transient;
		}else{
			// Make Request
			$timeout = 5;
			$context = stream_context_create(array(
				'http' => array(
					'timeout' => $timeout,
				),
			));
			$data = file_get_contents($url, false, $context);
			if($cash_id && $data){
				set_transient($cash_id, $data, $cash_time);
			}
		}
		if(!$data){
			return new WP_Error('error', 'Amazonから情報を取得できませんでした');
		}else{
			return simplexml_load_string($data);
		}
	}



	/**
	 * Return request url to AWS REST Servicce
	 * @param array $params
	 * @return string
	 */
	function build_url($params){
		//Add Default query
		$params['Service'] = self::SERVICE;
		$params['AWSAccessKeyId'] = $this->DevToken;
		$params['AssociateTag'] = $this->AssociatesID;
		$params['Version'] = self::VERSION;
		$params['Timestamp'] = $this->get_timestamp(false);
		//Sort Key by byte order
		ksort($params);
		//Make Query String
		$query_string = '';
		foreach($params as $k => $v){
			$query_string .= '&' . $this->urlencode($k) . '=' . $this->urlencode($v);
		}
		$query_string = substr($query_string, 1);
		//Create Signature
		$url_conponents = parse_url($this->endpoint);
		$string_to_sign = "GET\n{$url_conponents['host']}\n{$url_conponents['path']}\n{$query_string}";
		$signature = $this->get_signature($string_to_sign, $this->SAK);
		if(is_wp_error($signature)){
			return $signature;
		}else{
			return $this->endpoint."?".$query_string."&Signature=".$this->urlencode(base64_encode($signature));
		}
	}



	/**
	 * Encode URL according to RFC 3986
	 * @param string $str
	 * @return string
	 */
	function urlencode($str){
		return str_replace('%7E', '~', rawurlencode($str));
	}



	/**
	 * Get signature for AWS
	 * @param string $string_to_sign
	 * @param string $secret_access_key
	 * @return WP_Error|string
	 */
	function get_signature($string_to_sign, $secret_access_key){
		if (function_exists('hash_hmac')) {
            return hash_hmac('sha256', $string_to_sign, $secret_access_key, true);
        } elseif (function_exists('mhash')) {
            return mhash(MHASH_SHA256, $string_to_sign, $secret_access_key);
        }else{
			return new WP_Error('error', 'hash_hmacまたはmhash関数がインストールされている必要があります');
		}
	}



	/**
	 * Get Amazon Image.
	 *
	 * @param SimpleXMLElement $item (Amazon Xml)
	 * @param string $size (Image Size)
	 * @return string
	 */
	function get_image_src($item, $imgsize = 'medium') {
		switch($imgsize){
			case 'large':
				$url = (string)$item->LargeImage->URL ? $item->LargeImage->URL: plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg.png";
				break;
			case 'medium':
				$url = (string)$item->MediumImage->URL ? $item->MediumImage->URL: plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg.png";
				break;
			case 'small':
				$url = (string)$item->SmallImage->URL ? $item->SmallImage->URL: plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg_small.png";
				break;
			default:
				$url = plugin_dir_url(dirname(__FILE__))."assets/img/amazon_noimg.png";
				break;
		}
		return (string)$url;
	}



	/**
	 * Search item with string.
	 * @param string $query
	 * @param int $page
	 * @param string $index
	 * @return WP_Error|SimpleXMLElement
	 */
	function search_with($query, $page = 1, $index = 'ALL'){
		$param = array(
			'Operation' => 'ItemSearch',
			'SearchIndex' => (string)$index,
			'Keywords' => (string) $query,
			'ItemPage' => $page,
			'ResponseGroup' => 'Offers,Images,Small'
		);
		return $this->send_request($param);
	}



	/**
	 * Get Product detail with Asin
	 * @param string $asin
	 * @return WP_Error|SimpleXMLElement
	 */
	function get_itme_by_asin($asin){
		$param = array(
			'Operation' => 'ItemLookup',
			'IdType' => 'ASIN',
			'ItemId' => (string)$asin,
			'ResponseGroup' => 'Medium,Offers,Images,Reviews'
		);
		//Cash Result
		$id = "asin_{$asin}";
		return $this->send_request($param, $id);
	}



	/**
	 * Get Amazon Text.
	 *
	 * @param SimpleXMLElement $item
	 * @return array
	 */
	function get_atts($item){
		if($item->ItemAttributes){
			return $this->parse_object($item->ItemAttributes);
		}else{
			return array();
		}
	}



	/**
	 * Parse XMLElement to array
	 * @param SimpleXMLElement $object
	 * @return array
	 */
	function parse_object($object){
		$vars = array();
		foreach(get_object_vars($object) as $key => $val){
			if(is_object($val)){
				$vars[$key] = $this->parse_object($val);
			}elseif(is_array($val)){
				$vars[$key] = implode(', ', $val);
			}else{
				$vars[$key] = $val;
			}
		}
		return $vars;
	}



	/**
	 * Translate Attribute
	 * @param string $key
	 * @return string
	 */
	function atts_to_string($key){
		$atts = array(
			'Actor' => '出演者',
			'Address1' => '住所１',
			'Address2' => '住所２',
			'Address3' => '住所３',
			'AmazonMaximumAge' => '最高対象年体',
			'AmazonMinimumAge' => '最低対象年齢',
			'Amount' => '価格',
			'ApertureModes' => '絞りモード',
			'Artist' => 'アーティスト',
			'ASIN' => 'ASIN',
			'AspectRatio' => '縦横比',
			'AudienceRating' => '対象年齢',
			'AudioFormat' => 'メディア形式',
			'Author' => '著者',
			'BackFinding' => '金属',
			'BandMaterialType' => '材質',
			'Batteries' => 'バッテリー',
			'BatteriesIncluded' => '付属電池',
			'BatteryDescription' => '車両式別番号',
			'BatteryType' => '電池',
			'BezelMaterialType' => '台座材質',
			'Binding' => '商品カテゴリー',
			'Brand' => 'ブランド',
			'CalendarType' => '種類',
			'CameraManualFeatures' => 'マニュアル機能',
			'CaseDiameter' => '対角距離',
			'CaseMaterialType' => 'ケース材質',
			'CaseThickness' => 'ケース厚',
			'CaseType' => 'ケース種類',
			'CDRWDescription' => 'CD読み書き',
			'ChainType' => 'チェーン種類',
			'City' => '市区町村',
			'ClaspType' => '留金種類',
			'ClothingSize' => 'サイズ',
			'Color' => '色',
			'Compatibility' => '互換性',
			'CPUManufacturer' => 'CPU製造元',
			'CPUSpeed' => 'CPU速度',
			'CPUType' => 'CPUタイプ',
			'Creator' => 'クリエーター',
			'CurrencyCode' => '通貨',
			'Day' => '日',
			'DelayBetweenShots' => '撮影間隔',
			'Department' => '部門',
			'DetailPageURL' => 'URL',
			'DeweyDecimalNumber' => 'デューイ10進法番号',
			'DialColor' => '文字盤色',
			'DialWindowMaterialType' => 'カバー材質',
			'DigitalZoom' => 'ズーム比',
			'Director' => '監督',
			'DisplaySize' => 'ディスプレイサイズ',
			'DVDLayers' => 'DVD層',
			'DVDRWDescription' => 'DVD読み書き',
			'DVDSides' => '片面／両面',
			'EAN' => 'EAN',
			'Edition' => '版',
			'EpisodeSequence' => 'エピソード',
			'ESRBAgeRating' => 'ESRB',
			'ExternalDisplaySupportDescription' => '外部ディスプレイ対応',
			'FabricType' => '生地',
			'FaxNumber' => 'FAX番号',
			'Feature' => '特徴',
			'FirstIssueLeadTime' => '到着時間',
			'FlavorName' => 'フレーバー',
			'FloppyDiskDriveDescription' => 'フロッピードライブ',
			'Format' => 'フォーマット',
			'FormattedPrice' => '価格',
			'GemType' => '宝石',
			'GemTypeSetElement' => '宝石',
			'Genre' => 'ジャンル',
			'GolfClubFlex' => 'フレックス',
			'GolfClubLoft' => 'ロフト',
			'GraphicsCardInterface' => 'グラフィックカードIF',
			'GraphicsDescription' => 'グラフィックカード',
			'GraphicsMemorySize' => 'グラフィックカードメモリー',
			'HardDiskCount' => 'HDD数',
			'HardDiskSize' => 'HDDサイズ',
			'HasAutoFocus' => 'オートフォーカス',
			'HasBurstMode' => 'バーストモード',
			'HasInCameraEditing' => '編集機能',
			'HasRedEyeReduction' => '赤目補正',
			'HasSelfTimer' => 'セルフタイマー',
			'HasTripodMount' => '三脚マウント',
			'HasVideoOut' => 'ビデオ出力端子',
			'HasViewfinder' => 'ビューファインダー',
			'Height' => '高さ',
			'Hours' => '時間',
			'HoursOfOperation' => '営業移管',
			'IncludedSoftware' => '付属ソフト',
			'IncludesMp3Player' => '付属MP3プレイヤー',
			'Ingredients' => '原材料',
			'IngredientsSetElement' => '原材料',
			'IsAutographed' => 'サイン付き',
			'IsEligibleForTradeIn' => 'トレードイン',
			'ISBN' => 'ISBN',
			'IsFragile' => '壊れ物',
			'IsLabCreated' => 'ラボ',
			'IsMemorabilia' => '記念品',
			'ISOEquivalent' => 'ISO感度',
			'IssuesPerYear' => '発行回数',
			'KeyboardDescription' => 'キーボード',
			'Keywords' => 'キーワード',
			'Label' => 'レーベル',
			'LegalDisclaimer' => '免責事項',
			'Length' => '長さ',
			'LongSynopsis' => '要約',
			'LineVoltage' => '電圧',
			'MacroFocusRange' => 'マクロフォーカス',
			'MagazineType' => 'ジャンル',
			'Manufacturer' => '製造元',
			'ManufacturerMaximumAge' => '最高対象年齢',
			'ManufacturerMinimumAge' => '最低対象年齢',
			'ManufacturerPartsWarrantyDescription' => '部品保証',
			'MaterialType' => '材質',
			'MaterialTypeSetElement' => '材質',
			'MaximumAperture' => '最大絞り値',
			'MaximumColorDepth' => '最大色深度',
			'MaximumFocalLength' => '最大焦点距離',
			'MaximumHighResolutionImages' => '最大解像度',
			'MaximumHorizontalResolution' => '最大水平解像度',
			'MaximumLowResolutionImages' => '最低解像度',
			'MaximumResolution' => '最高解像度',
			'MaximumShutterSpeed' => '最高シャッタースピード',
			'MaximumVerticalResolution' => '最大垂直解像度',
			'MaximumWeightRecommendation' => '最大積載量',
			'MemorySlotsAvailable' => 'メモリスロット',
			'Message' => 'エラーメッセージ',
			'MetalStamp' => 'メタルスタンプ',
			'MetalType' => '使用金属',
			'MiniMovieDescription' => '説明',
			'MinimumFocalLength' => '最小焦点距離',
			'MinimumShutterSpeed' => '最大シャッター開放時間',
			'Model' => 'モデル',
			'ModemDescription' => 'モデム',
			'MonitorSize' => 'モニターサイズ',
			'MonitorViewableDiagonalSize' => 'モニター実サイズ',
			'MouseDescription' => 'マウス',
			'MPN' => '部品番号',
			'NativeResolution' => '最大解像度',
			'Neighborhood' => '近隣',
			'NetworkInterfaceDescription' => 'ネットワークインターフェース',
			'NotebookDisplayTechnology' => 'ディスプレイ',
			'NotebookPointingDeviceDescription' => 'ポインティングデバイス',
			'NumberOfDiscs' => 'ディスク枚数',
			'NumberOfIssues' => '号数',
			'NumberOfItems' => '商品数',
			'NumberOfPages' => 'ページ数',
			'NumberOfPearls' => '珠数',
			'NumberOfRapidFireShots' => '連射可能数',
			'NumberOfStones' => '石数',
			'NumberOfTracks' => '曲数',
			'OpticalZoom' => '光学ズーム',
			'OriginalAirDate' => '放映日',
			'OriginalReleaseDate' => 'リリース日',
			'PearlLustre' => '光沢',
			'PearlMinimumColor' => '色',
			'PearlShape' => '形',
			'PearlStringingMethod' => '連結方法',
			'PearlSurfaceBlemishes' => '傷',
			'PearlType' => '種類',
			'PearlUniformity' => '均一性',
			'PhoneNumber' => '電話番号',
			'PhotoFlashType' => 'フラッシュ',
			'PictureFormat' => '画像形式',
			'Platform' => 'OS',
			'PostalCode' => '郵便番号',
			'Price' => '価格',
			'PriceRating' => '値段',
			'ProcessorCount' => 'プロセッサ数',
			'ProductGroup' => 'カテゴリ',
			'PublicationDate' => '出版日',
			'Publisher' => '出版社',
			'ReadingLevel' => '難易度',
			'RegionCode' => 'リージョンコード',
			'ReleaseDate' => 'リリース日',
			'RemovableMemory' => 'メモリ取り外し',
			'ResolutionModes' => '解像度モード',
			'RingSize' => '指輪サイズ',
			'Role' => '役割',
			'RunningTime' => '収録時間',
			'SeasonSequence' => 'シーズン',
			'SecondaryCacheSize' => '2次キャッシュ',
			'SettingType' => '加工',
			'ShortSynopsis' => '説明',
			'Size' => 'サイズ',
			'SizePerPearl' => '真珠サイズ',
			'SKU' => 'SKU',
			'SoundCardDescription' => 'サウンドカード',
			'SpeakerDescription' => 'スピーカー',
			'SpecialFeatures' => '特殊な機能',
			'StartYear' => '放映開始年',
			'State' => '都道府県',
			'StoneClarity' => '透明度',
			'StoneColor' => '色',
			'StoneCut' => 'カット',
			'StoneShape' => '形',
			'StoneWeight' => '石重',
			'Studio' => 'スタジオ',
			'SubscriptionLength' => '購読基幹',
			'SupportedImageType' => '対応画像フォーマット',
			'SystemBusSpeed' => 'バス速度',
			'SystemMemorySize' => 'RAMサイズ',
			'SystemMemorySizeMax' => '搭載可能RAMサイズ',
			'SystemMemoryType' => 'RAM種類',
			'TheatricalReleaseDate' => '初上映日',
			'Title' => 'タイトル',
			'TotalDiamondWeight' => '重さ',
			'TotalExternalBaysFree' => '空きベイ数',
			'TotalFirewirePorts' => 'FireWallポート数',
			'TotalGemWeight' => 'カラット数',
			'TotalInternalBaysFree' => '空き内部ベイ数',
			'TotalMetalWeight' => '総重量',
			'TotalNTSCPALPorts' => 'ビデオポート数',
			'TotalPages' => 'ページ数',
			'TotalParallelPorts' => 'パラレルポート数',
			'TotalPCCardSlots' => 'カードスロット数',
			'TotalPCISlotsFree' => '空きカードスロット数',
			'TotalResults' => '合計数',
			'TotalSerialPorts' => 'シリアルポート数',
			'TotalSVideoOutPorts' => 'Sビデオコネクタ出力数',
			'TotalUSBPorts' => 'USBポート数',
			'TotalUSB2Ports' => 'USB2ポート数',
			'TotalVGAOutPorts' => 'VGA出力ポート数',
			'TradeInValue' => 'トレードイン',
			'Type' => 'タイプ',
			'Unit' => '単位',
			'UPC' => 'UPC',
			'VariationDenomination' => 'バリエーション',
			'VariationDescription' => 'バリエーション',
			'Warranty' => '保証条項',
			'WatchMovementType' => 'ムーブメント',
			'WaterResistanceDepth' => '防水仕様',
			'Weight' => '重さ',
			'Width' => '幅'
		);
		if(array_key_exists($key, $atts)){
			return $atts[$key];
		}else{
			return $key;
		}
	}



	/**
	 * Returns timestamp.
	 * @param boolean $with_suffix if set to true, return with suffix for query string. Default true.
	 * @return string
	 */
	function get_timestamp($with_suffix = true){
		$timestamp = gmdate('Y-m-d\TH:i:s\Z');
		if($with_suffix){
			$timestamp = 'Timestamp='.$timestamp;
		}
		return $timestamp;
	}



	/**
	 * ショートコードを登録する
	 */
	public function set_shortcode() {
		$this->short_codes = array('tmkm-amazon', 'tmkm-amazon-list');
	}



	/**
	 * Create HTML Source With Asin
	 * @param string $asin
	 * @return string
	 */
	public function format_amazon($asin, $extra_atts = array()) {

		if($this->is_asin($asin)){
			// Old format like [tmkm-amazon]000000000[/tmkm-amazon]
			$content = $extra_atts['description'];
		}elseif($this->is_asin($extra_atts['asin'])){
			// New format
			$content = $asin;
			$asin = $extra_atts['asin'];
		}else{
			return $this->error_message();
		}

		$result = $this->get_itme_by_asin($asin);

		if(is_wp_error($result)){
			//// Amazon function was returned false, so AWS is down
			return $this->error_message('アマゾンのサーバでエラーが起こっているかもしれません。一度ページを再読み込みしてみてください。');
		}else{
			// Amazon function returned XML data
			$status = $result->Items->Request->IsValid;
			if( $status == 'False' ){
				// Request is invalid
				return $this->error_message('与えられたリクエストが正しくありません');
			}else{
				// results were found, so display the products
				$item = $result->Items->Item[0];
				$atts = $this->get_atts($item);
				$goodsimage = $this->get_image_src($item, 'large');

				$url = $item->DetailPageURL;
				$Title = $atts['Title'];
				$ProductGroup = isset($this->searchIndex[$atts['ProductGroup']]) ? $this->searchIndex[$atts['ProductGroup']]: '不明' ;
				if(isset($atts['ProductGroup']) ){
					switch($atts['ProductGroup']){
						case 'Book':
							$ProductGroup = '書籍';
							break;
						case 'eBooks':
							$ProductGroup = 'Kindle本';
							break;
					}
				}
				$ProductGroup = " <small>[{$ProductGroup}]</small>";
				$price = $atts['ListPrice']['FormattedPrice'];
				$desc = $price ? "<p class=\"price\"><span class=\"label\">価格</span><em>{$price}</em></p>" : '';
				$filter = array(
					'author' => array('Author', 'Director', 'Actor', 'Artist', 'Creator'),
					'publisher' => array('Publisher', 'Studio', 'Label', 'Brand', 'Manufacturer'),
					'Date' => array('PublicationDate'),
					'allowable' => array('Binding', 'NumberOfPages', 'ISBN', 'Feature')
				);
				foreach($filter as $filter => $vals){
					foreach($vals as $val){
						if(isset($atts[$val])){
							$key = $this->atts_to_string($val);
							$value = esc_html(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $atts[$val]) ? mysql2date(get_option('date_format'), $atts[$val]) : $atts[$val]);
							$desc .= "<p><span class=\"label\">{$key}</span><em>{$value}</em></p>";
							if($filter != 'allowable' && $filter != 'author'){
								break;
							}
						}
					}
				}
				if($this->show_review && 'true' === (string)$item->CustomerReviews->HasReviews){
					$review = sprintf('<p class="review"><iframe src="%s"></iframe></p>', $item->CustomerReviews->IFrameURL);
				}else{
					$review = '';
				}
				if(!empty($content)){
					$desc .= sprintf('<p class="additional-description">%s</p>', $content);
				}
				$tag = <<<EOS
<div class="tmkm-amazon-view wp-hamazon-amazon">
<p class="tmkm-amazon-img"><a href="{$url}" target="_blank"><img src="{$goodsimage}" border="0" alt="{$Title}" /></a></p>
<p class="tmkm-amazon-title"><a href="{$url}" target="_blank">{$Title}{$ProductGroup}</a></p>
{$desc}{$review}
<p class="vendor"><a href="https://affiliate.amazon.co.jp/gp/advertising/api/detail/main.html">Supported by amazon Product Advertising API</a></p>
</div>
EOS;
				return apply_filters('wp_hamazon_amazon', $tag, $item, $extra_atts, $content);
			}
		}
	}
	
	
	
	/**
	 * 記事本文中のショートコードを個別商品表示 HTML に置き換える
	 * 
	 * @param $content
	 * @return $transformedstring
	 */
	public function shortcode_tmkm_amazon($atts = array(), $content = null){
		$atts = shortcode_atts(array(
			'description' => '',
			'asin' => '0',
		), $atts);
		return $this->format_amazon($content, $atts);
	}

	/**
	 * 文字列がASINコードであるかを検索
	 *
	 * @param $asin
	 * @return bool
	 */
	private function is_asin($asin){
		return (boolean)preg_match('/^[0-9a-zA-Z]{10,13}$/', trim($asin));
	}
	
	/**
	 * 書籍の一覧を取得する
	 * @global wpdb $wpdb
	 * @param array $attr
	 * @return string
	 */
	public function shortcode_tmkm_amazon_list($attr){
		global $wpdb;

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
	    			$display = $this->format_amazon($asin);
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
	 * 検索フォームを出力する
	 */
	public function show_form() {
		?>
		<form method="get" class="hamazon-search-form search-amazon" action="<?php echo plugin_dir_url(dirname(__FILE__)); ?>endpoint/amazon.php">
			<?php wp_nonce_field('amazon_search', '_wpnonce', false); ?>
			<p style="display: inline;"><a id="searchpagetop">Amazon 検索</a></p>&nbsp;
			<select name="SearchIndex">
				<?php foreach($this->searchIndex as $k => $v): ?>
				<option value="<?php echo $k; ?>"<?php if((isset($_GET['SearchIndex']) && $_GET['SearchIndex'] == $k) || (!isset($_GET['SearchIndex']) && $k == 'Books')) echo ' selected="selected"'; ?>>
					<?php echo $v; ?>
				</option>
				<?php endforeach; ?>
			</select>
			<input type="text" size="20" maxlength="50" value="<?php if(isset($_GET['keyword'])) echo esc_attr($_GET['keyword']); ?>" name="keyword" />&nbsp;
			<input class="button-primary" type="submit" style="cursor:pointer;" value="検索" />
		</form>
		<?php
	}
	
	
	
	/**
	 * 商品検索結果を表示する
	 */
	public function show_results() {
		// Get pagination
		if( isset( $_GET['page'] ) ){
			$page_num = max(1, (int) $_GET['page']);
		}else{
			$page_num = 1;
		}
		//Start Searching
		if(isset($_GET['keyword'], $_GET['_wpnonce']) && !empty( $_GET['keyword']) && wp_verify_nonce($_GET['_wpnonce'], 'amazon_search')){
			echo '<div id="amazon-search-result">';
			$keyword = (string) $_GET['keyword'];
			$searchindex = !empty( $_GET['SearchIndex'] ) ? $_GET['SearchIndex'] : 'Blended';
			$result = $this->search_with($keyword, $page_num, $searchindex);

			if(is_wp_error($result) ){
				// Amazon function was returned false, so AWS is down
				echo '<div class="error"><p>検索結果を取得できませんでした。amazonのサーバでエラーが起こっているかもしれません。</p></div>';
			}else{
				// Amazon function returned XML data
				if($result->Items->Request->Errors){
					printf('<div class="error"><p>%s</p></div>', $result->Items->Request->Errors->Error->Message);
				}else{
					// results were found, so display the products
					$total_results = $result->Items->TotalResults;
					$total_pages =  $result->Items->TotalPages;
					$per_page = $searchindex == 'Blended' ? 3 : 10;

					if( $total_results == 0 ){ // no result was found
						printf('<div class="error"><p>「%s」の検索結果が見つかりませんでした。</p></div>', esc_html($keyword));
					} else {
						// Pagenation
						if( $total_pages > 1 ) {
							$pagination = $this->paginate($total_pages, $page_num, 1, array(
								'SearchIndex' => $searchindex,
								'keyword' => $keyword,
								'_wpnonce' => wp_create_nonce('amazon_search'),
							));
						}else{
							$pagination = '';
						}
						// results were found
						$length = count($result->Items->Item);
						?>
							<div class="result-desc clearfix">
								<h1>「<?php echo esc_html($keyword); ?>」の検索結果: <?php echo number_format((string)$total_results); ?>件</h1>
								<?php echo $pagination; ?>
							</div><!-- //.result-desc -->
							<table class="wp-hamazon-product-table">
						<?php
						for($i = 0; $i < $length; $i++) {
							$item = $result->Items->Item[$i];
							$smallimage = $this->get_image_src($item,'small');
							$atts = $this->get_atts($item);
							?>
								<tr class="amazon">
									<th>
										<?php if($searchindex !== 'Blended'): ?>
										<em>No. <?php echo number_format( ($page_num - 1) * $per_page + $i + 1); ?></em><br />
										<?php endif; ?>
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
													echo $this->atts_to_string($key).": ".$atts[$key]."<br />";
												}
											}
										?>
										<label>コード: <input type="text" class="hamazon-target" size="40" value="[tmkm-amazon asin='<?php echo $item->ASIN; ?>'][/tmkm-amazon]" onclick="this.select();" /></label>
										<a class="button-primary hamazon-insert" data-target=".hamazon-target" href="#">挿入</a>
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
}