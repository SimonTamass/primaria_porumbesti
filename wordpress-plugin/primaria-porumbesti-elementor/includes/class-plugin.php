<?php
namespace PrimariaPorumbesti;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	private static ?Plugin $instance = null;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		require_once PORUMBESTI_WIDGETS_PATH . 'includes/class-assets.php';
		require_once PORUMBESTI_WIDGETS_PATH . 'includes/class-legacy-content.php';
		require_once PORUMBESTI_WIDGETS_PATH . 'includes/class-widget-registry.php';
		require_once PORUMBESTI_WIDGETS_PATH . 'includes/class-elementor.php';
		require_once PORUMBESTI_WIDGETS_PATH . 'includes/class-frontend-templates.php';

		add_action( 'init', array( $this, 'register_document_type' ) );
		add_action( 'add_meta_boxes_porumbesti_document', array( $this, 'add_document_meta_box' ) );
		add_action( 'save_post_porumbesti_document', array( $this, 'save_document_meta' ) );
		add_action( 'wp_ajax_porumbesti_contact', array( $this, 'handle_contact' ) );
		add_action( 'wp_ajax_nopriv_porumbesti_contact', array( $this, 'handle_contact' ) );
		add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
		Assets::instance();
		Frontend_Templates::instance();

		if ( is_admin() ) {
			require_once PORUMBESTI_WIDGETS_PATH . 'includes/class-template-applier.php';
			new Template_Applier();
		}

		if ( did_action( 'elementor/loaded' ) ) {
			Elementor_Integration::instance();
		} else {
			add_action( 'elementor/loaded', array( Elementor_Integration::class, 'instance' ) );
		}
	}

	public static function activate(): void {
		self::instance()->register_document_type();
		flush_rewrite_rules();
	}

	public function register_document_type(): void {
		if ( ! apply_filters( 'porumbesti_enable_document_type', true ) ) {
			return;
		}

		register_post_type(
			'porumbesti_document',
			array(
				'labels' => array(
					'name'          => __( 'Documente', 'primaria-porumbesti' ),
					'singular_name' => __( 'Document', 'primaria-porumbesti' ),
					'add_new_item'  => __( 'Adaugă document', 'primaria-porumbesti' ),
					'edit_item'     => __( 'Editează documentul', 'primaria-porumbesti' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-media-document',
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'documente' ),
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
			)
		);

		register_taxonomy(
			'porumbesti_document_category',
			'porumbesti_document',
			array(
				'labels'            => array(
					'name'          => __( 'Categorii documente', 'primaria-porumbesti' ),
					'singular_name' => __( 'Categorie document', 'primaria-porumbesti' ),
				),
				'public'            => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'categorie-document' ),
			)
		);
	}

	public function add_document_meta_box(): void {
		add_meta_box(
			'porumbesti-document-file',
			__( 'Fișierul documentului', 'primaria-porumbesti' ),
			array( $this, 'render_document_meta_box' ),
			'porumbesti_document',
			'normal',
			'high'
		);
	}

	public function render_document_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'porumbesti_document_meta', 'porumbesti_document_nonce' );
		$file = get_post_meta( $post->ID, 'porumbesti_file_url', true );
		$icon = get_post_meta( $post->ID, 'porumbesti_document_icon', true ) ?: 'PDF';
		?>
		<p><label for="porumbesti-file-url"><strong><?php esc_html_e( 'URL fișier (PDF, DOCX etc.)', 'primaria-porumbesti' ); ?></strong></label></p>
		<input class="widefat" id="porumbesti-file-url" name="porumbesti_file_url" type="url" value="<?php echo esc_attr( $file ); ?>" placeholder="https://…/document.pdf">
		<p class="description"><?php esc_html_e( 'Încărcați fișierul în Biblioteca Media și copiați aici URL-ul. Dacă rămâne gol, cardul deschide pagina documentului.', 'primaria-porumbesti' ); ?></p>
		<p><label for="porumbesti-document-icon"><strong><?php esc_html_e( 'Marcaj scurt', 'primaria-porumbesti' ); ?></strong></label></p>
		<input id="porumbesti-document-icon" name="porumbesti_document_icon" type="text" maxlength="6" value="<?php echo esc_attr( $icon ); ?>" placeholder="PDF">
		<p class="description"><?php esc_html_e( 'Exemple: PDF, HCL, AN, PV, FIN.', 'primaria-porumbesti' ); ?></p>
		<?php
	}

	public function save_document_meta( int $post_id ): void {
		if ( ! isset( $_POST['porumbesti_document_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['porumbesti_document_nonce'] ) ), 'porumbesti_document_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, 'porumbesti_file_url', esc_url_raw( wp_unslash( $_POST['porumbesti_file_url'] ?? '' ) ) );
		update_post_meta( $post_id, 'porumbesti_document_icon', strtoupper( sanitize_text_field( wp_unslash( $_POST['porumbesti_document_icon'] ?? 'PDF' ) ) ) );
	}

	public function handle_contact(): void {
		check_ajax_referer( 'porumbesti-contact', 'nonce' );
		$language = sanitize_key( wp_unslash( $_POST['language'] ?? 'ro' ) );
		$is_hungarian = 'hu' === $language;
		$messages = array(
			'sent'    => $is_hungarian ? 'Az üzenetet elküldtük. Köszönjük!' : 'Mesajul a fost trimis. Vă mulțumim!',
			'wait'    => $is_hungarian ? 'Kérjük, várjon egy percet az újabb üzenet elküldése előtt.' : 'Așteptați un minut înainte de a trimite din nou.',
			'invalid' => $is_hungarian ? 'Kérjük, töltse ki helyesen az összes kötelező mezőt.' : 'Completați corect toate câmpurile obligatorii.',
			'failed'  => $is_hungarian ? 'Az üzenetet nem sikerült elküldeni. Kérjük, ellenőrizze az email-beállításokat.' : 'Mesajul nu a putut fi trimis. Verificați configurarea emailului.',
		);
		if ( ! empty( $_POST['website'] ) ) {
			wp_send_json_success( array( 'message' => $messages['sent'] ) );
		}

		$ip_key = 'porumbesti_contact_' . md5( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
		if ( get_transient( $ip_key ) ) {
			wp_send_json_error( array( 'message' => $messages['wait'] ), 429 );
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
		$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
		$to      = sanitize_email( wp_unslash( $_POST['recipient'] ?? '' ) );
		$to_hash = sanitize_text_field( wp_unslash( $_POST['recipient_hash'] ?? '' ) );

		if ( ! $name || ! is_email( $email ) || ! $subject || ! $message || ! is_email( $to ) || ! hash_equals( wp_hash( $to . '|porumbesti_contact' ), $to_hash ) ) {
			wp_send_json_error( array( 'message' => $messages['invalid'] ), 422 );
		}

		$body = sprintf( "Nume: %s\nEmail: %s\n\n%s", $name, $email, $message );
		$sent = wp_mail( $to, '[Comuna Porumbești] ' . $subject, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => $messages['failed'] ), 500 );
		}

		set_transient( $ip_key, 1, MINUTE_IN_SECONDS );
		wp_send_json_success( array( 'message' => $messages['sent'] ) );
	}

	public function dependency_notice(): void {
		if ( current_user_can( 'activate_plugins' ) && ! did_action( 'elementor/loaded' ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Comuna Porumbești Elementor Widgets necesită pluginul Elementor activ.', 'primaria-porumbesti' ) . '</p></div>';
		}
	}
}
