<?php
$root = dirname( __DIR__ );
$files = array(
	$root . '/README.md',
	$root . '/README-HU.md',
	$root . '/readme.txt',
	$root . '/includes/widgets/class-news-grid.php',
	$root . '/includes/widgets/class-post-archive.php',
);

$content = '';
foreach ( $files as $file ) {
	$content .= file_get_contents( $file );
}

if ( preg_match( '/comuna[_ -]?agris|<span>CA<\/span>/i', $content ) ) {
	fwrite( STDERR, "Inherited Comuna Agris branding remains in the Porumbesti project.\n" );
	exit( 1 );
}

foreach ( array( 'class-news-grid.php', 'class-post-archive.php' ) as $filename ) {
	$widget = file_get_contents( $root . '/includes/widgets/' . $filename );
	if ( ! str_contains( $widget, "echo '<span>P</span>'" ) ) {
		fwrite( STDERR, "Porumbesti fallback monogram is missing from {$filename}.\n" );
		exit( 1 );
	}
}

echo "Project isolation smoke passed.\n";
