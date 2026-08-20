<?php
$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$assets = file_get_contents( $root . '/includes/class-assets.php' );
$section_heading = file_get_contents( $root . '/includes/widgets/class-section-heading.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );
$plugin = file_get_contents( $root . '/primaria-porumbesti-elementor.php' );

$checks = array(
	'all-pages admin action' => "admin_post_porumbesti_apply_all",
	'all-pages handler'      => 'function handle_apply_all',
	'generic page builder'   => 'function generic_page_data',
	'original source backup' => "SOURCE_META",
	'URL rollback guard'     => "new \\WP_Error( 'url_changed'",
	'identity rollback guard'=> "new \\WP_Error( 'identity_changed'",
	'Polylang relation backup'=> 'pll_get_post_translations',
	'render rollback guard'  => "new \\WP_Error( 'render_failed'",
	'five backup limit'      => 'private const MAX_BACKUPS = 5;',
);

foreach ( $checks as $label => $needle ) {
	if ( ! str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $assets, "'porumbesti-fonts'" ) || ! str_contains( $assets, 'assets/css/fonts.css' ) ) {
	fwrite( STDERR, "Font registration is incomplete.\n" );
	exit( 1 );
}

if ( ! str_contains( $assets, "add_filter( 'body_class'" ) || ! str_contains( $assets, "'porumbesti-home-page'" ) ) {
	fwrite( STDERR, "Homepage body-class scoping is incomplete.\n" );
	exit( 1 );
}

if ( ! str_contains( $section_heading, "'theme'" ) || ! str_contains( $section_heading, "' is-dark'" ) ) {
	fwrite( STDERR, "Section heading dark variant is incomplete.\n" );
	exit( 1 );
}

if ( ! str_contains( $css, 'font-size: 16px; line-height: 1.55' ) || ! str_contains( $css, 'text-wrap: balance' ) ) {
	fwrite( STDERR, "Typography contract does not match the local redesign.\n" );
	exit( 1 );
}

if ( ! str_contains( $plugin, "PORUMBESTI_WIDGETS_VERSION', '1.0.14'" ) ) {
	fwrite( STDERR, "Plugin version was not bumped.\n" );
	exit( 1 );
}

$home_ro_start = strpos( $applier, 'private function home_ro_data' );
$home_hu_start = strpos( $applier, 'private function home_hu_data' );
$mayor_ro_start = strpos( $applier, 'private function mayor_ro_data' );
$home_ro_block = false !== $home_ro_start && false !== $home_hu_start
	? substr( $applier, $home_ro_start, $home_hu_start - $home_ro_start )
	: '';
$home_hu_block = false !== $home_hu_start && false !== $mayor_ro_start
	? substr( $applier, $home_hu_start, $mayor_ro_start - $home_hu_start )
	: '';

if ( str_contains( $home_ro_block, 'original_content_sections' ) || str_contains( $home_hu_block, 'original_content_sections' ) ) {
	fwrite( STDERR, "Homepage must not append the full legacy source before its footer.\n" );
	exit( 1 );
}

if ( ! str_contains( $applier, "original_content_sections( \$page, 'ro', \$seed )" ) ) {
	fwrite( STDERR, "Internal-page legacy content preservation is missing.\n" );
	exit( 1 );
}

if ( ! str_contains( $applier, 'function normalize_legacy_content' ) || ! str_contains( $applier, 'function gallery_items' ) ) {
	fwrite( STDERR, "Legacy content conversion is incomplete.\n" );
	exit( 1 );
}

foreach ( array( 'Servicii publice transparente pentru Comuna Porumbești.', 'Informații utile astăzi', 'Anunțuri oficiale', 'Hotărâri recente', 'Istorie, cultură și natură în inima județului Satu Mare', 'Documente publice într-un singur loc', 'Guvernare transparentă, deschisă și participativă', "'category'     => 'anunturi'", "'title' => 'Monitorul Oficial Local'", "'title' => 'Telefoane utile'", "'title' => 'Legislație'", "'title' => 'Portal online'", "'title' => 'Consiliul Local'", "'title' => 'Galeria foto'", 'function expand_legacy_link_shortcodes', 'function expand_legacy_raw_html_shortcodes', 'function legacy_table_fallback', 'function legacy_post_queries' ) as $needle ) {
	if ( ! str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Missing original-content parity contract: {$needle}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'home-leadership-ro', 'home-leadership-hu', 'mayor-message-widget', 'hu-mayor-message-widget', 'hu-decisions-widget', 'RO · hiteles dokumentum', 'brand_logo()', 'assets/images/porumbesti-monogram.svg' ) as $needle ) {
	if ( ! str_contains( $applier, $needle ) && ! str_contains( $plugin, $needle ) ) {
		fwrite( STDERR, "Professional municipal homepage contract is incomplete: {$needle}.\n" );
		exit( 1 );
	}
}

foreach ( array( "'title'          => 'Bine ați venit'", "'reports-widget'", "'mayor-schedule'", "'mayor-cta'", "'documents-widget'", "'contact-form-widget'" ) as $needle ) {
	if ( str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Unrelated generated content remains: {$needle}.\n" );
		exit( 1 );
	}
}

$services_start = strpos( $applier, "'items_list' => \$this->repeater( 'services'" );
$services_end = false !== $services_start ? strpos( $applier, "\n\t\t\t\t\t\t\t) ),", $services_start ) : false;
$services_block = false !== $services_start && false !== $services_end ? substr( $applier, $services_start, $services_end - $services_start ) : '';
if ( 8 !== substr_count( $services_block, "array( 'icon' =>" ) ) {
	fwrite( STDERR, "Homepage must preserve exactly eight frequent-service cards.\n" );
	exit( 1 );
}

foreach ( array( "'theme'       => \$theme", "'background_color' => \$background", "'monitor-services-widget'", "'show_search'    => 'yes'", "'count'        => 6" ) as $needle ) {
	if ( ! str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Homepage local-design structure is incomplete: {$needle}.\n" );
		exit( 1 );
	}
}

foreach ( array( '.porumbesti-home-page .porumbesti-content-media', '.porumbesti-home-page .porumbesti-cta', '.porumbesti-home-page .porumbesti-news-image' ) as $needle ) {
	if ( ! str_contains( $css, $needle ) ) {
		fwrite( STDERR, "Homepage local-design styling is incomplete: {$needle}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $applier, "'post_content' => ''" ) ) {
	fwrite( STDERR, "Bulk rebuild must preserve legacy post content.\n" );
	exit( 1 );
}

echo "Template applier smoke passed.\n";
