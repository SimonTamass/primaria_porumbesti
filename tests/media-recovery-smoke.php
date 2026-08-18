<?php
define( 'ABSPATH', __DIR__ );

function wp_strip_all_tags( string $content ): string { return strip_tags( $content ); }
function sanitize_title( string $title ): string { return strtolower( trim( $title ) ); }
function sanitize_key( string $key ): string { return strtolower( preg_replace( '/[^a-z0-9_-]/i', '', $key ) ); }
function esc_html( string $value ): string { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function wp_get_attachment_image( int $id ): string { return '<img class="wp-image-' . $id . '" src="https://example.test/uploads/' . $id . '.jpg" alt="">'; }
function wp_get_attachment_caption( int $id ): string { return 5278 === $id ? 'Primar' : ''; }

require_once dirname( __DIR__ ) . '/includes/class-template-applier.php';

$reflection = new ReflectionClass( '\PrimariaPorumbesti\Template_Applier' );
$applier = $reflection->newInstanceWithoutConstructor();
$ids_method = $reflection->getMethod( 'legacy_media_ids' );
$aliases_method = $reflection->getMethod( 'slider_aliases' );
$normalize_method = $reflection->getMethod( 'normalize_legacy_content' );

$source = '[vc_row background_image="4100"][vc_single_image image="5278"][/vc_row][vc_gallery images="6001,6002"][rev_slider alias="home-ro"]';
$ids = $ids_method->invoke( $applier, $source );
sort( $ids );
if ( array( 4100, 5278, 6001, 6002 ) !== $ids ) {
	fwrite( STDERR, "Legacy media ID extraction failed.\n" );
	exit( 1 );
}

if ( array( 'home-ro' ) !== $aliases_method->invoke( $applier, $source ) ) {
	fwrite( STDERR, "Slider alias extraction failed.\n" );
	exit( 1 );
}

$normalized = $normalize_method->invoke( $applier, '[vc_row][vc_single_image image="5278"][vc_gallery images="6001,6002"][/vc_row][video mp4="https://example.test/media.mp4"][/video]' );
if ( ! str_contains( $normalized, 'wp-image-5278' ) || ! str_contains( $normalized, 'porumbesti-legacy-gallery' ) || ! str_contains( $normalized, '[video mp4=' ) || str_contains( $normalized, '[vc_' ) ) {
	fwrite( STDERR, "Legacy media expansion failed.\n" );
	exit( 1 );
}

$applier_source = file_get_contents( dirname( __DIR__ ) . '/includes/class-template-applier.php' );
foreach ( array( 'revslider_sliders', 'revslider_slides', 'home_ro_data( \\WP_Post $page )', 'mayor_ro_data( \\WP_Post $page )', "design_media( '2019/03/sisop.jpg', 'sgg-transparency' )" ) as $needle ) {
	if ( ! str_contains( $applier_source, $needle ) ) {
		fwrite( STDERR, "Missing media recovery contract: {$needle}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $applier_source, "design_media( '2018/07/hatter-13.jpg'" ) ) {
	fwrite( STDERR, "The approved local hero image is not assigned through widget media.\n" );
	exit( 1 );
}

echo "Media recovery smoke passed.\n";
