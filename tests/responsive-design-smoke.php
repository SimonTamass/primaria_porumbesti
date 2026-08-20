<?php
$root = dirname( __DIR__ );
$css = file_get_contents( $root . '/assets/css/frontend.css' );
$js = file_get_contents( $root . '/assets/js/frontend.js' );
$accessibility = file_get_contents( $root . '/includes/widgets/class-accessibility-tools.php' );
$alignment_fixture = file_get_contents( $root . '/tests/fixtures/control-alignment.html' );
$desktop_css = explode( '@media (max-width: 1040px)', $css, 2 )[0];

$checks = array(
	'accessibility panel follows floating stack' => array( $css, 'bottom: calc(100% + 12px);' ),
	'accessibility viewport height guard'        => array( $css, 'max-height: min(560px,calc(100dvh - 150px));' ),
	'accessibility panel scrolling'              => array( $css, 'overscroll-behavior: contain;' ),
	'open panel state'                           => array( $js, "widget.classList.toggle('is-panel-open', open);" ),
	'initial back-to-top sync'                   => array( $js, 'syncTop();' ),
	'escape close'                               => array( $js, "event.key === 'Escape'" ),
	'button form isolation'                      => array( $accessibility, 'type="button" data-porumbesti-a11y-toggle' ),
	'scale control class'                        => array( $accessibility, 'class="porumbesti-a11y-scale"' ),
	'header icon padding reset'                  => array( $css, 'place-items: center; padding: 0;' ),
	'floating control grid'                      => array( $css, '.porumbesti-floating button + button.is-visible { display: grid; }' ),
	'scale control grid'                         => array( $css, 'grid-template-columns: 34px 54px 34px;' ),
	'centered scale values'                      => array( $css, '.porumbesti-a11y-scale strong' ),
	'centered switch track'                      => array( $css, 'justify-content: flex-start; box-sizing: border-box;' ),
	'visual alignment fixture'                   => array( $alignment_fixture, 'Porumbești control alignment fixture' ),
	'intermediate desktop gutter breakpoint'    => array( $css, '@media (max-width: 1288px)' ),
	'tablet homepage gutter'                     => array( $css, 'padding-inline: 24px;' ),
	'mobile homepage gutter'                     => array( $css, 'padding-inline: 14px;' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( ! preg_match( '/\.porumbesti-menu \.sub-menu \{[^}]*display: none;/s', $desktop_css ) ) {
	fwrite( STDERR, "Desktop submenus still participate in hidden layout.\n" );
	exit( 1 );
}

if ( ! preg_match( '/\.porumbesti-menu li\.is-submenu-open > \.sub-menu \{[^}]*display: grid;/s', $desktop_css ) ) {
	fwrite( STDERR, "Desktop submenu open state is missing.\n" );
	exit( 1 );
}

echo "Responsive design smoke passed.\n";
