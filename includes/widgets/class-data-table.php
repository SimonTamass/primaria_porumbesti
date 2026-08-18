<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Data_Table extends Base {
	public function get_name(): string { return 'porumbesti-data-table'; }
	public function get_title(): string { return __( '17 · Adattábla / nyilvántartás', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-table'; }
	protected function register_controls(): void { $this->start_controls_section( 'table', array( 'label' => __( 'Táblázat', 'primaria-porumbesti' ) ) ); $this->add_control( 'caption', array( 'label' => __( 'Akadálymentes táblázatcím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Registru public' ) ); foreach ( array( 1, 2, 3, 4 ) as $i ) { $this->add_control( 'heading_' . $i, array( 'label' => sprintf( __( '%d. oszlop fejléce', 'primaria-porumbesti' ), $i ), 'type' => Controls_Manager::TEXT, 'default' => 1 === $i ? 'Denumire' : '' ) ); } $r = new Repeater(); foreach ( array( 1, 2, 3, 4 ) as $i ) { $r->add_control( 'cell_' . $i, array( 'label' => sprintf( __( '%d. oszlop', 'primaria-porumbesti' ), $i ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) ); } $r->add_control( 'url', array( 'label' => __( 'Sor linkje (opcionális)', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) ); $this->add_control( 'rows', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ cell_1 }}}', 'default' => array( array( 'cell_1' => 'Înregistrare publică', 'cell_2' => '2026', 'cell_3' => 'Publicat' ) ) ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $headings = array_filter( array( $s['heading_1'], $s['heading_2'], $s['heading_3'], $s['heading_4'] ), static fn( $v ) => '' !== $v ); $count = count( $headings ); ?><div class="porumbesti-table-wrap"><table class="porumbesti-table"><caption><?php echo esc_html( $s['caption'] ); ?></caption><thead><tr><?php foreach ( $headings as $heading ) : ?><th scope="col"><?php echo esc_html( $heading ); ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach ( $s['rows'] as $row ) : ?><tr><?php for ( $i = 1; $i <= $count; $i++ ) : ?><td><?php if ( 1 === $i && ! empty( $row['url']['url'] ) ) : ?><a <?php echo self::link_attrs( $row['url'] ); ?>><?php echo esc_html( $row[ 'cell_' . $i ] ); ?></a><?php else : echo esc_html( $row[ 'cell_' . $i ] ); endif; ?></td><?php endfor; ?></tr><?php endforeach; ?></tbody></table></div><?php }
}
