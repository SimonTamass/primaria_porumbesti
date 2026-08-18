<?php
$root = dirname( __DIR__ );
$snapshot_path = $root . '/content/live-content-snapshot.json';
$contract_path = $root . '/content/live-url-contract.json';
$manifest_path = $root . '/content/source-manifest.json';
$applier_path = $root . '/includes/class-template-applier.php';

foreach ( array( $snapshot_path, $contract_path, $manifest_path, $applier_path ) as $path ) {
	if ( ! is_file( $path ) ) {
		fwrite( STDERR, "Missing URL audit source: {$path}\n" );
		exit( 1 );
	}
}

$snapshot = json_decode( (string) file_get_contents( $snapshot_path ), true, 512, JSON_THROW_ON_ERROR );
$contract = json_decode( (string) file_get_contents( $contract_path ), true, 512, JSON_THROW_ON_ERROR );
$manifest = json_decode( (string) file_get_contents( $manifest_path ), true, 512, JSON_THROW_ON_ERROR );
$applier = (string) file_get_contents( $applier_path );

$collections = array( 'pages', 'posts', 'categories', 'media' );
foreach ( $collections as $collection ) {
	if ( ! isset( $snapshot['counts'][ $collection ], $snapshot[ $collection ] ) || (int) $snapshot['counts'][ $collection ] !== count( $snapshot[ $collection ] ) ) {
		fwrite( STDERR, "Incomplete live snapshot collection: {$collection}.\n" );
		exit( 1 );
	}
}

if ( count( $snapshot['pages'] ) < 40 || count( $snapshot['posts'] ) < 500 || count( $snapshot['categories'] ) < 30 || count( $snapshot['media'] ) < 4000 ) {
	fwrite( STDERR, "The public WordPress inventory is unexpectedly incomplete.\n" );
	exit( 1 );
}

$expected_routes = count( $snapshot['pages'] ) + count( $snapshot['posts'] ) + count( $snapshot['categories'] );
if ( $expected_routes !== count( $contract['publicRoutes'] ?? array() ) ) {
	fwrite( STDERR, "The public URL contract does not cover every page, post and category.\n" );
	exit( 1 );
}

$route_urls = array();
$route_identities = array();
foreach ( $contract['publicRoutes'] as $route ) {
	if ( empty( $route['kind'] ) || empty( $route['id'] ) || empty( $route['slug'] ) || empty( $route['url'] ) ) {
		fwrite( STDERR, "A public route is missing its kind, ID, slug or URL.\n" );
		exit( 1 );
	}
	$identity = $route['kind'] . ':' . $route['id'];
	if ( isset( $route_identities[ $identity ] ) ) {
		fwrite( STDERR, "Duplicate public route identity: {$identity}.\n" );
		exit( 1 );
	}
	$route_identities[ $identity ] = true;
	$route_urls[ untrailingslashit_for_test( $route['url'] ) ] = true;
}

foreach ( $manifest['pageMappings'] as $mapping ) {
	foreach ( $mapping as $url ) {
		if ( ! isset( $route_urls[ untrailingslashit_for_test( $url ) ] ) ) {
			fwrite( STDERR, "A core RO/HU source URL is absent from the live URL contract: {$url}.\n" );
			exit( 1 );
		}
	}
}

preg_match_all( "/home_url\\( '([^']+)' \\)/", $applier, $hardcoded_paths );
foreach ( array_unique( $hardcoded_paths[1] ?? array() ) as $path ) {
	$url = 'https://primariaporumbesti.ro' . $path;
	if ( ! isset( $route_urls[ untrailingslashit_for_test( $url ) ] ) ) {
		fwrite( STDERR, "A hardcoded WordPress route is not present in the live URL contract: {$path}.\n" );
		exit( 1 );
	}
}

$coverage = $contract['localCoverage'] ?? array();
if ( 14 !== (int) ( $coverage['staticViewCount'] ?? 0 ) || count( $coverage['staticViews'] ?? array() ) !== 14 ) {
	fwrite( STDERR, "The fourteen bilingual static views are not fully represented in local coverage.\n" );
	exit( 1 );
}
$covered_page_urls = array();
foreach ( $coverage['staticViews'] as $view ) {
	if ( ! is_file( $root . '/' . $view['local'] ) ) {
		fwrite( STDERR, "A mapped static view is missing locally: {$view['local']}.\n" );
		exit( 1 );
	}
	$covered_page_urls[ untrailingslashit_for_test( $view['url'] ) ] = true;
}
foreach ( $coverage['genericPages'] ?? array() as $route ) {
	$covered_page_urls[ untrailingslashit_for_test( $route['url'] ) ] = true;
}
foreach ( $contract['publicRoutes'] as $route ) {
	if ( 'page' === $route['kind'] && ! isset( $covered_page_urls[ untrailingslashit_for_test( $route['url'] ) ] ) ) {
		fwrite( STDERR, "A published page has no static or generic local rebuild coverage: {$route['url']}.\n" );
		exit( 1 );
	}
}
if ( count( $snapshot['posts'] ) !== (int) ( $coverage['dynamicPostCount'] ?? -1 ) || count( $snapshot['categories'] ) !== (int) ( $coverage['dynamicCategoryCount'] ?? -1 ) ) {
	fwrite( STDERR, "Dynamic post/category coverage is incomplete.\n" );
	exit( 1 );
}

$check_by_url = array();
foreach ( $contract['checks'] ?? array() as $check ) {
	$check_by_url[ $check['url'] ] = $check;
}
foreach ( $contract['publicRoutes'] as $route ) {
	$check = $check_by_url[ $route['url'] ] ?? null;
	if ( ! $check || 200 !== (int) ( $check['status'] ?? 0 ) ) {
		fwrite( STDERR, "A currently published route is not confirmed as HTTP 200: {$route['url']}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'preserveIds', 'preserveSlugs', 'preserveParents', 'preservePermalinks', 'preservePolylangRelations', 'documentsRemainOnSourceUrls' ) as $policy ) {
	if ( true !== ( $contract['policy'][ $policy ] ?? false ) ) {
		fwrite( STDERR, "Missing URL preservation policy: {$policy}.\n" );
		exit( 1 );
	}
}

$write_start = strpos( $applier, 'private function write_elementor_page' );
$write_end = false !== $write_start ? strpos( $applier, 'private function renders_without_error', $write_start ) : false;
$write_block = false !== $write_start && false !== $write_end ? substr( $applier, $write_start, $write_end - $write_start ) : '';
if ( ! $write_block || str_contains( $write_block, 'wp_update_post(' ) || str_contains( $write_block, 'wp_insert_post(' ) || str_contains( $write_block, 'pll_save_post_translations(' ) ) {
	fwrite( STDERR, "The Elementor writer must not mutate WordPress identity or translation records.\n" );
	exit( 1 );
}

foreach ( array( 'post_identity_contract', 'post_identity_matches', "new \\WP_Error( 'identity_changed'", "'translations' =>", "'identity'    =>", 'pll_save_post_translations' ) as $needle ) {
	if ( ! str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Missing identity preservation guard: {$needle}.\n" );
		exit( 1 );
	}
}

echo 'URL contract smoke passed: ' . count( $contract['publicRoutes'] ) . " public routes preserved.\n";

function untrailingslashit_for_test( string $url ): string {
	return rtrim( $url, "/\\" );
}
