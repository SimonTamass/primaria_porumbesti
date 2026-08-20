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
$integrated_profile = $reflection->getMethod( 'integrated_profile_content' );
$mayor_message = $reflection->getMethod( 'mayor_message_from_source' );

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

$mayor_source = '<h2>Primar</h2><figure class="porumbesti-legacy-media"><img class="wp-image-51" src="https://example.test/Toth-Zoltan3-1-739x1024.jpg" alt="Toth Zoltan"></figure><h4>- Tóth Zoltán</h4><h4>- Data naşterii: 1973. 11.27.</h4><h4>- Studii: Economist Facultatea de științe Economice Vasile Goldis</h4><p><a class="porumbesti-legacy-button" href="https://example.test/DA-Toth-Zoltan-2025.pdf">Declaratia de avere 2025</a><a class="porumbesti-legacy-button" href="https://example.test/DI-Toth-Zoltan-2025.pdf">Declaratie de interese 2025</a></p>';
$profile_content = $integrated_profile->invoke(
	$applier,
	$mayor_source,
	array( 'id' => 51, 'url' => 'https://example.test/Toth-Zoltan3-1.jpg' ),
	array( 'Primar', 'Tóth Zoltán' )
);

if ( str_contains( $profile_content, '<figure' ) || str_contains( $profile_content, '<img' ) || str_contains( $profile_content, '<h2>Primar' ) || str_contains( $profile_content, '>Tóth Zoltán<' ) ) {
	fwrite( STDERR, "Reused mayor identity or portrait was not deduplicated.\n" );
	exit( 1 );
}

$preserved_profile_details = array(
	'Data naşterii: 1973. 11.27.',
	'Studii: Economist Facultatea de științe Economice Vasile Goldis',
	'https://example.test/DA-Toth-Zoltan-2025.pdf',
	'https://example.test/DI-Toth-Zoltan-2025.pdf',
	'Declaratia de avere 2025',
	'Declaratie de interese 2025',
);
foreach ( $preserved_profile_details as $detail ) {
	if ( ! str_contains( $profile_content, $detail ) ) {
		fwrite( STDERR, "Mayor source detail was not preserved: {$detail}\n" );
		exit( 1 );
	}
}

if ( 2 !== substr_count( $profile_content, 'porumbesti-profile-fact' ) ) {
	fwrite( STDERR, "Mayor facts were not converted into profile rows.\n" );
	exit( 1 );
}

$welcome_source = '<h1>Tisztelt Látogatók!</h1><h3>Megtiszteltetés számomra, hogy a település polgármestereként, önkormányzatunk nevében köszönthetek minden érdeklődőt.</h3><h3>Nehéz feladat, hiszen mi, lokálpatrióták talán elfogultak vagyunk.</h3><h3>De ez így helyes.</h3><h4>Mindannyiunk közös hite, lelkesedése, szorgalma, kitartása és az összefogás képessége tette Kökényesdet azzá, amilyennek most láthatjuk.</h4><h4>Arra törekszünk, hogy honlapunk minden fontos, hasznos helyi információt tartalmazzon.</h4><h4>Tóth Zoltán</h4><h4>Vezetőség</h4>';
$welcome_message = $mayor_message->invoke( $applier, $welcome_source, 'hu' );
foreach ( array( 'Megtiszteltetés számomra', 'Nehéz feladat', 'De ez így helyes.', 'Mindannyiunk közös hite', 'Arra törekszünk', 'Tóth Zoltán · Polgármester' ) as $phrase ) {
	if ( ! str_contains( $welcome_message, $phrase ) ) {
		fwrite( STDERR, "Homepage welcome message was not preserved: {$phrase}.\n" );
		exit( 1 );
	}
}
if ( str_contains( $welcome_message, 'Vezetőség' ) ) {
	fwrite( STDERR, "Homepage welcome message leaked leadership markup.\n" );
	exit( 1 );
}

echo "Legacy content smoke passed.\n";
