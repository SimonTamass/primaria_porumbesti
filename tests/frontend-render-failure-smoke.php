<?php
namespace Elementor {
	final class Plugin {
		public static $instance;
	}
}

namespace PrimariaPorumbesti\Widgets {
	abstract class Base {}
}

namespace {
	define( 'ABSPATH', __DIR__ );
	define( 'PORUMBESTI_WIDGETS_PATH', dirname( __DIR__ ) . '/' );

	function add_filter(): void {}
	function add_action(): void {}

	$porumbesti_render_events = array();
	function do_action( string $hook ): void {
		global $porumbesti_render_events;
		$porumbesti_render_events[] = $hook;
	}

	final class Porumbesti_Fake_Elements_Manager {
		public bool $fail = true;

		public function create_element_instance(): object {
			$fail = $this->fail;
			return new class( $fail ) {
				public function __construct( private bool $fail ) {}
				public function print_element(): void {
					echo 'partial';
					if ( $this->fail ) {
						throw new \RuntimeException( 'Simulated Elementor render failure.' );
					}
					echo '-complete';
				}
			};
		}
	}

	require_once dirname( __DIR__ ) . '/includes/class-frontend-templates.php';

	$manager = new Porumbesti_Fake_Elements_Manager();
	\Elementor\Plugin::$instance = (object) array( 'elements_manager' => $manager );
	$frontend = \PrimariaPorumbesti\Frontend_Templates::instance();
	$method = new \ReflectionMethod( $frontend, 'render_widget' );

	ob_start();
	$failed = $method->invoke( $frontend, '\\stdClass', 'porumbesti-test', array(), 'failure' );
	$failed_output = ob_get_clean();
	if ( false !== $failed || '' !== $failed_output || ! in_array( 'porumbesti_frontend_render_error', $porumbesti_render_events, true ) ) {
		fwrite( STDERR, "Widget failure was not contained.\n" );
		exit( 1 );
	}

	$manager->fail = false;
	ob_start();
	$succeeded = $method->invoke( $frontend, '\\stdClass', 'porumbesti-test', array(), 'success' );
	$success_output = ob_get_clean();
	if ( true !== $succeeded || 'partial-complete' !== $success_output ) {
		fwrite( STDERR, "Successful widget output was not preserved.\n" );
		exit( 1 );
	}

	echo "Frontend render failure smoke passed.\n";
}
