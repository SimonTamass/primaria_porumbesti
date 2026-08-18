<?php
$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$form = file_get_contents( $root . '/includes/widgets/class-contact-form.php' );
$details = file_get_contents( $root . '/includes/widgets/class-contact-details.php' );
$plugin = file_get_contents( $root . '/includes/class-plugin.php' );
$js = file_get_contents( $root . '/assets/js/frontend.js' );

$checks = array(
	'contact page recognition' => array( $applier, "preg_match( '/contact|elerhet/'" ),
	'contact details placement' => array( $applier, "'porumbesti-contact-details'" ),
	'contact form placement' => array( $applier, "'porumbesti-contact-form'" ),
	'Romanian contact copy' => array( $applier, "'Trimiteți-ne un mesaj'" ),
	'Hungarian contact copy' => array( $applier, "'Írjon nekünk'" ),
	'embedded office map' => array( $applier, '47.9839429,22.9718739' ),
	'preserved fax control' => array( $details, "add_control( 'fax'" ),
	'preserved fiscal identifier' => array( $details . $applier, '17530869' ),
	'localized field labels' => array( $form, "add_control( 'name_label'" ),
	'contact language field' => array( $form, 'name="language"' ),
	'secure POST fallback' => array( $form, 'method="post"' ),
	'direct AJAX fallback action' => array( $form, "admin_url( 'admin-ajax.php' )" ),
	'fallback action field' => array( $form, 'name="action" value="porumbesti_contact"' ),
	'fallback nonce field' => array( $form, "wp_create_nonce( 'porumbesti-contact' )" ),
	'localized server response' => array( $plugin, "'hu' === \$language" ),
	'honeypot validation' => array( $plugin, "! empty( \$_POST['website'] )" ),
	'per-minute rate limit' => array( $plugin, 'MINUTE_IN_SECONDS' ),
	'recipient integrity check' => array( $plugin, "hash_equals( wp_hash( \$to . '|porumbesti_contact' ), \$to_hash )" ),
	'server-side field validation' => array( $plugin, '! $name || ! is_email( $email ) || ! $subject || ! $message' ),
	'client-side form initialization' => array( $js, "all('.porumbesti-contact-form:not([data-ready])'" ),
	'native validity check' => array( $js, 'form.reportValidity()' ),
	'no duplicate AJAX action' => array( $js, "if (!data.has('action'))" ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "Contact page smoke passed.\n";
