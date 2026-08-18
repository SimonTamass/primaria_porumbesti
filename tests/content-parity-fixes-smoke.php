<?php

$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$news = file_get_contents( $root . '/includes/widgets/class-news-grid.php' );
$contact = file_get_contents( $root . '/includes/widgets/class-contact-details.php' );

$checks = array(
	'descendant category expansion' => array( $news, "get_term_children( (int) \$term->term_id, 'category' )" ),
	'descendant category error guard' => array( $news, "! is_wp_error( \$children )" ),
	'same-slug post recovery' => array( $applier, 'function same_slug_post_content' ),
	'same-slug page integration' => array( $applier, '$same_slug_content = $this->same_slug_post_content( $page, $language )' ),
	'dynamic sitemap generation' => array( $applier, 'function sitemap_content' ),
	'sitemap page integration' => array( $applier, "'sitemap' === sanitize_title( \$page->post_name )" ),
	'Romanian civic-status homepage panel' => array( $applier, "\$this->repeater( 'home-status-ro'" ),
	'Romanian dynamic council decisions' => array( $applier, "'category'     => 'hotarari-ale-consiului-local-ro'" ),
	'official secondary phone setting' => array( $applier, "'phone_secondary' => '0361 525 288'" ),
	'official secondary phone control' => array( $contact, "add_control( 'phone_secondary'" ),
	'official secondary phone link' => array( $contact, "\$s['phone_secondary']" ),
	'official contact email' => array( $applier, 'mailto:primar@primariaporumbesti.ro' ),
	'official monitor link' => array( $applier, '/ro/monitorul-oficial-local-2/' ),
	'forms link' => array( $applier, '/ro/formulare-tipizate/' ),
	'useful phones link' => array( $applier, '/ro/telefone-utile/' ),
	'legislation link' => array( $applier, '/ro/legislatie/' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'hu-announcements-widget' ) as $widget ) {
	$start = strpos( $applier, "'{$widget}'" );
	$block = false !== $start ? substr( $applier, $start, 500 ) : '';
	if ( ! str_contains( $block, "'count' => 6" ) ) {
		fwrite( STDERR, "Homepage listing is not expanded: {$widget}.\n" );
		exit( 1 );
	}
}

echo "Content parity fixes smoke passed.\n";
