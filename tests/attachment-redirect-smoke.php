<?php
$root = dirname( __DIR__ );
$frontend = file_get_contents( $root . '/includes/class-frontend-templates.php' );

$checks = array(
	'early attachment redirect hook' => "add_action( 'template_redirect', array( \$this, 'redirect_attachment_page' ), 1 )",
	'attachment request guard'       => '! is_attachment()',
	'queried attachment lookup'      => 'get_queried_object_id()',
	'original media lookup'          => 'wp_get_attachment_url( $attachment_id )',
	'permanent safe redirect'        => "wp_safe_redirect( \$media_url, 301, 'Primaria Porumbesti' )",
);

foreach ( $checks as $label => $needle ) {
	if ( ! str_contains( $frontend, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "Attachment redirect smoke passed.\n";
