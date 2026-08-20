<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Accessibility_Tools extends Base {
	public function get_name(): string { return 'porumbesti-accessibility'; }
	public function get_title(): string { return __( '03 · Accesibilitate', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-accessibility'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Etichete', 'primaria-porumbesti' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Titlu', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Accesibilitate' ) );
		$this->add_control( 'position', array( 'label' => __( 'Poziție', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'right' => 'Dreapta', 'left' => 'Stânga' ), 'default' => 'right' ) );
		$this->add_control( 'text_size_label', array( 'label' => __( 'Szövegméret', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Mărime text' ) );
		$this->add_control( 'contrast_label', array( 'label' => __( 'Nagy kontraszt', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Contrast ridicat' ) );
		$this->add_control( 'grayscale_label', array( 'label' => __( 'Szürkeárnyalat', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Tonuri de gri' ) );
		$this->add_control( 'underline_label', array( 'label' => __( 'Aláhúzott hivatkozások', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Linkuri subliniate' ) );
		$this->add_control( 'reset_label', array( 'label' => __( 'Visszaállítás', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Resetează setările' ) );
		$this->add_control( 'options_label', array( 'label' => __( 'Akadálymentesítési beállítások', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Opțiuni de accesibilitate' ) );
		$this->add_control( 'back_to_top_label', array( 'label' => __( 'Vissza az oldal tetejére', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Înapoi sus' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$panel_id = 'porumbesti-a11y-panel-' . $this->get_id();
		$title_id = 'porumbesti-a11y-title-' . $this->get_id();
		?>
		<div class="porumbesti-a11y porumbesti-a11y-<?php echo esc_attr( $s['position'] ); ?>">
			<div id="<?php echo esc_attr( $panel_id ); ?>" class="porumbesti-a11y-panel" role="dialog" aria-labelledby="<?php echo esc_attr( $title_id ); ?>" hidden><h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $s['title'] ); ?></h2><div class="porumbesti-a11y-row"><span><?php echo esc_html( $s['text_size_label'] ); ?></span><span class="porumbesti-a11y-scale"><button type="button" data-porumbesti-scale="down" aria-label="<?php echo esc_attr( $s['text_size_label'] . ' −' ); ?>">−</button><strong data-porumbesti-scale-label>100%</strong><button type="button" data-porumbesti-scale="up" aria-label="<?php echo esc_attr( $s['text_size_label'] . ' +' ); ?>">+</button></span></div><div class="porumbesti-a11y-row"><span><?php echo esc_html( $s['contrast_label'] ); ?></span><button type="button" class="porumbesti-switch" data-porumbesti-a11y="contrast" aria-label="<?php echo esc_attr( $s['contrast_label'] ); ?>" aria-pressed="false"><i aria-hidden="true"></i></button></div><div class="porumbesti-a11y-row"><span><?php echo esc_html( $s['grayscale_label'] ); ?></span><button type="button" class="porumbesti-switch" data-porumbesti-a11y="grayscale" aria-label="<?php echo esc_attr( $s['grayscale_label'] ); ?>" aria-pressed="false"><i aria-hidden="true"></i></button></div><div class="porumbesti-a11y-row"><span><?php echo esc_html( $s['underline_label'] ); ?></span><button type="button" class="porumbesti-switch" data-porumbesti-a11y="underline" aria-label="<?php echo esc_attr( $s['underline_label'] ); ?>" aria-pressed="false"><i aria-hidden="true"></i></button></div><button type="button" class="porumbesti-button porumbesti-button-soft" data-porumbesti-reset><?php echo esc_html( $s['reset_label'] ); ?></button></div>
			<div class="porumbesti-floating"><button type="button" data-porumbesti-a11y-toggle aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" aria-label="<?php echo esc_attr( $s['options_label'] ); ?>"><svg class="porumbesti-a11y-toggle-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="4.5" r="2"></circle><path d="M5 8.5c4.5 1.5 9.5 1.5 14 0M12 10v10M8.5 20 12 14l3.5 6"></path></svg></button><button type="button" data-porumbesti-top aria-label="<?php echo esc_attr( $s['back_to_top_label'] ); ?>">↑</button></div>
		</div>
		<?php
	}
}
