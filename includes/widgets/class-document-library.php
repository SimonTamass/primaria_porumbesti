<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Document_Library extends Base {
	public function get_name(): string { return 'porumbesti-document-library'; }
	public function get_title(): string { return __( '21 · Dinamikus dokumentumtár', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-library-open'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Dokumentumtár', 'primaria-porumbesti' ) ) );
		$this->common_heading_controls( 'Documente', 'Bibliotecă publică' );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Adatforrás', 'primaria-porumbesti' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'automatic'      => __( 'Automatikus: meglévő tartalom', 'primaria-porumbesti' ),
					'porumbesti_document' => __( 'Documente tartalomtípus', 'primaria-porumbesti' ),
					'legacy_posts'   => __( 'Meglévő bejegyzések és kategóriák', 'primaria-porumbesti' ),
				),
				'default' => 'automatic',
			)
		);
		$this->add_control( 'legacy_keywords', array( 'label' => __( 'Dokumentumkategória kulcsszavak', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'hotarari,anunt,convocator,proiect,buget,declaratii,document', 'condition' => array( 'source!' => 'porumbesti_document' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'Dokumentumok száma', 'primaria-porumbesti' ), 'type' => Controls_Manager::NUMBER, 'default' => 12, 'min' => 1, 'max' => 100 ) );
		$this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => array( '2' => '2', '3' => '3' ), 'default' => '3' ) );
		$this->add_control( 'show_filters', array( 'label' => __( 'Szűrők', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'show_search', array( 'label' => __( 'Gyorskereső', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$source = $s['source'] ?? 'automatic';
		if ( 'automatic' === $source ) {
			$counts = wp_count_posts( 'porumbesti_document' );
			$source = $counts && ! empty( $counts->publish ) ? 'porumbesti_document' : 'legacy_posts';
		}

		$args = array(
			'post_type'           => 'porumbesti_document' === $source ? 'porumbesti_document' : 'post',
			'posts_per_page'      => (int) $s['count'],
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
		);

		if ( 'legacy_posts' === $source ) {
			$category_ids = $this->legacy_category_ids( (string) ( $s['legacy_keywords'] ?? '' ) );
			if ( $category_ids ) {
				$args['category__in'] = $category_ids;
			}
		}

		$q = new WP_Query( $args );
		$terms = $this->query_terms( $q, $source );
		?><section class="porumbesti-document-library"><?php $this->render_heading( $s ); ?><div class="porumbesti-library-tools"><?php if ( 'yes' === $s['show_filters'] && $terms ) : ?><div class="porumbesti-filters"><button class="is-active" data-porumbesti-filter="all"><?php esc_html_e( 'Toate', 'primaria-porumbesti' ); ?></button><?php foreach ( $terms as $term ) : ?><button data-porumbesti-filter="<?php echo esc_attr( $term['slug'] ); ?>"><?php echo esc_html( $term['name'] ); ?></button><?php endforeach; ?></div><?php endif; ?><?php if ( 'yes' === $s['show_search'] ) : ?><label class="porumbesti-library-search"><span class="screen-reader-text"><?php esc_html_e( 'Caută document', 'primaria-porumbesti' ); ?></span><input type="search" data-porumbesti-doc-search placeholder="<?php esc_attr_e( 'Caută în documente…', 'primaria-porumbesti' ); ?>"></label><?php endif; ?></div><div class="porumbesti-grid porumbesti-grid-<?php echo esc_attr( $s['columns'] ); ?>" data-porumbesti-filter-items><?php
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$post_id = get_the_ID();
				$post_terms = 'porumbesti_document' === $source ? wp_get_post_terms( $post_id, 'porumbesti_document_category' ) : get_the_category( $post_id );
				$post_terms = is_wp_error( $post_terms ) ? array() : $post_terms;
				$slugs = wp_list_pluck( $post_terms, 'slug' );
				$file = 'porumbesti_document' === $source ? get_post_meta( $post_id, 'porumbesti_file_url', true ) : '';
				$url = $file ?: get_permalink( $post_id );
				$icon = 'porumbesti_document' === $source ? get_post_meta( $post_id, 'porumbesti_document_icon', true ) : $this->legacy_icon( get_the_title( $post_id ), $slugs );
				$icon = $icon ?: 'DOC';
				?><a class="porumbesti-doc-card" data-porumbesti-category="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>" data-porumbesti-title="<?php echo esc_attr( strtolower( get_the_title( $post_id ) ) ); ?>" href="<?php echo esc_url( $url ); ?>"><b><?php echo esc_html( $icon ); ?></b><span><strong><?php echo esc_html( get_the_title( $post_id ) ); ?></strong><small><?php echo esc_html( get_the_date( '', $post_id ) ); ?></small></span></a><?php
			}
		} else {
			echo '<div class="porumbesti-empty">' . esc_html__( 'Nu există încă documente publicate pentru această selecție.', 'primaria-porumbesti' ) . '</div>';
		}
		wp_reset_postdata(); ?></div><div class="porumbesti-no-results" hidden><?php esc_html_e( 'Nu am găsit documente.', 'primaria-porumbesti' ); ?></div></section><?php
	}

	private function legacy_category_ids( string $raw_keywords ): array {
		$keywords = array_values( array_filter( array_map( 'sanitize_title', explode( ',', $raw_keywords ) ) ) );
		if ( ! $keywords ) {
			return array();
		}

		$ids = array();
		$categories = get_categories( array( 'hide_empty' => true ) );
		foreach ( $categories as $category ) {
			$haystack = sanitize_title( $category->slug . '-' . $category->name );
			foreach ( $keywords as $keyword ) {
				if ( str_contains( $haystack, $keyword ) ) {
					$ids[] = (int) $category->term_id;
					break;
				}
			}
		}
		return array_values( array_unique( $ids ) );
	}

	private function query_terms( WP_Query $query, string $source ): array {
		$terms = array();
		foreach ( $query->posts as $post ) {
			$post_terms = 'porumbesti_document' === $source ? wp_get_post_terms( $post->ID, 'porumbesti_document_category' ) : get_the_category( $post->ID );
			if ( is_wp_error( $post_terms ) ) {
				continue;
			}
			foreach ( $post_terms as $term ) {
				$terms[ $term->slug ] = array( 'slug' => $term->slug, 'name' => $term->name );
			}
		}
		return array_values( $terms );
	}

	private function legacy_icon( string $title, array $slugs ): string {
		$value = sanitize_title( $title . '-' . implode( '-', $slugs ) );
		if ( str_contains( $value, 'hotarar' ) ) { return 'HCL'; }
		if ( str_contains( $value, 'anunt' ) ) { return 'AN'; }
		if ( str_contains( $value, 'buget' ) ) { return 'BUG'; }
		if ( str_contains( $value, 'convocator' ) || str_contains( $value, 'proiect' ) ) { return 'PH'; }
		if ( str_contains( $value, 'declarat' ) ) { return 'DEC'; }
		return 'DOC';
	}
}
