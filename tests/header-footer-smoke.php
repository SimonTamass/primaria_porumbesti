<?php
$root = dirname( __DIR__ );
$assets = file_get_contents( $root . '/includes/class-assets.php' );
$header = file_get_contents( $root . '/includes/widgets/class-site-header.php' );
$footer = file_get_contents( $root . '/includes/widgets/class-site-footer.php' );
$home_hero = file_get_contents( $root . '/includes/widgets/class-home-hero.php' );
$page_hero = file_get_contents( $root . '/includes/widgets/class-page-hero.php' );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );
$js = file_get_contents( $root . '/assets/js/frontend.js' );

$checks = array(
	'dashicons dependency'        => array( $assets, "array( 'porumbesti-fonts', 'dashicons' )" ),
	'accessible menu connection' => array( $header, 'aria-controls="<?php echo esc_attr( $nav_id ); ?>"' ),
	'namespaced sticky control'  => array( $header, "add_control( 'porumbesti_sticky'" ),
	'accessible submenu walker'  => array( $header, 'class Header_Menu_Walker' ),
	'submenu toggle control'     => array( $header, 'data-porumbesti-submenu-toggle' ),
	'localized submenu control'  => array( $header, "new Header_Menu_Walker( \$s['submenu_label'] )" ),
	'safe submenu formatting'    => array( $header, "str_replace( '%s', \$title, \$this->submenu_label )" ),
	'home skip-link target'      => array( $home_hero, 'id="main-content"' ),
	'page skip-link target'      => array( $page_hero, 'id="main-content"' ),
	'four-level menu rendering'  => array( $header, "'depth' => 4" ),
	'search icon'                => array( $header, 'dashicons-search' ),
	'menu icon'                  => array( $header, 'dashicons-menu-alt3' ),
	'language flags'             => array( $header, 'porumbesti-flag-<?php echo esc_attr' ),
	'footer contact link'        => array( $footer, "'contact_url'" ),
	'footer monitor link'        => array( $footer, "'monitor_url'" ),
	'footer utility navigation'  => array( $footer, 'Linkuri subsol' ),
	'footer back-to-top icon'    => array( $footer, 'dashicons-arrow-up-alt2' ),
	'dynamic footer routes'      => array( $applier, "'contact_url' => \$this->link( \$routes['contact'] )" ),
	'header height'              => array( $css, 'min-height: 78px;' ),
	'local footer grid'          => array( $css, 'grid-template-columns: 1.2fr repeat(4, 1fr);' ),
	'deduplicated language item' => array( $css, '.porumbesti-menu > .lang-item, .porumbesti-menu > .pll-parent-menu-item { display: none; }' ),
	'theme-safe menu sizing'     => array( $css, '.porumbesti-menu a { box-sizing: border-box; }' ),
	'local mobile breakpoint'    => array( $css, '@media (max-width: 1040px)' ),
	'hover bridge'               => array( $css, '.porumbesti-menu .sub-menu::before' ),
	'nested desktop flyout'      => array( $css, '.porumbesti-menu .sub-menu .sub-menu' ),
	'nested overflow reversal'   => array( $css, '.menu-item-has-children.opens-left > .sub-menu' ),
	'mobile accordion'           => array( $css, '.porumbesti-menu li.is-submenu-open > .sub-menu { display: grid; }' ),
	'all-level menu discovery'   => array( $js, "all('.porumbesti-menu .menu-item-has-children', header)" ),
	'forgiving close delay'      => array( $js, 'window.setTimeout(() => closeSubmenu(item), 220)' ),
	'keyboard submenu opening'   => array( $js, "['ArrowDown', 'ArrowUp', 'ArrowRight', 'ArrowLeft']" ),
	'escape focus restoration'   => array( $js, 'closeSubmenu(openItem, true)' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $header, "add_control( 'sticky'" ) || ! str_contains( $applier, "'porumbesti_sticky' => 'yes'" ) ) {
	fwrite( STDERR, "Header sticky control still conflicts with Elementor Pro.\n" );
	exit( 1 );
}

echo "Header and footer smoke passed.\n";
