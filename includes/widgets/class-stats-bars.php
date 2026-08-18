<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Stats_Bars extends Base {
	public function get_name(): string {
		return 'porumbesti-stats-bars';
	}

	public function get_title(): string {
		return __( '18 · Statisztika / megoszlás', 'primaria-porumbesti' );
	}

	public function get_icon(): string {
		return 'eicon-skill-bar';
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'stats', array( 'label' => __( 'Adatok', 'primaria-porumbesti' ) ) );
		$this->common_heading_controls( 'Componență', 'Distribuția consiliului' );

		$repeater = new Repeater();
		$repeater->add_control( 'label', array( 'label' => __( 'Megnevezés', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Grup' ) );
		$repeater->add_control( 'value', array( 'label' => __( 'Sáv értéke', 'primaria-porumbesti' ), 'type' => Controls_Manager::NUMBER, 'default' => 50, 'min' => 0, 'max' => 100 ) );
		$repeater->add_control( 'display_value', array( 'label' => __( 'Megjelenített érték', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'description' => __( 'Üresen a sáv értéke jelenik meg.', 'primaria-porumbesti' ) ) );
		$repeater->add_control( 'suffix', array( 'label' => __( 'Utótag', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => '%' ) );
		$repeater->add_control( 'color', array( 'label' => __( 'Szín', 'primaria-porumbesti' ), 'type' => Controls_Manager::COLOR, 'default' => '#123b5d' ) );

		$this->add_control(
			'items_list',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label }}} · {{{ value }}}{{{ suffix }}}',
				'default'     => array(
					array( 'label' => 'UDMR', 'value' => 55, 'color' => '#123b5d' ),
					array( 'label' => 'PNL', 'value' => 27, 'color' => '#1d4ed8' ),
					array( 'label' => 'PSD', 'value' => 18, 'color' => '#b42318' ),
				),
			)
		);
		$this->end_controls_section();
		$this->register_common_style_controls();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		?>
		<section class="porumbesti-stats">
			<?php $this->render_heading( $settings ); ?>
			<div class="porumbesti-stats-list">
				<?php foreach ( $settings['items_list'] as $item ) : ?>
					<?php
					$value         = max( 0, min( 100, (float) $item['value'] ) );
					$display_value = '' !== (string) ( $item['display_value'] ?? '' ) ? $item['display_value'] : $item['value'];
					?>
					<div class="porumbesti-stat">
						<div>
							<strong><?php echo esc_html( $item['label'] ); ?></strong>
							<span><?php echo esc_html( $display_value . $item['suffix'] ); ?></span>
						</div>
						<i><b style="width:<?php echo esc_attr( $value ); ?>%;background:<?php echo esc_attr( $item['color'] ); ?>"></b></i>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
