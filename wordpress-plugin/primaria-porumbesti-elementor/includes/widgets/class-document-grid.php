<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Document_Grid extends Base {
	public function get_name(): string { return 'porumbesti-document-grid'; }
	public function get_title(): string { return __( '20 · Kézi dokumentumkártyák', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-document-file'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'items', array( 'label' => __( 'Dokumentumok', 'primaria-porumbesti' ) ) );
		$r = new Repeater();
		$r->add_control( 'icon', array( 'label' => __( 'Jel', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'HCL' ) );
		$r->add_control( 'title', array( 'label' => __( 'Cím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Hotărârea Consiliului Local' ) );
		$r->add_control( 'meta', array( 'label' => __( 'Dátum / leírás', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Publicat recent' ) );
		$r->add_control( 'category', array( 'label' => __( 'Szűrőkategória', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Hotărâri' ) );
		$r->add_control( 'url', array( 'label' => __( 'Fájl vagy oldal', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ icon }}} · {{{ title }}}', 'default' => array( array( 'icon' => 'HCL', 'title' => 'H.C.L. nr. 13–16 / 2026', 'meta' => '18 mai 2026' ), array( 'icon' => 'PDF', 'title' => 'Document public', 'meta' => 'Publicat recent' ) ) ) );
		$this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( '1' => '1', '2' => '2', '3' => '3' ), 'default' => '3' ) );
		$this->add_control( 'filters', array( 'label' => __( 'Kategóriaszűrők', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes' ) );
		$this->add_control( 'all_label', array( 'label' => __( 'Összes szűrő felirata', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Toate' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $cats = array_unique( array_filter( array_column( $s['items_list'], 'category' ) ) ); ?><div class="porumbesti-document-widget"><?php if ( 'yes' === $s['filters'] && $cats ) : ?><div class="porumbesti-filters"><button class="is-active" data-porumbesti-filter="all"><?php echo esc_html( $s['all_label'] ); ?></button><?php foreach ( $cats as $cat ) : ?><button data-porumbesti-filter="<?php echo esc_attr( sanitize_title( $cat ) ); ?>"><?php echo esc_html( $cat ); ?></button><?php endforeach; ?></div><?php endif; ?><div class="porumbesti-grid porumbesti-grid-<?php echo esc_attr( $s['columns'] ); ?>" data-porumbesti-filter-items><?php foreach ( $s['items_list'] as $item ) : ?><a class="porumbesti-doc-card" data-porumbesti-category="<?php echo esc_attr( sanitize_title( $item['category'] ) ); ?>" <?php echo self::link_attrs( $item['url'] ); ?>><b><?php echo esc_html( $item['icon'] ); ?></b><span><strong><?php echo esc_html( $item['title'] ); ?></strong><small><?php echo esc_html( $item['meta'] ); ?></small></span></a><?php endforeach; ?></div></div><?php }
}
