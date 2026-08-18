<?php
namespace PrimariaPorumbesti\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Contact_Details extends Base {
	public function get_name(): string { return 'porumbesti-contact-details'; }
	public function get_title(): string { return __( '13 · Kapcsolati adatok', 'primaria-porumbesti' ); }
	public function get_icon(): string { return 'eicon-map-pin'; }

	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Kapcsolat', 'primaria-porumbesti' ) ) );
		$this->common_heading_controls( 'Contact', 'Primăria Comunei Porumbești' );
		$this->add_control( 'address', array( 'label' => __( 'Cím', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Porumbești, sat Porumbești, nr. 17C, jud. Satu Mare, 447152' ) );
		$this->add_control( 'address_code', array( 'label' => __( 'Cím rövid felirata', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'LOC' ) );
		$this->add_control( 'phone', array( 'label' => __( 'Telefon', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => '0361 525 288' ) );
		$this->add_control( 'phone_secondary', array( 'label' => __( 'Másodlagos telefon', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => '0361 525 288' ) );
		$this->add_control( 'fax', array( 'label' => __( 'Fax', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => '0361 525 288' ) );
		$this->add_control( 'email', array( 'label' => __( 'Email', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'primar@primariaporumbesti.ro' ) );
		$this->add_control( 'hours', array( 'label' => __( 'Nyitvatartás', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'hours_code', array( 'label' => __( 'Nyitvatartás rövid felirata', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'ORĂ' ) );
		$this->add_control( 'map_embed', array( 'label' => __( 'Google Maps beágyazási URL', 'primaria-porumbesti' ), 'type' => Controls_Manager::URL, 'description' => __( 'A Google Maps „Térkép beágyazása” iframe src értéke.', 'primaria-porumbesti' ) ) );
		$this->add_control( 'map_title', array( 'label' => __( 'Térkép akadálymentes címe', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'Hartă' ) );
		$this->add_control( 'registration_label', array( 'label' => __( 'Institutional identifier label', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => 'CIF' ) );
		$this->add_control( 'registration_value', array( 'label' => __( 'Institutional identifier', 'primaria-porumbesti' ), 'type' => Controls_Manager::TEXT, 'default' => '17530869' ) );
		$this->end_controls_section();
		$this->register_common_style_controls();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();
		?>
		<div class="porumbesti-contact-layout">
			<div class="porumbesti-contact-card">
				<?php $this->render_heading( $s ); ?>
				<div class="porumbesti-detail-list">
					<div><b><?php echo esc_html( $s['address_code'] ); ?></b><span><?php echo nl2br( esc_html( $s['address'] ) ); ?></span></div>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', $s['phone'] ) ); ?>"><b>TEL</b><span><?php echo esc_html( $s['phone'] ); ?></span></a>
					<?php if ( ! empty( $s['phone_secondary'] ) ) : ?><a href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', $s['phone_secondary'] ) ); ?>"><b>TEL</b><span><?php echo esc_html( $s['phone_secondary'] ); ?></span></a><?php endif; ?>
					<?php if ( ! empty( $s['fax'] ) ) : ?><div><b>FAX</b><span><?php echo esc_html( $s['fax'] ); ?></span></div><?php endif; ?>
					<a href="mailto:<?php echo esc_attr( antispambot( $s['email'] ) ); ?>"><b>@</b><span><?php echo esc_html( antispambot( $s['email'] ) ); ?></span></a>
					<?php if ( ! empty( $s['registration_value'] ) ) : ?><div><b><?php echo esc_html( $s['registration_label'] ); ?></b><span><?php echo esc_html( $s['registration_value'] ); ?></span></div><?php endif; ?>
					<?php if ( ! empty( $s['hours'] ) ) : ?><div><b><?php echo esc_html( $s['hours_code'] ); ?></b><span><?php echo nl2br( esc_html( $s['hours'] ) ); ?></span></div><?php endif; ?>
				</div>
			</div>
			<?php if ( ! empty( $s['map_embed']['url'] ) ) : ?><iframe class="porumbesti-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?php echo esc_url( $s['map_embed']['url'] ); ?>" title="<?php echo esc_attr( $s['map_title'] ); ?>"></iframe><?php endif; ?>
		</div>
		<?php
	}
}
