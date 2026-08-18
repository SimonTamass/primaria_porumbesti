<?php

$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$frontend = file_get_contents( $root . '/includes/class-frontend-templates.php' );
$footer = file_get_contents( $root . '/includes/widgets/class-site-footer.php' );
$contact = file_get_contents( $root . '/includes/widgets/class-contact-details.php' );
$prototype = file_get_contents( $root . '/tools/build-prototype.mjs' );

$official_urls = array(
	'https://server58.romania-webhosting.com:2096/',
	'https://www.facebook.com/KOKENYESD/',
	'https://portal.primariaporumbesti.ro/',
	'https://sgg.gov.ro/new/guvernare-transparenta-deschisa-si-participativa-standardizare-armonizare-dialog-imbunatatit-cod-sipoca-35/',
);

foreach ( $official_urls as $url ) {
	foreach ( array( 'template applier' => $applier, 'global frontend' => $frontend, 'prototype' => $prototype ) as $surface => $source ) {
		if ( ! str_contains( $source, $url ) ) {
			fwrite( STDERR, "Missing official URL from {$surface}: {$url}\n" );
			exit( 1 );
		}
	}
}

$preserved_surfaces = array(
	"original_content_sections( \$page, 'ro', 'home-ro-'",
	"original_content_sections( \$page, 'hu', 'home-hu-'",
	"original_content_sections( \$page, 'ro', \$seed )",
);
foreach ( $preserved_surfaces as $needle ) {
	if ( ! str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Missing complete original-content preservation: {$needle}\n" );
		exit( 1 );
	}
}
if ( substr_count( $applier, "original_content_sections( \$page, 'ro', \$seed )" ) < 4 ) {
	fwrite( STDERR, "Not every specialized Romanian page preserves the original content.\n" );
	exit( 1 );
}

$checks = array(
	'original tables and document links' => array( $applier, "<(?:a|audio|figure|iframe|img|table|video)\\b" ),
	'original unplaced media' => array( $applier, "'-original-media-widget'" ),
	'footer external controls' => array( $footer, "add_control( 'external_links'" ),
	'footer external rendering' => array( $footer, 'porumbesti-footer-external' ),
	'SGG transparency image' => array( $applier, "design_media( '2019/03/sisop.jpg', 'sgg-transparency' )" ),
	'official map coordinates' => array( $applier, '47.9839429,22.9718739' ),
	'optional public hours' => array( $contact, "if ( ! empty( \$s['hours'] ) )" ),
);
foreach ( $checks as $label => $check ) {
	if ( ! str_contains( $check[0], $check[1] ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

$combined = $applier . $contact . $prototype;
foreach ( array( 'media_item_from_id( 4295', '47.8816707', '23.0048293', 'Luni–Vineri · 08:00–16:00', 'Hétfő–péntek · 08:00–16:00' ) as $stale_data ) {
	if ( str_contains( $combined, $stale_data ) ) {
		fwrite( STDERR, "Unverified or stale public data remains: {$stale_data}\n" );
		exit( 1 );
	}
}

echo "Full content parity smoke passed.\n";
