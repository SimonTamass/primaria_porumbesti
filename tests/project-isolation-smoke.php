<?php
$root = dirname( __DIR__ );
$files = array(
	$root . '/README.md',
	$root . '/README-HU.md',
	$root . '/readme.txt',
	$root . '/primaria-porumbesti-elementor.php',
);
$extensions = array( 'css', 'html', 'js', 'json', 'md', 'php', 'py', 'svg', 'txt', 'xml', 'yaml', 'yml' );
foreach ( array( 'assets', 'includes', 'templates', 'tools' ) as $directory ) {
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root . '/' . $directory, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $iterator as $item ) {
		if ( $item->isFile() && in_array( strtolower( $item->getExtension() ), $extensions, true ) ) {
			$files[] = $item->getPathname();
		}
	}
}

$content = '';
foreach ( $files as $file ) {
	$content .= file_get_contents( $file );
}

$unrelated_project_name = 'ag' . 'ris';
$unrelated_monogram = '<span>' . 'C' . 'A</span>';
if ( str_contains( strtolower( $content ), $unrelated_project_name ) || str_contains( $content, $unrelated_monogram ) ) {
	fwrite( STDERR, "Unrelated legacy branding remains in the Porumbesti project.\n" );
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
