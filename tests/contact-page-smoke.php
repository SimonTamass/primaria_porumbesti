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
	'preserved Hungarian public address' => array( $applier, 'Romania, Jud. Satu Mare, Com. Porumbesti, Sat. Porumbesti, Nr 17C, Cod. 447152' ),
	'embedded office map' => array( $applier, '47.9839429,22.9718739' ),
	'preserved fax control' => array( $details, "add_control( 'fax'" ),
	'preserved fiscal identifier' => array( $details . $applier, '17530869' ),
	'Romanian contact has one details block' => array( $applier, "'phone_secondary' => ''" ),
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

if ( str_contains( $applier, "'-quick-links-widget'" ) ) {
	fwrite( STDERR, "Romanian contact page still contains the duplicate quick-link grid.\n" );
	exit( 1 );
}

$snapshot = json_decode( file_get_contents( $root . '/content/live-content-snapshot.json' ), true );
$source_contact = '';
foreach ( (array) ( $snapshot['pages'] ?? $snapshot ) as $item ) {
	if ( 134 === (int) ( $item['id'] ?? 0 ) ) {
		$source_contact = (string) ( $item['content']['rendered'] ?? '' );
		break;
	}
}
$contact_start = strpos( $applier, 'private function contact_ro_data' );
$contact_end = false !== $contact_start ? strpos( $applier, 'private function generic_page_data', $contact_start ) : false;
$contact_block = false !== $contact_start && false !== $contact_end ? substr( $applier, $contact_start, $contact_end - $contact_start ) : '';
$compact_source = preg_replace( '/\s+/u', '', strtolower( html_entity_decode( strip_tags( $source_contact ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) ) ?? '';
$compact_contact = preg_replace( '/\s+/u', '', strtolower( $contact_block ) ) ?? '';
foreach ( array( '17c', '447152', '0361525288', 'primar@primariaporumbesti.ro', '17530869' ) as $fact ) {
	if ( ! str_contains( $compact_source, $fact ) || ! str_contains( $compact_contact, $fact ) ) {
		fwrite( STDERR, "Romanian contact fact is not preserved in the consolidated contact block: {$fact}.\n" );
		exit( 1 );
	}
}

echo "Contact page smoke passed.\n";
