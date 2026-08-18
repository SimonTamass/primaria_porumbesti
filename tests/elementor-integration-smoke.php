<?php
define( 'ABSPATH', __DIR__ );

function add_action(): void {}
function add_filter(): void {}

require_once dirname( __DIR__ ) . '/includes/class-elementor.php';

$reflection = new ReflectionClass( \PrimariaPorumbesti\Elementor_Integration::class );
$integration = $reflection->newInstanceWithoutConstructor();
$data = array(
	array(
		'elType'  => 'container',
		'elements' => array(
			array(
				'widgetType' => 'porumbesti-site-header',
				'settings'   => array( 'sticky' => 'yes' ),
			),
			array(
				'widgetType' => 'third-party-widget',
				'settings'   => array( 'sticky' => 'top' ),
			),
		),
	),
);

$normalized = $integration->normalize_header_settings( $data );
$header_settings = $normalized[0]['elements'][0]['settings'];
$other_settings = $normalized[0]['elements'][1]['settings'];

if ( 'yes' !== ( $header_settings['porumbesti_sticky'] ?? '' ) || isset( $header_settings['sticky'] ) ) {
	fwrite( STDERR, "Legacy header sticky setting was not normalized.\n" );
	exit( 1 );
}

if ( 'top' !== ( $other_settings['sticky'] ?? '' ) ) {
	fwrite( STDERR, "A third-party sticky setting was modified.\n" );
	exit( 1 );
}

echo "Elementor integration smoke passed.\n";
