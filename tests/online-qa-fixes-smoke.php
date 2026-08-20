<?php
$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$form = file_get_contents( $root . '/includes/widgets/class-contact-form.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'announcements category route' => array( $applier, "/ro/category/anunturi/" ),
	'forms category route' => array( $applier, "/ro/category/formulare-tipizate/" ),
	'budget category route' => array( $applier, "/ro/category/buget/" ),
	'assets declarations category route' => array( $applier, "/ro/category/declaratie-de-avere/" ),
	'official decisions category route' => array( $applier, "/ro/category/hotarari-ale-consiului-local-ro/" ),
	'Hungarian gallery category route' => array( $applier, "/hu/category/galeria-foto/" ),
	'Romanian gallery fallback route' => array( $applier, "/ro/prezentarea-comunei-porumbesti/" ),
	'Romanian interface subtitle' => array( $applier, "'brand_subtitle' => 'Primăria Comunei Porumbești'" ),
	'Named empty submenu fallback' => array( file_get_contents( $root . '/includes/widgets/class-site-header.php' ), '$empty_submenu_label' ),
	'Romanian footer subtitle' => array( file_get_contents( $root . '/includes/widgets/class-site-footer.php' ), 'Primăria Comunei Porumbești' ),
	'dynamic executive route' => array( $applier, "array( 'dispozitiile-autoritatii-executive' )" ),
	'dynamic document library' => array( $applier, "'porumbesti-document-library'" ),
	'direct child-benefit PDF' => array( $applier, "/wp-content/uploads/2016/11/acordarea-indemizatiei-de-crestere-a-copilului.pdf" ),
	'full-bleed overflow containment' => array( $css, '.porumbesti-global-main { overflow-x: hidden; overflow-x: clip; }' ),
	'contact POST fallback' => array( $form, 'method="post"' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

$ro_routes_start = strpos( $applier, "'home_ro'       =>" );
$ro_routes_end = false !== $ro_routes_start ? strpos( $applier, "\n\t\t);", $ro_routes_start ) : false;
$ro_routes = false !== $ro_routes_start && false !== $ro_routes_end ? substr( $applier, $ro_routes_start, $ro_routes_end - $ro_routes_start ) : '';
if ( str_contains( $ro_routes, "/hu/category/galeria-foto/" ) ) {
	fwrite( STDERR, "Romanian routes must not point to the Hungarian gallery archive.\n" );
	exit( 1 );
}

$forbidden = array(
	"home_url( '/ro/anunt-idividual/' )",
	"home_url( '/ro/hotararea-consiliului-local-nr-13-16-2026-2/' )",
	"home_url( '/ro/declaratii-de-interese/' )",
	"home_url( '/ro/biroul-de-circumscriptie-nr-8-porumbesti/' )",
	"home_url( '/ro/taxe-si-impozite-locale/' )",
	"home_url( '/ro/urbanism/' )",
	"home_url( '/ro/registru-agricol/' )",
);

foreach ( $forbidden as $route ) {
	if ( str_contains( $applier, $route ) ) {
		fwrite( STDERR, "Legacy broken route remains: {$route}.\n" );
		exit( 1 );
	}
}

echo "Online QA fixes smoke passed.\n";
