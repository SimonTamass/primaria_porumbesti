<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Site_Footer extends Base {
	public function get_name(): string { return 'porumbesti-site-footer'; }
	public function get_title(): string { return __( '02 · Footer complet', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-footer'; }

	protected function register_controls(): void {
		$this->start_controls_section( 'brand', array( 'label' => __( 'Instituție', 'primaria-porumbesti' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Titlu', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Comuna Porumbești' ) );
		$this->add_control( 'subtitle', array( 'label' => __( 'Subtitlu', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Primăria · Kökényesd Község' ) );
		$this->add_control( 'description', array( 'label' => __( 'Descriere', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Portal oficial pentru cetățeni, documente publice și comunicări administrative.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'columns', array( 'label' => __( 'Coloane de linkuri', 'primaria-porumbesti' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'column', array( 'label' => __( 'Coloană', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Primăria' ) );
		$rep->add_control( 'label', array( 'label' => __( 'Text', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Conducere' ) );
		$rep->add_control( 'url', array( 'label' => __( 'Link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'links', array( 'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(), 'title_field' => '{{{ column }}} · {{{ label }}}', 'default' => array( array( 'column' => 'Primăria', 'label' => 'Conducere' ), array( 'column' => 'Primăria', 'label' => 'Consiliul Local' ), array( 'column' => 'Informații publice', 'label' => 'Anunțuri' ), array( 'column' => 'Informații publice', 'label' => 'Monitorul Oficial' ) ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'external', array( 'label' => __( 'Hivatalos külső hivatkozások', 'primaria-porumbesti' ) ) );
		$this->add_control( 'external_title', array( 'label' => __( 'Oszlop címe', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Resurse oficiale' ) );
		$external = new Repeater();
		$external->add_control( 'label', array( 'label' => __( 'Text', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT ) );
		$external->add_control( 'url', array( 'label' => __( 'Link', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'external_links', array( 'type' => Controls_Manager::REPEATER, 'fields' => $external->get_controls(), 'title_field' => '{{{ label }}}' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'contact', array( 'label' => __( 'Contact', 'primaria-porumbesti' ) ) );
		$this->add_control( 'phone', array( 'label' => __( 'Telefon', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => '0361 525 288' ) );
		$this->add_control( 'email', array( 'label' => __( 'Email', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'primar@primariaporumbesti.ro' ) );
		$this->add_control( 'address', array( 'label' => __( 'Adresă', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'România, jud. Satu Mare, com. Porumbești, sat Porumbești, nr. 17C, cod 447152' ) );
		$this->add_control( 'copyright', array( 'label' => __( 'Copyright', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Toate drepturile rezervate Comuna Porumbești.' ) );
		$this->add_control( 'contact_url', array( 'label' => __( 'Link Contact', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'monitor_url', array( 'label' => __( 'Link Monitorul Oficial', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'contact_title', array( 'label' => __( 'Kapcsolati oszlop címe', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Contact' ) );
		$this->add_control( 'contact_link_text', array( 'label' => __( 'Kapcsolati link', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Contact' ) );
		$this->add_control( 'monitor_link_text', array( 'label' => __( 'Hivatalos közlöny link', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Monitorul Oficial' ) );
		$this->add_control( 'footer_nav_label', array( 'label' => __( 'Alsó navigáció címkéje', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Linkuri subsol' ) );
		$this->add_control( 'back_to_top_label', array( 'label' => __( 'Vissza az oldal tetejére', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Înapoi sus' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();
		$columns = array();
		foreach ( $s['links'] as $item ) { $columns[ $item['column'] ][] = $item; }
		$external_links = isset( $s['external_links'] ) && is_array( $s['external_links'] ) ? $s['external_links'] : array();
		$contact_url = ! empty( $s['contact_url']['url'] ) ? $s['contact_url'] : array( 'url' => home_url( '/ro/contact/' ) );
		$monitor_url = ! empty( $s['monitor_url']['url'] ) ? $s['monitor_url'] : array( 'url' => home_url( '/ro/monitorul-oficial-local/' ) );
		?>
		<footer class="porumbesti-footer"><div class="porumbesti-shell porumbesti-footer-grid">
			<div class="porumbesti-footer-brand"><div class="porumbesti-brand"><span class="porumbesti-brand-logo"><img src="<?php echo esc_url( PORUMBESTI_WIDGETS_URL . 'assets/images/porumbesti-monogram.svg' ); ?>" alt="" width="52" height="52"></span><span><strong><?php echo esc_html( $s['title'] ); ?></strong><small><?php echo esc_html( $s['subtitle'] ); ?></small></span></div><p><?php echo esc_html( $s['description'] ); ?></p></div>
			<?php foreach ( $columns as $title => $items ) : ?><div><h2><?php echo esc_html( $title ); ?></h2><div class="porumbesti-footer-links"><?php foreach ( $items as $item ) : ?><a <?php echo self::link_attrs( $item['url'] ); ?>><?php echo esc_html( $item['label'] ); ?></a><?php endforeach; ?></div></div><?php endforeach; ?>
			<div><h2><?php echo esc_html( $s['contact_title'] ); ?></h2><div class="porumbesti-footer-contact"><a href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', $s['phone'] ) ); ?>"><?php echo esc_html( $s['phone'] ); ?></a><a href="mailto:<?php echo esc_attr( antispambot( $s['email'] ) ); ?>"><?php echo esc_html( antispambot( $s['email'] ) ); ?></a><p><?php echo nl2br( esc_html( $s['address'] ) ); ?></p></div></div>
			<?php if ( $external_links ) : ?><div><h2><?php echo esc_html( $s['external_title'] ); ?></h2><div class="porumbesti-footer-links porumbesti-footer-external"><?php foreach ( $external_links as $item ) : ?><?php if ( ! empty( $item['label'] ) && ! empty( $item['url']['url'] ) ) : ?><a <?php echo self::link_attrs( $item['url'] ); ?>><?php echo esc_html( $item['label'] ); ?></a><?php endif; ?><?php endforeach; ?></div></div><?php endif; ?>
		</div><div class="porumbesti-shell porumbesti-footer-bottom"><span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $s['copyright'] ); ?></span><nav aria-label="<?php echo esc_attr( $s['footer_nav_label'] ); ?>"><a <?php echo self::link_attrs( $contact_url ); ?>><?php echo esc_html( $s['contact_link_text'] ); ?></a><span aria-hidden="true">·</span><a <?php echo self::link_attrs( $monitor_url ); ?>><?php echo esc_html( $s['monitor_link_text'] ); ?></a><a class="porumbesti-back-to-top" href="#top" aria-label="<?php echo esc_attr( $s['back_to_top_label'] ); ?>" title="<?php echo esc_attr( $s['back_to_top_label'] ); ?>"><span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span></a></nav></div></footer>
		<?php
	}
}
