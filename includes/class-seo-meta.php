<?php
namespace PrimariaPorumbesti;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SEO_Meta {
	private static ?SEO_Meta $instance = null;

	public static function instance(): SEO_Meta {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_head', array( $this, 'render' ), 5 );
	}

	public function render(): void {
		if ( is_admin() || is_feed() || is_embed() || wp_doing_ajax() || ! apply_filters( 'porumbesti_output_seo_meta', true ) ) {
			return;
		}

		$language    = $this->language();
		$title       = wp_get_document_title();
		$description = $this->description( $language );
		$url         = $this->canonical_url();
		$image       = $this->image_url();
		$locale      = 'hu' === $language ? 'hu_HU' : 'ro_RO';
		$alternate   = 'hu' === $language ? 'ro_RO' : 'hu_HU';
		$site_name   = 'hu' === $language ? 'Kökényesd Község' : 'Comuna Porumbești';
		$type        = is_singular( array( 'post', 'porumbesti_document' ) ) ? 'article' : 'website';

		$this->meta( 'name', 'description', $description );
		$this->meta( 'property', 'og:type', $type );
		$this->meta( 'property', 'og:title', $title );
		$this->meta( 'property', 'og:description', $description );
		$this->meta( 'property', 'og:url', $url );
		$this->meta( 'property', 'og:site_name', $site_name );
		$this->meta( 'property', 'og:locale', $locale );
		$this->meta( 'property', 'og:locale:alternate', $alternate );
		$this->meta( 'name', 'twitter:card', $image ? 'summary_large_image' : 'summary' );
		$this->meta( 'name', 'twitter:title', $title );
		$this->meta( 'name', 'twitter:description', $description );

		if ( $image ) {
			$this->meta( 'property', 'og:image', $image );
			$this->meta( 'name', 'twitter:image', $image );
		}

		if ( 'article' === $type ) {
			$post = get_post();
			if ( $post instanceof \WP_Post ) {
				$this->meta( 'property', 'article:published_time', get_post_time( DATE_W3C, true, $post ) );
				$this->meta( 'property', 'article:modified_time', get_post_modified_time( DATE_W3C, true, $post ) );
			}
		}
	}

	private function language(): string {
		$language = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
		if ( in_array( $language, array( 'ro', 'hu' ), true ) ) {
			return $language;
		}
		$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
		return str_contains( $path, '/hu/' ) ? 'hu' : 'ro';
	}

	private function description( string $language ): string {
		$defaults = array(
			'ro' => 'Site-ul oficial al Primăriei Comunei Porumbești: servicii publice, anunțuri, hotărâri, documente și informații pentru cetățeni.',
			'hu' => 'Kökényesd Község Polgármesteri Hivatalának hivatalos oldala: ügyintézés, felhívások, határozatok, dokumentumok és közérdekű információk.',
		);

		if ( ! is_singular() ) {
			return $defaults[ $language ];
		}

		$post = get_post();
		if ( ! $post instanceof \WP_Post ) {
			return $defaults[ $language ];
		}

		$excerpt = (string) get_the_excerpt( $post );
		$excerpt = preg_replace( '/\[[^\]]+\]/u', ' ', $excerpt ) ?? $excerpt;
		$excerpt = html_entity_decode( wp_strip_all_tags( $excerpt ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$excerpt = trim( preg_replace( '/\s+/u', ' ', $excerpt ) ?? $excerpt );
		if ( strlen( $excerpt ) >= 40 ) {
			return wp_html_excerpt( $excerpt, 155, '…' );
		}

		$title = html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$description = 'hu' === $language
			? sprintf( '%s – Kökényesd Község Polgármesteri Hivatala által közzétett közérdekű információk és dokumentumok.', $title )
			: sprintf( '%s – informații și documente publicate de Primăria Comunei Porumbești.', $title );
		return wp_html_excerpt( $description, 155, '…' );
	}

	private function canonical_url(): string {
		$url = is_singular() ? (string) wp_get_canonical_url() : '';
		if ( $url ) {
			return $url;
		}
		$request = wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' );
		return home_url( '/' . ltrim( $request, '/' ) );
	}

	private function image_url(): string {
		if ( ! is_singular() ) {
			return '';
		}
		$image = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
		return is_string( $image ) ? $image : '';
	}

	private function meta( string $attribute, string $key, string $value ): void {
		if ( '' === trim( $value ) ) {
			return;
		}
		echo '<meta ' . esc_attr( $attribute ) . '="' . esc_attr( $key ) . '" content="' . esc_attr( $value ) . '">' . "\n";
	}
}
