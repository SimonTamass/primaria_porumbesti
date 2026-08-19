<?php
$root = dirname( __DIR__ );
$applier = (string) file_get_contents( $root . '/includes/class-template-applier.php' );

$route_contracts = array(
	'Romanian vice mayor route' => array(
		"'vice_mayor'    => \$this->page_url( array( 'viceprimar' ), '/ro/viceprimar/' )",
		"'vice_mayor'    => array( array( 'viceprimar' ), array( 'alpolgarmester' ) )",
	),
	'Romanian secretary route' => array(
		"'secretary'     => \$this->page_url( array( 'secretar' ), '/ro/secretar/' )",
		"'secretary'     => array( array( 'secretar' ), array( 'jegyzo' ) )",
	),
);

foreach ( $route_contracts as $label => $needles ) {
	foreach ( $needles as $needle ) {
		if ( ! str_contains( $applier, $needle ) ) {
			fwrite( STDERR, "Missing {$label}.\n" );
			exit( 1 );
		}
	}
}

$home_ro_start = strpos( $applier, 'private function home_ro_data' );
$home_hu_start = strpos( $applier, 'private function home_hu_data' );
$mayor_ro_start = strpos( $applier, 'private function mayor_ro_data' );
$home_ro = false !== $home_ro_start && false !== $home_hu_start
	? substr( $applier, $home_ro_start, $home_hu_start - $home_ro_start )
	: '';
$home_hu = false !== $home_hu_start && false !== $mayor_ro_start
	? substr( $applier, $home_hu_start, $mayor_ro_start - $home_hu_start )
	: '';

foreach ( array( $home_ro, $home_hu ) as $home ) {
	foreach ( array( "\$routes['vice_mayor']", "\$routes['secretary']" ) as $route ) {
		if ( ! str_contains( $home, $route ) ) {
			fwrite( STDERR, "A leadership card is missing its role-specific URL.\n" );
			exit( 1 );
		}
	}
}

echo "Leadership route smoke passed.\n";
