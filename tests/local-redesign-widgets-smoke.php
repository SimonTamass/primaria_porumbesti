<?php
$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$home_hero = file_get_contents( $root . '/includes/widgets/class-home-hero.php' );
$page_hero = file_get_contents( $root . '/includes/widgets/class-page-hero.php' );
$stats = file_get_contents( $root . '/includes/widgets/class-stats-bars.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'local redesign router' => array( $applier, 'function specialized_ro_page_data' ),
	'municipality composition' => array( $applier, 'function comuna_ro_data' ),
	'council composition' => array( $applier, 'function council_ro_data' ),
	'public information composition' => array( $applier, 'function public_info_ro_data' ),
	'official gazette composition' => array( $applier, 'function monitor_ro_data' ),
	'contact composition' => array( $applier, 'function contact_ro_data' ),
	'leadership schedule widget' => array( $applier, "'porumbesti-schedule-grid'" ),
	'council member widget' => array( $applier, "'porumbesti-council-members'" ),
	'council distribution widget' => array( $applier, "'porumbesti-stats-bars'" ),
	'public document widget' => array( $applier, "'porumbesti-document-grid'" ),
	'contact details widget' => array( $applier, "'porumbesti-contact-details'" ),
	'contact form widget' => array( $applier, "'porumbesti-contact-form'" ),
	'local home hero media' => array( $applier, "'2018/07/hatter-13.jpg'" ),
	'local page hero media' => array( $applier, "'2018/07/hatter-13.jpg'" ),
	'bilingual Romanian brand' => array( $applier, "'Primăria · Kökényesd Község'" ),
	'official email action' => array( $applier, 'mailto:primar@primariaporumbesti.ro' ),
	'Romanian municipality route' => array( $applier, "'location'      => \$this->page_url( array( 'prezentarea-comunei-porumbesti' )" ),
	'Romanian departments route' => array( $applier, "'departments'   => \$this->page_url( array( 'departamente' )" ),
	'editable home background' => array( $home_hero, "add_control( 'background'" ),
	'editable page background' => array( $page_hero, "add_control( 'background'" ),
	'council display count' => array( $stats, "'display_value'" ),
	'background image rendering' => array( $css, 'var(--porumbesti-page-image,none)' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

$specialized_start = strpos( $applier, 'private function specialized_ro_page_data' );
$generic_start = strpos( $applier, 'private function generic_page_data' );
$specialized = false !== $specialized_start && false !== $generic_start ? substr( $applier, $specialized_start, $generic_start - $specialized_start ) : '';
foreach ( array( 'istoria-comunei', 'componenta-consiliului-local', 'formulare-tipizate', 'monitorul-oficial-local', '/contact/' ) as $route ) {
	if ( ! str_contains( $specialized, $route ) ) {
		fwrite( STDERR, "Missing specialized route: {$route}.\n" );
		exit( 1 );
	}
}

echo "Local redesign widget smoke passed.\n";
