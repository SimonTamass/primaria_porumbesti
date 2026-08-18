<?php

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ );
	define( 'PORUMBESTI_WIDGETS_PATH', dirname( __DIR__ ) . DIRECTORY_SEPARATOR );
	class Walker_Nav_Menu {
		public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ): void {}
	}
}

namespace Elementor {
	abstract class Widget_Base {}
	final class Controls_Manager {}
	final class Group_Control_Typography {}
	final class Group_Control_Image_Size {}
	final class Repeater {}
}

namespace {
	require_once PORUMBESTI_WIDGETS_PATH . 'includes/class-widget-registry.php';

	$manager = new class() {
		public array $widgets = array();

		public function register( object $widget ): void {
			$this->widgets[] = $widget;
		}
	};

	\PrimariaPorumbesti\Widget_Registry::register( $manager );
	$names = array_map( static fn( object $widget ): string => $widget->get_name(), $manager->widgets );

	if ( 24 !== count( $names ) ) {
		throw new RuntimeException( sprintf( 'Expected 24 widgets, got %d.', count( $names ) ) );
	}
	if ( count( $names ) !== count( array_unique( $names ) ) ) {
		throw new RuntimeException( 'Widget names must be unique.' );
	}
	foreach ( $names as $name ) {
		if ( ! str_starts_with( $name, 'porumbesti-' ) ) {
			throw new RuntimeException( 'Unexpected widget name: ' . $name );
		}
	}

	echo 'Widget registry smoke passed: ' . count( $names ) . PHP_EOL;
}
