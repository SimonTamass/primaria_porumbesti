<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Photo_Gallery extends Base {
	public function get_name(): string { return 'porumbesti-photo-gallery'; }
	public function get_title(): string { return __( '16 · Fotógaléria', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-gallery-grid'; }
	protected function register_controls(): void { $this->start_controls_section( 'gallery', array( 'label' => __( 'Képek', 'primaria-porumbesti' ) ) ); $this->common_heading_controls( 'Galerie', 'Comuna în imagini' ); $r = new Repeater(); $r->add_control( 'image', array( 'label' => __( 'Kép', 'primaria-porumbesti' ), 'type' => Controls_Manager::MEDIA ) ); $r->add_control( 'caption', array( 'label' => __( 'Képaláírás', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT ) ); $this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ caption }}}' ) ); $this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( '2' => '2', '3' => '3', '4' => '4' ), 'default' => '3' ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><section class="porumbesti-gallery-widget"><?php $this->render_heading( $s ); ?><div class="porumbesti-gallery porumbesti-grid-<?php echo esc_attr( $s['columns'] ); ?>"><?php foreach ( $s['items_list'] as $item ) : if ( empty( $item['image']['url'] ) ) continue; ?><a href="<?php echo esc_url( $item['image']['url'] ); ?>" data-porumbesti-lightbox data-porumbesti-lightbox-group="porumbesti-<?php echo esc_attr( $this->get_id() ); ?>" data-porumbesti-lightbox-caption="<?php echo esc_attr( $item['caption'] ); ?>"><img src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="<?php echo esc_attr( $item['caption'] ); ?>" loading="lazy"><?php if ( $item['caption'] ) : ?><span><?php echo esc_html( $item['caption'] ); ?></span><?php endif; ?></a><?php endforeach; ?></div></section><?php }
}
