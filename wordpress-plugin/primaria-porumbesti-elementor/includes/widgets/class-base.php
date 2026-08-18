<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
abstract class Base extends Widget_Base {
	public function get_categories(): array {
		return array( 'primaria-porumbesti' );
	}

	public function get_style_depends(): array {
		return array( 'porumbesti-widgets' );
	}

	public function get_script_depends(): array {
		return array( 'porumbesti-widgets' );
	}

	public function get_keywords(): array {
		return array( 'porumbesti', 'comuna', 'primarie', 'municipality', 'elementor' );
	}

	protected function register_common_style_controls(): void {
		$this->start_controls_section(
			'porumbesti_common_style',
			array(
				'label' => __( 'Megjelenés', 'primaria-porumbesti' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'porumbesti_brand_color',
			array(
				'label'     => __( 'Kiemelőszín', 'primaria-porumbesti' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--porumbesti-brand: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'porumbesti_title_color',
			array(
				'label'     => __( 'Címszín', 'primaria-porumbesti' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--porumbesti-ink: {{VALUE}};',
					'{{WRAPPER}} .porumbesti-title, {{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'porumbesti_text_color',
			array(
				'label'     => __( 'Szövegszín', 'primaria-porumbesti' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--porumbesti-muted: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'porumbesti_surface_color',
			array(
				'label'     => __( 'Háttérszín', 'primaria-porumbesti' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--porumbesti-surface: {{VALUE}};',
					'{{WRAPPER}} > .elementor-widget-container' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'porumbesti_radius',
			array(
				'label'      => __( 'Sarokkerekítés', 'primaria-porumbesti' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
				'selectors'  => array( '{{WRAPPER}}' => '--porumbesti-radius: {{SIZE}}{{UNIT}}; --porumbesti-radius-lg: calc({{SIZE}}{{UNIT}} + 8px);' ),
			)
		);

		$this->add_responsive_control(
			'porumbesti_widget_padding',
			array(
				'label'      => __( 'Belső térköz', 'primaria-porumbesti' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} > .elementor-widget-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'porumbesti_title_typography',
				'label'    => __( 'Cím tipográfia', 'primaria-porumbesti' ),
				'selector' => '{{WRAPPER}} .porumbesti-title, {{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3',
			)
		);

		$this->end_controls_section();
	}

	protected function common_heading_controls( string $default_kicker = '', string $default_title = '' ): void {
		$this->add_control( 'kicker', array( 'label' => __( 'Supratitlu', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => $default_kicker, 'label_block' => true ) );
		$this->add_control( 'title', array( 'label' => __( 'Titlu', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => $default_title, 'label_block' => true ) );
		$this->add_control( 'description', array( 'label' => __( 'Descriere', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
	}

	protected function render_heading( array $settings, string $level = 'h2' ): void {
		$level = in_array( $level, array( 'h1', 'h2', 'h3' ), true ) ? $level : 'h2';
		if ( ! empty( $settings['kicker'] ) ) {
			echo '<div class="porumbesti-kicker">' . esc_html( $settings['kicker'] ) . '</div>';
		}
		if ( ! empty( $settings['title'] ) ) {
			echo '<' . esc_attr( $level ) . ' class="porumbesti-title">' . esc_html( $settings['title'] ) . '</' . esc_attr( $level ) . '>';
		}
		if ( ! empty( $settings['description'] ) ) {
			echo '<p class="porumbesti-lead">' . wp_kses_post( nl2br( $settings['description'] ) ) . '</p>';
		}
	}

	protected static function link_attrs( array $link ): string {
		if ( empty( $link['url'] ) ) {
			return '';
		}
		$attrs = ' href="' . esc_url( $link['url'] ) . '"';
		if ( ! empty( $link['is_external'] ) ) {
			$attrs .= ' target="_blank"';
		}
		$rel = array();
		if ( ! empty( $link['nofollow'] ) ) {
			$rel[] = 'nofollow';
		}
		if ( ! empty( $link['is_external'] ) ) {
			$rel[] = 'noopener';
		}
		if ( $rel ) {
			$attrs .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
		}
		return $attrs;
	}

	protected static function menus(): array {
		$options = array( '' => __( '— Selectați meniul —', 'primaria-porumbesti' ) );
		foreach ( wp_get_nav_menus() as $menu ) {
			$options[ (string) $menu->term_id ] = $menu->name;
		}
		return $options;
	}

	protected static function post_types(): array {
		$options = array();
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			$options[ $type->name ] = $type->labels->singular_name;
		}
		return $options;
	}
}
