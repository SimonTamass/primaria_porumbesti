<?php
namespace PrimariaPorumbesti;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Legacy_Content {
	private static function shortcode_media_ids( string $attributes ): array {
		$ids = array();
		if ( preg_match_all( '/\b(?:attach_images|image|images|ids)\s*=\s*(?:["\'“”„″]([^"\'“”„″\]]*)["\'“”„″]|([^\s\]]+))/iu', $attributes, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$value = (string) ( $match[1] ?: $match[2] );
				if ( preg_match_all( '/\d+/', $value, $numbers ) ) {
					$ids = array_merge( $ids, array_map( 'intval', $numbers[0] ) );
				}
			}
		}
		return array_values( array_unique( array_filter( $ids ) ) );
	}

	private static function image_markup( array $ids, bool $gallery = false ): string {
		$images = '';
		foreach ( $ids as $attachment_id ) {
			$image = wp_get_attachment_image( $attachment_id, 'large', false, array( 'loading' => 'lazy' ) );
			if ( ! $image ) {
				continue;
			}
			$caption = wp_get_attachment_caption( $attachment_id );
			$images .= '<figure class="porumbesti-legacy-media">' . $image . ( $caption ? '<figcaption>' . esc_html( $caption ) . '</figcaption>' : '' ) . '</figure>';
		}
		return $gallery && $images ? '<div class="porumbesti-legacy-gallery">' . $images . '</div>' : $images;
	}

	private static function attributes( string $attributes ): array {
		$parsed = array();
		if ( preg_match_all( '/([a-zA-Z][a-zA-Z0-9_-]*)\s*=\s*(?:["\'“”„″]([^"\'“”„″\]]*)["\'“”„″]|([^\s\]]+))/u', $attributes, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$parsed[ strtolower( $match[1] ) ] = html_entity_decode( (string) ( $match[2] ?: $match[3] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			}
		}
		return $parsed;
	}

	private static function expand_media( string $content ): string {
		$content = preg_replace_callback(
			'/\[vc_single_image\b([^\]]*)\](?:\[\/vc_single_image\])?/iu',
			static fn( array $matches ): string => self::image_markup( self::shortcode_media_ids( $matches[1] ) ),
			$content
		) ?? $content;
		$content = preg_replace_callback(
			'/\[(?:vc_gallery|gallery|qode_advanced_image_gallery)\b([^\]]*)\](?:\[\/(?:vc_gallery|gallery|qode_advanced_image_gallery)\])?/iu',
			static fn( array $matches ): string => self::image_markup( self::shortcode_media_ids( $matches[1] ), true ),
			$content
		) ?? $content;
		return $content;
	}

	private static function expand_links( string $content ): string {
		$content = preg_replace_callback(
			'/\[(?:otw_shortcode_button|otw-button)\b([^\]]*)\](.*?)\[\/(?:otw_shortcode_button|otw-button)\]/isu',
			static function ( array $matches ): string {
				$attributes = self::attributes( $matches[1] );
				$url = (string) ( $attributes['href'] ?? $attributes['link'] ?? '' );
				return $url ? '<a class="porumbesti-legacy-button" href="' . esc_url( $url ) . '">' . wp_kses_post( $matches[2] ) . '</a>' : $matches[2];
			},
			$content
		) ?? $content;
		$content = preg_replace_callback(
			'/\[(?:button|qode_button)\b([^\]]*)\](?:\[\/(?:button|qode_button)\])?/iu',
			static function ( array $matches ): string {
				$attributes = self::attributes( $matches[1] );
				$url = (string) ( $attributes['link'] ?? $attributes['href'] ?? '' );
				$text = (string) ( $attributes['text'] ?? $attributes['title'] ?? $url );
				return $url ? '<a class="porumbesti-legacy-button" href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>' : esc_html( $text );
			},
			$content
		) ?? $content;
		return $content;
	}

	public static function normalize( string $content ): string {
		$content = self::expand_links( $content );
		$content = self::expand_media( $content );
		$protected_media = array();
		$content = preg_replace_callback(
			'/\[\/?(?:audio|caption|embed|playlist|video)\b[^\]]*\]/iu',
			static function ( array $matches ) use ( &$protected_media ): string {
				$key = '<!--porumbesti-protected-media-' . count( $protected_media ) . '-->';
				$protected_media[ $key ] = $matches[0];
				return $key;
			},
			$content
		) ?? $content;
		$content = preg_replace( '/\[(?:\/?)[a-zA-Z][a-zA-Z0-9_-]*(?:\s[^\]]*)?\]/u', '', $content ) ?? $content;
		$content = strtr( $content, $protected_media );
		$content = preg_replace( '/<h1(\s[^>]*)?>/i', '<h2$1>', $content ) ?? $content;
		$content = preg_replace( '/<\/h1>/i', '</h2>', $content ) ?? $content;
		$content = preg_replace( '/<p>\s*(?:&nbsp;)?\s*<\/p>/i', '', $content ) ?? $content;
		return trim( $content );
	}
}
