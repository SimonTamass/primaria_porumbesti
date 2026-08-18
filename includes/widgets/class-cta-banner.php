<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Cta_Banner extends Base {
	public function get_name(): string { return 'porumbesti-cta-banner'; }
	public function get_title(): string { return __( '15 · CTA / intézményi banner', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-call-to-action'; }
	protected function register_controls(): void { $this->start_controls_section( 'content', array( 'label' => __( 'Banner', 'primaria-porumbesti' ) ) ); $this->common_heading_controls( 'Transparență administrativă', 'Guvernare transparentă, deschisă și participativă' ); $this->add_control( 'image', array( 'label' => __( 'Logó / bannerkép', 'primaria-porumbesti' ), 'type' => Controls_Manager::MEDIA ) ); $this->add_group_control( Group_Control_Image_Size::get_type(), array( 'name' => 'image', 'default' => 'large' ) ); $this->add_control( 'button_text', array( 'label' => __( 'Gomb', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT ) ); $this->add_control( 'button_link', array( 'label' => __( 'Link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $has_content = ! empty( $s['kicker'] ) || ! empty( $s['title'] ) || ! empty( $s['description'] ) || ! empty( $s['button_text'] ); ?><section class="porumbesti-cta<?php echo $has_content ? '' : ' is-image-only'; ?>"><?php if ( $has_content ) : ?><div><?php $this->render_heading( $s ); ?><?php if ( $s['button_text'] ) : ?><a class="porumbesti-button porumbesti-button-light" <?php echo self::link_attrs( $s['button_link'] ); ?>><?php echo esc_html( $s['button_text'] ); ?></a><?php endif; ?></div><?php endif; ?><?php if ( $s['image']['url'] ) : ?><div class="porumbesti-cta-image"><?php echo Group_Control_Image_Size::get_attachment_image_html( $s, 'image', 'image' ); ?></div><?php endif; ?></section><?php }
}
