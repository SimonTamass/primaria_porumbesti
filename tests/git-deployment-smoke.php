<?php
$root = dirname( __DIR__ );
$bootstrap_path = $root . '/primaria-porumbesti-elementor.php';
$readme_path = $root . '/readme.txt';

if ( ! is_file( $bootstrap_path ) || ! is_file( $root . '/includes/class-plugin.php' ) ) {
	fwrite( STDERR, "The repository is no longer an installable plugin root.\n" );
	exit( 1 );
}

$bootstrap = (string) file_get_contents( $bootstrap_path );
$readme = (string) file_get_contents( $readme_path );

foreach (
	array(
		'cPanel-managed update URI' => 'Update URI: https://primariaporumbesti.ro/cpanel-git-managed',
		'plugin-root path resolution' => "plugin_dir_path( __FILE__ )",
		'plugin-root URL resolution' => "plugin_dir_url( __FILE__ )",
		'relative plugin bootstrap' => "PORUMBESTI_WIDGETS_PATH . 'includes/class-plugin.php'",
	) as $label => $needle
) {
	if ( ! str_contains( $bootstrap, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( ! preg_match( '/^ \* Version: ([0-9]+\.[0-9]+\.[0-9]+)$/m', $bootstrap, $header_version )
	|| ! preg_match( "/PORUMBESTI_WIDGETS_VERSION', '([0-9]+\.[0-9]+\.[0-9]+)'/", $bootstrap, $constant_version )
	|| ! preg_match( '/^Stable tag: ([0-9]+\.[0-9]+\.[0-9]+)$/m', $readme, $stable_version )
	|| $header_version[1] !== $constant_version[1]
	|| $header_version[1] !== $stable_version[1]
) {
	fwrite( STDERR, "Plugin header, asset cache and stable-tag versions are not synchronized.\n" );
	exit( 1 );
}

echo "Git deployment smoke passed for plugin version {$header_version[1]}.\n";
