<?php

define( 'ABSPATH', __DIR__ );

class WP_Post {
	public int $ID;
	public string $post_name;
	public string $post_type;
	public string $post_content;
	public string $post_title;
	public string $language;
	public string $permalink;

	public function __construct( int $id, string $name, string $type, string $content, string $title, string $language, string $permalink ) {
		$this->ID = $id;
		$this->post_name = $name;
		$this->post_type = $type;
		$this->post_content = $content;
		$this->post_title = $title;
		$this->language = $language;
		$this->permalink = $permalink;
	}
}

$fixture_posts = array();

function get_posts( array $args ): array {
	global $fixture_posts;
	$types = (array) $args['post_type'];
	return array_values( array_filter( $fixture_posts, static function ( WP_Post $post ) use ( $args, $types ): bool {
		return in_array( $post->post_type, $types, true ) && ( empty( $args['name'] ) || $post->post_name === $args['name'] );
	} ) );
}
function pll_get_post_language( int $post_id ): string { global $fixture_posts; foreach ( $fixture_posts as $post ) { if ( $post->ID === $post_id ) { return $post->language; } } return ''; }
function get_permalink( WP_Post $post ): string { return $post->permalink; }
function get_the_title( WP_Post $post ): string { return $post->post_title; }
function wp_parse_url( string $url, int $component = -1 ) { return parse_url( $url, $component ); }
function wp_strip_all_tags( string $content ): string { return strip_tags( $content ); }
function remove_accents( string $value ): string { return strtr( $value, array( 'í' => 'i', 'ș' => 's', 'ț' => 't' ) ); }
function sanitize_title( string $value ): string { return strtolower( trim( preg_replace( '/[^a-z0-9-]+/i', '-', $value ), '-' ) ); }
function untrailingslashit( string $value ): string { return rtrim( $value, "/\\" ); }
function esc_url( string $value ): string { return $value; }
function esc_html( string $value ): string { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function wp_kses_post( string $value ): string { return $value; }

require_once dirname( __DIR__ ) . '/includes/class-template-applier.php';

$fixture_posts = array(
	new WP_Post( 3, 'collision', 'post', '<p>Rossz nyelvű tartalom.</p>', 'Magyar', 'hu', 'https://example.test/hu/collision/' ),
	new WP_Post( 2, 'collision', 'post', '<p>Recovered public body. <a href="https://example.test/file.pdf">PDF</a></p>', 'Recovered post', 'ro', 'https://example.test/ro/collision/' ),
	new WP_Post( 1, 'collision', 'page', '', 'Collision page', 'ro', 'https://example.test/ro/collision/' ),
	new WP_Post( 4, 'another-page', 'page', '', 'Another page', 'ro', 'https://example.test/ro/another-page/' ),
);

$reflection = new ReflectionClass( '\PrimariaPorumbesti\Template_Applier' );
$applier = $reflection->newInstanceWithoutConstructor();
$same_slug = $reflection->getMethod( 'same_slug_post_content' );
$sitemap = $reflection->getMethod( 'sitemap_content' );

$recovered = $same_slug->invoke( $applier, $fixture_posts[2], 'ro' );
if ( ! str_contains( $recovered, 'Recovered public body' ) || str_contains( $recovered, 'Rossz nyelvű' ) ) {
	fwrite( STDERR, "Same-slug content recovery did not select the matching language.\n" );
	exit( 1 );
}

$map = $sitemap->invoke( $applier, 'ro' );
if ( ! str_contains( $map, 'Another page' ) || str_contains( $map, 'Magyar' ) ) {
	fwrite( STDERR, "Language-aware sitemap generation failed.\n" );
	exit( 1 );
}
if ( 1 !== substr_count( $map, 'https://example.test/ro/collision/' ) ) {
	fwrite( STDERR, "Sitemap URL de-duplication failed.\n" );
	exit( 1 );
}

echo "Content recovery behavior smoke passed.\n";
