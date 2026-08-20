<?php
$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$assets = file_get_contents( $root . '/includes/class-assets.php' );
$header = file_get_contents( $root . '/includes/widgets/class-site-header.php' );
$footer = file_get_contents( $root . '/includes/widgets/class-site-footer.php' );
$search = file_get_contents( $root . '/includes/widgets/class-search-box.php' );
$home_hero = file_get_contents( $root . '/includes/widgets/class-home-hero.php' );
$news = file_get_contents( $root . '/includes/widgets/class-news-grid.php' );
$accessibility = file_get_contents( $root . '/includes/widgets/class-accessibility-tools.php' );

$checks = array(
	'hungarian bulk action'       => array( $applier, 'admin_post_porumbesti_apply_all_hu' ),
	'hungarian bulk handler'      => array( $applier, 'function handle_apply_all_hu' ),
	'hungarian published pages'   => array( $applier, 'function published_hu_pages' ),
	'language-aware page builder' => array( $applier, "generic_page_data( \$page, \$type, \$language )" ),
	'hungarian home template'     => array( $applier, 'function home_hu_data' ),
	'hungarian mayor template'    => array( $applier, 'function mayor_hu_data' ),
	'hungarian menu scoring'      => array( $applier, "array( 'magyar', 'hungar', 'hu' )" ),
	'safe route fallback'         => array( $applier, "( \$ro_routes[ \$key ] ?? \$home_hu )" ),
	'verified public-info route'  => array( $applier, "array( 'public_info', 'announcements' )" ),
	'hungarian current language'  => array( $applier, "array( 'code' => 'HU', 'label' => 'Magyar'" ),
	'hungarian home body class'   => array( $assets, "array( 'prima', 'fooldal' )" ),
	'localized submenu labels'    => array( $header, 'new Header_Menu_Walker( $s[\'submenu_label\'], $s[\'language_label\'] )' ),
	'localized footer labels'     => array( $footer, "\$s['monitor_link_text']" ),
	'language-preserving search'  => array( $search, 'name="lang"' ),
	'language-preserving hero'    => array( $home_hero, 'search_language' ),
	'language-filtered news'      => array( $news, "\$args['lang']" ),
	'localized accessibility'     => array( $accessibility, "\$s['text_size_label']" ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'Közérdekű ügyek', 'Községünk', 'Felhívások', 'Helyi hivatalos közlöny' ) as $section ) {
	if ( ! str_contains( $applier, $section ) ) {
		fwrite( STDERR, "Missing Hungarian homepage section: {$section}.\n" );
		exit( 1 );
	}
}

$home_start = strpos( $applier, 'private function home_hu_data' );
$home_end = false !== $home_start ? strpos( $applier, 'private function mayor_ro_data', $home_start ) : false;
$home_block = false !== $home_start && false !== $home_end ? substr( $applier, $home_start, $home_end - $home_start ) : '';
foreach ( array( 'Acasă', 'Caută', 'Anunțuri oficiale', 'Toate drepturile rezervate' ) as $romanian ) {
	if ( str_contains( $home_block, $romanian ) ) {
		fwrite( STDERR, "Romanian UI text remains in Hungarian homepage: {$romanian}.\n" );
		exit( 1 );
	}
}

echo "Hungarian rebuild smoke passed.\n";
