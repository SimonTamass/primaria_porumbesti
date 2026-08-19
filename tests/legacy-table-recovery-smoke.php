<?php
define( 'ABSPATH', __DIR__ );

function wp_strip_all_tags( string $content ): string { return strip_tags( $content ); }
function esc_url( string $url ): string { return $url; }
function esc_html( string $value ): string { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }

require_once dirname( __DIR__ ) . '/includes/class-template-applier.php';

$reflection = new ReflectionClass( '\PrimariaPorumbesti\Template_Applier' );
$applier = $reflection->newInstanceWithoutConstructor();
$normalize = $reflection->getMethod( 'normalize_legacy_content' );

$fixtures = array(
	2 => array(
		'source'  => '[vc_row][vc_column][vc_raw_html]JTVCdGFibGUlMjBpZCUzRDIlMjAlMkYlNUQ=[/vc_raw_html][/vc_column][/vc_row]',
		'needles' => array( 'Departamente', 'Taxe şi Impozite Locale:', 'Moldovan Charleta Diana', 'mailto:taxa_si_impozite@primariaporumbesti.ro', 'Marozsan Andrea' ),
	),
	1 => array(
		'source'  => '[vc_raw_html]JTVCdGFibGUlMjBpZCUzRDElMjAlMkYlNUQ=[/vc_raw_html]',
		'needles' => array( 'Hivatali részlegek', 'Adónyilvántartási szakosztály:', 'Csorba Levente', 'mailto:secretar@primariaporumbesti.ro' ),
	),
	4 => array(
		'source'  => '[vc_raw_html]JTVCdGFibGUlMjBpZCUzRDQlMjAlMkYlNUQ=[/vc_raw_html]',
		'needles' => array( 'Conducere', 'Tóth Zoltán', 'Simon Ilie', 'Jakab Andrea Elisabeta', 'referent superior' ),
	),
);

foreach ( $fixtures as $table_id => $fixture ) {
	$normalized = $normalize->invoke( $applier, $fixture['source'] );
	if ( ! str_contains( $normalized, '<table class="porumbesti-table">' ) || str_contains( $normalized, 'JTVCdGFibG' ) || str_contains( $normalized, '[table' ) ) {
		fwrite( STDERR, "Legacy table {$table_id} was not decoded into fallback markup.\n" );
		exit( 1 );
	}
	foreach ( $fixture['needles'] as $needle ) {
		if ( ! str_contains( $normalized, $needle ) ) {
			fwrite( STDERR, "Legacy table {$table_id} is missing preserved content: {$needle}.\n" );
			exit( 1 );
		}
	}
}

echo "Legacy table recovery smoke passed.\n";
