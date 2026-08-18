<?php
$root = dirname( __DIR__ );
$home_hero = file_get_contents( $root . '/includes/widgets/class-home-hero.php' );
$page_hero = file_get_contents( $root . '/includes/widgets/class-page-hero.php' );
$single = file_get_contents( $root . '/includes/widgets/class-single-post.php' );
$archive = file_get_contents( $root . '/includes/widgets/class-post-archive.php' );
$frontend = file_get_contents( $root . '/includes/class-frontend-templates.php' );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'page title band markup' => array( $page_hero, 'porumbesti-page-hero porumbesti-title-band' ),
	'editable home hero image' => array( $home_hero, "add_control( 'background'" ),
	'editable page hero image' => array( $page_hero, "add_control( 'background'" ),
	'home hero image variable' => array( $home_hero, '--porumbesti-hero-image' ),
	'page hero image variable' => array( $page_hero, '--porumbesti-page-image' ),
	'single title band markup' => array( $single, '<header class="porumbesti-title-band">' ),
	'archive title band markup' => array( $archive, 'porumbesti-archive-header porumbesti-title-band' ),
	'native fallback title band' => array( $frontend, 'porumbesti-title-band-inner' ),
	'shared title background' => array( $css, '--porumbesti-title-bg: #123b5d;' ),
	'gradient shared title bands' => array( $css, '.porumbesti-title-band { background: var(--porumbesti-title-bg); background-image: linear-gradient(102deg,var(--porumbesti-burgundy),var(--porumbesti-teal) 58%,var(--porumbesti-brand-dark)) !important;' ),
	'local homepage image composition' => array( $css, 'var(--porumbesti-hero-image,none)' ),
	'local page image composition' => array( $css, 'var(--porumbesti-page-image,none)' ),
	'full-width single title band' => array( $css, '.porumbesti-single > .porumbesti-title-band, .porumbesti-archive-header.porumbesti-title-band' ),
	'clipped full-bleed overflow' => array( $css, '.porumbesti-global-main { overflow-x: hidden; overflow-x: clip; }' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $applier, "'background' => \$this->design_media" ) && ! str_contains( $applier, "'background'     => \$hero_image" ) ) {
	fwrite( STDERR, "The automatic Elementor rebuild does not assign local hero imagery.\n" );
	exit( 1 );
}

echo "Title band smoke passed.\n";
