<?php
/**
 * Intentionally keeps porumbesti_document posts and terms on uninstall.
 * Municipal records must never be removed automatically.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
