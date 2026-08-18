<?php
namespace PrimariaPorumbesti;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Frontend_Templates {
	private static ?Frontend_Templates $instance = null;
	private const WEBMAIL_URL = 'https://server58.romania-webhosting.com:2096/';
	private const FACEBOOK_URL = 'https://www.facebook.com/KOKENYESD/';
	private const PORTAL_URL = 'https://portal.primariaporumbesti.ro/';
	private const TRANSPARENCY_URL = 'https://sgg.gov.ro/new/guvernare-transparenta-deschisa-si-participativa-standardizare-armonizare-dialog-imbunatatit-cod-sipoca-35/';
	private const MAP_URL = 'https://www.google.hu/maps/place/Prim%C4%83ria+Porumbe%C5%9Fti/@47.984662,22.9673973,16z/data=!4m5!3m4!1s0x473818293864b625:0xb9325b24df54cf20!8m2!3d47.9839429!4d22.9718739';
	private array $menu_cache = array();

	public static function instance(): Frontend_Templates {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'template_redirect', array( $this, 'redirect_attachment_page' ), 1 );
		add_filter( 'template_include', array( $this, 'template_include' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 30 );
		add_action( 'pre_get_posts', array( $this, 'scope_explicit_language' ), 1 );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_filter( 'language_attributes', array( $this, 'language_attributes' ) );
	}

	private function brand_logo(): array {
		return array(
			'id'  => '',
			'url' => PORUMBESTI_WIDGETS_URL . 'assets/images/porumbesti-monogram.svg',
		);
	}

	public function redirect_attachment_page(): void {
		if ( is_admin() || is_feed() || is_embed() || wp_doing_ajax() || ! is_attachment() ) {
			return;
		}

		$attachment_id = get_queried_object_id();
		$media_url = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';
		if ( ! $media_url ) {
			return;
		}

		wp_safe_redirect( $media_url, 301, 'Primaria Porumbesti' );
		exit;
	}

	private function applies(): bool {
		return ! is_admin() && ! is_feed() && ! is_embed() && ! wp_doing_ajax()
			&& ( is_archive() || is_home() || is_search() || is_singular( array( 'post', 'porumbesti_document' ) ) );
	}

	public function template_include( string $template ): string {
		if ( ! $this->applies() || ! did_action( 'elementor/loaded' ) ) {
			return $template;
		}
		return PORUMBESTI_WIDGETS_PATH . 'templates/frontend-elementor.php';
	}

	public function enqueue_assets(): void {
		if ( ! $this->applies() ) {
			return;
		}
		wp_enqueue_style( 'elementor-frontend' );
		wp_enqueue_style( 'porumbesti-widgets' );
		if ( isset( \Elementor\Plugin::$instance->frontend ) ) {
			\Elementor\Plugin::$instance->frontend->enqueue_scripts();
		}
		wp_enqueue_script( 'porumbesti-widgets' );
	}

	public function body_classes( array $classes ): array {
		if ( $this->applies() ) {
			$classes[] = 'porumbesti-global-template';
			$classes[] = 'elementor-default';
		}
		return array_values( array_unique( $classes ) );
	}

