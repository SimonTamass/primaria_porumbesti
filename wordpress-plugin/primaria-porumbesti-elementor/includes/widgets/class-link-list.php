<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Link_List extends Base {
	public function get_name(): string { return 'porumbesti-link-list'; }
	public function get_title(): string { return __( '12 · Linklista / oldalsáv', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-editor-list-ul'; }
	protected function register_controls(): void { $this->start_controls_section( 'links', array( 'label' => __( 'Linkek', 'primaria-porumbesti' ) ) ); $this->add_control( 'title', array( 'label' => __( 'Cím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Linkuri utile' ) ); $r = new Repeater(); $r->add_control( 'icon', array( 'label' => __( 'Jel', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'DOC' ) ); $r->add_control( 'label', array( 'label' => __( 'Cím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Document util' ) ); $r->add_control( 'meta', array( 'label' => __( 'Alcím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT ) ); $r->add_control( 'url', array( 'label' => __( 'Link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) ); $this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ icon }}} · {{{ label }}}' ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><div class="porumbesti-link-widget"><?php if ( $s['title'] ) : ?><h2><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?><?php foreach ( $s['items_list'] as $item ) : ?><a class="porumbesti-doc-card" <?php echo self::link_attrs( $item['url'] ); ?>><b><?php echo esc_html( $item['icon'] ); ?></b><span><strong><?php echo esc_html( $item['label'] ); ?></strong><small><?php echo esc_html( $item['meta'] ); ?></small></span></a><?php endforeach; ?></div><?php }
}
