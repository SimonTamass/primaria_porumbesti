<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Services_Grid extends Base {
	public function get_name(): string { return 'porumbesti-services-grid'; }
	public function get_title(): string { return __( '07 · Szolgáltatások rács', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-gallery-grid'; }
	protected function register_controls(): void { $this->start_controls_section( 'items', array( 'label' => __( 'Szolgáltatások', 'primaria-porumbesti' ) ) ); $r = new Repeater(); $r->add_control( 'icon', array( 'label' => __( 'Rövid jel', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'TAX' ) ); $r->add_control( 'image', array( 'label' => __( 'Kép', 'primaria-porumbesti' ), 'type' => Controls_Manager::MEDIA ) ); $r->add_control( 'title', array( 'label' => __( 'Cím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Taxe și impozite' ) ); $r->add_control( 'description', array( 'label' => __( 'Leírás', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Informații și servicii pentru cetățeni.' ) ); $r->add_control( 'url', array( 'label' => __( 'Link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) ); $this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ icon }}} · {{{ title }}}', 'default' => array( array( 'icon' => 'TAX', 'title' => 'Taxe și impozite' ), array( 'icon' => 'PDF', 'title' => 'Formulare tipizate' ), array( 'icon' => 'URB', 'title' => 'Urbanism' ), array( 'icon' => 'AGR', 'title' => 'Registru agricol' ) ) ) ); $this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( '2' => '2', '3' => '3', '4' => '4' ), 'default' => '4' ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	private function service_icon( string $icon ): string {
		$key = preg_replace( '/[^A-Z0-9]/', '', strtoupper( remove_accents( $icon ) ) );
		$paths = '<path d="M4 20V8l8-5 8 5v12M2 20h20M8 20v-7h8v7"></path>';
		if ( in_array( $key, array( 'MOL', 'HK', 'PDF', 'EXE', 'DEL', 'REG', 'STAT', 'ALTE' ), true ) ) {
			$paths = '<path d="M6 3h8l4 4v14H6z"></path><path d="M14 3v5h5M9 13h6M9 17h6"></path>';
		} elseif ( 'TEL' === $key ) {
			$paths = '<path d="M7 3h3l1.5 4-2 1.5a15 15 0 0 0 6 6l1.5-2L21 14v3c0 2.2-1.8 4-4 4C9.3 21 3 14.7 3 7c0-2.2 1.8-4 4-4z"></path>';
		} elseif ( in_array( $key, array( 'LEG', 'JOG' ), true ) ) {
			$paths = '<path d="M4 5.5A3.5 3.5 0 0 1 7.5 2H12v18H7.5A3.5 3.5 0 0 0 4 23zM20 5.5A3.5 3.5 0 0 0 16.5 2H12v18h4.5A3.5 3.5 0 0 1 20 23z"></path>';
		} elseif ( in_array( $key, array( 'AN', 'FH' ), true ) ) {
			$paths = '<path d="m4 13 3 1 9 5V5L7 10H4zM7 14v5h3l1-3"></path><path d="M19 8v8"></path>';
		} elseif ( in_array( $key, array( 'PO', 'PORT' ), true ) ) {
			$paths = '<rect x="4" y="4" width="16" height="12" rx="2"></rect><path d="M2 20h20M9 20l1-4h4l1 4"></path>';
		} elseif ( in_array( $key, array( 'CL', 'HT' ), true ) ) {
			$paths = '<circle cx="8" cy="8" r="3"></circle><circle cx="17" cy="9" r="2.5"></circle><path d="M2.5 20c.4-4 2.2-6 5.5-6s5.1 2 5.5 6M14 15c3.6-.7 6.2 1 7 5"></path>';
		} elseif ( in_array( $key, array( 'FOT', 'FOTO', 'GF' ), true ) ) {
			$paths = '<rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m4 17 5-4 3 3 3-2 5 4"></path>';
		} elseif ( in_array( $key, array( 'TAX', 'ADO' ), true ) ) {
			$paths = '<path d="M3 7h18v13H3zM3 10h18M16 15h2"></path><path d="M6 7V4h12v3"></path>';
		} elseif ( in_array( $key, array( 'AGR', 'MG' ), true ) ) {
			$paths = '<path d="M20 4C10 4 5 9 5 19c10 0 15-5 15-15z"></path><path d="M5 19c3-5 7-8 12-11"></path>';
		} elseif ( in_array( $key, array( 'SOC', 'SZOC' ), true ) ) {
			$paths = '<path d="M12 21S3 15.5 3 9.5A4.5 4.5 0 0 1 12 8a4.5 4.5 0 0 1 9 1.5C21 15.5 12 21 12 21z"></path>';
		} elseif ( in_array( $key, array( 'FIN', 'PENZ' ), true ) ) {
			$paths = '<path d="M4 20V10h4v10M10 20V4h4v16M16 20v-7h4v7M2 20h20"></path>';
		}
		return '<svg class="porumbesti-service-svg" viewBox="0 0 24 24" aria-hidden="true">' . $paths . '</svg>';
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><div class="porumbesti-grid porumbesti-grid-<?php echo esc_attr( $s['columns'] ); ?>"><?php foreach ( $s['items_list'] as $item ) : $image = (string) ( $item['image']['url'] ?? '' ); ?><a class="porumbesti-service-card<?php echo $image ? ' has-image' : ''; ?>" <?php echo self::link_attrs( $item['url'] ); ?>><?php if ( $image ) : ?><span class="porumbesti-service-image"><img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy"></span><?php else : ?><span class="porumbesti-icon-box" aria-hidden="true"><?php echo $this->service_icon( (string) $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php endif; ?><h3><?php echo esc_html( $item['title'] ); ?></h3><?php if ( $item['description'] ) : ?><p><?php echo esc_html( $item['description'] ); ?></p><?php endif; ?><i aria-hidden="true">→</i></a><?php endforeach; ?></div><?php }
}