	public function scope_explicit_language( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! isset( $_GET['lang'] ) ) {
			return;
		}
		$language = sanitize_key( wp_unslash( $_GET['lang'] ) );
		if ( in_array( $language, array( 'ro', 'hu' ), true ) ) {
			$query->set( 'lang', $language );
		}
	}

	public function language_attributes( string $output ): string {
		if ( ! $this->applies() ) {
			return $output;
		}
		$locale = 'hu' === $this->language() ? 'hu-HU' : 'ro-RO';
		return preg_replace( '/\blang=(["\']).*?\1/i', 'lang="' . $locale . '"', $output ) ?? $output;
	}

	private function language(): string {
		$request_language = isset( $_GET['lang'] ) ? sanitize_key( wp_unslash( $_GET['lang'] ) ) : '';
		if ( in_array( $request_language, array( 'ro', 'hu' ), true ) ) {
			return $request_language;
		}
		$language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
		if ( in_array( $language, array( 'ro', 'hu' ), true ) ) {
			return $language;
		}
		$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
		return str_contains( $path, '/hu/' ) ? 'hu' : 'ro';
	}

	private function link( string $url ): array {
		return array( 'url' => $url, 'is_external' => '', 'nofollow' => '' );
	}

	private function copy( string $language ): array {
		if ( 'hu' === $language ) {
			return array(
				'official' => 'Kökényesd Község Polgármesteri Hivatalának hivatalos oldala, Szatmár megye, Románia', 'trust' => 'Biztonságos kapcsolat',
				'brand' => 'Kökényesd Község', 'subtitle' => 'Kökényesd Község Polgármesteri Hivatala', 'monitor' => 'Helyi hivatalos közlöny', 'contact' => 'Elérhetőség',
				'skip' => 'Ugrás a tartalomhoz', 'language' => 'Nyelvválasztás', 'nav' => 'Fő navigáció', 'search' => 'Keresés', 'open' => 'Menü megnyitása', 'close_menu' => 'Menü bezárása', 'submenu' => '%s almenüjének megnyitása',
				'search_title' => 'Keresés a portálon', 'search_placeholder' => 'Dokumentumok, felhívások és szolgáltatások keresése…', 'search_button' => 'Keresés', 'close' => 'Bezárás',
				'office' => 'Polgármesteri Hivatal', 'leadership' => 'Vezetőség', 'council' => 'Helyi tanács', 'public' => 'Közérdekű', 'announcements' => 'Felhívások',
				'description' => 'Hivatalos portál ügyintézéshez, közérdekű dokumentumokhoz és önkormányzati tájékoztatáshoz.', 'address' => 'Románia, Szatmár megye, Kökényesd község, Kökényesd, 17C., 447152', 'copyright' => 'Minden jog fenntartva, Kökényesd Község.',
				'footer_nav' => 'Lábléc hivatkozások', 'back_top' => 'Vissza az oldal tetejére', 'accessibility' => 'Akadálymentesítés', 'text_size' => 'Szövegméret', 'contrast' => 'Nagy kontraszt', 'grayscale' => 'Szürkeárnyalat', 'underline' => 'Hivatkozások aláhúzása', 'reset' => 'Beállítások visszaállítása', 'options' => 'Akadálymentesítési beállítások',
				'archive_kicker' => 'Hírek és közérdekű információk', 'search_prefix' => 'Keresési eredmények: %s', 'read_more' => 'Tovább →', 'pagination' => 'Oldalak', 'empty' => 'Nincs megjeleníthető bejegyzés.', 'article' => 'Bejegyzés', 'home' => 'Kezdőlap', 'share' => 'Megosztás', 'copy_link' => 'Hivatkozás másolása',
			);
		}
		return array(
			'official' => 'Site oficial al Primăriei Comunei Porumbești, județul Satu Mare, România', 'trust' => 'Conexiune securizată',
			'brand' => 'Comuna Porumbești', 'subtitle' => 'Primăria Comunei Porumbești', 'monitor' => 'Monitorul Oficial', 'contact' => 'Contact',
			'skip' => 'Sari la conținut', 'language' => 'Alege limba', 'nav' => 'Navigație principală', 'search' => 'Caută', 'open' => 'Deschide meniul', 'close_menu' => 'Închide meniul', 'submenu' => 'Deschide submeniul pentru %s',
			'search_title' => 'Căutare în portal', 'search_placeholder' => 'Căutați documente, anunțuri, servicii…', 'search_button' => 'Caută', 'close' => 'Închide',
			'office' => 'Primăria', 'leadership' => 'Conducere', 'council' => 'Consiliul Local', 'public' => 'Informații publice', 'announcements' => 'Anunțuri',
			'description' => 'Portal oficial pentru cetățeni, documente publice și comunicări administrative.', 'address' => 'România, jud. Satu Mare, com. Porumbești, sat Porumbești, nr. 17C, cod 447152', 'copyright' => 'Toate drepturile rezervate Comuna Porumbești.',
			'footer_nav' => 'Linkuri subsol', 'back_top' => 'Înapoi sus', 'accessibility' => 'Accesibilitate', 'text_size' => 'Mărime text', 'contrast' => 'Contrast ridicat', 'grayscale' => 'Tonuri de gri', 'underline' => 'Linkuri subliniate', 'reset' => 'Resetează setările', 'options' => 'Opțiuni de accesibilitate',
			'archive_kicker' => 'Actualități', 'search_prefix' => 'Rezultate pentru: %s', 'read_more' => 'Citește articolul →', 'pagination' => 'Navigare pagini', 'empty' => 'Nu există articole.', 'article' => 'Articol', 'home' => 'Acasă', 'share' => 'Distribuie', 'copy_link' => 'Copiază linkul',
		);
	}

	private function routes( string $language ): array {
		$prefix = 'hu' === $language ? '/hu/' : '/ro/';
		return array(
			'home' => home_url( $prefix . ( 'hu' === $language ? 'fooldal/' : 'prima/' ) ),
			'mayor' => home_url( $prefix . ( 'hu' === $language ? 'polgarmester/' : 'primar/' ) ),
			'council' => home_url( $prefix . ( 'hu' === $language ? 'a-helyi-tanacs-szerkezete/' : 'componenta-consiliului-local/' ) ),
			'announcements' => home_url( $prefix . ( 'hu' === $language ? 'category/felhivasok/' : 'category/anunturi/' ) ),
			'monitor' => 'hu' === $language ? home_url( '/ro/monitorul-oficial-local-2/' ) : home_url( '/ro/monitorul-oficial-local-2/' ),
			'contact' => home_url( $prefix . ( 'hu' === $language ? 'elerhetosegeink/' : 'contact/' ) ),
		);
	}

	private function menu_id( string $language ): string {
		if ( isset( $this->menu_cache[ $language ] ) ) {
			return $this->menu_cache[ $language ];
		}
		$best = '';
		$best_score = -1;
		foreach ( wp_get_nav_menus() as $menu ) {
			$items = (array) wp_get_nav_menu_items( $menu->term_id );
			if ( ! $items ) {
				continue;
			}
			$name = strtolower( remove_accents( $menu->name ) );
			$score = count( $items );
			$score += str_contains( $name, 'hu' === $language ? 'magyar' : 'roman' ) ? 5000 : 0;
			foreach ( $items as $item ) {
				$path = (string) wp_parse_url( (string) ( $item->url ?? '' ), PHP_URL_PATH );
				$score += str_contains( $path, '/' . $language . '/' ) ? 100 : 0;
			}
			if ( $score > $best_score ) {
				$best_score = $score;
				$best = (string) $menu->term_id;
			}
		}
		$this->menu_cache[ $language ] = $best;
		return $best;
	}

	private function current_url(): string {
		$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
		return home_url( $path ?: '/' );
	}

	private function other_language_url( string $other_language ): string {
		$translated_id = 0;
		if ( is_singular() && function_exists( 'pll_get_post' ) ) {
			$translated_id = (int) pll_get_post( get_queried_object_id(), $other_language );
		} elseif ( ( is_category() || is_tag() || is_tax() ) && function_exists( 'pll_get_term' ) ) {
			$term = get_queried_object();
			$translated_id = (int) pll_get_term( get_queried_object_id(), $other_language );
			if ( $translated_id ) {
				$url = get_term_link( $translated_id, $term instanceof \WP_Term ? $term->taxonomy : '' );
				return is_wp_error( $url ) ? home_url( '/' . $other_language . '/' ) : $url;
			}
		}
		if ( $translated_id ) {
			return (string) get_permalink( $translated_id );
		}
		return function_exists( 'pll_home_url' ) ? (string) pll_home_url( $other_language ) : home_url( '/' . $other_language . '/' );
	}

	private function report_render_error( string $context, \Throwable $error ): void {
		do_action( 'porumbesti_frontend_render_error', $context, $error );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( 'Primaria Porumbesti frontend render error [%s]: %s', $context, $error->getMessage() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	private function render_widget( string $class, string $type, array $settings, string $seed ): bool {
		$widget_files = array(
			'\\PrimariaPorumbesti\\Widgets\\Site_Header' => 'site-header',
			'\\PrimariaPorumbesti\\Widgets\\Site_Footer' => 'site-footer',
			'\\PrimariaPorumbesti\\Widgets\\Accessibility_Tools' => 'accessibility-tools',
			'\\PrimariaPorumbesti\\Widgets\\Search_Box' => 'search-box',
			'\\PrimariaPorumbesti\\Widgets\\Post_Archive' => 'post-archive',
			'\\PrimariaPorumbesti\\Widgets\\Single_Post' => 'single-post',
		);
		$buffer_level = ob_get_level();
		ob_start();
		try {
			if ( ! class_exists( '\\PrimariaPorumbesti\\Widgets\\Base' ) ) {
				require_once PORUMBESTI_WIDGETS_PATH . 'includes/widgets/class-base.php';
			}
			if ( ! class_exists( $class ) && isset( $widget_files[ $class ] ) ) {
				require_once PORUMBESTI_WIDGETS_PATH . 'includes/widgets/class-' . $widget_files[ $class ] . '.php';
			}
			if ( ! class_exists( $class ) || ! isset( \Elementor\Plugin::$instance->elements_manager ) ) {
				ob_end_clean();
				return false;
			}
			$data = array(
				'id'         => substr( md5( 'porumbesti-global-' . $seed ), 0, 7 ),
				'elType'     => 'widget',
				'widgetType' => $type,
				'settings'   => $settings,
			);
			$widget = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $data );
			if ( ! $widget ) {
				ob_end_clean();
				return false;
			}
			$widget->print_element();
			$output = ob_get_clean();
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return true;
		} catch ( \Throwable $error ) {
			while ( ob_get_level() > $buffer_level ) {
				ob_end_clean();
			}
			$this->report_render_error( $type, $error );
			return false;
		}
	}

	private function render_native_header( string $language, array $copy, array $routes ): void {
		$menu_id = $this->menu_id( $language );
		echo '<div id="top" class="porumbesti-header-wrap porumbesti-render-fallback"><div class="porumbesti-govbar"><div class="porumbesti-shell porumbesti-govbar-inner"><div class="porumbesti-official">' . esc_html( $copy['official'] ) . '</div><div class="porumbesti-gov-actions"><a href="' . esc_url( self::WEBMAIL_URL ) . '">Mail</a></div></div></div><header class="porumbesti-site-header"><div class="porumbesti-shell porumbesti-header-inner">';
		echo '<a class="porumbesti-brand" href="' . esc_url( $routes['home'] ) . '"><span class="porumbesti-brand-logo"><img src="' . esc_url( PORUMBESTI_WIDGETS_URL . 'assets/images/porumbesti-monogram.svg' ) . '" alt="" width="52" height="52"></span><span><strong>' . esc_html( $copy['brand'] ) . '</strong><small>' . esc_html( $copy['subtitle'] ) . '</small></span></a>';
		if ( $menu_id ) {
			wp_nav_menu( array( 'menu' => (int) $menu_id, 'container' => 'nav', 'container_class' => 'porumbesti-main-nav', 'menu_class' => 'porumbesti-menu', 'fallback_cb' => false, 'depth' => 4 ) );
		}
		echo '<a class="porumbesti-button porumbesti-button-primary" href="' . esc_url( $routes['monitor'] ) . '">' . esc_html( $copy['monitor'] ) . '</a></div></header></div>';
	}

	private function render_native_content( array $copy, array $routes ): void {
		echo '<main id="main-content" class="porumbesti-global-main porumbesti-render-fallback"><div class="porumbesti-shell">';
		if ( is_singular( array( 'post', 'porumbesti_document' ) ) ) {
			$post_id = get_queried_object_id();
			echo '<article class="porumbesti-single"><header class="porumbesti-title-band"><div class="porumbesti-title-band-inner"><div class="porumbesti-breadcrumbs"><a href="' . esc_url( $routes['home'] ) . '">' . esc_html( $copy['home'] ) . '</a><span>/</span><span>' . esc_html( get_the_title( $post_id ) ) . '</span></div><div class="porumbesti-kicker">' . esc_html( $copy['article'] ) . '</div><h1>' . esc_html( get_the_title( $post_id ) ) . '</h1><div class="porumbesti-single-meta"><time>' . esc_html( get_the_date( '', $post_id ) ) . '</time></div></div></header>';
			if ( has_post_thumbnail( $post_id ) ) {
				echo '<figure class="porumbesti-single-image">' . get_the_post_thumbnail( $post_id, 'full' ) . '</figure>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '<div class="porumbesti-single-content">' . wp_kses_post( wpautop( (string) get_post_field( 'post_content', $post_id ) ) ) . '</div></article>';
		} else {
			$title = is_search() ? str_replace( '%s', get_search_query(), $copy['search_prefix'] ) : ( is_archive() ? get_the_archive_title() : $copy['archive_kicker'] );
			echo '<header class="porumbesti-archive-header porumbesti-title-band"><div class="porumbesti-title-band-inner"><div class="porumbesti-breadcrumbs"><a href="' . esc_url( $routes['home'] ) . '">' . esc_html( $copy['home'] ) . '</a><span>/</span><span>' . esc_html( wp_strip_all_tags( $title ) ) . '</span></div><div class="porumbesti-kicker">' . esc_html( $copy['archive_kicker'] ) . '</div><h1>' . wp_kses_post( $title ) . '</h1></div></header><div class="porumbesti-grid porumbesti-grid-3">';
			global $wp_query;
			if ( $wp_query instanceof \WP_Query ) {
				$wp_query->rewind_posts();
			}
			$has_posts = false;
			while ( have_posts() ) {
				$has_posts = true;
				the_post();
				echo '<article class="porumbesti-news-card"><a href="' . esc_url( get_permalink() ) . '"><div class="porumbesti-news-body"><time>' . esc_html( get_the_date() ) . '</time><h2>' . esc_html( get_the_title() ) . '</h2><p>' . esc_html( wp_trim_words( get_the_excerpt(), 24 ) ) . '</p><strong class="porumbesti-read-more">' . esc_html( $copy['read_more'] ) . '</strong></div></a></article>';
			}
			echo '</div>';
			if ( ! $has_posts ) {
				echo '<div class="porumbesti-empty">' . esc_html( $copy['empty'] ) . '</div>';
			}
			echo '<nav class="porumbesti-pagination" aria-label="' . esc_attr( $copy['pagination'] ) . '">' . wp_kses_post( paginate_links( array( 'type' => 'list' ) ) ) . '</nav>';
		}
		echo '</div></main>';
	}

	private function render_native_footer( array $copy ): void {
		echo '<footer class="porumbesti-footer porumbesti-render-fallback"><div class="porumbesti-shell porumbesti-footer-bottom"><span>&copy; ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html( $copy['copyright'] ) . '</span><nav aria-label="' . esc_attr( $copy['footer_nav'] ) . '"><a href="mailto:primar@primariaporumbesti.ro">primar@primariaporumbesti.ro</a><span aria-hidden="true">·</span><a href="' . esc_url( self::FACEBOOK_URL ) . '">Facebook</a><span aria-hidden="true">·</span><a href="' . esc_url( self::PORTAL_URL ) . '">Portal</a><span aria-hidden="true">·</span><a href="' . esc_url( self::TRANSPARENCY_URL ) . '">SGG</a><span aria-hidden="true">·</span><a href="' . esc_url( self::MAP_URL ) . '">Map</a></nav></div></footer>';
	}

	public function render(): void {
		$language = $this->language();
		$other = 'hu' === $language ? 'ro' : 'hu';
		$copy = $this->copy( $language );
		$routes = $this->routes( $language );
		$languages = 'hu' === $language
			? array( array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( $this->current_url() ) ), array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( $this->other_language_url( $other ) ) ) )
			: array( array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( $this->current_url() ) ), array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( $this->other_language_url( $other ) ) ) );

		$header_rendered = $this->render_widget( '\\PrimariaPorumbesti\\Widgets\\Site_Header', 'porumbesti-site-header', array(
			'official_text' => $copy['official'], 'trust_text' => is_ssl() ? $copy['trust'] : '', 'mail_url' => $this->link( self::WEBMAIL_URL ), 'logo' => $this->brand_logo(),
			'brand_title' => $copy['brand'], 'brand_subtitle' => $copy['subtitle'], 'home_url' => $this->link( $routes['home'] ), 'menu_id' => $this->menu_id( $language ), 'cta_text' => $copy['monitor'], 'cta_link' => $this->link( $routes['monitor'] ), 'porumbesti_sticky' => 'yes', 'language_items' => $languages,
			'skip_label' => $copy['skip'], 'language_label' => $copy['language'], 'nav_label' => $copy['nav'], 'search_label' => $copy['search'], 'menu_open_label' => $copy['open'], 'menu_close_label' => $copy['close_menu'], 'submenu_label' => $copy['submenu'],
		), 'header-' . $language );
		if ( ! $header_rendered ) {
			$this->render_native_header( $language, $copy, $routes );
		}
		$this->render_widget( '\\PrimariaPorumbesti\\Widgets\\Search_Box', 'porumbesti-search-box', array( 'title' => $copy['search_title'], 'placeholder' => $copy['search_placeholder'], 'button_text' => $copy['search_button'], 'close_label' => $copy['close'], 'language' => $language, 'modal' => 'yes' ), 'search-' . $language );

		ob_start();
		if ( is_singular( array( 'post', 'porumbesti_document' ) ) ) {
			$content_rendered = $this->render_widget( '\\PrimariaPorumbesti\\Widgets\\Single_Post', 'porumbesti-single-post', array( 'show_image' => 'yes', 'show_author' => '', 'home_label' => $copy['home'], 'home_url' => $this->link( $routes['home'] ), 'article_label' => $copy['article'] ), 'single-' . $language );
		} else {
			$content_rendered = $this->render_widget( '\\PrimariaPorumbesti\\Widgets\\Post_Archive', 'porumbesti-post-archive', array( 'columns' => '3', 'show_archive_header' => 'yes', 'fallback_title' => $copy['archive_kicker'], 'excerpt_words' => 24, 'archive_kicker' => $copy['archive_kicker'], 'home_label' => $copy['home'], 'home_url' => $this->link( $routes['home'] ), 'search_prefix' => $copy['search_prefix'], 'read_more_text' => $copy['read_more'], 'pagination_label' => $copy['pagination'], 'empty_text' => $copy['empty'], 'article_label' => $copy['article'] ), 'archive-' . $language );
		}
		$content_output = ob_get_clean();
		if ( $content_rendered ) {
			echo '<main id="main-content" class="porumbesti-global-main"><div class="porumbesti-shell">' . $content_output . '</div></main>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			$this->render_native_content( $copy, $routes );
		}

		$footer_links = array(
			array( 'column' => $copy['office'], 'label' => $copy['leadership'], 'url' => $this->link( $routes['mayor'] ) ), array( 'column' => $copy['office'], 'label' => $copy['council'], 'url' => $this->link( $routes['council'] ) ),
			array( 'column' => $copy['public'], 'label' => $copy['announcements'], 'url' => $this->link( $routes['announcements'] ) ), array( 'column' => $copy['public'], 'label' => $copy['monitor'], 'url' => $this->link( $routes['monitor'] ) ),
		);
		$external_links = array(
			array( 'label' => 'Facebook · Kökényesd', 'url' => $this->link( self::FACEBOOK_URL ) ),
			array( 'label' => 'hu' === $language ? 'Online ügyintézési portál' : 'Portal online', 'url' => $this->link( self::PORTAL_URL ) ),
			array( 'label' => 'hu' === $language ? 'Átlátható kormányzás · SGG' : 'Guvernare transparentă · SGG', 'url' => $this->link( self::TRANSPARENCY_URL ) ),
			array( 'label' => 'hu' === $language ? 'Hivatal a térképen' : 'Primăria pe hartă', 'url' => $this->link( self::MAP_URL ) ),
		);
		$footer_rendered = $this->render_widget( '\\PrimariaPorumbesti\\Widgets\\Site_Footer', 'porumbesti-site-footer', array( 'title' => $copy['brand'], 'subtitle' => $copy['subtitle'], 'description' => $copy['description'], 'links' => $footer_links, 'phone' => '0361 525 288', 'email' => 'primar@primariaporumbesti.ro', 'address' => $copy['address'], 'copyright' => $copy['copyright'], 'contact_url' => $this->link( $routes['contact'] ), 'monitor_url' => $this->link( $routes['monitor'] ), 'contact_title' => $copy['contact'], 'contact_link_text' => $copy['contact'], 'monitor_link_text' => $copy['monitor'], 'footer_nav_label' => $copy['footer_nav'], 'back_to_top_label' => $copy['back_top'], 'external_title' => 'hu' === $language ? 'Hivatalos hivatkozások' : 'Resurse oficiale', 'external_links' => $external_links ), 'footer-' . $language );
		if ( ! $footer_rendered ) {
			$this->render_native_footer( $copy );
		}
		$this->render_widget( '\\PrimariaPorumbesti\\Widgets\\Accessibility_Tools', 'porumbesti-accessibility', array( 'title' => $copy['accessibility'], 'position' => 'right', 'text_size_label' => $copy['text_size'], 'contrast_label' => $copy['contrast'], 'grayscale_label' => $copy['grayscale'], 'underline_label' => $copy['underline'], 'reset_label' => $copy['reset'], 'options_label' => $copy['options'], 'back_to_top_label' => $copy['back_top'] ), 'accessibility-' . $language );
	}

	public function render_safely(): void {
		$buffer_level = ob_get_level();
		ob_start();
		try {
			$this->render();
			$output = ob_get_clean();
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} catch ( \Throwable $error ) {
			while ( ob_get_level() > $buffer_level ) {
				ob_end_clean();
			}
			$this->report_render_error( 'global-template', $error );
			$language = $this->language();
			$copy = $this->copy( $language );
			$routes = $this->routes( $language );
			$this->render_native_header( $language, $copy, $routes );
			$this->render_native_content( $copy, $routes );
			$this->render_native_footer( $copy );
		}
	}
}
