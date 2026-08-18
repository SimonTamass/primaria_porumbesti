<?php

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ );
	class WP_Query {
		public static array $last_args = array();
		public function __construct( array $args ) { self::$last_args = $args; }
		public function have_posts(): bool { return false; }
	}
	function sanitize_title( string $value ): string { return strtolower( trim( $value ) ); }
	function sanitize_key( string $value ): string { return strtolower( trim( $value ) ); }
	function get_category_by_slug( string $slug ): ?object { return 'parent' === $slug ? (object) array( 'term_id' => 10 ) : null; }
	function get_term_children( int $term_id, string $taxonomy ): array { return 10 === $term_id && 'category' === $taxonomy ? array( 11, 12 ) : array(); }
	function is_wp_error( $value ): bool { return false; }
	function esc_html( string $value ): string { return $value; }
}

namespace Elementor {
	class Widget_Base {
		public static array $settings = array();
		protected function get_settings_for_display(): array { return self::$settings; }
	}
	class Controls_Manager {
		public const SELECT = 'select';
		public const TEXT = 'text';
		public const NUMBER = 'number';
		public const SWITCHER = 'switcher';
	}
	class Repeater {}
}

namespace PrimariaPorumbesti\Widgets {
	abstract class Base extends \Elementor\Widget_Base {
		protected static function post_types(): array { return array( 'post' => 'Post' ); }
		protected function register_common_style_controls(): void {}
	}
}

namespace {
	require_once dirname( __DIR__ ) . '/includes/widgets/class-news-grid.php';
	\Elementor\Widget_Base::$settings = array(
		'post_type' => 'post',
		'category' => 'parent',
		'language' => 'ro',
		'count' => 6,
		'columns' => '3',
		'orderby' => 'date',
		'show_excerpt' => 'yes',
		'show_category' => 'yes',
		'show_date' => 'yes',
		'empty_text' => 'Empty',
		'read_more_text' => 'More',
	);
	$widget = new \PrimariaPorumbesti\Widgets\News_Grid();
	$render = ( new \ReflectionClass( $widget ) )->getMethod( 'render' );
	ob_start();
	$render->invoke( $widget );
	ob_end_clean();

	if ( array( 10, 11, 12 ) !== \WP_Query::$last_args['category__in'] ) {
		fwrite( STDERR, "News grid did not include descendant categories.\n" );
		exit( 1 );
	}
	if ( 'ro' !== \WP_Query::$last_args['lang'] || 6 !== \WP_Query::$last_args['posts_per_page'] ) {
		fwrite( STDERR, "News grid lost language or count settings.\n" );
		exit( 1 );
	}

	echo "News grid query smoke passed.\n";
}
