<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Search_Box extends Base {
	public function get_name(): string { return 'porumbesti-search-box'; }
	public function get_title(): string { return __( '24 · Kereső / keresési modal', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-search'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Kereső', 'primaria-porumbesti' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Cím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Căutare în portal' ) );
		$this->add_control( 'placeholder', array( 'label' => __( 'Helyőrző', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Căutați documente, anunțuri, servicii…' ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Gombfelirat', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Caută' ) );
		$this->add_control( 'close_label', array( 'label' => __( 'Bezárás címkéje', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Închide' ) );
		$this->add_control( 'language', array( 'label' => __( 'Nyelv', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( '' => 'Automatikus', 'ro' => 'Română', 'hu' => 'Magyar' ), 'default' => '' ) );
		$this->add_control( 'modal', array( 'label' => __( 'Modal módban', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes', 'description' => __( 'A Header keresőgombja ezt a modalt nyitja meg.', 'primaria-porumbesti' ) ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $modal = 'yes' === $s['modal']; $title_id = 'porumbesti-search-title-' . $this->get_id(); $input_id = 'porumbesti-search-input-' . $this->get_id(); ?><div class="porumbesti-search-widget <?php echo $modal ? 'is-modal' : ''; ?>" <?php echo $modal ? 'hidden' : ''; ?> data-porumbesti-search-modal><div class="porumbesti-search-dialog" role="<?php echo $modal ? 'dialog' : 'search'; ?>" aria-labelledby="<?php echo esc_attr( $title_id ); ?>" <?php echo $modal ? 'aria-modal="true"' : ''; ?>><?php if ( $modal ) : ?><button type="button" data-porumbesti-search-close aria-label="<?php echo esc_attr( $s['close_label'] ); ?>">×</button><?php endif; ?><h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $s['title'] ); ?></h2><form role="search" aria-label="<?php echo esc_attr( $s['title'] ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>"><?php if ( ! empty( $s['language'] ) ) : ?><input type="hidden" name="lang" value="<?php echo esc_attr( $s['language'] ); ?>"><?php endif; ?><label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $s['title'] ); ?></label><input id="<?php echo esc_attr( $input_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $s['placeholder'] ); ?>"><button class="porumbesti-button porumbesti-button-primary" type="submit"><?php echo esc_html( $s['button_text'] ); ?></button></form></div></div><?php }
}
