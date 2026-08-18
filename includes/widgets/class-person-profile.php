<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Person_Profile extends Base {
	public function get_name(): string { return 'porumbesti-person-profile'; }
	public function get_title(): string { return __( '09 · Vezetői profil', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-person'; }
	protected function register_controls(): void { $this->start_controls_section( 'profile', array( 'label' => __( 'Személy', 'primaria-porumbesti' ) ) ); $this->add_control( 'photo', array( 'label' => __( 'Fotó', 'primaria-porumbesti' ), 'type' => Controls_Manager::MEDIA ) ); $this->add_group_control( Group_Control_Image_Size::get_type(), array( 'name' => 'photo', 'default' => 'large' ) ); $this->add_control( 'role', array( 'label' => __( 'Tisztség', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Primar' ) ); $this->add_control( 'name', array( 'label' => __( 'Név', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Tóth Zoltán' ) ); $this->add_control( 'subtitle', array( 'label' => __( 'Alcím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Primarul Comunei Porumbești' ) ); $this->add_control( 'bio', array( 'label' => __( 'Bemutatkozás', 'primaria-porumbesti' ), 'type' => Controls_Manager::WYSIWYG ) ); $this->add_control( 'phone', array( 'label' => __( 'Telefon', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT ) ); $this->add_control( 'email', array( 'label' => __( 'Email', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT ) ); $this->add_control( 'office', array( 'label' => __( 'Fogadóóra / információ', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><article class="porumbesti-person"><div class="porumbesti-person-photo"><?php if ( $s['photo']['url'] ) { echo Group_Control_Image_Size::get_attachment_image_html( $s, 'photo', 'photo' ); } else { echo '<span>◎</span>'; } ?></div><div class="porumbesti-person-body"><span class="porumbesti-tag"><?php echo esc_html( $s['role'] ); ?></span><h2><?php echo esc_html( $s['name'] ); ?></h2><p class="porumbesti-muted"><?php echo esc_html( $s['subtitle'] ); ?></p><?php echo wp_kses_post( $s['bio'] ); ?><div class="porumbesti-detail-list"><?php if ( $s['phone'] ) : ?><a href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', $s['phone'] ) ); ?>"><b>TEL</b><span><?php echo esc_html( $s['phone'] ); ?></span></a><?php endif; ?><?php if ( $s['email'] ) : ?><a href="mailto:<?php echo esc_attr( antispambot( $s['email'] ) ); ?>"><b>@</b><span><?php echo esc_html( antispambot( $s['email'] ) ); ?></span></a><?php endif; ?><?php if ( $s['office'] ) : ?><div><b>ORĂ</b><span><?php echo esc_html( $s['office'] ); ?></span></div><?php endif; ?></div></div></article><?php }
}
