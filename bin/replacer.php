<?php
/**
 * Library fixer.
 *
 * thewirecutter/paapi5-php-sdk is blocked by php lint error.
 * So fix it.
 *
 * PHP Fatal error:  Cannot use "parent" when current class scope has no parent in vendor/thewirecutter/paapi5-php-sdk/src/com/amazon/paapi5/v1/Properties.php on line 176
 *
 * Fatal error: Cannot use "parent" when current class scope has no parent in vendor/thewirecutter/paapi5-php-sdk/src/com/amazon/paapi5/v1/Properties.php on line 176
 * Errors parsing vendor/thewirecutter/paapi5-php-sdk/src/com/amazon/paapi5/v1/Properties.php
 */

$list_to_fix = [
	[ 'vendor/thewirecutter/paapi5-php-sdk/src/com/amazon/paapi5/v1/Properties.php', '$invalidProperties = parent::listInvalidProperties();

        return $invalidProperties;', 'return [];' ]
];

$done = [];

foreach ( $list_to_fix as list( $path, $needle, $replaced ) ) {
	if ( ! file_exists( $path ) ) {
		printf( 'File not found: %s', $path );
		exit( 1 );
	}
	$contents = file_get_contents( $path );
	if ( false === strpos( $contents, $needle ) ) {
		printf( 'Needle not found: %s %s', $path . "\n\n", $needle );
		exit( 1 );
	}
	$content = str_replace( $needle, $replaced, $contents );
	if( file_put_contents( $path, $content ) ) {
		$done[] = $path;
	}
}

printf( "These files are replaced: \n%s\n", implode( "\n", $done ) );
exit( 0 );
