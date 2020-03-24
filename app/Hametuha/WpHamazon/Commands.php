<?php

namespace Hametuha\WpHamazon;

use Hametuha\WpHamazon\Constants\AmazonConstants;

/**
 * CLI for WP Hamazon
 */
class Commands extends \WP_CLI_Command {

	/**
	 * Search items in amazon
	 *
	 * Search amazon item with provided query.
	 *
	 * @throws \Exception
	 * @param array $args
	 * @param array $attr
	 * @synopsis <query> [--page=<paged>] [--index=<index>]
	 */
	public function search( $args, $attr ) {
		list( $query ) = $args;
		$attr = wp_parse_args( $attr, [
			'page'  => 1,
			'index' => 'All',
		] );
		$result = AmazonConstants::search_with( $query, $attr['page'], $attr['index'] );
		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}
		\WP_CLI::line( sprintf( '%s found.', $result['total_result'] ) );
		\WP_CLI::line( '-------------' );
		foreach ( $result['items'] as $item ) {
			$table = new \cli\Table();
			$table->setHeaders( [ 'Key', 'Value' ] );
			foreach ( [ 'title', 'price', 'category', 'asin', 'date', 'url' ] as $key ) {
				$table->addRow( [ ucfirst( $key ), $item[ $key ] ] );
			}
			$table->display();
		}
	}

}
