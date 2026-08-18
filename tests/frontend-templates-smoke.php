<?php
$root = dirname( __DIR__ );
$plugin = file_get_contents( $root . '/includes/class-plugin.php' );
$frontend = file_get_contents( $root . '/includes/class-frontend-templates.php' );
$template = file_get_contents( $root . '/templates/frontend-elementor.php' );
$archive = file_get_contents( $root . '/includes/widgets/class-post-archive.php' );
$single = file_get_contents( $root . '/includes/widgets/class-single-post.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'frontend service registration' => array( $plugin, 'Frontend_Templates::instance()' ),
	'archive route coverage' => array( $frontend, 'is_archive() || is_home() || is_search()' ),
	'single route coverage' => array( $frontend, "is_singular( array( 'post', 'porumbesti_document' ) )" ),
	'URL-preserving template filter' => array( $frontend, "add_filter( 'template_include'" ),
	'Elementor archive widget' => array( $frontend, "'porumbesti-post-archive'" ),
	'Elementor single widget' => array( $frontend, "'porumbesti-single-post'" ),
	'defensive widget loading' => array( $frontend, "require_once PORUMBESTI_WIDGETS_PATH . 'includes/widgets/class-'" ),
	'Elementor widget factory' => array( $frontend, 'elements_manager->create_element_instance( $data )' ),
	'isolated widget failure handling' => array( $frontend, 'catch ( \\Throwable $error )' ),
	'functional WordPress fallback' => array( $frontend, 'render_native_content( array $copy, array $routes )' ),
	'transactional global renderer' => array( $frontend, 'function render_safely' ),
	'language-aware shared header' => array( $frontend, "'porumbesti-site-header'" ),
	'explicit search language' => array( $frontend, "\$_GET['lang']" ),
	'explicit query language scope' => array( $frontend, "\$query->set( 'lang', \$language )" ),
	'localized HTML language' => array( $frontend, "'hu-HU' : 'ro-RO'" ),
	'configured Elementor frontend' => array( $frontend, 'frontend->enqueue_scripts();' ),
	'language-aware shared footer' => array( $frontend, "'porumbesti-site-footer'" ),
	'Porumbesti monogram in shared templates' => array( $frontend, 'assets/images/porumbesti-monogram.svg' ),
	'WordPress document shell' => array( $template, 'wp_head();' ),
	'safe template entrypoint' => array( $template, 'render_safely();' ),
	'localized archive labels' => array( $archive, "\$s['read_more_text']" ),
	'unified archive title band' => array( $archive, 'porumbesti-archive-header porumbesti-title-band' ),
	'document-aware single content' => array( $single, "' has-document-list'" ),
	'unified single title band' => array( $single, '<header class="porumbesti-title-band">' ),
	'global template layout' => array( $css, '.porumbesti-global-main' ),
	'download card layout' => array( $css, '.porumbesti-download-item' ),
);

foreach ( $checks as $label => $check ) {
	if ( ! str_contains( $check[0], $check[1] ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $single, 'porumbesti-share' ) || str_contains( $frontend, "'show_share' => 'yes'" ) ) {
	fwrite( STDERR, "Single-post sharing UI was not removed.\n" );
	exit( 1 );
}

echo "Frontend templates smoke passed.\n";
