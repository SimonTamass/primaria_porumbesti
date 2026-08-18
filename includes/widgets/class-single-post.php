<?php
namespace PrimariaPorumbesti\Widgets;

use PrimariaPorumbesti\Legacy_Content;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Single_Post extends Base {
	public function get_name(): string {
		return 'porumbesti-single-post';
	}

	public function get_title(): string {
		return __( '23 · Egyedi blogbejegyzés', 'primaria-porumbesti' );
	}

	public function get_icon(): string {
		return 'eicon-single-post';
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'layout', array( 'label' => __( 'Tartalom', 'primaria-porumbesti' ) ) );
		$this->add_control( 'show_image', array( 'label' => __( 'Kiemelt kép', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'show_author', array( 'label' => __( 'Szerző', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes' ) );
		$this->add_control( 'home_label', array( 'label' => __( 'Kezdőlap felirata', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Acasă' ) );
		$this->add_control( 'home_url', array( 'label' => __( 'Kezdőlap hivatkozása', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/' ) ) );
		$this->add_control( 'article_label', array( 'label' => __( 'Bejegyzés felirata', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Articol' ) );
		$this->end_controls_section();
		$this->register_common_style_controls();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			echo '<div class="porumbesti-empty">' . esc_html__( 'Acest widget se afișează într-un șablon pentru articol individual.', 'primaria-porumbesti' ) . '</div>';
			return;
		}

		$categories = get_the_category( $post_id );
		$content = Legacy_Content::normalize( (string) get_post_field( 'post_content', $post_id ) );
		$document_count = preg_match_all( '/href=["\'][^"\']+\.(?:csv|docx?|od[st]|pdf|pptx?|rtf|xlsx?|zip)(?:[?#][^"\']*)?["\']/i', $content );
		$article_class = 'porumbesti-single' . ( $document_count ? ' has-document-list' : '' );
		?>
		<article class="<?php echo esc_attr( $article_class ); ?>">
			<header class="porumbesti-title-band">
				<div class="porumbesti-title-band-inner">
				<div class="porumbesti-breadcrumbs">
					<a <?php echo self::link_attrs( $settings['home_url'] ); ?>><?php echo esc_html( $settings['home_label'] ); ?></a><span>/</span><span><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
				</div>
				<div class="porumbesti-kicker"><?php echo esc_html( $categories[0]->name ?? $settings['article_label'] ); ?></div>
				<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
				<div class="porumbesti-single-meta">
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></time>
					<?php if ( 'yes' === $settings['show_author'] ) : ?><span><?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ) ); ?></span><?php endif; ?>
					<?php if ( $categories ) : ?><a href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a><?php endif; ?>
				</div>
				</div>
			</header>
			<?php if ( 'yes' === $settings['show_image'] && has_post_thumbnail( $post_id ) ) : ?>
				<figure class="porumbesti-single-image"><?php echo get_the_post_thumbnail( $post_id, 'full' ); ?></figure>
			<?php endif; ?>
			<div class="porumbesti-single-layout">
				<div class="porumbesti-single-content"><?php echo apply_filters( 'the_content', $content ); ?></div>
			</div>
		</article>
		<?php
	}
}
