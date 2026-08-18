<?php
define( 'ABSPATH', __DIR__ );

function wp_get_attachment_image( int $id ): string {
	return '<img class="wp-image-' . $id . '" src="image-' . $id . '.jpg">';
}
function wp_get_attachment_caption(): string { return ''; }
function esc_html( string $value ): string { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( string $value ): string { return $value; }
function wp_kses_post( string $value ): string { return $value; }

require_once dirname( __DIR__ ) . '/includes/class-legacy-content.php';

$legacy = '[vc_row][vc_column][qode_advanced_image_gallery type=”grid” images=”8559,8560″][vc_column_text]Descarcare PDF: <a href="document.pdf">ANUNT</a>[/vc_column_text][/vc_column][/vc_row]';
$normalized = \PrimariaPorumbesti\Legacy_Content::normalize( $legacy );

if ( 2 !== substr_count( $normalized, 'porumbesti-legacy-media' ) || str_contains( $normalized, '[vc_' ) || str_contains( $normalized, '[qode_' ) || ! str_contains( $normalized, 'document.pdf' ) ) {
	fwrite( STDERR, "Legacy post content normalization failed.\n" );
	exit( 1 );
}

$protected = \PrimariaPorumbesti\Legacy_Content::normalize( '[video src="movie.mp4"][/video]' );
if ( ! str_contains( $protected, '[video' ) || ! str_contains( $protected, '[/video]' ) ) {
	fwrite( STDERR, "Native media shortcode protection failed.\n" );
	exit( 1 );
}

echo "Legacy post content smoke passed.\n";
