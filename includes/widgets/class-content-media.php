<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Content_Media extends Base {
	public function get_name(): string { return 'porumbesti-content-media'; }
	public function get_title(): string { return __( '08 · Tartalom + kép', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-image-box'; }
	protected function register_controls(): void { $this->start_controls_section( 'content', array( 'label' => __( 'Tartalom', 'primaria-porumbesti' ) ) ); $this->common_heading_controls( 'Comuna', 'Istorie, cultură și natură' ); $this->add_control( 'content', array( 'label' => __( 'Szöveg', 'primaria-porumbesti' ), 'type' => Controls_Manager::WYSIWYG, 'default' => '<p>Adăugați aici conținutul paginii.</p>' ) ); $this->add_control( 'image', array( 'label' => __( 'Kép', 'primaria-porumbesti' ), 'type' => Controls_Manager::MEDIA ) ); $this->add_group_control( Group_Control_Image_Size::get_type(), array( 'name' => 'image', 'default' => 'large' ) ); $this->add_control( 'image_side', array( 'label' => __( 'Kép helye', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'right' => 'Jobbra', 'left' => 'Balra' ), 'default' => 'right' ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $has_image = ! empty( $s['image']['id'] ) || ! empty( $s['image']['url'] ); $content = do_shortcode( wpautop( (string) $s['content'] ) ); ?><div class="porumbesti-content-media is-<?php echo esc_attr( $s['image_side'] ); ?> <?php echo $has_image ? 'has-media' : 'has-no-media'; ?>"><div><?php $this->render_heading( $s ); ?><div class="porumbesti-richtext"><?php echo wp_kses_post( $content ); ?></div></div><?php if ( $has_image ) : ?><div class="porumbesti-media-frame"><?php echo Group_Control_Image_Size::get_attachment_image_html( $s, 'image', 'image' ); ?></div><?php endif; ?></div><?php }
}
