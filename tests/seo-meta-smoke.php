<?php
$root = dirname( __DIR__ );
$plugin = file_get_contents( $root . '/includes/class-plugin.php' );
$seo = file_get_contents( $root . '/includes/class-seo-meta.php' );

$checks = array(
	'SEO service loading' => array( $plugin, "includes/class-seo-meta.php" ),
	'SEO service registration' => array( $plugin, 'SEO_Meta::instance()' ),
	'head rendering hook' => array( $seo, "add_action( 'wp_head'" ),
	'meta description' => array( $seo, "'description', \$description" ),
	'Open Graph title' => array( $seo, "'og:title', \$title" ),
	'Open Graph description' => array( $seo, "'og:description', \$description" ),
	'Open Graph canonical URL' => array( $seo, "'og:url', \$url" ),
	'Romanian locale' => array( $seo, "'ro_RO'" ),
	'Hungarian locale' => array( $seo, "'hu_HU'" ),
	'Twitter summary card' => array( $seo, "'twitter:card'" ),
	'featured image metadata' => array( $seo, "get_the_post_thumbnail_url" ),
	'escaped metadata output' => array( $seo, "esc_attr( \$value )" ),
);

foreach ( $checks as $label => $check ) {
	if ( ! str_contains( $check[0], $check[1] ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "SEO metadata smoke passed.\n";
