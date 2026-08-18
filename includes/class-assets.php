<?php
namespace PrimariaPorumbesti;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Assets {
	private static ?Assets $instance = null;

	public static function instance(): Assets {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
	}

	public function body_classes( array $classes ): array {
		if ( is_page( array( 'prima', 'fooldal' ) ) ) {
			$classes[] = 'porumbesti-home-page';
		}

		return $classes;
	}

	public function register(): void {
		$language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
		$is_hungarian = 'hu' === $language;
		wp_register_style( 'porumbesti-fonts', PORUMBESTI_WIDGETS_URL . 'assets/css/fonts.css', array(), PORUMBESTI_WIDGETS_VERSION );
		wp_register_style( 'porumbesti-widgets', PORUMBESTI_WIDGETS_URL . 'assets/css/frontend.css', array( 'porumbesti-fonts', 'dashicons' ), PORUMBESTI_WIDGETS_VERSION );
		wp_register_script( 'porumbesti-widgets', PORUMBESTI_WIDGETS_URL . 'assets/js/frontend.js', array(), PORUMBESTI_WIDGETS_VERSION, true );
		wp_localize_script(
			'porumbesti-widgets',
			'porumbestiWidgets',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'porumbesti-contact' ),
				'i18n'    => array(
					'error'   => $is_hungarian ? 'Hiba történt. Kérjük, próbálja újra.' : esc_html__( 'A apărut o eroare. Încercați din nou.', 'primaria-porumbesti' ),
					'sending' => $is_hungarian ? 'Küldés…' : esc_html__( 'Se trimite…', 'primaria-porumbesti' ),
					'openImage'     => $is_hungarian ? 'Kép megnyitása nagy méretben' : esc_html__( 'Deschide imaginea la dimensiune mare', 'primaria-porumbesti' ),
					'closeLightbox' => $is_hungarian ? 'Bezárás' : esc_html__( 'Închide', 'primaria-porumbesti' ),
					'previousImage' => $is_hungarian ? 'Előző kép' : esc_html__( 'Imaginea anterioară', 'primaria-porumbesti' ),
					'nextImage'     => $is_hungarian ? 'Következő kép' : esc_html__( 'Imaginea următoare', 'primaria-porumbesti' ),
					'imageCounter'  => $is_hungarian ? '%1$d / %2$d kép' : esc_html__( 'Imaginea %1$d din %2$d', 'primaria-porumbesti' ),
					'downloadFile'  => $is_hungarian ? 'Dokumentum letöltése' : esc_html__( 'Descarcă documentul', 'primaria-porumbesti' ),
				),
			)
		);
	}

	public function enqueue_editor_styles(): void {
		wp_enqueue_style( 'porumbesti-editor', PORUMBESTI_WIDGETS_URL . 'assets/css/editor.css', array(), PORUMBESTI_WIDGETS_VERSION );
	}
}
