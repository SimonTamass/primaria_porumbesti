<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Schedule_Grid extends Base {
	public function get_name(): string { return 'porumbesti-schedule-grid'; }
	public function get_title(): string { return __( '10 · Fogadóórák', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-calendar'; }
	protected function register_controls(): void { $this->start_controls_section( 'content', array( 'label' => __( 'Program', 'primaria-porumbesti' ) ) ); $this->common_heading_controls( 'Program audiențe', 'Acces la conducere' ); $r = new Repeater(); $r->add_control( 'icon', array( 'label' => __( 'Jel', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'PR' ) ); $r->add_control( 'title', array( 'label' => __( 'Tisztség', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Primar' ) ); $r->add_control( 'time', array( 'label' => __( 'Időpont', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Program disponibil la sediul Primăriei' ) ); $this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ title }}} · {{{ time }}}', 'default' => array( array( 'icon' => 'PR', 'title' => 'Primar', 'time' => 'Program disponibil la sediul Primăriei' ), array( 'icon' => 'VP', 'title' => 'Viceprimar', 'time' => 'Program disponibil la sediul Primăriei' ) ) ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><section class="porumbesti-info-panel"><?php $this->render_heading( $s ); ?><div class="porumbesti-grid porumbesti-grid-2"><?php foreach ( $s['items_list'] as $item ) : ?><div class="porumbesti-detail"><b><?php echo esc_html( $item['icon'] ); ?></b><span><strong><?php echo esc_html( $item['title'] ); ?></strong><small><?php echo esc_html( $item['time'] ); ?></small></span></div><?php endforeach; ?></div></section><?php }
}
