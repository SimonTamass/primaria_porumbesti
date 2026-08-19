<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class News_Grid extends Base {
	public function get_name(): string { return 'porumbesti-news-grid'; }
	public function get_title(): string { return __( '19 · Dinamikus hírek', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-posts-grid'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'query', array( 'label' => __( 'Lekérdezés', 'primaria-porumbesti' ) ) );
		$this->add_control( 'post_type', array( 'label' => __( 'Tartalomtípus', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => self::post_types(), 'default' => 'post' ) );
		$this->add_control( 'category', array( 'label' => __( 'Kategória slug', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'description' => __( 'Üresen hagyva minden kategória.', 'primaria-porumbesti' ) ) );
		$this->add_control( 'language', array( 'label' => __( 'Nyelv', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( '' => 'Automatikus', 'ro' => 'Română', 'hu' => 'Magyar' ), 'default' => '' ) );
		$this->add_control( 'count', array( 'label' => __( 'Elemek száma', 'primaria-porumbesti' ), 'type' => Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 200 ) );
		$this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( '2' => '2', '3' => '3', '4' => '4' ), 'default' => '3' ) );
		$this->add_control( 'orderby', array( 'label' => __( 'Rendezés', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'date' => 'Dátum', 'title' => 'Cím', 'modified' => 'Módosítás', 'menu_order' => 'Menüsorrend' ), 'default' => 'date' ) );
		$this->add_control( 'show_excerpt', array( 'label' => __( 'Kivonat', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'show_category', array( 'label' => __( 'Kategória', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'show_date', array( 'label' => __( 'Dátum', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'empty_text', array( 'label' => __( 'Üres állapot', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Nu există articole publicate pentru această selecție.' ) );
		$this->add_control( 'read_more_text', array( 'label' => __( 'Tovább felirat', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Citește mai mult →' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$show_category = 'yes' === ( $s['show_category'] ?? 'yes' );
		$show_date = 'yes' === ( $s['show_date'] ?? 'yes' );
		$args = array( 'post_type' => $s['post_type'], 'posts_per_page' => (int) $s['count'], 'post_status' => 'publish', 'orderby' => $s['orderby'], 'order' => 'DESC', 'ignore_sticky_posts' => true );
		if ( ! empty( $s['language'] ) ) { $args['lang'] = sanitize_key( $s['language'] ); }
		if ( $s['category'] && 'post' === $s['post_type'] ) {
			$slugs = array_filter( array_map( 'sanitize_title', preg_split( '/\s*,\s*/', $s['category'] ) ?: array() ) );
			$term_ids = array();
			foreach ( $slugs as $slug ) {
				$term = get_category_by_slug( $slug );
				if ( ! $term ) {
					continue;
				}
				$term_ids[] = (int) $term->term_id;
				$children = get_term_children( (int) $term->term_id, 'category' );
				if ( ! is_wp_error( $children ) ) {
					$term_ids = array_merge( $term_ids, array_map( 'intval', $children ) );
				}
			}
			$args['category__in'] = $term_ids ? array_values( array_unique( $term_ids ) ) : array( 0 );
		}
		$q = new WP_Query( $args );
		if ( ! $q->have_posts() ) { echo '<div class="porumbesti-empty">' . esc_html( $s['empty_text'] ) . '</div>'; return; }
		echo '<div class="porumbesti-grid porumbesti-grid-' . esc_attr( $s['columns'] ) . '">';
		while ( $q->have_posts() ) { $q->the_post(); $cats = get_the_category(); ?>
			<article class="porumbesti-news-card"><a href="<?php the_permalink(); ?>"><div class="porumbesti-news-image"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); } else { echo '<span>P</span>'; } ?></div><div class="porumbesti-news-body"><?php if ( $show_category || $show_date ) : ?><div class="porumbesti-meta"><?php if ( $show_category ) : ?><span><?php echo esc_html( $cats[0]->name ?? get_post_type_object( get_post_type() )->labels->singular_name ); ?></span><?php endif; ?><?php if ( $show_date ) : ?><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><?php endif; ?></div><?php endif; ?><h3><?php the_title(); ?></h3><?php if ( 'yes' === $s['show_excerpt'] ) : ?><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p><?php endif; ?><strong class="porumbesti-read-more"><?php echo esc_html( $s['read_more_text'] ); ?></strong></div></a></article>
		<?php }
		echo '</div>'; wp_reset_postdata();
	}
}
