<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Header_Menu_Walker extends \Walker_Nav_Menu {
	private string $submenu_label;

	public function __construct( string $submenu_label = 'Deschide submeniul pentru %s' ) {
		$this->submenu_label = $submenu_label;
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ): void {
		parent::start_el( $output, $data_object, $depth, $args, $current_object_id );

		if ( ! in_array( 'menu-item-has-children', (array) $data_object->classes, true ) ) {
			return;
		}

		$title = wp_strip_all_tags( $data_object->title );
		$label = str_contains( $this->submenu_label, '%s' )
			? str_replace( '%s', $title, $this->submenu_label )
			: trim( $this->submenu_label . ' ' . $title );
		$icon = 0 === (int) $depth ? 'dashicons-arrow-down-alt2' : 'dashicons-arrow-right-alt2';
		$output .= '<button class="porumbesti-submenu-toggle" type="button" data-porumbesti-submenu-toggle aria-expanded="false" aria-label="' . esc_attr( $label ) . '" title="' . esc_attr( $label ) . '"><span class="dashicons ' . esc_attr( $icon ) . '" aria-hidden="true"></span></button>';
	}
}

final class Site_Header extends Base {
	public function get_name(): string { return 'porumbesti-site-header'; }
	public function get_title(): string { return __( '01 · Header complet', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-header'; }

	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Identitate și navigație', 'primaria-porumbesti' ) ) );
		$this->add_control( 'official_text', array( 'label' => __( 'Text bară oficială', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Site oficial al Primăriei Comunei Porumbești, județul Satu Mare, România', 'label_block' => true ) );
		$this->add_control( 'trust_text', array( 'label' => __( 'Insignă încredere', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Conexiune securizată' ) );
		$this->add_control( 'mail_url', array( 'label' => __( 'Link Mail', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL, 'placeholder' => 'https://' ) );
		$this->add_control( 'logo', array( 'label' => __( 'Logo', 'primaria-porumbesti' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_group_control( Group_Control_Image_Size::get_type(), array( 'name' => 'logo', 'default' => 'thumbnail' ) );
		$this->add_control( 'brand_title', array( 'label' => __( 'Nume instituție', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Comuna Porumbești' ) );
		$this->add_control( 'brand_subtitle', array( 'label' => __( 'Subtitlu', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Primăria · Kökényesd Község' ) );
		$this->add_control( 'home_url', array( 'label' => __( 'Link logo', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/' ) ) );
		$this->add_control( 'menu_id', array( 'label' => __( 'Meniu WordPress', 'primaria-porumbesti' ), 'type' => Controls_Manager::SELECT, 'options' => self::menus() ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Text buton', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Monitorul Oficial' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Link buton', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'porumbesti_sticky', array( 'label' => __( 'Header fix la derulare', 'primaria-porumbesti' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'interface_labels', array( 'label' => __( 'Etichete interfață', 'primaria-porumbesti' ) ) );
		$this->add_control( 'skip_label', array( 'label' => __( 'Salt la conținut', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Sari la conținut' ) );
		$this->add_control( 'language_label', array( 'label' => __( 'Selector limbă', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Alege limba' ) );
		$this->add_control( 'nav_label', array( 'label' => __( 'Navigație', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Navigație principală' ) );
		$this->add_control( 'search_label', array( 'label' => __( 'Căutare', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Caută' ) );
		$this->add_control( 'menu_open_label', array( 'label' => __( 'Deschidere meniu', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Deschide meniul' ) );
		$this->add_control( 'menu_close_label', array( 'label' => __( 'Închidere meniu', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Închide meniul' ) );
		$this->add_control( 'submenu_label', array( 'label' => __( 'Deschidere submeniu', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Deschide submeniul pentru %s', 'description' => __( 'Păstrați %s pentru numele elementului părinte.', 'primaria-porumbesti' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'languages', array( 'label' => __( 'Limbi', 'primaria-porumbesti' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'code', array( 'label' => __( 'Cod', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'RO' ) );
		$rep->add_control( 'label', array( 'label' => __( 'Denumire', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Română' ) );
		$rep->add_control( 'url', array( 'label' => __( 'Link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'language_items', array( 'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(), 'title_field' => '{{{ code }}} · {{{ label }}}', 'default' => array( array( 'code' => 'RO', 'label' => 'Română' ), array( 'code' => 'HU', 'label' => 'Magyar' ) ) ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();
		$current = $s['language_items'][0] ?? array( 'code' => 'RO', 'label' => 'Română' );
		$current_code = strtolower( sanitize_key( $current['code'] ?? 'ro' ) );
		$current_flag = in_array( $current_code, array( 'ro', 'hu' ), true ) ? $current_code : 'ro';
		$nav_id = 'porumbesti-main-nav-' . $this->get_id();
		$lang_id = 'porumbesti-lang-menu-' . $this->get_id();
		?>
		<div id="top" class="porumbesti-header-wrap <?php echo 'yes' === $s['porumbesti_sticky'] ? 'is-sticky' : ''; ?>">
			<a class="porumbesti-skip-link" href="#main-content"><?php echo esc_html( $s['skip_label'] ); ?></a>
			<div class="porumbesti-govbar"><div class="porumbesti-shell porumbesti-govbar-inner">
				<div class="porumbesti-official"><span class="porumbesti-flag porumbesti-flag-<?php echo esc_attr( $current_flag ); ?>" aria-hidden="true"><i></i><i></i><i></i></span><span><?php echo esc_html( $s['official_text'] ); ?></span><?php if ( $s['trust_text'] ) : ?><span class="porumbesti-trust"><?php echo esc_html( $s['trust_text'] ); ?></span><?php endif; ?></div>
				<div class="porumbesti-gov-actions"><?php if ( ! empty( $s['mail_url']['url'] ) ) : ?><a <?php echo self::link_attrs( $s['mail_url'] ); ?>>Mail</a><?php endif; ?>
					<div class="porumbesti-lang"><button type="button" class="porumbesti-lang-trigger" aria-expanded="false" aria-controls="<?php echo esc_attr( $lang_id ); ?>" aria-label="<?php echo esc_attr( $s['language_label'] ); ?>"><span class="porumbesti-flag porumbesti-flag-<?php echo esc_attr( $current_flag ); ?>" aria-hidden="true"><i></i><i></i><i></i></span><?php echo esc_html( $current['code'] ); ?><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
						<div id="<?php echo esc_attr( $lang_id ); ?>" class="porumbesti-lang-menu"><?php foreach ( $s['language_items'] as $item ) : ?><?php $flag = strtolower( sanitize_key( $item['code'] ?? 'ro' ) ); $item_language = in_array( $flag, array( 'ro', 'hu' ), true ) ? $flag : 'ro'; ?><a <?php echo self::link_attrs( $item['url'] ); ?> lang="<?php echo esc_attr( $item_language ); ?>" hreflang="<?php echo esc_attr( $item_language ); ?>"<?php echo $item_language === $current_flag ? ' aria-current="page"' : ''; ?>><span class="porumbesti-flag porumbesti-flag-<?php echo esc_attr( $item_language ); ?>" aria-hidden="true"><i></i><i></i><i></i></span><strong><?php echo esc_html( $item['code'] ); ?></strong><?php echo esc_html( $item['label'] ); ?></a><?php endforeach; ?></div>
					</div>
				</div>
			</div></div>
			<header class="porumbesti-site-header"><div class="porumbesti-shell porumbesti-header-inner">
				<a class="porumbesti-brand" <?php echo self::link_attrs( $s['home_url'] ); ?>>
					<?php if ( ! empty( $s['logo']['id'] ) || ! empty( $s['logo']['url'] ) ) : ?><span class="porumbesti-brand-logo"><?php if ( ! empty( $s['logo']['id'] ) ) { echo Group_Control_Image_Size::get_attachment_image_html( $s, 'logo', 'logo' ); } else { ?><img src="<?php echo esc_url( $s['logo']['url'] ); ?>" alt="" width="52" height="52"><?php } ?></span><?php else : ?><span class="porumbesti-brand-logo"><img src="<?php echo esc_url( PORUMBESTI_WIDGETS_URL . 'assets/images/porumbesti-monogram.svg' ); ?>" alt="" width="52" height="52"></span><?php endif; ?>
					<span><strong><?php echo esc_html( $s['brand_title'] ); ?></strong><small><?php echo esc_html( $s['brand_subtitle'] ); ?></small></span>
				</a>
				<nav id="<?php echo esc_attr( $nav_id ); ?>" class="porumbesti-main-nav" aria-label="<?php echo esc_attr( $s['nav_label'] ); ?>">
				<?php
				if ( $s['menu_id'] ) {
					wp_nav_menu( array( 'menu' => (int) $s['menu_id'], 'container' => false, 'menu_class' => 'porumbesti-menu', 'fallback_cb' => false, 'depth' => 4, 'walker' => new Header_Menu_Walker( $s['submenu_label'] ) ) );
				} elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					echo '<div class="porumbesti-editor-note">' . esc_html__( 'Selectați un meniu WordPress în panoul din stânga.', 'primaria-porumbesti' ) . '</div>';
				}
				?>
				</nav>
				<div class="porumbesti-header-actions"><button class="porumbesti-icon-button" type="button" data-porumbesti-search aria-label="<?php echo esc_attr( $s['search_label'] ); ?>" title="<?php echo esc_attr( $s['search_label'] ); ?>"><span class="dashicons dashicons-search" aria-hidden="true"></span></button><?php if ( $s['cta_text'] ) : ?><a class="porumbesti-button porumbesti-button-primary" <?php echo self::link_attrs( $s['cta_link'] ); ?>><?php echo esc_html( $s['cta_text'] ); ?></a><?php endif; ?><button class="porumbesti-icon-button porumbesti-nav-toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $nav_id ); ?>" aria-label="<?php echo esc_attr( $s['menu_open_label'] ); ?>" title="<?php echo esc_attr( $s['menu_open_label'] ); ?>" data-label-open="<?php echo esc_attr( $s['menu_open_label'] ); ?>" data-label-close="<?php echo esc_attr( $s['menu_close_label'] ); ?>"><span class="dashicons dashicons-menu-alt3" aria-hidden="true"></span></button></div>
			</div></header>
		</div>
		<?php
	}
}
