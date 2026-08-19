<?php
/**
 * Plugin Name: Primăria Porumbești Elementor Widgets
 * Description: Bilingual Elementor widget suite, templates and document library for the Porumbești/Kökényesd municipal portal.
 * Version: 1.0.10
 * Author: Primăria Comunei Porumbești
 * Text Domain: primaria-porumbesti
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Update URI: https://primariaporumbesti.ro/cpanel-git-managed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PORUMBESTI_WIDGETS_VERSION', '1.0.10' );
define( 'PORUMBESTI_WIDGETS_FILE', __FILE__ );
define( 'PORUMBESTI_WIDGETS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PORUMBESTI_WIDGETS_URL', plugin_dir_url( __FILE__ ) );

require_once PORUMBESTI_WIDGETS_PATH . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( '\PrimariaPorumbesti\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

add_action(
	'plugins_loaded',
	static function (): void {
		\PrimariaPorumbesti\Plugin::instance();
	}
);
