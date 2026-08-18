<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Page_Hero extends Base {
	public function get_name(): string { return 'porumbesti-page-hero'; }
	public function get_title(): string { return __( '05 · Hero belső oldal', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-banner'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Tartalom', 'primaria-porumbesti' ) ) );
		$this->common_heading_controls( '', 'Titlul paginii' );
		$this->add_control( 'background', array( 'label' => __( 'Háttérkép', 'primaria-porumbesti' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_control( 'parent_label', array( 'label' => __( 'Morzsa szülő', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Acasă' ) );
		$this->add_control( 'parent_link', array( 'label' => __( 'Morzsa link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/' ) ) );
		$this->add_control( 'current_label', array( 'label' => __( 'Aktuális oldal', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Pagina curentă' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $background = esc_url_raw( (string) ( $s['background']['url'] ?? '' ) ); $style = $background ? '--porumbesti-page-image:url("' . $background . '")' : ''; ?><section id="main-content" class="porumbesti-page-hero porumbesti-title-band"<?php echo $style ? ' style="' . esc_attr( $style ) . '"' : ''; ?>><div class="porumbesti-shell porumbesti-title-band-inner"><div class="porumbesti-breadcrumbs"><a <?php echo self::link_attrs( $s['parent_link'] ); ?>><?php echo esc_html( $s['parent_label'] ); ?></a><span>/</span><span><?php echo esc_html( $s['current_label'] ); ?></span></div><?php $this->render_heading( $s, 'h1' ); ?></div></section><?php }
}
