<?php
$root = dirname( __DIR__ );
$applier = (string) file_get_contents( $root . '/includes/class-template-applier.php' );

foreach (
	array(
		'language readiness check' => 'function language_layer_ready',
		'Polylang language function' => "function_exists( 'pll_get_post_language' )",
		'Polylang translation lookup' => "function_exists( 'pll_get_post' )",
		'Polylang relation lookup' => "function_exists( 'pll_get_post_translations' )",
		'bilingual admin warning' => 'Reconstrucția este blocată.',
		'blocked-action error code' => 'polylang_required',
		'backend write guard' => 'function require_language_layer',
		'disabled full rebuild buttons' => "\$language_layer_ready ? array() : array( 'disabled' => 'disabled' )",
	) as $label => $needle
) {
	if ( ! str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

foreach (
	array(
		array( 'public function handle_apply()', 'public function handle_restore()' ),
		array( 'public function handle_apply_group()', 'public function handle_apply_all()' ),
		array( 'private function handle_apply_all_language', 'private function apply_template' ),
	) as $bounds
) {
	$start = strpos( $applier, $bounds[0] );
	$end = false !== $start ? strpos( $applier, $bounds[1], $start ) : false;
	$handler = false !== $start && false !== $end ? substr( $applier, $start, $end - $start ) : '';
	if ( ! str_contains( $handler, '$this->require_language_layer();' ) ) {
		fwrite( STDERR, "A rebuild handler can bypass the Polylang safety guard.\n" );
		exit( 1 );
	}
}

echo "Language rebuild guard smoke passed.\n";
