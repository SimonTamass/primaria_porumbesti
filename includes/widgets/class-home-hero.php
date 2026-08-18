<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Home_Hero extends Base {
	public function get_name(): string { return 'porumbesti-home-hero'; }
	public function get_title(): string { return __( '04 · Hero nyitóoldal', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-slider-push'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Tartalom', 'primaria-porumbesti' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Állapot', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Informații oficiale pentru cetățeni', 'label_block' => true ) );
		$this->add_control( 'title', array( 'label' => __( 'Főcím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Servicii publice transparente pentru Comuna Porumbești.' ) );
		$this->add_control( 'description', array( 'label' => __( 'Leírás', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Portal modern pentru documente, formulare, hotărâri, anunțuri oficiale și informații utile pentru cetățeni.' ) );
		$this->add_control( 'background', array( 'label' => __( 'Háttérkép', 'primaria-porumbesti' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_control( 'primary_text', array( 'label' => __( 'Elsődleges gomb', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Vezi documentele' ) );
		$this->add_control( 'primary_link', array( 'label' => __( 'Elsődleges link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'secondary_text', array( 'label' => __( 'Másodlagos gomb', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Contact rapid' ) );
		$this->add_control( 'secondary_link', array( 'label' => __( 'Másodlagos link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'show_search', array( 'label' => __( 'Kereső megjelenítése', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'search_label', array( 'label' => __( 'Kereső címkéje', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Caută' ) );
		$this->add_control( 'search_placeholder', array( 'label' => __( 'Kereső helyőrzője', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Căutați formulare, hotărâri, anunțuri…' ) );
		$this->add_control( 'search_button', array( 'label' => __( 'Kereső gombja', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Caută' ) );
		$this->add_control( 'search_language', array( 'label' => __( 'Kereső nyelve', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( '' => 'Automatikus', 'ro' => 'Română', 'hu' => 'Magyar' ), 'default' => '' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'updates', array( 'label' => __( 'Kiemelt újdonságok', 'primaria-porumbesti' ) ) );
		$this->add_control( 'updates_title', array( 'label' => __( 'Panel címe', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Noutăți din portal' ) );
		$r = new Repeater();
		$r->add_control( 'day', array( 'label' => __( 'Nap / jel', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => '08' ) );
		$r->add_control( 'title', array( 'label' => __( 'Cím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Anunț important' ) );
		$r->add_control( 'meta', array( 'label' => __( 'Meta', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Publicat recent' ) );
		$r->add_control( 'url', array( 'label' => __( 'Link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'updates_items', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ day }}} · {{{ title }}}', 'default' => array( array( 'day' => '08', 'title' => 'ANUNȚ INDIVIDUAL', 'meta' => 'Publicat recent' ), array( 'day' => '18', 'title' => 'H.C.L. nr. 13–16 / 2026', 'meta' => 'Hotărâri publicate' ), array( 'day' => '24', 'title' => 'H.C.L. nr. 4–9 / 2026', 'meta' => 'Arhivă Consiliul Local' ) ) ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$has_actions = ! empty( $s['primary_text'] ) || ! empty( $s['secondary_text'] );
		$has_panel = ! empty( $s['updates_title'] ) || ! empty( $s['updates_items'] );
		$background = esc_url_raw( (string) ( $s['background']['url'] ?? '' ) );
		$style = $background ? '--porumbesti-hero-image:url("' . $background . '")' : '';
		?><section id="main-content" class="porumbesti-home-hero<?php echo $has_panel ? '' : ' is-simple'; ?>"<?php echo $style ? ' style="' . esc_attr( $style ) . '"' : ''; ?>><div class="porumbesti-shell porumbesti-hero-grid"><div><?php if ( $s['eyebrow'] ) : ?><div class="porumbesti-eyebrow"><i></i><?php echo esc_html( $s['eyebrow'] ); ?></div><?php endif; ?><h1><?php echo esc_html( $s['title'] ); ?></h1><?php if ( $s['description'] ) : ?><p><?php echo esc_html( $s['description'] ); ?></p><?php endif; ?><?php if ( $has_actions ) : ?><div class="porumbesti-actions"><?php if ( $s['primary_text'] ) : ?><a class="porumbesti-button porumbesti-button-primary" <?php echo self::link_attrs( $s['primary_link'] ); ?>><?php echo esc_html( $s['primary_text'] ); ?></a><?php endif; ?><?php if ( $s['secondary_text'] ) : ?><a class="porumbesti-button porumbesti-button-light" <?php echo self::link_attrs( $s['secondary_link'] ); ?>><?php echo esc_html( $s['secondary_text'] ); ?></a><?php endif; ?></div><?php endif; ?><?php if ( 'yes' === $s['show_search'] ) : ?><form class="porumbesti-hero-search" role="search" aria-label="<?php echo esc_attr( $s['search_label'] ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>"><?php if ( ! empty( $s['search_language'] ) ) : ?><input type="hidden" name="lang" value="<?php echo esc_attr( $s['search_language'] ); ?>"><?php endif; ?><label class="screen-reader-text" for="porumbesti-s-<?php echo esc_attr( $this->get_id() ); ?>"><?php echo esc_html( $s['search_label'] ); ?></label><input id="porumbesti-s-<?php echo esc_attr( $this->get_id() ); ?>" name="s" type="search" placeholder="<?php echo esc_attr( $s['search_placeholder'] ); ?>"><button><?php echo esc_html( $s['search_button'] ); ?></button></form><?php endif; ?></div><?php if ( $has_panel ) : ?><aside class="porumbesti-hero-panel"><?php if ( $s['updates_title'] ) : ?><h2><?php echo esc_html( $s['updates_title'] ); ?></h2><?php endif; ?><?php foreach ( $s['updates_items'] as $item ) : ?><a class="porumbesti-update" <?php echo self::link_attrs( $item['url'] ); ?>><b><?php echo esc_html( $item['day'] ); ?></b><span><strong><?php echo esc_html( $item['title'] ); ?></strong><small><?php echo esc_html( $item['meta'] ); ?></small></span></a><?php endforeach; ?></aside><?php endif; ?></div></section><?php
	}
}
