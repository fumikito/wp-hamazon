<?php

namespace Hametuha\WpHamazon\Constants;


class AmazonLocales {

	/**
	 * Get locale informations.
	 *
	 * @see {https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region}
	 * @return array
	 */
	public static function get_locales() {
		$locales  = [
			'AU'            => [
				'label'  => 'Australia',
				'region' => 'us-west-2',
				'domain' => 'com.au',
			],
			'BZ'               => [
				'label'  => 'Brasil',
				'domain' => 'com.br',
			],
			'CA'               => [
				'label'  => 'Canada',
				'domain' => 'ca',
			],
			'FR'               => [
				'label'  => 'France',
				'region' => 'eu-west-1',
				'domain' => 'fr',
			],
			'DE'              => [
				'label'  => 'Deautsch',
				'region' => 'eu-west-1',
				'domain' => 'de',
			],
			'IN'                => [
				'label'  => 'India',
				'region' => 'eu-west-1',
				'domain' => 'in',
			],
			'IT'                => [
				'label'  => 'Italia',
				'region' => 'eu-west-1',
				'domain' => 'it',
			],
			'JP'                => [
				'label'  => '日本',
				'region' => 'us-west-2',
				'domain' => 'co.jp',
			],
			'MX'               => [
				'label'  => 'México',
				'domain' => 'com.mx',
			],
			'NL'          => [
				'label'  => 'Nederland',
				'region' => 'eu-west-1',
				'domain' => 'nl',
			],
			'SP'            => [
				'label'  => 'Singapore',
				'region' => 'us-west-2',
				'domain' => 'sg',
			],
			'ES'                => [
				'label'  => 'España',
				'region' => 'eu-west-1',
				'domain' => 'es',
			],
			'TK'               => [
				'label'  => 'Türkiye',
				'region' => 'eu-west-1',
				'domain' => 'com.tr',
			],
			'AE' => [
				'label'  => 'الإمارات العربيّة المتّحدة',
				'region' => 'eu-west-1',
				'domain' => 'ae',
			],
			'UK'       => [
				'label'  => 'United Kingdom',
				'region' => 'eu-west-1',
				'domain' => 'co.uk',
			],
			'US'        => [
				'label'  => 'United States',
			],
		];
		$filtered = [];
		foreach ( $locales as $locale => $option ) {
			$filtered[ $locale ] = wp_parse_args( $option, [
				'label'  => '',
				'region' => 'us-east-1',
				'domain' => 'com',
			] );
		}

		return $filtered;
	}

	/**
	 *
	 * Get locales.
	 *
	 * @return array
	 */
	public static function get_locale_labels() {
		$filtered = [];
		foreach ( self::get_locales() as $key => $locale ) {
			$filtered[ $locale['label'] ] = $key;
		}
		return $filtered;
	}

	/**
	 * Geet host
	 *
	 * @param string $locale
	 * @return string
	 */
	public static function get_host( $locale ) {
		return self::get_key( 'domain', $locale );
	}

	/**
	 * Get host and key.
	 *
	 * @param string $locale
	 *
	 * @return string
	 */
	public static function get_region( $locale ) {
		return self::get_key( 'region', $locale );
	}

	/**
	 * Get current locale.
	 *
	 * @return string
	 */
	public static function get_language_locale() {
		$locale = get_user_locale();
		if ( 'ja' === $locale ) {
			$locale = 'ja_JP';
		}
		return $locale;
	}

	/**
	 * Get available sort order.
	 *
	 * @return array
	 */
	public static function get_sort_orders() {
		return [
			'AvgCustomerReviews' => _x( 'Review Rank', 'Sort order', 'hamazon' ),
			'Featured'           => _x( 'Featured', 'Sort order', 'hamazon' ),
			'NewestArrival'      => _x( 'Newer', 'Sort order', 'hamazon' ),
			'Price:HighToLow'    => _x( 'Higher Cost', 'Sort order', 'hamazon' ),
			'Price:LowToHigh'    => _x( 'Cheaper Cost', 'Sort order', 'hamazon' ),
			'Relevance'          => _x( 'Relevant', 'Sort order', 'hamazon' ),
		];
	}

	/**
	 * Get key
	 *
	 * @param string $key
	 * @param string $locale
	 * @return string
	 */
	private static function get_key( $key, $locale ) {
		$locales = self::get_locales();
		if ( ! isset( $locales[ $locale ] ) || ! isset( $locales[ $locale ][ $key ] ) ) {
			return '';
		}
		$value = $locales[ $locale ][ $key ];
		switch ( $key ) {
			case 'domain':
				$value = sprintf( 'webservices.amazon.%s', $value );
				break;
		}
		return $value;
	}
}
