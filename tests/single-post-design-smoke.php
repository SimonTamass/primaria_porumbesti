<?php
$root = dirname( __DIR__ );
$single = file_get_contents( $root . '/includes/widgets/class-single-post.php' );
$frontend = file_get_contents( $root . '/includes/class-frontend-templates.php' );
$assets = file_get_contents( $root . '/includes/class-assets.php' );
$js = file_get_contents( $root . '/assets/js/frontend.js' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'document classification' => array( $single, '$document_count' ),
	'document extension detection' => array( $js, 'const documentExtension' ),
	'document list enhancement' => array( $js, 'function initDocumentLists' ),
	'localized download label' => array( $assets, "'downloadFile'" ),
	'download list grid' => array( $css, '.porumbesti-download-list' ),
	'download item design' => array( $css, '.porumbesti-download-item' ),
	'inline download design' => array( $css, '.porumbesti-inline-download' ),
	'mobile document layout' => array( $css, '.porumbesti-download-list { grid-template-columns: 1fr; }' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $single, 'porumbesti-share' ) || str_contains( $frontend, "'show_share' => 'yes'" ) || str_contains( $css, '.porumbesti-share' ) ) {
	fwrite( STDERR, "Obsolete single-post sharing UI remains.\n" );
	exit( 1 );
}

echo "Single post design smoke passed.\n";
