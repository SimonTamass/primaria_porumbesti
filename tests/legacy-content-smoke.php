<?php
define( 'ABSPATH', __DIR__ );

function wp_strip_all_tags( string $content ): string {
	return strip_tags( $content );
}

function sanitize_title( string $title ): string {
	$title = strtolower( trim( $title ) );
	return preg_replace( '/[^a-z0-9-]+/', '-', $title );
}
function esc_url( string $url ): string { return $url; }
function esc_html( string $value ): string { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function wp_kses_post( string $content ): string { return $content; }

require_once dirname( __DIR__ ) . '/includes/class-template-applier.php';

$reflection = new ReflectionClass( '\PrimariaPorumbesti\Template_Applier' );
$applier = $reflection->newInstanceWithoutConstructor();
$normalize = $reflection->getMethod( 'normalize_legacy_content' );
$category = $reflection->getMethod( 'legacy_category_slug' );
$queries = $reflection->getMethod( 'legacy_post_queries' );

$legacy = '[vc_row][vc_column_text]<h1>Galeria Foto</h1><p>Text păstrat.</p>[/vc_column_text][masonry_blog order="DESC" category="galeria-foto-2018"][/vc_row]';
$normalized = $normalize->invoke( $applier, $legacy );

if ( str_contains( $normalized, '[' ) || ! str_contains( $normalized, '<h2>Galeria Foto</h2>' ) || ! str_contains( $normalized, 'Text păstrat.' ) ) {
	fwrite( STDERR, "Legacy layout shortcode normalization failed.\n" );
	exit( 1 );
}

if ( 'galeria-foto-2018' !== $category->invoke( $applier, $legacy ) ) {
	fwrite( STDERR, "Legacy masonry category detection failed.\n" );
	exit( 1 );
}

$post_queries = $queries->invoke( $applier, '[latest_post number_of_colums="3" number_of_rows="1" category="regulament"] [masonry_blog number_of_posts="100" category="anunturi"]' );
if ( 2 !== count( $post_queries ) || 3 !== $post_queries[0]['count'] || 'regulament' !== $post_queries[0]['category'] || 100 !== $post_queries[1]['count'] || 'anunturi' !== $post_queries[1]['category'] ) {
	fwrite( STDERR, "Legacy post query recovery failed.\n" );
	exit( 1 );
}

$links = $normalize->invoke( $applier, '<strong>[otw_shortcode_button href="https://example.test/file.pdf"]Declarație[/otw_shortcode_button]</strong> [button text="Hotărâri 2026" link="https://example.test/ro/2026/"]' );
if ( ! str_contains( $links, 'href="https://example.test/file.pdf"' ) || ! str_contains( $links, '>Declarație</a>' ) || ! str_contains( $links, 'href="https://example.test/ro/2026/"' ) || ! str_contains( $links, '>Hotărâri 2026</a>' ) ) {
	fwrite( STDERR, "Legacy button link recovery failed.\n" );
	exit( 1 );
}

$nested = '<div class="porumbesti-header-wrap">Duplicated header</div><div class="porumbesti-richtext"><div class="porumbesti-richtext"><p>Conținut real.</p></div></div><footer class="porumbesti-footer">Duplicated footer</footer>';
$normalized_nested = $normalize->invoke( $applier, $nested );
if ( str_contains( $normalized_nested, 'Duplicated header' ) || str_contains( $normalized_nested, 'Duplicated footer' ) || ! str_contains( $normalized_nested, 'Conținut real.' ) ) {
	fwrite( STDERR, "Nested Elementor content extraction failed.\n" );
	exit( 1 );
}

echo "Legacy content smoke passed.\n";
