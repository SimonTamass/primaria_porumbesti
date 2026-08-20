<?php
namespace PrimariaPorumbesti;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Template_Applier {
	private const NONCE_ACTION = 'porumbesti_apply_template';
	private const NONCE_RESTORE = 'porumbesti_restore_template';
	private const NONCE_GROUP = 'porumbesti_apply_group';
	private const NONCE_ALL = 'porumbesti_apply_all';
	private const NONCE_ALL_HU = 'porumbesti_apply_all_hu';
	private const BACKUP_META = '_porumbesti_rebuild_backups';
	private const MANIFEST_META = '_porumbesti_rebuild_manifest';
	private const SOURCE_META = '_porumbesti_original_page_content';
	private const MAX_BACKUPS = 5;
	private const WEBMAIL_URL = 'https://server58.romania-webhosting.com:2096/';
	private const FACEBOOK_URL = 'https://www.facebook.com/KOKENYESD/';
	private const PORTAL_URL = 'https://portal.primariaporumbesti.ro/';
	private const TRANSPARENCY_URL = 'https://sgg.gov.ro/new/guvernare-transparenta-deschisa-si-participativa-standardizare-armonizare-dialog-imbunatatit-cod-sipoca-35/';
	private const MAP_URL = 'https://www.google.hu/maps/place/Prim%C4%83ria+Porumbe%C5%9Fti/@47.984662,22.9673973,16z/data=!4m5!3m4!1s0x473818293864b625:0xb9325b24df54cf20!8m2!3d47.9839429!4d22.9718739';
	private const MAP_EMBED_URL = 'https://www.google.com/maps?q=47.9839429,22.9718739&z=16&output=embed';
	private const ELEMENTOR_META = array(
		'_elementor_edit_mode',
		'_elementor_template_type',
		'_elementor_version',
		'_elementor_page_settings',
		'_wp_page_template',
		'_elementor_data',
		'_elementor_css',
	);
	private array $menu_id_cache = array();
	private array $routes_cache = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_post_porumbesti_apply_template', array( $this, 'handle_apply' ) );
		add_action( 'admin_post_porumbesti_restore_template', array( $this, 'handle_restore' ) );
		add_action( 'admin_post_porumbesti_apply_group', array( $this, 'handle_apply_group' ) );
		add_action( 'admin_post_porumbesti_apply_all', array( $this, 'handle_apply_all' ) );
		add_action( 'admin_post_porumbesti_apply_all_hu', array( $this, 'handle_apply_all_hu' ) );
	}

	public function register_admin_page(): void {
		add_management_page(
			__( 'Comuna Porumbești rebuild', 'primaria-porumbesti' ),
			__( 'Comuna Porumbești rebuild', 'primaria-porumbesti' ),
			'edit_pages',
			'porumbesti-rebuild',
			array( $this, 'render_admin_page' )
		);
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Nu aveți permisiunea necesară.', 'primaria-porumbesti' ) );
		}

		$page_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
		$status  = isset( $_GET['porumbesti_status'] ) ? sanitize_key( wp_unslash( $_GET['porumbesti_status'] ) ) : '';
		$error_code = isset( $_GET['porumbesti_error'] ) ? sanitize_key( wp_unslash( $_GET['porumbesti_error'] ) ) : '';
		$error_summary = isset( $_GET['porumbesti_error_summary'] ) ? sanitize_text_field( wp_unslash( $_GET['porumbesti_error_summary'] ) ) : '';
		$group_done = isset( $_GET['porumbesti_group'] ) ? sanitize_key( wp_unslash( $_GET['porumbesti_group'] ) ) : '';
		$group_ok = isset( $_GET['porumbesti_ok'] ) ? absint( $_GET['porumbesti_ok'] ) : 0;
		$group_failed = isset( $_GET['porumbesti_failed'] ) ? absint( $_GET['porumbesti_failed'] ) : 0;
		$all_done = isset( $_GET['porumbesti_all'] ) ? absint( $_GET['porumbesti_all'] ) : 0;
		$language_layer_ready = $this->language_layer_ready();
		$routes  = array( 'ro' => $this->routes( 'ro' ), 'hu' => $this->routes( 'hu' ) );
		$targets = $this->template_targets();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Comuna Porumbești rebuild', 'primaria-porumbesti' ); ?></h1>
			<?php if ( ! $language_layer_ready ) : ?>
				<div class="notice notice-error"><p><strong><?php esc_html_e( 'Reconstrucția este blocată.', 'primaria-porumbesti' ); ?></strong> <?php esc_html_e( 'Polylang trebuie să fie activ pentru identificarea sigură a paginilor române și maghiare și pentru păstrarea relațiilor de traducere. Az átépítéshez aktiválni kell a már telepített Polylang bővítményt.', 'primaria-porumbesti' ); ?></p></div>
			<?php endif; ?>
			<?php if ( 'applied' === $status ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Șablonul Elementor a fost aplicat fără schimbarea adresei paginii.', 'primaria-porumbesti' ); ?></p></div>
			<?php elseif ( 'restored' === $status ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Ultima copie de siguranță a paginii a fost restaurată.', 'primaria-porumbesti' ); ?></p></div>
			<?php elseif ( 'error' === $status ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'Operațiunea nu a putut fi finalizată. Pagina nu a fost modificată.', 'primaria-porumbesti' ); ?><?php if ( $error_code ) : ?> <code><?php echo esc_html( $error_code ); ?></code><?php endif; ?></p></div>
			<?php endif; ?>
			<?php if ( $group_done ) : ?><div class="notice <?php echo $group_failed ? 'notice-warning' : 'notice-success'; ?>"><p><?php echo esc_html( sprintf( __( 'Grupul %1$s: %2$d pagini reconstruite, %3$d erori. URL-urile au fost verificate individual.', 'primaria-porumbesti' ), $group_done, $group_ok, $group_failed ) ); ?></p></div><?php endif; ?>
			<?php if ( $all_done ) : ?><div class="notice <?php echo $group_failed ? 'notice-warning' : 'notice-success'; ?>"><p><?php echo esc_html( sprintf( 'Reconstrucție completă: %1$d pagini reconstruite, %2$d erori. Fiecare adresă URL a fost verificată.', $group_ok, $group_failed ) ); ?></p></div><?php endif; ?>
			<?php if ( $error_summary ) : ?><div class="notice notice-info"><p><?php echo esc_html( 'Coduri de eroare: ' . $error_summary ); ?></p></div><?php endif; ?>
			<p><?php esc_html_e( 'Instrumentul păstrează ID-ul, titlul, limba, părintele, slugul și adresa URL. Înainte de fiecare aplicare salvează automat starea curentă.', 'primaria-porumbesti' ); ?></p>
			<table class="widefat striped" style="max-width:980px">
				<thead><tr><th><?php esc_html_e( 'Șablon', 'primaria-porumbesti' ); ?></th><th><?php esc_html_e( 'Pagină țintă', 'primaria-porumbesti' ); ?></th><th><?php esc_html_e( 'Siguranță', 'primaria-porumbesti' ); ?></th><th><?php esc_html_e( 'Acțiune', 'primaria-porumbesti' ); ?></th></tr></thead>
				<tbody>
					<?php foreach ( $targets as $template => $target ) :
						$post = $target['post_id'] ? get_post( $target['post_id'] ) : null;
						$backups = $post ? $this->backups( (int) $post->ID ) : array();
						$manifest = $post ? get_post_meta( (int) $post->ID, self::MANIFEST_META, true ) : array();
						?>
					<tr<?php echo $page_id && $post && $page_id === (int) $post->ID ? ' style="box-shadow:inset 4px 0 #2271b1"' : ''; ?>>
						<th scope="row"><?php echo esc_html( $target['label'] ); ?></th>
						<td><?php if ( $post ) : ?><strong><?php echo esc_html( get_the_title( $post ) ); ?></strong><br><code><?php echo esc_html( get_permalink( $post ) ); ?></code><?php else : ?><span style="color:#b32d2e"><?php esc_html_e( 'Pagina nu a fost găsită automat.', 'primaria-porumbesti' ); ?></span><?php endif; ?></td>
						<td><?php echo esc_html( sprintf( __( '%d copii disponibile', 'primaria-porumbesti' ), count( $backups ) ) ); ?><?php if ( is_array( $manifest ) && ! empty( $manifest['applied_at'] ) ) : ?><br><small><?php echo esc_html( sprintf( __( 'Ultima aplicare: %s, versiunea %s', 'primaria-porumbesti' ), $manifest['applied_at'], $manifest['version'] ?? '' ) ); ?></small><?php endif; ?></td>
						<td>
							<?php if ( $language_layer_ready && $post && current_user_can( 'edit_post', $post->ID ) ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-right:8px">
									<input type="hidden" name="action" value="porumbesti_apply_template">
									<input type="hidden" name="template" value="<?php echo esc_attr( $template ); ?>">
									<input type="hidden" name="post_id" value="<?php echo (int) $post->ID; ?>">
									<?php wp_nonce_field( self::NONCE_ACTION . '_' . (int) $post->ID ); ?>
									<?php submit_button( $target['button'], 'primary', 'submit', false ); ?>
								</form>
								<?php if ( $backups ) : ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block">
										<input type="hidden" name="action" value="porumbesti_restore_template">
										<input type="hidden" name="post_id" value="<?php echo (int) $post->ID; ?>">
										<?php wp_nonce_field( self::NONCE_RESTORE . '_' . (int) $post->ID ); ?>
										<?php submit_button( __( 'Restaurează versiunea anterioară', 'primaria-porumbesti' ), 'secondary', 'submit', false ); ?>
									</form>
								<?php endif; ?>
							<?php else : ?><button class="button button-primary" disabled><?php echo esc_html( $target['button'] ); ?></button><?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<h2 style="margin-top:28px"><?php esc_html_e( 'Reconstrucție în grupuri', 'primaria-porumbesti' ); ?></h2>
			<p><?php esc_html_e( 'Sunt incluse separat paginile române și maghiare publicate. Paginile private și ciornele nu sunt modificate. Fiecare pagină primește propria copie de siguranță.', 'primaria-porumbesti' ); ?></p>
			<table class="widefat striped" style="max-width:980px"><thead><tr><th><?php esc_html_e( 'Grup', 'primaria-porumbesti' ); ?></th><th><?php esc_html_e( 'Pagini', 'primaria-porumbesti' ); ?></th><th><?php esc_html_e( 'Descriere', 'primaria-porumbesti' ); ?></th><th><?php esc_html_e( 'Acțiune', 'primaria-porumbesti' ); ?></th></tr></thead><tbody>
			<?php foreach ( $this->rebuild_groups() as $group => $definition ) : $group_pages = $this->group_pages( $group ); ?>
				<tr><th scope="row"><?php echo esc_html( $definition['label'] ); ?></th><td><?php echo (int) count( $group_pages ); ?></td><td><?php echo esc_html( $definition['description'] ); ?></td><td><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="porumbesti_apply_group"><input type="hidden" name="group" value="<?php echo esc_attr( $group ); ?>"><?php wp_nonce_field( self::NONCE_GROUP . '_' . $group ); ?><?php submit_button( sprintf( __( 'Reconstruiește grupul (%d)', 'primaria-porumbesti' ), count( $group_pages ) ), 'secondary', 'submit', false, $language_layer_ready && count( $group_pages ) ? array() : array( 'disabled' => 'disabled' ) ); ?></form></td></tr>
			<?php endforeach; ?>
			</tbody></table>
			<h2 style="margin-top:28px"><?php esc_html_e( 'Reconstrucție completă', 'primaria-porumbesti' ); ?></h2>
			<p><?php esc_html_e( 'Aplicați separat sistemul Elementor pe paginile române sau maghiare publicate, fără schimbarea adreselor.', 'primaria-porumbesti' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="porumbesti_apply_all"><?php wp_nonce_field( self::NONCE_ALL ); ?><?php submit_button( sprintf( __( 'Reconstruiește toate paginile române (%d)', 'primaria-porumbesti' ), count( $this->published_ro_pages() ) + count( array_filter( wp_list_pluck( $this->template_targets( 'ro' ), 'post_id' ) ) ) ), 'primary', 'submit', false, $language_layer_ready ? array() : array( 'disabled' => 'disabled' ) ); ?></form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:10px"><input type="hidden" name="action" value="porumbesti_apply_all_hu"><?php wp_nonce_field( self::NONCE_ALL_HU ); ?><?php submit_button( sprintf( __( 'Reconstruiește toate paginile maghiare (%d)', 'primaria-porumbesti' ), count( $this->published_hu_pages() ) + count( array_filter( wp_list_pluck( $this->template_targets( 'hu' ), 'post_id' ) ) ) ), 'primary', 'submit', false, $language_layer_ready ? array() : array( 'disabled' => 'disabled' ) ); ?></form>
			<h2 style="margin-top:28px"><?php esc_html_e( 'Rute folosite de șablon', 'primaria-porumbesti' ); ?></h2>
			<table class="widefat striped" style="max-width:820px"><tbody><?php foreach ( $routes as $language => $language_routes ) : ?><?php foreach ( $language_routes as $key => $route ) : ?><tr><th scope="row"><?php echo esc_html( strtoupper( $language ) . ' · ' . $key ); ?></th><td><code><?php echo esc_html( $route ); ?></code></td></tr><?php endforeach; ?><?php endforeach; ?></tbody></table>
		</div>
		<?php
	}

	public function handle_apply(): void {
		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$template = isset( $_POST['template'] ) ? sanitize_key( wp_unslash( $_POST['template'] ) ) : '';

		if ( ! $post_id || ! check_admin_referer( self::NONCE_ACTION . '_' . $post_id ) ) {
			wp_die( esc_html__( 'Cerere invalidă.', 'primaria-porumbesti' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Nu aveți permisiunea necesară.', 'primaria-porumbesti' ) );
		}
		$this->require_language_layer();

		$targets = $this->template_targets();
		if ( ! isset( $targets[ $template ] ) || $post_id !== (int) $targets[ $template ]['post_id'] ) {
			wp_safe_redirect( admin_url( 'tools.php?page=porumbesti-rebuild&post_id=' . $post_id . '&porumbesti_status=error' ) );
			exit;
		}

		$result     = $this->apply_template( $post_id, $template );
		$status     = is_wp_error( $result ) ? 'error' : 'applied';
		$error_code = is_wp_error( $result ) ? sanitize_key( $result->get_error_code() ) : '';

		wp_safe_redirect( admin_url( 'tools.php?page=porumbesti-rebuild&post_id=' . $post_id . '&porumbesti_status=' . $status . ( $error_code ? '&porumbesti_error=' . rawurlencode( $error_code ) : '' ) ) );
		exit;
	}

	public function handle_restore(): void {
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id || ! check_admin_referer( self::NONCE_RESTORE . '_' . $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Cerere invalidă.', 'primaria-porumbesti' ) );
		}

		$status = $this->restore_latest( $post_id ) ? 'restored' : 'error';
		wp_safe_redirect( admin_url( 'tools.php?page=porumbesti-rebuild&post_id=' . $post_id . '&porumbesti_status=' . $status ) );
		exit;
	}

	public function handle_apply_group(): void {
		$group = isset( $_POST['group'] ) ? sanitize_key( wp_unslash( $_POST['group'] ) ) : '';
		$groups = $this->rebuild_groups();
		if ( ! isset( $groups[ $group ] ) || ! check_admin_referer( self::NONCE_GROUP . '_' . $group ) || ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Cerere invalidă.', 'primaria-porumbesti' ) );
		}
		$this->require_language_layer();

		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 180 );
		}

		$ok = 0;
		$failed = 0;
		$language = str_starts_with( $group, 'hu_' ) ? 'hu' : 'ro';
		foreach ( $this->group_pages( $group ) as $page ) {
			if ( ! current_user_can( 'edit_post', $page->ID ) ) {
				++$failed;
				continue;
			}
			$type = $this->classify_page( $page );
			$result = $this->write_elementor_page( $page, 'page_' . $language . '_' . $type, $this->generic_page_data( $page, $type, $language ) );
			if ( is_wp_error( $result ) ) {
				++$failed;
			} else {
				++$ok;
			}
		}

		wp_safe_redirect( admin_url( 'tools.php?page=porumbesti-rebuild&porumbesti_group=' . rawurlencode( $group ) . '&porumbesti_ok=' . $ok . '&porumbesti_failed=' . $failed ) );
		exit;
	}

	public function handle_apply_all(): void {
		$this->handle_apply_all_language( 'ro', self::NONCE_ALL );
	}

	public function handle_apply_all_hu(): void {
		$this->handle_apply_all_language( 'hu', self::NONCE_ALL_HU );
	}

	private function handle_apply_all_language( string $language, string $nonce ): void {
		if ( ! check_admin_referer( $nonce ) || ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Cerere invalidă.', 'primaria-porumbesti' ) );
		}
		$this->require_language_layer();

		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 600 );
		}

		$ok = 0;
		$failed = 0;
		$error_codes = array();
		foreach ( $this->template_targets( $language ) as $template => $target ) {
			$post_id = (int) $target['post_id'];
			$result = $post_id && current_user_can( 'edit_post', $post_id ) ? $this->apply_template( $post_id, $template ) : new \WP_Error( 'permission_denied' );
			if ( is_wp_error( $result ) ) {
				++$failed;
				$code = $result->get_error_code();
				$error_codes[ $code ] = ( $error_codes[ $code ] ?? 0 ) + 1;
			} else {
				++$ok;
			}
		}

		foreach ( $this->published_pages( $language ) as $page ) {
			if ( ! current_user_can( 'edit_post', $page->ID ) ) {
				++$failed;
				$error_codes['permission_denied'] = ( $error_codes['permission_denied'] ?? 0 ) + 1;
				continue;
			}
			$type = $this->classify_page( $page );
			$result = $this->write_elementor_page( $page, 'page_' . $language . '_' . $type, $this->generic_page_data( $page, $type, $language ) );
			if ( is_wp_error( $result ) ) {
				++$failed;
				$code = $result->get_error_code();
				$error_codes[ $code ] = ( $error_codes[ $code ] ?? 0 ) + 1;
			} else {
				++$ok;
			}
		}

		$summary = implode( ', ', array_map( static fn( $code, $count ): string => $code . ':' . $count, array_keys( $error_codes ), $error_codes ) );
		wp_safe_redirect( admin_url( 'tools.php?page=porumbesti-rebuild&porumbesti_all=1&porumbesti_language=' . rawurlencode( $language ) . '&porumbesti_ok=' . $ok . '&porumbesti_failed=' . $failed . '&porumbesti_error_summary=' . rawurlencode( $summary ) ) );
		exit;
	}

	private function apply_template( int $post_id, string $template ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
			return new \WP_Error( 'invalid_page', __( 'Pagina țintă nu este validă.', 'primaria-porumbesti' ) );
		}

		$data = match ( $template ) {
			'home_ro'  => $this->home_ro_data( $post ),
			'mayor_ro' => $this->mayor_ro_data( $post ),
			'home_hu'  => $this->home_hu_data( $post ),
			'mayor_hu' => $this->mayor_hu_data( $post ),
			default    => array(),
		};
		if ( ! $data ) {
			return new \WP_Error( 'invalid_template', __( 'Șablonul solicitat nu este disponibil.', 'primaria-porumbesti' ) );
		}

		return $this->write_elementor_page( $post, $template, $data );
	}

	private function language_layer_ready(): bool {
		return function_exists( 'pll_get_post_language' )
			&& function_exists( 'pll_get_post' )
			&& function_exists( 'pll_get_post_translations' );
	}

	private function require_language_layer(): void {
		if ( $this->language_layer_ready() ) {
			return;
		}

		wp_safe_redirect( admin_url( 'tools.php?page=porumbesti-rebuild&porumbesti_status=error&porumbesti_error=polylang_required' ) );
		exit;
	}

	private function write_elementor_page( \WP_Post $post, string $template, array $data ) {
		$post_id = (int) $post->ID;
		$identity_before = $this->post_identity_contract( $post );
		$encoded_data = wp_json_encode( $data );
		if ( false === $encoded_data ) {
			return new \WP_Error( 'elementor_json_failed', __( 'Datele Elementor nu au putut fi codificate.', 'primaria-porumbesti' ) );
		}

		$permalink_before = get_permalink( $post_id );
		if ( ! metadata_exists( 'post', $post_id, self::SOURCE_META ) && '' !== trim( (string) $post->post_content ) ) {
			update_post_meta( $post_id, self::SOURCE_META, wp_slash( $post->post_content ) );
		}
		$this->save_backup( $post_id, 'before_' . $template );
		wp_save_post_revision( $post_id );

		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.33.0' );
		update_post_meta( $post_id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
		update_post_meta( $post_id, '_wp_page_template', 'elementor_canvas' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( $encoded_data ) );

		delete_post_meta( $post_id, '_elementor_css' );

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		clean_post_cache( $post_id );
		if ( untrailingslashit( (string) $permalink_before ) !== untrailingslashit( (string) get_permalink( $post_id ) ) ) {
			$this->restore_latest( $post_id );
			return new \WP_Error( 'url_changed', __( 'Adresa URL s-a modificat; schimbarea a fost anulată automat.', 'primaria-porumbesti' ) );
		}
		if ( ! $this->post_identity_matches( $identity_before ) ) {
			$this->restore_latest( $post_id );
			return new \WP_Error( 'identity_changed', __( 'Identitatea paginii sau relatia de traducere s-a modificat; schimbarea a fost anulata automat.', 'primaria-porumbesti' ) );
		}
		if ( ! $this->renders_without_error( $post_id ) ) {
			$this->restore_latest( $post_id );
			return new \WP_Error( 'render_failed', __( 'Randarea Elementor a eșuat; versiunea anterioară a fost restaurată automat.', 'primaria-porumbesti' ) );
		}

		update_post_meta(
			$post_id,
			self::MANIFEST_META,
			array(
				'template'   => $template,
				'version'    => PORUMBESTI_WIDGETS_VERSION,
				'applied_at' => current_time( 'mysql' ),
				'url'        => $permalink_before,
				'identity_hash' => hash( 'sha256', (string) wp_json_encode( $identity_before ) ),
				'hash'       => hash( 'sha256', wp_json_encode( $data ) ),
			)
		);

		return true;
	}

	private function renders_without_error( int $post_id ): bool {
		if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
			return true;
		}

		try {
			$rendered = (string) \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $post_id, true );
			return '' !== trim( $rendered );
		} catch ( \Throwable $error ) {
			do_action( 'porumbesti_template_render_error', $post_id, $error );
			return false;
		}
	}

	private function post_identity_contract( \WP_Post $post ): array {
		$translations = function_exists( 'pll_get_post_translations' ) ? pll_get_post_translations( (int) $post->ID ) : array();
		$translations = is_array( $translations ) ? array_map( 'intval', $translations ) : array();
		ksort( $translations );

		return array(
			'ID'           => (int) $post->ID,
			'post_type'    => (string) $post->post_type,
			'post_name'    => (string) $post->post_name,
			'post_title'   => (string) $post->post_title,
			'post_parent'  => (int) $post->post_parent,
			'post_status'  => (string) $post->post_status,
			'post_excerpt' => (string) $post->post_excerpt,
			'menu_order'   => (int) $post->menu_order,
			'permalink'    => untrailingslashit( (string) get_permalink( $post->ID ) ),
			'language'     => function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( (int) $post->ID, 'slug' ) : '',
			'translations' => $translations,
		);
	}

	private function post_identity_matches( array $expected ): bool {
		$post_id = (int) ( $expected['ID'] ?? 0 );
		$current = $post_id ? get_post( $post_id ) : null;
		if ( ! $current instanceof \WP_Post ) {
			return false;
		}

		return $expected === $this->post_identity_contract( $current );
	}

	private function find_home_ro_page(): int {
		$page = get_page_by_path( 'prima', OBJECT, 'page' );
		if ( $page instanceof \WP_Post ) {
			return (int) $page->ID;
		}

		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'title'          => 'Prima',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return $pages ? (int) $pages[0] : 0;
	}

	private function find_mayor_ro_page(): int {
		return $this->find_ro_page( array( 'primar' ) );
	}

	private function find_home_hu_page(): int {
		return $this->find_language_page( array( 'fooldal' ), 'hu' );
	}

	private function find_mayor_hu_page(): int {
		$translated = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $this->find_mayor_ro_page(), 'hu' ) : 0;
		return $translated ?: $this->find_language_page( array( 'polgarmester' ), 'hu' );
	}

	private function find_ro_page( array $slugs ): int {
		return $this->find_language_page( $slugs, 'ro' );
	}

	private function find_language_page( array $slugs, string $language ): int {
		foreach ( $slugs as $slug ) {
			$pages = get_posts(
				array(
					'name'             => $slug,
					'post_type'        => 'page',
					'post_status'      => array( 'publish', 'draft', 'private' ),
					'posts_per_page'   => -1,
					'suppress_filters' => false,
					'lang'             => $language,
				)
			);
			foreach ( $pages as $page ) {
				$page_language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
				$permalink = get_permalink( $page );
				if ( $language === $page_language || ( ! $page_language && str_contains( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/' . $language . '/' ) ) ) {
					return (int) $page->ID;
				}
			}
		}
		return 0;
	}

	private function template_targets( string $language = '' ): array {
		$targets = array(
			'home_ro'  => array(
				'label'   => __( 'Index român', 'primaria-porumbesti' ),
				'button'  => __( 'Aplică indexul român Elementor', 'primaria-porumbesti' ),
				'post_id' => $this->find_home_ro_page(),
			),
			'mayor_ro' => array(
				'label'   => __( 'Pagina Primar', 'primaria-porumbesti' ),
				'button'  => __( 'Aplică pagina Primar Elementor', 'primaria-porumbesti' ),
				'post_id' => $this->find_mayor_ro_page(),
			),
			'home_hu'  => array(
				'label'   => __( 'Magyar index', 'primaria-porumbesti' ),
				'button'  => __( 'A magyar Elementor-index alkalmazása', 'primaria-porumbesti' ),
				'post_id' => $this->find_home_hu_page(),
			),
			'mayor_hu' => array(
				'label'   => __( 'Polgármester oldal', 'primaria-porumbesti' ),
				'button'  => __( 'A magyar polgármesteri oldal alkalmazása', 'primaria-porumbesti' ),
				'post_id' => $this->find_mayor_hu_page(),
			),
		);
		if ( in_array( $language, array( 'ro', 'hu' ), true ) ) {
			return array_filter( $targets, static fn( string $key ): bool => str_ends_with( $key, '_' . $language ), ARRAY_FILTER_USE_KEY );
		}
		return $targets;
	}

	private function rebuild_groups(): array {
		$groups = array(
			'administration' => array(
				'label'       => __( 'Primărie și conducere', 'primaria-porumbesti' ),
				'description' => __( 'Conducere, consiliu local, departamente și pagina de contact.', 'primaria-porumbesti' ),
			),
			'documents'      => array(
				'label'       => __( 'Documente și transparență', 'primaria-porumbesti' ),
				'description' => __( 'Hotărâri, anunțuri, proiecte, declarații, bugete, registre și formulare.', 'primaria-porumbesti' ),
			),
			'community'      => array(
				'label'       => __( 'Comuna și servicii', 'primaria-porumbesti' ),
				'description' => __( 'Istorie, localizare, servicii, turism, sport, personalități și pagini informative.', 'primaria-porumbesti' ),
			),
			'galleries'      => array(
				'label'       => __( 'Galerii', 'primaria-porumbesti' ),
				'description' => __( 'Pagini foto și galerii istorice.', 'primaria-porumbesti' ),
			),
		);
		$hungarian = array(
			'administration' => array( 'label' => 'Magyar · Polgármesteri hivatal és vezetőség', 'description' => 'Polgármester, helyi tanács, hivatali részlegek és elérhetőségek.' ),
			'documents'      => array( 'label' => 'Magyar · Dokumentumok és közérdekű adatok', 'description' => 'Felhívások, határozatok, közlöny, szabályzatok, nyilvántartások és formanyomtatványok.' ),
			'community'      => array( 'label' => 'Magyar · Község és szolgáltatások', 'description' => 'Történet, elhelyezkedés, turizmus, sport, kultúra és közösségi oldalak.' ),
			'galleries'      => array( 'label' => 'Magyar · Galériák', 'description' => 'Fényképes oldalak, események és történelmi galériák.' ),
		);
		foreach ( $hungarian as $key => $definition ) {
			$groups['hu_' . $key] = $definition;
		}
		return $groups;
	}

	private function published_ro_pages(): array {
		return $this->published_pages( 'ro' );
	}

	private function published_hu_pages(): array {
		return $this->published_pages( 'hu' );
	}

	private function published_pages( string $language ): array {
		$pages = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => 'publish',
				'posts_per_page'   => -1,
				'orderby'          => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'suppress_filters' => false,
				'lang'             => $language,
			)
		);
		$excluded = 'hu' === $language
			? array_filter( array( $this->find_home_hu_page(), $this->find_mayor_hu_page() ) )
			: array_filter( array( $this->find_home_ro_page(), $this->find_mayor_ro_page() ) );
		return array_values(
			array_filter(
				$pages,
				function ( $page ) use ( $excluded, $language ): bool {
					if ( in_array( (int) $page->ID, $excluded, true ) ) {
						return false;
					}
					$page_language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
					$path = (string) wp_parse_url( get_permalink( $page ), PHP_URL_PATH );
					return $language === $page_language || ( ! $page_language && str_contains( $path, '/' . $language . '/' ) );
				}
			)
		);
	}

	private function classify_page( \WP_Post $page ): string {
		$slug = strtolower( remove_accents( $page->post_name ) );
		$title = strtolower( remove_accents( $page->post_title ) );
		$haystack = $slug . ' ' . $title;

		if ( preg_match( '/galeria|gallery|foto|fénykép|fenykep/', $haystack ) ) {
			return 'galleries';
		}
		if ( preg_match( '/contact|viceprimar|secretar|departament|consili|cuvantul-primarului|elerhet|alpolgarmester|jegyzo|reszleg|tanacs|polgarmester/', $haystack ) ) {
			return 'administration';
		}
		if ( preg_match( '/anunt|hotarar|document|declarati|buget|dare-de-seama|dispoziti|financiar|formular|legisl|monitorul-oficial|proces|proiect|registr|regulament|autorizati|certificat|taxe|impozit|convocator|minute|informare|statut|felhivas|hatarozat|nyilatkozat|koltsegvet|rendelkezes|penzugy|formanyomtatvany|jogalkotas|kozlony|jegyzokonyv|nyilvantartas|szabalyzat|torveny|kozerd/', $haystack ) ) {
			return 'documents';
		}
		return 'community';
	}

	private function group_pages( string $group ): array {
		$language = str_starts_with( $group, 'hu_' ) ? 'hu' : 'ro';
		$type = 'hu' === $language ? substr( $group, 3 ) : $group;
		return array_values(
			array_filter(
				$this->published_pages( $language ),
				fn( $page ): bool => $type === $this->classify_page( $page )
			)
		);
	}

	private function menu_id( string $language = 'ro' ): string {
		if ( isset( $this->menu_id_cache[ $language ] ) ) {
			return $this->menu_id_cache[ $language ];
		}
		$menus = wp_get_nav_menus();
		if ( ! $menus ) {
			$this->menu_id_cache[ $language ] = '';
			return $this->menu_id_cache[ $language ];
		}

		$best_id = '';
		$best_score = -1;
		foreach ( $menus as $menu ) {
			$name = strtolower( remove_accents( $menu->name ) );
			$items = wp_get_nav_menu_items( $menu->term_id );
			$count = is_array( $items ) ? count( $items ) : 0;
			if ( 0 === $count ) {
				continue;
			}

			$score = $count;
			$language_names = 'hu' === $language ? array( 'fo magyar', 'fő magyar', 'magyar fo menu', 'magyar fő menü' ) : array( 'fo roman', 'fő roman', 'fo romana', 'meniu principal roman' );
			$language_tokens = 'hu' === $language ? array( 'magyar', 'hungar', 'hu' ) : array( 'roman', 'romana', 'ro' );
			$opposite_tokens = 'hu' === $language ? array( 'roman', 'romana' ) : array( 'magyar', 'hungar' );
			if ( in_array( $name, $language_names, true ) ) {
				$score += 10000;
			} elseif ( ( str_contains( $name, 'fo' ) || str_contains( $name, 'fő' ) || str_contains( $name, 'principal' ) ) && array_filter( $language_tokens, fn( string $token ): bool => str_contains( $name, $token ) ) ) {
				$score += 5000;
			} elseif ( array_filter( $language_tokens, fn( string $token ): bool => str_contains( $name, $token ) ) ) {
				$score += 1000;
			}
			foreach ( (array) $items as $item ) {
				$path = (string) wp_parse_url( (string) ( $item->url ?? '' ), PHP_URL_PATH );
				$score += str_contains( $path, '/' . $language . '/' ) ? 120 : 0;
			}
			if ( array_filter( $opposite_tokens, fn( string $token ): bool => str_contains( $name, $token ) ) ) {
				$score -= 6000;
			}
			if ( str_contains( $name, 'bal' ) || str_contains( $name, 'sidebar' ) ) {
				$score -= 5000;
			}

			if ( $score > $best_score ) {
				$best_score = $score;
				$best_id = (string) $menu->term_id;
			}
		}

		$this->menu_id_cache[ $language ] = $best_id;
		return $this->menu_id_cache[ $language ];
	}

	private function backups( int $post_id ): array {
		$backups = get_post_meta( $post_id, self::BACKUP_META, true );
		return is_array( $backups ) ? $backups : array();
	}

	private function save_backup( int $post_id, string $reason ): void {
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$meta = array();
		foreach ( self::ELEMENTOR_META as $key ) {
			$meta[ $key ] = array(
				'exists' => metadata_exists( 'post', $post_id, $key ),
				'value'  => get_post_meta( $post_id, $key, true ),
			);
		}

		$backups = $this->backups( $post_id );
		$backups[] = array(
			'created_at'  => current_time( 'mysql' ),
			'reason'      => $reason,
			'post_content'=> $post->post_content,
			'permalink'   => get_permalink( $post_id ),
			'identity'    => $this->post_identity_contract( $post ),
			'meta'        => $meta,
		);
		$backups = array_slice( $backups, -self::MAX_BACKUPS );
		update_post_meta( $post_id, self::BACKUP_META, $backups );
	}

	private function restore_latest( int $post_id ): bool {
		$backups = $this->backups( $post_id );
		$backup = array_pop( $backups );
		if ( ! is_array( $backup ) ) {
			return false;
		}

		$identity = is_array( $backup['identity'] ?? null ) ? $backup['identity'] : array();
		$post_update = array( 'ID' => $post_id, 'post_content' => (string) ( $backup['post_content'] ?? '' ) );
		foreach ( array( 'post_name', 'post_title', 'post_parent', 'post_status', 'post_excerpt', 'menu_order' ) as $field ) {
			if ( array_key_exists( $field, $identity ) ) {
				$post_update[ $field ] = $identity[ $field ];
			}
		}
		$result = wp_update_post( $post_update, true );
		if ( is_wp_error( $result ) ) {
			return false;
		}
		if ( ! empty( $identity['translations'] ) && is_array( $identity['translations'] ) && function_exists( 'pll_save_post_translations' ) ) {
			pll_save_post_translations( array_map( 'intval', $identity['translations'] ) );
		}

		foreach ( self::ELEMENTOR_META as $key ) {
			$state = $backup['meta'][ $key ] ?? array( 'exists' => false, 'value' => '' );
			if ( empty( $state['exists'] ) ) {
				delete_post_meta( $post_id, $key );
			} else {
				$value = $state['value'] ?? '';
				update_post_meta( $post_id, $key, is_string( $value ) ? wp_slash( $value ) : $value );
			}
		}
		update_post_meta( $post_id, self::BACKUP_META, $backups );
		delete_post_meta( $post_id, self::MANIFEST_META );
		clean_post_cache( $post_id );
		return ! $identity || $this->post_identity_matches( $identity );
	}

	private function routes( string $language = 'ro' ): array {
		if ( isset( $this->routes_cache[ $language ] ) ) {
			return $this->routes_cache[ $language ];
		}
		if ( 'hu' === $language ) {
			$home_hu = $this->page_url( array( 'fooldal' ), '/hu/fooldal/', 'hu' );
			$ro_routes = $this->routes( 'ro' );
			$definitions = array(
				'public_info'   => array( array(), array() ),
				'monitor'       => array( array( 'monitorul-oficial-local-2' ), array() ),
				'contact'       => array( array( 'contact' ), array( 'elerhetosegeink' ) ),
				'announcements' => array( array(), array() ),
				'decisions'     => array( array(), array() ),
				'forms'         => array( array(), array( 'forma-nyomtatvanyok' ) ),
				'taxes'         => array( array( 'taxe-si-impozite-locale' ), array() ),
				'urbanism'      => array( array( 'urbanism' ), array() ),
				'agricultural'  => array( array( 'registru-agricol' ), array() ),
				'mayor'         => array( array( 'primar' ), array( 'polgarmester' ) ),
				'vice_mayor'    => array( array( 'viceprimar' ), array( 'alpolgarmester' ) ),
				'secretary'     => array( array( 'secretar' ), array( 'jegyzo' ) ),
				'council'       => array( array( 'componenta-consiliului-local' ), array( 'a-helyi-tanacs-szerkezete' ) ),
				'history'       => array( array( 'istoria-comunei' ), array( 'kozsegunk-tortenete' ) ),
				'monuments'     => array( array(), array( 'rathonyi-akos', 'jendrassik-jeno' ) ),
				'tourism'       => array( array( 'asociatia-sportiva-ugocea-porumbesti' ), array( 'a-kokenyesdi-ugocsa-csapatanak-tortenete' ) ),
				'twinned'       => array( array(), array() ),
				'deliberative'  => array( array( 'hotararile-autoritatii-deliberative' ), array() ),
				'executive'     => array( array( 'dispozitiile-autoritatii-executive' ), array() ),
				'financial'     => array( array( 'documente-si-informatii-financiare' ), array() ),
			);
			$routes = array( 'home_ro' => $this->page_url( array( 'prima' ), '/ro/prima/', 'ro' ), 'home_hu' => $home_hu );
			foreach ( $definitions as $key => $definition ) {
				$hu_page_id = $definition[1] ? $this->find_language_page( $definition[1], 'hu' ) : 0;
				$fallback = $hu_page_id && 'publish' === get_post_status( $hu_page_id ) ? (string) get_permalink( $hu_page_id ) : ( $ro_routes[ $key ] ?? $home_hu );
				if ( in_array( $key, array( 'public_info', 'announcements' ), true ) ) {
					$fallback = home_url( '/hu/category/felhivasok/' );
				}
				$routes[ $key ] = $this->translated_url( $this->find_ro_page( $definition[0] ), 'hu', $fallback );
			}
			$routes['location'] = $routes['history'];
			$routes['galleries'] = home_url( '/hu/category/galeria-foto/' );
			$routes['phones'] = $this->page_url( array( 'hasznos-telefonszamok' ), '/hu/hasznos-telefonszamok/', 'hu' );
			$routes['legislation'] = $this->page_url( array( 'jogalkotas' ), '/hu/jogalkotas/', 'hu' );
			$routes['portal'] = self::PORTAL_URL;
			$routes['events'] = $routes['announcements'];
			$routes['journal'] = $routes['announcements'];
			$routes['law17'] = $routes['public_info'];
			$this->routes_cache['hu'] = $routes;
			return $routes;
		}
		$this->routes_cache['ro'] = array(
			'home_ro'       => $this->page_url( array( 'prima' ), '/ro/prima/' ),
			'home_hu'       => $this->page_url( array( 'fooldal' ), '/hu/fooldal/', 'hu' ),
			'public_info'   => home_url( '/ro/category/anunturi/' ),
			'monitor'       => $this->page_url( array( 'monitorul-oficial-local-2' ), '/ro/monitorul-oficial-local-2/' ),
			'contact'       => $this->page_url( array( 'contact' ), '/ro/contact/' ),
			'announcements' => home_url( '/ro/category/anunturi/' ),
			'decisions'     => home_url( '/ro/category/hotarari-ale-consiului-local-ro/' ),
			'forms'         => home_url( '/ro/category/formulare-tipizate/' ),
			'taxes'         => $this->page_url( array( 'taxe-si-impozite-locale' ), '/ro/departamente/' ),
			'urbanism'      => $this->page_url( array( 'urbanism' ), '/ro/departamente/' ),
			'agricultural'  => $this->page_url( array( 'registru-agricol' ), '/ro/departamente/' ),
			'departments'   => $this->page_url( array( 'departamente' ), '/ro/departamente/' ),
			'mayor'         => $this->page_url( array( 'primar' ), '/ro/primar/' ),
			'vice_mayor'    => $this->page_url( array( 'viceprimar' ), '/ro/viceprimar/' ),
			'secretary'     => $this->page_url( array( 'secretar' ), '/ro/secretar/' ),
			'council'       => $this->page_url( array( 'componenta-consiliului-local' ), '/ro/componenta-consiliului-local/' ),
			'history'       => $this->page_url( array( 'istoria-comunei' ), '/ro/istoria-comunei/' ),
			'location'      => $this->page_url( array( 'prezentarea-comunei-porumbesti' ), '/ro/prezentarea-comunei-porumbesti/' ),
			'monuments'     => $this->page_url( array( 'istoria-comunei' ), '/ro/istoria-comunei/' ),
			'tourism'       => $this->page_url( array( 'asociatia-sportiva-ugocea-porumbesti' ), '/ro/asociatia-sportiva-ugocea-porumbesti/' ),
			'twinned'       => $this->page_url( array( 'istoria-comunei' ), '/ro/istoria-comunei/' ),
			'deliberative'  => $this->page_url( array( 'hotararile-autoritatii-deliberative' ), '/ro/hotararile-autoritatii-deliberative/' ),
			'executive'     => $this->page_url( array( 'dispozitiile-autoritatii-executive' ), '/ro/dispozitiile-autoritatii-executive/' ),
			'financial'     => $this->page_url( array( 'documente-si-informatii-financiare' ), '/ro/documente-si-informatii-financiare/' ),
			'phones'        => $this->page_url( array( 'telefone-utile' ), '/ro/telefone-utile/' ),
			'legislation'   => $this->page_url( array( 'legislatie' ), '/ro/legislatie/' ),
			'portal'        => self::PORTAL_URL,
			'galleries'     => home_url( '/hu/category/galeria-foto/' ),
		);
		return $this->routes_cache['ro'];
	}

	private function page_url( array $slugs, string $fallback, string $language = 'ro' ): string {
		foreach ( $slugs as $slug ) {
			$pages = get_posts(
				array(
					'name'             => $slug,
					'post_type'        => 'page',
					'post_status'      => 'publish',
					'posts_per_page'   => -1,
					'suppress_filters' => false,
					'lang'             => $language,
				)
			);
			foreach ( $pages as $page ) {
				$page_language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
				$permalink = get_permalink( $page );
				if ( $language === $page_language || ( ! $page_language && str_contains( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/' . $language . '/' ) ) ) {
					return $permalink;
				}
			}
		}
		return home_url( $fallback );
	}

	private function translated_url( int $post_id, string $language, string $fallback ): string {
		if ( $post_id && function_exists( 'pll_get_post' ) ) {
			$translated_id = (int) pll_get_post( $post_id, $language );
			if ( $translated_id && 'publish' === get_post_status( $translated_id ) ) {
				return get_permalink( $translated_id );
			}
		}
		return $fallback;
	}

	private function id( string $seed ): string {
		return substr( md5( 'porumbesti-home-ro-' . $seed ), 0, 7 );
	}

	private function link( string $url = '' ): array {
		return array(
			'url'         => $url,
			'is_external' => '',
			'nofollow'    => '',
		);
	}

	private function media(): array {
		return array(
			'id'  => '',
			'url' => '',
		);
	}

	private function brand_logo(): array {
		return array(
			'id'  => '',
			'url' => PORUMBESTI_WIDGETS_URL . 'assets/images/porumbesti-monogram.svg',
		);
	}

	private function design_media( string $relative_path, string $origin = 'local-redesign' ): array {
		$uploads = wp_get_upload_dir();
		if ( empty( $uploads['baseurl'] ) ) {
			return $this->media();
		}
		$item = $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . ltrim( $relative_path, '/' ), $origin );
		return $item ? $item['image'] : $this->media();
	}

	private function media_item_from_id( int $attachment_id, string $origin ): ?array {
		if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
			return null;
		}
		$url = (string) wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( ! $url ) {
			return null;
		}
		return array(
			'image'   => array( 'id' => $attachment_id, 'url' => $url ),
			'caption' => wp_get_attachment_caption( $attachment_id ) ?: get_the_title( $attachment_id ),
			'_origin' => $origin,
		);
	}

	private function media_item_from_url( string $url, string $origin ): ?array {
		$url = esc_url_raw( html_entity_decode( trim( $url, " \t\n\r\0\x0B\\\"'()" ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		if ( ! $url || ! preg_match( '/\.(?:avif|gif|jpe?g|png|svg|webp)(?:\?.*)?$/i', $url ) ) {
			return null;
		}
		$attachment_id = (int) attachment_url_to_postid( $url );
		$item = $attachment_id ? $this->media_item_from_id( $attachment_id, $origin ) : null;
		if ( $item ) {
			return $item;
		}
		return array(
			'image'   => array( 'id' => '', 'url' => $url ),
			'caption' => '',
			'_origin' => $origin,
		);
	}

	private function shortcode_media_ids( string $attributes ): array {
		$ids = array();
		if ( preg_match_all( '/(?:^|\s)(?:attach_images|background_image|bg_image|image|images|ids)\s*=\s*(["\'])(.*?)\1/is', $attributes, $matches ) ) {
			foreach ( $matches[2] as $value ) {
				if ( preg_match_all( '/\d+/', $value, $numbers ) ) {
					foreach ( $numbers[0] as $number ) {
						$ids[] = (int) $number;
					}
				}
			}
		}
		return array_values( array_unique( array_filter( $ids ) ) );
	}

	private function legacy_media_ids( string $content ): array {
		$ids = $this->shortcode_media_ids( $content );
		if ( preg_match_all( '/\bwp-image-(\d+)\b/i', $content, $matches ) ) {
			$ids = array_merge( $ids, array_map( 'intval', $matches[1] ) );
		}
		return array_values( array_unique( array_filter( $ids ) ) );
	}

	private function is_background_media_id( string $content, int $attachment_id ): bool {
		return (bool) preg_match( '/(?:background_image|bg_image)\s*=\s*(["\'])[^"\']*\b' . $attachment_id . '\b[^"\']*\1/i', $content );
	}

	private function legacy_media_urls( string $content ): array {
		$content = str_replace( '\\/', '/', html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		if ( ! preg_match_all( '#https?://[^\s"\'<>\\)]+/wp-content/uploads/[^\s"\'<>\\)]+#i', $content, $matches ) ) {
			return array();
		}
		return array_values( array_unique( $matches[0] ) );
	}

	private function slider_aliases( string $content ): array {
		$aliases = array();
		if ( preg_match_all( '/\[rev_slider\b([^\]]*)\]/i', $content, $matches ) ) {
			foreach ( $matches[1] as $attributes ) {
				if ( preg_match( '/\balias\s*=\s*(["\'])([^"\']+)\1/i', $attributes, $alias ) ) {
					$aliases[] = sanitize_key( $alias[2] );
				} elseif ( preg_match( '/^\s+([a-z0-9_-]+)/i', $attributes, $alias ) ) {
					$aliases[] = sanitize_key( $alias[1] );
				}
			}
		}
		return array_values( array_unique( array_filter( $aliases ) ) );
	}

	private function slider_media_items( string $content ): array {
		$aliases = $this->slider_aliases( $content );
		if ( ! $aliases ) {
			return array();
		}
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return array();
		}
		$sliders_table = $wpdb->prefix . 'revslider_sliders';
		$slides_table = $wpdb->prefix . 'revslider_slides';
		$items = array();
		foreach ( $aliases as $alias ) {
			// Slider Revolution stores legacy v5 module and slide media in these two tables.
			$slider = $wpdb->get_row( $wpdb->prepare( "SELECT id, params FROM {$sliders_table} WHERE alias = %s LIMIT 1", $alias ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( ! is_array( $slider ) ) {
				continue;
			}
			$payloads = array( (string) ( $slider['params'] ?? '' ) );
			$slides = $wpdb->get_results( $wpdb->prepare( "SELECT params, layers FROM {$slides_table} WHERE slider_id = %d ORDER BY slide_order ASC", (int) $slider['id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( (array) $slides as $slide ) {
				$payloads[] = (string) ( $slide['params'] ?? '' );
				$payloads[] = (string) ( $slide['layers'] ?? '' );
			}
			foreach ( $this->legacy_media_urls( implode( "\n", $payloads ) ) as $url ) {
				$item = $this->media_item_from_url( $url, 'slider' );
				if ( $item ) {
					$items[] = $item;
				}
			}
		}
		return $items;
	}

	private function legacy_media_items( \WP_Post $page, string $content ): array {
		$items = array();
		$seen = array();
		$add = static function ( ?array $item ) use ( &$items, &$seen ): void {
			if ( ! $item ) {
				return;
			}
			$id = (int) ( $item['image']['id'] ?? 0 );
			$url = (string) ( $item['image']['url'] ?? '' );
			$key = $id ? 'id:' . $id : 'url:' . strtolower( strtok( $url, '?' ) ?: $url );
			if ( isset( $seen[ $key ] ) ) {
				return;
			}
			$seen[ $key ] = true;
			$items[] = $item;
		};

		$featured_id = (int) get_post_thumbnail_id( $page->ID );
		$add( $this->media_item_from_id( $featured_id, 'featured' ) );
		foreach ( $this->slider_media_items( $content ) as $item ) {
			$add( $item );
		}
		foreach ( $this->legacy_media_ids( $content ) as $attachment_id ) {
			$origin = str_contains( $content, 'wp-image-' . $attachment_id ) ? 'inline' : ( $this->is_background_media_id( $content, $attachment_id ) ? 'background' : 'shortcode' );
			$add( $this->media_item_from_id( $attachment_id, $origin ) );
		}
		foreach ( $this->legacy_media_urls( $content ) as $url ) {
			$origin = preg_match( '/<img\b[^>]*(?:src|data-src)=["\'][^"\']*' . preg_quote( basename( strtok( $url, '?' ) ?: $url ), '/' ) . '/i', $content ) ? 'inline' : 'source-url';
			$add( $this->media_item_from_url( $url, $origin ) );
		}
		foreach ( get_attached_media( 'image', $page->ID ) as $attachment ) {
			$add( $attachment instanceof \WP_Post ? $this->media_item_from_id( $attachment->ID, 'attached' ) : null );
		}
		return $items;
	}

	private function public_media_items( array $items ): array {
		return array_map(
			static fn( array $item ): array => array( 'image' => $item['image'], 'caption' => $item['caption'] ?? '' ),
			$items
		);
	}

	private function primary_media( array $items ): array {
		foreach ( $items as $item ) {
			if ( in_array( $item['_origin'] ?? '', array( 'background', 'featured', 'slider', 'source-url' ), true ) ) {
				return $item['image'];
			}
		}
		return $this->media();
	}

	private function unplaced_media_items( array $items, string $normalized_content, array $primary ): array {
		$unused = array();
		foreach ( $items as $item ) {
			$id = (int) ( $item['image']['id'] ?? 0 );
			$url = (string) ( $item['image']['url'] ?? '' );
			if ( ( $id && $id === (int) ( $primary['id'] ?? 0 ) ) || ( $url && $url === (string) ( $primary['url'] ?? '' ) ) ) {
				continue;
			}
			if ( ( $id && str_contains( $normalized_content, 'wp-image-' . $id ) ) || ( $url && str_contains( $normalized_content, basename( strtok( $url, '?' ) ?: $url ) ) ) ) {
				continue;
			}
			$unused[] = $item;
		}
		return $this->public_media_items( $unused );
	}

	private function repeater( string $seed, array $items ): array {
		foreach ( $items as $index => $item ) {
			$items[ $index ]['_id'] = substr( md5( $seed . '-' . $index ), 0, 7 );
		}

		return $items;
	}

	private function widget( string $seed, string $type, array $settings = array() ): array {
		return array(
			'id'         => $this->id( $seed ),
			'elType'     => 'widget',
			'settings'   => $settings,
			'elements'   => array(),
			'widgetType' => $type,
		);
	}

	private function container( string $seed, array $elements, array $settings = array() ): array {
		return array(
			'id'       => $this->id( $seed ),
			'elType'   => 'container',
			'settings' => array_merge(
				array(
					'content_width' => 'boxed',
					'boxed_width'   => array( 'unit' => 'px', 'size' => 1240, 'sizes' => array() ),
					'flex_direction' => 'column',
					'gap'            => array( 'unit' => 'px', 'size' => 0, 'sizes' => array() ),
					'padding'        => array(
						'unit'     => 'px',
						'top'      => '0',
						'right'    => '0',
						'bottom'   => '0',
						'left'     => '0',
						'isLinked' => true,
					),
				),
				$settings
			),
			'elements' => $elements,
		);
	}

	private function original_page_content( \WP_Post $page ): string {
		$stored = get_post_meta( $page->ID, self::SOURCE_META, true );
		if ( is_string( $stored ) && '' !== trim( $stored ) ) {
			return $stored;
		}
		if ( '' !== trim( (string) $page->post_content ) ) {
			return $page->post_content;
		}
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
			$rendered = (string) \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $page->ID, true );
			if ( '' !== trim( $rendered ) ) {
				update_post_meta( $page->ID, self::SOURCE_META, wp_slash( $rendered ) );
				return $rendered;
			}
		}
		$language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
		return 'hu' === $language ? '<p>Az oldal tartalmának frissítése folyamatban van.</p>' : '<p>Conținutul acestei pagini este în curs de actualizare.</p>';
	}

	private function content_matches_language( \WP_Post $post, string $language ): bool {
		if ( function_exists( 'pll_get_post_language' ) ) {
			$post_language = (string) pll_get_post_language( $post->ID, 'slug' );
			if ( $post_language ) {
				return $language === $post_language;
			}
		}

		$path = (string) wp_parse_url( get_permalink( $post ), PHP_URL_PATH );
		if ( preg_match( '#/(ro|hu)(?:/|$)#', $path, $matches ) ) {
			return $language === $matches[1];
		}
		return true;
	}

	private function same_slug_post_content( \WP_Post $page, string $language ): string {
		$posts = get_posts( array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'name'             => $page->post_name,
			'posts_per_page'   => 10,
			'orderby'          => 'ID',
			'order'            => 'DESC',
			'suppress_filters' => false,
			'lang'             => $language,
		) );

		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post || ! $this->content_matches_language( $post, $language ) ) {
				continue;
			}
			$content = trim( (string) $post->post_content );
			if ( ! $content ) {
				continue;
			}
			$normalized = $this->normalize_legacy_content( $content );
			$plain = strtolower( remove_accents( trim( wp_strip_all_tags( $normalized ) ) ) );
			$is_placeholder = str_contains( $plain, 'curs de actualizare' ) || str_contains( $plain, 'frissitese folyamatban' );
			$has_media = (bool) preg_match( '/<(?:a|audio|figure|iframe|img|video)\b/i', $normalized );
			if ( ( $plain && ! $is_placeholder ) || $has_media ) {
				return $content;
			}
		}
		return '';
	}

	private function sitemap_content( string $language ): string {
		$items = get_posts( array(
			'post_type'        => array( 'page', 'post' ),
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
			'lang'             => $language,
		) );
		$groups = array( 'page' => array(), 'post' => array() );
		$seen = array();
		foreach ( $items as $item ) {
			if ( ! $item instanceof \WP_Post || ! isset( $groups[ $item->post_type ] ) || ! $this->content_matches_language( $item, $language ) ) {
				continue;
			}
			$url = get_permalink( $item );
			$key = untrailingslashit( strtolower( (string) $url ) );
			if ( ! $url || isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$groups[ $item->post_type ][] = '<li><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $item ) ) . '</a></li>';
		}

		$labels = 'hu' === $language
			? array( 'page' => 'Oldalak', 'post' => 'Bejegyzések' )
			: array( 'page' => 'Pagini', 'post' => 'Articole' );
		$html = '<div class="porumbesti-sitemap">';
		foreach ( array( 'page', 'post' ) as $post_type ) {
			if ( ! $groups[ $post_type ] ) {
				continue;
			}
			$html .= '<h2>' . esc_html( $labels[ $post_type ] ) . '</h2><ul>' . implode( '', $groups[ $post_type ] ) . '</ul>';
		}
		return $html . '</div>';
	}

	private function legacy_image_markup( array $ids, bool $gallery = false ): string {
		if ( ! function_exists( 'wp_get_attachment_image' ) ) {
			return '';
		}
		$images = '';
		foreach ( array_unique( array_filter( array_map( 'intval', $ids ) ) ) as $attachment_id ) {
			$image = wp_get_attachment_image( $attachment_id, 'large', false, array( 'loading' => 'lazy' ) );
			if ( ! $image ) {
				continue;
			}
			$caption = wp_get_attachment_caption( $attachment_id );
			$images .= '<figure class="porumbesti-legacy-media">' . $image . ( $caption ? '<figcaption>' . esc_html( $caption ) . '</figcaption>' : '' ) . '</figure>';
		}
		return $gallery && $images ? '<div class="porumbesti-legacy-gallery">' . $images . '</div>' : $images;
	}

	private function expand_legacy_media_shortcodes( string $content ): string {
		$content = preg_replace_callback(
			'/\[vc_single_image\b([^\]]*)\](?:\[\/vc_single_image\])?/i',
			fn( array $matches ): string => $this->legacy_image_markup( $this->shortcode_media_ids( $matches[1] ) ),
			$content
		) ?? $content;
		$content = preg_replace_callback(
			'/\[(?:vc_gallery|gallery)\b([^\]]*)\](?:\[\/(?:vc_gallery|gallery)\])?/i',
			fn( array $matches ): string => $this->legacy_image_markup( $this->shortcode_media_ids( $matches[1] ), true ),
			$content
		) ?? $content;
		return $content;
	}

	private function legacy_shortcode_attributes( string $attributes ): array {
		$parsed = array();
		if ( preg_match_all( '/([a-zA-Z][a-zA-Z0-9_-]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s\]]+))/', $attributes, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$parsed[ strtolower( $match[1] ) ] = html_entity_decode( $match[2] ?: ( $match[3] ?: $match[4] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			}
		}
		return $parsed;
	}

	private function expand_legacy_link_shortcodes( string $content ): string {
		$content = preg_replace_callback(
			'/\[(?:otw_shortcode_button|otw-button)\b([^\]]*)\](.*?)\[\/(?:otw_shortcode_button|otw-button)\]/is',
			function ( array $matches ): string {
				$attributes = $this->legacy_shortcode_attributes( $matches[1] );
				$url = (string) ( $attributes['href'] ?? $attributes['link'] ?? '' );
				return $url ? '<a class="porumbesti-legacy-button" href="' . esc_url( $url ) . '">' . wp_kses_post( $matches[2] ) . '</a>' : $matches[2];
			},
			$content
		) ?? $content;
		$content = preg_replace_callback(
			'/\[(?:button|qode_button)\b([^\]]*)\](?:\[\/(?:button|qode_button)\])?/i',
			function ( array $matches ): string {
				$attributes = $this->legacy_shortcode_attributes( $matches[1] );
				$url = (string) ( $attributes['link'] ?? $attributes['href'] ?? '' );
				$text = (string) ( $attributes['text'] ?? $attributes['title'] ?? $url );
				return $url ? '<a class="porumbesti-legacy-button" href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>' : esc_html( $text );
			},
			$content
		) ?? $content;
		return $content;
	}

	private function legacy_table_cell_markup( string $value ): string {
		$lines = preg_split( '/\R/u', trim( $value ) ) ?: array();
		$markup = array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( preg_match( '/^[^\s@]+@[^\s@]+\.[^\s@]+$/u', $line ) ) {
				$markup[] = '<a href="' . esc_url( 'mailto:' . $line ) . '">' . esc_html( $line ) . '</a>';
			} else {
				$markup[] = esc_html( $line );
			}
		}
		return implode( '<br>', $markup );
	}

	private function legacy_table_fallback( int $table_id ): string {
		$tables = array(
			1 => array(
				'caption' => 'Hivatali részlegek',
				'rows'    => array(
					array( 'Adónyilvántartási szakosztály:', 'Moldovan Charleta Diana', "Tel: 0361525288\ntaxa_si_impozite@primariaporumbesti.ro" ),
					array( 'Mezőgazdasági szakosztály:', 'Zelicskovics Ioan Zoltan', 'agent_agricol@primariaporumbesti.ro' ),
					array( 'Településrendészeti szakosztály:', 'Iakab Gheorghe', 'agent_agricol@primariaporumbesti.ro' ),
					array( 'Szociális szakosztály:', "Csinos Beata-Tunde\nJakab Andrea- Elisabeta", 'asistent_sociala@primariaporumbesti.ro' ),
					array( "Titkár:\nAnyakönyvvezető:", 'Csorba Levente', 'secretar@primariaporumbesti.ro' ),
					array( 'Könyvelőség:', 'Zelicskovics Maria - Simoneta', "Tel: 0361525288\ncontabilitate@primariaporumbesti.ro" ),
					array( 'Katasztrófa védelmi felűgyelő:', 'Iakab Gheorghe', 'isu@primariaporumbesti.ro' ),
					array( "Projekt Vezető:\nTurisztikai vezető", 'Rus Szabina-Maria', 'achizitie_publica@primariaporumbesti.ro' ),
					array( "Projekt Vezető:\nUtazási ügynök", 'Lukacs Annamaria', 'asistent_sociala@primariaporumbesti.ro' ),
				),
			),
			2 => array(
				'caption' => 'Departamente',
				'rows'    => array(
					array( 'Taxe şi Impozite Locale:', 'Moldovan Charleta Diana', "Tel: 0361525288\ntaxa_si_impozite@primariaporumbesti.ro" ),
					array( 'Registru Agricol:', 'Zelicskovics Ioan Zoltan', 'agent_agricol@primariaporumbesti.ro' ),
					array( 'Urbanism:', 'Iakab Gheorghe', 'agent_agricol@primariaporumbesti.ro' ),
					array( 'Asistenţa Socială:', "Csinos Beata-Tunde\nJakab Andrea- Elisabeta", 'asistent_sociala@primariaporumbesti.ro' ),
					array( "Secretar\nStare Civilă", 'Marozsan Andrea', 'secretar@primariaporumbesti.ro' ),
					array( 'Contabilitate:', 'Zelicskovics Maria - Simoneta', "Tel: 0361525288\ncontabilitate@primariaporumbesti.ro" ),
					array( 'SVSU:', 'Iakab Gheorghe', 'isu@primariaporumbesti.ro' ),
					array( "Implementare proiecte:\nGhid turistic", 'Rus Szabina-Maria', 'achizitie_publica@primariaporumbesti.ro' ),
					array( "Implementare proiecte:\nAgent turistic", 'Lukacs Annamaria', 'asistent_sociala@primariaporumbesti.ro' ),
				),
			),
			4 => array(
				'caption' => 'Conducere',
				'rows'    => array(
					array( 'Tóth Zoltán', 'primar' ),
					array( 'Simon Ilie', 'viceprimar' ),
					array( 'Marozsan Andrea', 'secretar' ),
					array( 'Zelicskovics Maria Simoneta', 'consilier superior' ),
					array( 'Iakab Gheorghe', 'inspector protectie civila' ),
					array( 'Csinos Beata Tunde', 'consilier superior' ),
					array( 'Zelicskovics Ioan Zoltan', 'consilier superior' ),
					array( 'Moldovan Diana Charlotte', 'consilier principal' ),
					array( 'Jakab Andrea Elisabeta', 'referent superior' ),
				),
			),
		);

		if ( empty( $tables[ $table_id ] ) ) {
			return '';
		}

		$table = $tables[ $table_id ];
		$html = '<div class="porumbesti-table-wrap"><table class="porumbesti-table"><caption>' . esc_html( $table['caption'] ) . '</caption><tbody>';
		foreach ( $table['rows'] as $row ) {
			$html .= '<tr>';
			foreach ( $row as $cell ) {
				$html .= '<td>' . $this->legacy_table_cell_markup( $cell ) . '</td>';
			}
			$html .= '</tr>';
		}
		return $html . '</tbody></table></div>';
	}

	private function expand_legacy_table_shortcodes( string $content ): string {
		return preg_replace_callback(
			'/\[table\s+id\s*=\s*["\']?(\d+)["\']?[^\]]*\](?:\[\/table\])?/i',
			function ( array $matches ): string {
				if ( function_exists( 'shortcode_exists' ) && shortcode_exists( 'table' ) && function_exists( 'do_shortcode' ) ) {
					$rendered = do_shortcode( $matches[0] );
					if ( '' !== trim( $rendered ) && $matches[0] !== $rendered ) {
						return $rendered;
					}
				}
				return $this->legacy_table_fallback( (int) $matches[1] );
			},
			$content
		) ?? $content;
	}

	private function expand_legacy_raw_html_shortcodes( string $content ): string {
		$content = preg_replace_callback(
			'/\[vc_raw_html\b[^\]]*\](.*?)\[\/vc_raw_html\]/is',
			static function ( array $matches ): string {
				$encoded = preg_replace( '/\s+/', '', html_entity_decode( wp_strip_all_tags( $matches[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
				$decoded = base64_decode( (string) $encoded, true );
				if ( false === $decoded ) {
					return $matches[1];
				}
				return preg_match( '/%[0-9a-f]{2}/i', $decoded ) ? rawurldecode( $decoded ) : $decoded;
			},
			$content
		) ?? $content;

		return $this->expand_legacy_table_shortcodes( $content );
	}

	private function normalize_legacy_content( string $content ): string {
		if ( str_contains( $content, 'porumbesti-header-wrap' ) || str_contains( $content, 'elementor-widget-porumbesti-site-header' ) ) {
			$content = $this->extract_nested_richtext( $content );
		}
		$content = $this->expand_legacy_raw_html_shortcodes( $content );
		$content = $this->expand_legacy_link_shortcodes( $content );
		$content = $this->expand_legacy_media_shortcodes( $content );
		$protected_media = array();
		$content = preg_replace_callback(
			'/\[\/?(?:audio|caption|embed|playlist|video)\b[^\]]*\]/i',
			static function ( array $matches ) use ( &$protected_media ): string {
				$key = '<!--porumbesti-protected-media-' . count( $protected_media ) . '-->';
				$protected_media[ $key ] = $matches[0];
				return $key;
			},
			$content
		) ?? $content;
		$content = preg_replace( '/\[(?:\/?)[a-zA-Z][a-zA-Z0-9_-]*(?:\s[^\]]*)?\]/u', '', $content ) ?? $content;
		$content = strtr( $content, $protected_media );
		$content = preg_replace( '/<h1(\s[^>]*)?>/i', '<h2$1>', $content ) ?? $content;
		$content = preg_replace( '/<\/h1>/i', '</h2>', $content ) ?? $content;
		$content = preg_replace( '/<p>\s*(?:&nbsp;)?\s*<\/p>/i', '', $content ) ?? $content;
		$content = trim( $content );
		$has_media = (bool) preg_match( '/<(?:audio|figure|iframe|img|video)\b/i', $content );
		return '' !== wp_strip_all_tags( $content ) || $has_media ? $content : '<p>Conținutul acestei pagini este în curs de actualizare.</p>';
	}

	private function extract_nested_richtext( string $content ): string {
		if ( ! class_exists( '\DOMDocument' ) || ! class_exists( '\DOMXPath' ) ) {
			return '';
		}

		$previous_errors = libxml_use_internal_errors( true );
		$document = new \DOMDocument();
		$loaded = $document->loadHTML( '<?xml encoding="utf-8" ?><div id="porumbesti-source-root">' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_errors );
		if ( ! $loaded ) {
			return '';
		}

		$xpath = new \DOMXPath( $document );
		$nodes = $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " porumbesti-richtext ") and not(.//*[contains(concat(" ", normalize-space(@class), " "), " porumbesti-richtext ")])]' );
		if ( ! $nodes || 0 === $nodes->length ) {
			return '';
		}

		$node = $nodes->item( $nodes->length - 1 );
		$html = '';
		foreach ( $node->childNodes as $child ) {
			$html .= $document->saveHTML( $child );
		}
		return $html;
	}

	private function legacy_post_queries( string $content ): array {
		if ( ! preg_match_all( '/\[(masonry_blog|latest_post)\b([^\]]*)\]/i', $content, $matches, PREG_SET_ORDER ) ) {
			return array();
		}

		$queries = array();
		foreach ( $matches as $match ) {
			$attributes = $this->legacy_shortcode_attributes( $match[2] );
			$category = trim( (string) ( $attributes['category'] ?? '' ) );
			if ( ! $category ) {
				continue;
			}
			$categories = array_filter( array_map( 'sanitize_title', preg_split( '/\s*,\s*/', $category ) ?: array() ) );
			$count = (int) ( $attributes['number_of_posts'] ?? 0 );
			if ( 'latest_post' === strtolower( $match[1] ) ) {
				$columns = max( 1, (int) ( $attributes['number_of_colums'] ?? $attributes['number_of_columns'] ?? 3 ) );
				$rows = max( 1, (int) ( $attributes['number_of_rows'] ?? 1 ) );
				$count = $columns * $rows;
			}
			$queries[] = array(
				'category'     => implode( ',', $categories ),
				'count'        => $count ?: 6,
				'columns'      => min( 4, max( 2, (int) ( $attributes['number_of_colums'] ?? $attributes['number_of_columns'] ?? 3 ) ) ),
				'orderby'      => strtolower( (string) ( $attributes['order_by'] ?? 'date' ) ),
				'show_excerpt' => 'yes',
			);
		}
		return $queries;
	}

	private function legacy_category_slug( string $content ): string {
		$queries = $this->legacy_post_queries( $content );
		return (string) ( $queries[0]['category'] ?? '' );
	}

	private function gallery_items( \WP_Post $page, string $category_slug, string $source_content = '' ): array {
		$items = $this->legacy_media_items( $page, $source_content );
		$seen = array();
		foreach ( $items as $item ) {
			$id = (int) ( $item['image']['id'] ?? 0 );
			$url = (string) ( $item['image']['url'] ?? '' );
			$seen[ $id ? 'id:' . $id : 'url:' . strtolower( $url ) ] = true;
		}
		if ( $category_slug ) {
			$category = get_category_by_slug( $category_slug );
			if ( $category ) {
				foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 100, 'category' => $category->term_id ) ) as $post ) {
					$thumbnail_id = (int) get_post_thumbnail_id( $post->ID );
					if ( $thumbnail_id ) {
						$item = $this->media_item_from_id( $thumbnail_id, 'category' );
						if ( $item && ! isset( $seen[ 'id:' . $thumbnail_id ] ) ) {
							$seen[ 'id:' . $thumbnail_id ] = true;
							$items[] = $item;
						}
						continue;
					}
					foreach ( get_attached_media( 'image', $post->ID ) as $attachment ) {
						$item = $attachment instanceof \WP_Post ? $this->media_item_from_id( $attachment->ID, 'category' ) : null;
						if ( $item && ! isset( $seen[ 'id:' . $attachment->ID ] ) ) {
							$seen[ 'id:' . $attachment->ID ] = true;
							$items[] = $item;
						}
					}
				}
			}
		}
		return $this->public_media_items( $items );
	}

	private function recent_updates( string $language, int $count = 3 ): array {
		$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => max( 1, $count * 3 ), 'orderby' => 'date', 'order' => 'DESC', 'suppress_filters' => false, 'lang' => $language ) );
		$items = array();
		foreach ( $posts as $post ) {
			$post_language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post->ID, 'slug' ) : '';
			$path = (string) wp_parse_url( get_permalink( $post ), PHP_URL_PATH );
			if ( $post_language && $language !== $post_language ) {
				continue;
			}
			if ( ! $post_language && ! str_contains( $path, '/' . $language . '/' ) ) {
				continue;
			}
			$items[] = array( 'day' => get_the_date( 'd', $post ), 'title' => get_the_title( $post ), 'meta' => get_the_date( 'Y. m. d.', $post ), 'url' => $this->link( get_permalink( $post ) ) );
			if ( count( $items ) >= $count ) {
				break;
			}
		}
		return $this->repeater( 'updates-' . $language, $items );
	}

	private function interface_copy( string $language ): array {
		if ( 'hu' === $language ) {
			return array(
				'official' => 'Kökényesd Község Polgármesteri Hivatalának hivatalos oldala, Szatmár megye, Románia', 'trust' => 'Biztonságos kapcsolat',
				'brand_subtitle' => 'Kökényesd Község Polgármesteri Hivatala', 'cta' => 'Helyi hivatalos közlöny', 'home' => 'Kezdőlap',
				'skip' => 'Ugrás a tartalomhoz', 'language' => 'Nyelvválasztás', 'nav' => 'Fő navigáció', 'search' => 'Keresés',
				'menu_open' => 'Menü megnyitása', 'menu_close' => 'Menü bezárása', 'submenu' => '%s almenüjének megnyitása',
				'search_title' => 'Keresés a portálon', 'search_placeholder' => 'Dokumentumok, felhívások és szolgáltatások keresése…', 'search_button' => 'Keresés', 'close' => 'Bezárás',
				'footer_description' => 'Hivatalos portál ügyintézéshez, közérdekű dokumentumokhoz és önkormányzati tájékoztatáshoz.',
				'office' => 'Polgármesteri Hivatal', 'leadership' => 'Vezetőség', 'council' => 'Helyi tanács', 'public' => 'Közérdekű', 'announcements' => 'Felhívások', 'monitor' => 'Helyi hivatalos közlöny',
				'contact' => 'Elérhetőség', 'footer_nav' => 'Lábléc hivatkozások', 'back_top' => 'Vissza az oldal tetejére', 'copyright' => 'Minden jog fenntartva, Kökényesd Község.',
				'address' => 'Románia, Szatmár megye, Kökényesd község, Kökényesd, 17C., 447152',
				'accessibility' => 'Akadálymentesítés', 'text_size' => 'Szövegméret', 'contrast' => 'Nagy kontraszt', 'grayscale' => 'Szürkeárnyalat', 'underline' => 'Hivatkozások aláhúzása', 'reset' => 'Beállítások visszaállítása', 'options' => 'Akadálymentesítési beállítások',
				'empty_posts' => 'Ehhez a válogatáshoz jelenleg nincs közzétett bejegyzés.', 'read_more' => 'Tovább →', 'all' => 'Összes',
			);
		}
		return array(
			'official' => 'Site oficial al Primăriei Comunei Porumbești, județul Satu Mare, România', 'trust' => 'Conexiune securizată',
			'brand_subtitle' => 'Primăria · Kökényesd Község', 'cta' => 'Monitorul Oficial', 'home' => 'Acasă',
			'skip' => 'Sari la conținut', 'language' => 'Alege limba', 'nav' => 'Navigație principală', 'search' => 'Caută',
			'menu_open' => 'Deschide meniul', 'menu_close' => 'Închide meniul', 'submenu' => 'Deschide submeniul pentru %s',
			'search_title' => 'Căutare în portal', 'search_placeholder' => 'Căutați documente, anunțuri, servicii…', 'search_button' => 'Caută', 'close' => 'Închide',
			'footer_description' => 'Portal oficial modernizat pentru cetățeni, documente publice și comunicări administrative.',
			'office' => 'Primăria', 'leadership' => 'Conducere', 'council' => 'Consiliul Local', 'public' => 'Informații publice', 'announcements' => 'Anunțuri', 'monitor' => 'Monitorul Oficial',
			'contact' => 'Contact', 'footer_nav' => 'Linkuri subsol', 'back_top' => 'Înapoi sus', 'copyright' => 'Toate drepturile rezervate Comuna Porumbești.',
			'address' => 'România, jud. Satu Mare, com. Porumbești, sat Porumbești, nr. 17C, cod 447152',
			'accessibility' => 'Accesibilitate', 'text_size' => 'Mărime text', 'contrast' => 'Contrast ridicat', 'grayscale' => 'Tonuri de gri', 'underline' => 'Linkuri subliniate', 'reset' => 'Resetează setările', 'options' => 'Opțiuni de accesibilitate',
			'empty_posts' => 'Nu există articole publicate pentru această selecție.', 'read_more' => 'Citește mai mult →', 'all' => 'Toate',
		);
	}

	private function header_settings( \WP_Post $page, string $language, array $routes, string $seed ): array {
		$copy = $this->interface_copy( $language );
		$other_language = 'hu' === $language ? 'ro' : 'hu';
		$other_url = $this->translated_url( $page->ID, $other_language, $routes[ 'home_' . $other_language ] );
		$language_items = 'hu' === $language
			? array( array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( get_permalink( $page ) ) ), array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( $other_url ) ) )
			: array( array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( get_permalink( $page ) ) ), array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( $other_url ) ) );
		return array(
			'official_text' => $copy['official'], 'trust_text' => is_ssl() ? $copy['trust'] : '', 'mail_url' => $this->link( self::WEBMAIL_URL ), 'logo' => $this->brand_logo(),
			'brand_title' => 'hu' === $language ? 'Kökényesd Község' : 'Comuna Porumbești', 'brand_subtitle' => $copy['brand_subtitle'], 'home_url' => $this->link( $routes[ 'home_' . $language ] ), 'menu_id' => $this->menu_id( $language ),
			'cta_text' => $copy['cta'], 'cta_link' => $this->link( $routes['monitor'] ), 'porumbesti_sticky' => 'yes', 'language_items' => $this->repeater( $seed . '-languages', $language_items ),
			'skip_label' => $copy['skip'], 'language_label' => $copy['language'], 'nav_label' => $copy['nav'], 'search_label' => $copy['search'],
			'menu_open_label' => $copy['menu_open'], 'menu_close_label' => $copy['menu_close'], 'submenu_label' => $copy['submenu'],
		);
	}

	private function search_settings( string $language ): array {
		$copy = $this->interface_copy( $language );
		return array( 'title' => $copy['search_title'], 'placeholder' => $copy['search_placeholder'], 'button_text' => $copy['search_button'], 'close_label' => $copy['close'], 'language' => $language, 'modal' => 'yes' );
	}

	private function footer_settings( string $language, array $routes, string $seed ): array {
		$copy = $this->interface_copy( $language );
		$is_hungarian = 'hu' === $language;
		$links = array(
			array( 'column' => $copy['office'], 'label' => $copy['leadership'], 'url' => $this->link( $routes['mayor'] ) ),
			array( 'column' => $copy['office'], 'label' => $copy['council'], 'url' => $this->link( $routes['council'] ) ),
			array( 'column' => $copy['public'], 'label' => $copy['announcements'], 'url' => $this->link( $routes['announcements'] ) ),
			array( 'column' => $copy['public'], 'label' => $copy['monitor'], 'url' => $this->link( $routes['monitor'] ) ),
		);
		if ( 'ro' === $language ) {
			array_splice( $links, 1, 0, array( array( 'column' => $copy['office'], 'label' => 'Departamente', 'url' => $this->link( $routes['departments'] ) ) ) );
			array_splice( $links, 4, 0, array( array( 'column' => $copy['public'], 'label' => 'Formulare tipizate', 'url' => $this->link( $routes['forms'] ) ) ) );
		}
		$external_links = array(
			array( 'label' => 'Facebook · Kökényesd', 'url' => $this->link( self::FACEBOOK_URL ) ),
			array( 'label' => $is_hungarian ? 'Online ügyintézési portál' : 'Portal online', 'url' => $this->link( self::PORTAL_URL ) ),
			array( 'label' => $is_hungarian ? 'Átlátható kormányzás · SGG' : 'Guvernare transparentă · SGG', 'url' => $this->link( self::TRANSPARENCY_URL ) ),
			array( 'label' => $is_hungarian ? 'Hivatal a térképen' : 'Primăria pe hartă', 'url' => $this->link( self::MAP_URL ) ),
		);
		return array(
			'title' => 'hu' === $language ? 'Kökényesd Község' : 'Comuna Porumbești', 'subtitle' => $copy['brand_subtitle'], 'description' => $copy['footer_description'],
			'links' => $this->repeater( $seed . '-links', $links ),
			'phone' => '0361 525 288', 'email' => 'primar@primariaporumbesti.ro', 'address' => $copy['address'], 'copyright' => $copy['copyright'],
			'contact_url' => $this->link( $routes['contact'] ), 'monitor_url' => $this->link( $routes['monitor'] ), 'contact_title' => $copy['contact'],
			'contact_link_text' => $copy['contact'], 'monitor_link_text' => $copy['monitor'], 'footer_nav_label' => $copy['footer_nav'], 'back_to_top_label' => $copy['back_top'],
			'external_title' => $is_hungarian ? 'Hivatalos hivatkozások' : 'Resurse oficiale', 'external_links' => $this->repeater( $seed . '-external-links', $external_links ),
		);
	}

	private function accessibility_settings( string $language ): array {
		$copy = $this->interface_copy( $language );
		return array( 'title' => $copy['accessibility'], 'position' => 'right', 'text_size_label' => $copy['text_size'], 'contrast_label' => $copy['contrast'], 'grayscale_label' => $copy['grayscale'], 'underline_label' => $copy['underline'], 'reset_label' => $copy['reset'], 'options_label' => $copy['options'], 'back_to_top_label' => $copy['back_top'] );
	}

	private function content_label_key( string $content ): string {
		$content = html_entity_decode( wp_strip_all_tags( $content ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$content = preg_replace( '/^[\s\p{Pd}:]+|[\s\p{Pd}:]+$/u', '', str_replace( "\xc2\xa0", ' ', $content ) ) ?? '';
		$content = preg_replace( '/\s+/u', ' ', $content ) ?? $content;
		$content = function_exists( 'remove_accents' ) ? remove_accents( $content ) : $content;
		return strtolower( trim( $content ) );
	}

	private function content_without_duplicate_headings( string $content, array $labels ): string {
		$keys = array_values( array_filter( array_map( fn( string $label ): string => $this->content_label_key( $label ), $labels ) ) );
		$content = preg_replace_callback(
			'/<h([1-6])\b[^>]*>(.*?)<\/h\1>/is',
			function ( array $matches ) use ( $keys ): string {
				$key = $this->content_label_key( $matches[2] );
				return '' === $key || in_array( $key, $keys, true ) ? '' : $matches[0];
			},
			$content
		) ?? $content;
		return trim( preg_replace( '/<p\b[^>]*>\s*(?:&nbsp;|\x{00a0})?\s*<\/p>/iu', '', $content ) ?? $content );
	}

	private function media_url_key( string $url ): string {
		$path = (string) parse_url( html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), PHP_URL_PATH );
		$name = strtolower( rawurldecode( basename( $path ) ) );
		return preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $name ) ?? $name;
	}

	private function markup_uses_media( string $markup, array $media ): bool {
		$id = (int) ( $media['id'] ?? 0 );
		if ( $id && preg_match( '/\bwp-image-' . $id . '\b/i', $markup ) ) {
			return true;
		}
		$key = $this->media_url_key( (string) ( $media['url'] ?? '' ) );
		if ( '' === $key || ! preg_match_all( '/(?:src|data-src)\s*=\s*(["\'])(.*?)\1/is', $markup, $matches ) ) {
			return false;
		}
		foreach ( $matches[2] as $url ) {
			if ( $key === $this->media_url_key( $url ) ) {
				return true;
			}
		}
		return false;
	}

	private function content_without_reused_media( string $content, array $media ): string {
		if ( empty( $media['id'] ) && empty( $media['url'] ) ) {
			return $content;
		}
		$content = preg_replace_callback(
			'/<figure\b[^>]*>.*?<\/figure>/is',
			fn( array $matches ): string => $this->markup_uses_media( $matches[0], $media ) ? '' : $matches[0],
			$content
		) ?? $content;
		$content = preg_replace_callback(
			'/<img\b[^>]*>/is',
			fn( array $matches ): string => $this->markup_uses_media( $matches[0], $media ) ? '' : $matches[0],
			$content
		) ?? $content;
		return trim( preg_replace( '/<a\b[^>]*>\s*<\/a>/is', '', $content ) ?? $content );
	}

	private function integrated_profile_content( string $content, array $media, array $duplicate_labels ): string {
		$content = $this->content_without_reused_media( $content, $media );
		$content = $this->content_without_duplicate_headings( $content, $duplicate_labels );
		$content = preg_replace_callback(
			'/<h[1-6]\b[^>]*>(.*?)<\/h[1-6]>/is',
			static function ( array $matches ): string {
				$value = preg_replace( '/^\s*[\p{Pd}]\s*/u', '', $matches[1] ) ?? $matches[1];
				return '<p class="porumbesti-profile-fact">' . $value . '</p>';
			},
			$content
		) ?? $content;
		return trim( $content );
	}

	private function mayor_message_from_source( string $normalized_content, string $language ): string {
		$start_marker = 'hu' === $language ? 'Megtiszteltetés számomra' : 'În calitate de primar';
		$stop_markers = 'hu' === $language
			? array( 'Tóth Zoltán', 'Vezetőség', 'Polgármester', 'Alpolgármester', 'Jegyző' )
			: array( 'Tóth Zoltán', 'Conducere', 'Primar', 'Viceprimar', 'Secretar' );
		if ( ! preg_match_all( '/<(?:h[1-6]|p)\b[^>]*>(.*?)<\/(?:h[1-6]|p)>/is', $normalized_content, $matches ) ) {
			return '';
		}
		$started = false;
		$paragraphs = array();
		foreach ( $matches[1] as $markup ) {
			$value = html_entity_decode( wp_strip_all_tags( $markup ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			$value = preg_replace( '/\s+/u', ' ', trim( $value ) ) ?? trim( $value );
			if ( ! $value ) {
				continue;
			}
			if ( ! $started ) {
				if ( ! str_contains( $value, $start_marker ) ) {
					continue;
				}
				$started = true;
			}
			foreach ( $stop_markers as $stop_marker ) {
				if ( str_starts_with( $value, $stop_marker ) ) {
					$started = false;
					break 2;
				}
			}
			$paragraphs[] = '<p class="porumbesti-mayor-quote">' . esc_html( $value ) . '</p>';
		}
		if ( ! $paragraphs ) {
			return '';
		}
		$author = 'hu' === $language ? 'Tóth Zoltán · Polgármester' : 'Tóth Zoltán · Primar';
		$paragraphs[] = '<p><strong>' . esc_html( $author ) . '</strong></p>';
		return implode( '', $paragraphs );
	}

	private function homepage_mayor_message( \WP_Post $page, string $language ): string {
		$source = $this->normalize_legacy_content( $this->original_page_content( $page ) );
		$message = $this->mayor_message_from_source( $source, $language );
		if ( '' !== $message ) {
			return $message;
		}
		if ( 'hu' === $language ) {
			return '<p class="porumbesti-mayor-quote">Kökényesd község polgármestereként szeretettel köszöntöm honlapunk látogatóit. Célunk, hogy a helyi közigazgatás által kezelt információkat nyitottan, átláthatóan és könnyen elérhetően tegyük közzé.</p><p><strong>Tóth Zoltán · Polgármester</strong></p>';
		}
		return '<p class="porumbesti-mayor-quote">În calitate de primar al comunei Porumbești, adresez un sincer bun venit tuturor celor ce au accesat acest site. Dorim să oferim informația produsă și gestionată de administrația publică locală într-un mod deschis și transparent.</p><p><strong>Tóth Zoltán · Primar</strong></p>';
	}

	private function original_content_sections( \WP_Post $page, string $language, string $seed ): array {
		$source_content = $this->original_page_content( $page );
		$normalized_content = $this->normalize_legacy_content( $source_content );
		$normalized_content = $this->content_without_duplicate_headings( $normalized_content, array( get_the_title( $page ) ) );
		$has_visible_content = '' !== trim( wp_strip_all_tags( $normalized_content ) )
			|| (bool) preg_match( '/<(?:a|audio|figure|iframe|img|table|video)\b/i', $normalized_content );
		if ( ! $has_visible_content ) {
			return array();
		}

		$is_hungarian = 'hu' === $language;
		return array(
			$this->container(
				$seed . '-additional-content',
				array(
					$this->widget( $seed . '-additional-content-widget', 'porumbesti-content-media', array(
						'kicker'      => $is_hungarian ? 'Teljes körű tájékoztatás' : 'Informații complete',
						'title'       => $is_hungarian ? 'Részletek és dokumentumok' : 'Detalii și documente',
						'description' => '',
						'content'     => $normalized_content,
						'image'       => array( 'id' => 0, 'url' => '' ),
						'image_side'  => 'right',
					) ),
				),
				array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) )
			),
		);
	}

	private function specialized_ro_page_data( \WP_Post $page ): array {
		$identity = strtolower( remove_accents( $page->post_name . ' ' . $page->post_title ) );
		return match ( true ) {
			(bool) preg_match( '/istoria-comunei|prezentarea-comunei-porumbesti/', $identity ) => $this->comuna_ro_data( $page ),
			(bool) preg_match( '/componenta-consiliului-local/', $identity ) => $this->council_ro_data( $page ),
			(bool) preg_match( '/formulare-tipizate/', $identity ) => $this->public_info_ro_data( $page ),
			(bool) preg_match( '/monitorul-oficial-local-2|monitorul-oficial-local/', $identity ) => $this->monitor_ro_data( $page ),
			(bool) preg_match( '/contact/', $identity ) => $this->contact_ro_data( $page ),
			default => array(),
		};
	}

	private function redesign_page_start( \WP_Post $page, string $seed, string $title, string $description, string $kicker, array $background ): array {
		$routes = $this->routes();
		return array(
			$this->container( $seed . '-header', array(
				$this->widget( $seed . '-header-widget', 'porumbesti-site-header', $this->header_settings( $page, 'ro', $routes, $seed . '-header' ) ),
				$this->widget( $seed . '-search-widget', 'porumbesti-search-box', $this->search_settings( 'ro' ) ),
			), array( 'content_width' => 'full' ) ),
			$this->container( $seed . '-hero', array(
				$this->widget( $seed . '-hero-widget', 'porumbesti-page-hero', array(
					'kicker' => $kicker,
					'title' => $title,
					'description' => $description,
					'background' => $background,
					'parent_label' => 'Acasă',
					'parent_link' => $this->link( $routes['home_ro'] ),
					'current_label' => $title,
				) ),
			), array( 'content_width' => 'full' ) ),
		);
	}

	private function redesign_page_end( string $seed ): array {
		$routes = $this->routes();
		return $this->container( $seed . '-footer', array(
			$this->widget( $seed . '-footer-widget', 'porumbesti-site-footer', $this->footer_settings( 'ro', $routes, $seed . '-footer' ) ),
			$this->widget( $seed . '-accessibility-widget', 'porumbesti-accessibility', $this->accessibility_settings( 'ro' ) ),
		), array( 'content_width' => 'full' ) );
	}

	private function comuna_ro_data( \WP_Post $page ): array {
		$seed = 'comuna-' . $page->ID;
		$routes = $this->routes();
		$page_title = get_the_title( $page );
		$source = $this->normalize_legacy_content( $this->original_page_content( $page ) );
		$data = $this->redesign_page_start(
			$page,
			$seed,
			$page_title,
			'O pagină nativă pentru istorie, localizare, monumente, personalități, sport și turism.',
			'',
			$this->design_media( '2018/07/hatter-13.jpg', 'comuna-page-hero' )
		);
		$data[] = $this->container( $seed . '-identity', array(
			$this->widget( $seed . '-identity-widget', 'porumbesti-content-media', array(
				'kicker' => 'Identitate locală',
				'title' => 'Istoria comunei',
				'description' => '',
				'content' => '<p>Comuna Porumbești păstrează o identitate locală puternică, cu tradiții românești și maghiare vizibile în viața comunității.</p>' . $source,
				'image' => $this->media(),
				'image_side' => 'right',
			) ),
		), array( 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->section( $seed . '-location-heading', 'Localizare', 'Porumbești, județul Satu Mare', '', 'Vezi adresa primăriei', $routes['contact'], 'light', '#ffffff' );
		$data[] = $this->container( $seed . '-location', array(
			$this->widget( $seed . '-location-widget', 'porumbesti-services-grid', array(
				'columns' => '3',
				'items_list' => $this->repeater( $seed . '-location-items', array(
					array( 'icon' => 'SM', 'title' => 'Județ', 'description' => 'Comuna se află în județul Satu Mare, în nord-vestul României.', 'url' => $this->link( $routes['location'] ) ),
					array( 'icon' => '447152', 'title' => 'Cod poștal', 'description' => 'Codul administrativ folosit în datele de contact este 447152.', 'url' => $this->link( $routes['contact'] ) ),
					array( 'icon' => '17C', 'title' => 'Sediu', 'description' => 'Primăria funcționează în satul Porumbești, nr. 17C.', 'url' => $this->link( $routes['contact'] ) ),
				) ),
			) ),
		), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->section( $seed . '-culture-heading', 'Comunitate', 'Istorie, prezentare și sport local' );
		$data[] = $this->container( $seed . '-culture', array(
			$this->widget( $seed . '-culture-widget', 'porumbesti-services-grid', array(
				'columns' => '4',
				'items_list' => $this->repeater( $seed . '-culture-items', array(
					array( 'icon' => 'IST', 'title' => 'Istoria comunei', 'description' => 'Conținutul istoric publicat pe site.', 'url' => $this->link( $routes['history'] ) ),
					array( 'icon' => 'PRE', 'title' => 'Prezentarea comunei', 'description' => 'Informații generale despre localitate.', 'url' => $this->link( $routes['location'] ) ),
					array( 'icon' => 'UG', 'title' => 'Asociația Sportivă Ugocea', 'description' => 'Istoria și activitatea sportului local.', 'url' => $this->link( $routes['tourism'] ) ),
					array( 'icon' => 'FOT', 'title' => 'Galeria foto', 'description' => 'Imagini publicate de comunitate.', 'url' => $this->link( $routes['galleries'] ) ),
				) ),
			) ),
		), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->section( $seed . '-tourism-heading', 'Sport local', 'Asociația Sportivă Ugocea Porumbești', '', '', '', 'light', '#ffffff' );
		$data[] = $this->container( $seed . '-tourism', array(
			$this->widget( $seed . '-tourism-widget', 'porumbesti-services-grid', array(
				'columns' => '3',
				'items_list' => $this->repeater( $seed . '-tourism-items', array(
					array( 'icon' => 'UG', 'title' => 'Istoria echipei', 'description' => 'Povestea comunității sportive locale.', 'url' => $this->link( $routes['tourism'] ) ),
					array( 'icon' => 'CL', 'title' => 'Comunitate', 'description' => 'Activități și inițiative pentru locuitori.', 'url' => $this->link( $routes['history'] ) ),
					array( 'icon' => 'FOT', 'title' => 'Imagini locale', 'description' => 'Galeria foto publicată pe site.', 'url' => $this->link( $routes['galleries'] ) ),
				) ),
			) ),
		), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->redesign_page_end( $seed );
		return $data;
	}

	private function council_ro_data( \WP_Post $page ): array {
		$seed = 'council-' . $page->ID;
		$routes = $this->routes();
		$data = $this->redesign_page_start(
			$page,
			$seed,
			'Consiliul Local',
			'Componența Consiliului Local al Comunei Porumbești · Mandat 2024–2028 · 11 membri.',
			'',
			$this->design_media( '2018/07/hatter-13.jpg', 'council-page-hero' )
		);
		$data[] = $this->section( $seed . '-members-heading', 'Mandat 2024–2028', 'Componența consiliului' );
		$data[] = $this->container( $seed . '-members', array(
			$this->widget( $seed . '-members-widget', 'porumbesti-council-members', array(
				'columns' => '3',
				'items_list' => $this->repeater( $seed . '-member-items', array(
					array( 'name' => 'Nița Nicolae', 'party' => 'PNL', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Móra Botond', 'party' => 'AMT', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Márton Zsuzsanna', 'party' => 'AMT', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Deák Zoltán', 'party' => 'AMT', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Barbos Samuel', 'party' => 'PFD', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Kósa Krisztián', 'party' => 'UDMR', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Dolhai Andrei', 'party' => 'UDMR', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Jager Elisabeta-Edith', 'party' => 'UDMR', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Tatar Georghe', 'party' => 'PNL', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Docsa Gábor', 'party' => 'UDMR', 'role' => 'Consilier local', 'email' => '' ),
					array( 'name' => 'Papp Réka Izabella', 'party' => 'UDMR', 'role' => 'Consilier local', 'email' => '' ),
				) ),
			) ),
		), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '40', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->container( $seed . '-composition', array(
			$this->widget( $seed . '-composition-widget', 'porumbesti-stats-bars', array(
				'kicker' => 'Componență pe partide', 'title' => 'UDMR 5 · AMT 3 · PNL 2 · PFD 1', 'description' => '',
				'items_list' => $this->repeater( $seed . '-composition-items', array(
					array( 'label' => 'UDMR', 'value' => 45, 'display_value' => '5', 'suffix' => '', 'color' => '#123b5d' ),
					array( 'label' => 'AMT', 'value' => 27, 'display_value' => '3', 'suffix' => '', 'color' => '#b85c42' ),
					array( 'label' => 'PNL', 'value' => 18, 'display_value' => '2', 'suffix' => '', 'color' => '#1d4ed8' ),
					array( 'label' => 'PFD', 'value' => 9, 'display_value' => '1', 'suffix' => '', 'color' => '#b45309' ),
				) ),
			) ),
			$this->widget( $seed . '-links-widget', 'porumbesti-link-list', array(
				'title' => 'Documente utile',
				'items_list' => $this->repeater( $seed . '-link-items', array(
					array( 'icon' => 'CL', 'label' => 'Componenta consiliului local', 'meta' => 'Pagina curentă', 'url' => $this->link( get_permalink( $page ) ) ),
					array( 'icon' => 'MOL', 'label' => 'Hotărâri autoritatea deliberativă', 'meta' => 'Monitorul Oficial Local', 'url' => $this->link( $routes['deliberative'] ) ),
					array( 'icon' => 'DA', 'label' => 'Declarații de avere și interese', 'meta' => 'Transparență', 'url' => $this->link( home_url( '/ro/category/declaratie-de-avere/' ) ) ),
				) ),
			) ),
		), array( 'gap' => array( 'unit' => 'px', 'size' => 32, 'sizes' => array() ), 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->section( $seed . '-decisions-heading', 'Arhivă actualizată', 'Hotărâri recente publicate online', '', 'Arhiva hotărârilor', $routes['decisions'], 'light', '#ffffff' );
		$data[] = $this->container( $seed . '-decisions', array(
			$this->widget( $seed . '-decisions-widget', 'porumbesti-news-grid', array(
				'post_type' => 'post', 'category' => 'hotarari-ale-consiului-local-ro', 'language' => 'ro', 'count' => 3, 'columns' => '3', 'orderby' => 'date',
				'show_excerpt' => 'yes', 'show_category' => 'yes', 'show_date' => 'yes', 'empty_text' => 'Nu există hotărâri publicate pentru această selecție.', 'read_more_text' => 'Vezi documentul →',
			) ),
		), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data = array_merge( $data, $this->original_content_sections( $page, 'ro', $seed ) );
		$data[] = $this->redesign_page_end( $seed );
		return $data;
	}

	private function public_info_ro_data( \WP_Post $page ): array {
		$seed = 'public-info-' . $page->ID;
		$source = $this->normalize_legacy_content( $this->original_page_content( $page ) );
		$source = str_replace(
			'/ro/formulare-tipizate/acordarea-indemizatiei-de-crestere-a-copilului/',
			'/wp-content/uploads/2016/11/acordarea-indemizatiei-de-crestere-a-copilului.pdf',
			$source
		);
		$data = $this->redesign_page_start(
			$page,
			$seed,
			'Informații publice',
			'Anunțuri, formulare, bugete, dare de seamă, Legea 17, APIA și arhive publice.',
			'',
			$this->design_media( '2018/07/hatter-13.jpg', 'public-info-page-hero' )
		);
		$data[] = $this->section( $seed . '-library-heading', 'Documente', 'Bibliotecă publică' );
		$data[] = $this->container( $seed . '-library', array(
			$this->widget( $seed . '-library-widget', 'porumbesti-document-grid', array(
				'columns' => '3', 'filters' => 'yes', 'all_label' => 'Toate',
				'items_list' => $this->repeater( $seed . '-document-items', array(
					array( 'icon' => 'AN', 'title' => 'Anunțuri', 'meta' => 'Comunicări curente', 'category' => 'Informații', 'url' => $this->link( $this->routes()['announcements'] ) ),
					array( 'icon' => 'FT', 'title' => 'Formulare tipizate', 'meta' => 'Cereri și formulare', 'category' => 'Formulare', 'url' => $this->link( $this->routes()['forms'] ) ),
					array( 'icon' => 'TEL', 'title' => 'Telefoane utile', 'meta' => 'Date publice de contact', 'category' => 'Informații', 'url' => $this->link( $this->routes()['phones'] ) ),
					array( 'icon' => 'BUG', 'title' => 'Buget', 'meta' => 'Documente financiare', 'category' => 'Buget', 'url' => $this->link( home_url( '/ro/category/buget/' ) ) ),
					array( 'icon' => 'DS', 'title' => 'Dare de seamă', 'meta' => 'Arhivă publică', 'category' => 'Arhivă', 'url' => $this->link( home_url( '/ro/category/dare-de-seama/' ) ) ),
					array( 'icon' => 'DA', 'title' => 'Declarații de avere', 'meta' => 'Transparență', 'category' => 'Declarații', 'url' => $this->link( home_url( '/ro/category/declaratie-de-avere/' ) ) ),
					array( 'icon' => 'PC', 'title' => 'Publicații de căsătorie 2026', 'meta' => 'Arhivă publică', 'category' => 'Publicații', 'url' => $this->link( home_url( '/ro/category/publicatii-de-casatorie-2026/' ) ) ),
					array( 'icon' => 'LEG', 'title' => 'Legislație', 'meta' => 'Acte normative', 'category' => 'Informații', 'url' => $this->link( $this->routes()['legislation'] ) ),
					array( 'icon' => 'POAD', 'title' => 'POAD', 'meta' => 'Documente publice', 'category' => 'Arhivă', 'url' => $this->link( home_url( '/ro/category/poad/' ) ) ),
					array( 'icon' => 'MOL', 'title' => 'Monitorul Oficial Local', 'meta' => 'Registre oficiale', 'category' => 'Monitor', 'url' => $this->link( $this->routes()['monitor'] ) ),
				) ),
			) ),
		), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->container( $seed . '-forms', array(
			$this->widget( $seed . '-forms-widget', 'porumbesti-content-media', array(
				'kicker' => 'Formulare', 'title' => 'Formulare tipizate', 'description' => 'Cereri, formulare și legături utile, organizate în noua structură.',
				'content' => $source, 'image' => $this->media(), 'image_side' => 'right',
			) ),
		), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->section( $seed . '-other-heading', 'Alte secțiuni utile', 'Galerie, firme și legislație' );
		$data[] = $this->container( $seed . '-other', array(
			$this->widget( $seed . '-other-widget', 'porumbesti-services-grid', array(
				'columns' => '3',
				'items_list' => $this->repeater( $seed . '-other-items', array(
					array( 'icon' => 'GF', 'title' => 'Galeria foto', 'description' => 'Arhiva foto publicată pe site.', 'url' => $this->link( $this->routes()['galleries'] ) ),
					array( 'icon' => 'AN', 'title' => 'Anunțuri', 'description' => 'Comunicări actuale pentru locuitori.', 'url' => $this->link( $this->routes()['announcements'] ) ),
					array( 'icon' => 'LEG', 'title' => 'Legislație', 'description' => 'Documente legislative și trimiteri utile.', 'url' => $this->link( $this->routes()['legislation'] ) ),
				) ),
			) ),
		), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->redesign_page_end( $seed );
		return $data;
	}

	private function monitor_ro_data( \WP_Post $page ): array {
		$seed = 'monitor-' . $page->ID;
		$data = $this->redesign_page_start(
			$page,
			$seed,
			'Monitorul Oficial Local',
			'Acces organizat la documentele publice obligatorii ale autorității locale.',
			'',
			$this->design_media( '2018/07/hatter-13.jpg', 'monitor-page-hero' )
		);
		$data[] = $this->section( $seed . '-categories-heading', 'Categorii oficiale', 'Documente publice' );
		$data[] = $this->container( $seed . '-categories', array(
			$this->widget( $seed . '-categories-widget', 'porumbesti-services-grid', array(
				'columns' => '3',
				'items_list' => $this->repeater( $seed . '-category-items', array(
					array( 'icon' => 'UAT', 'title' => 'Statutul Unității Administrativ-Teritoriale', 'description' => 'Documentele de bază ale UAT Comuna Porumbești.', 'url' => $this->link( $this->page_url( array( 'statutul-unitatii-administrativ-teritoriale' ), $this->routes()['monitor'] ) ) ),
					array( 'icon' => 'REG', 'title' => 'Regulamente privind procedurile administrative', 'description' => 'Reguli și proceduri administrative locale.', 'url' => $this->link( $this->page_url( array( 'regulamentele-privind-procedurile-administrative' ), $this->routes()['monitor'] ) ) ),
					array( 'icon' => 'HCL', 'title' => 'Hotărârile autorității deliberative', 'description' => 'Hotărâri ale Consiliului Local.', 'url' => $this->link( $this->routes()['deliberative'] ) ),
					array( 'icon' => 'DIS', 'title' => 'Dispozițiile autorității executive', 'description' => 'Dispoziții ale autorității executive locale.', 'url' => $this->link( $this->routes()['executive'] ) ),
					array( 'icon' => 'FIN', 'title' => 'Documente și informații financiare', 'description' => 'Buget, execuții și date financiare publice.', 'url' => $this->link( $this->routes()['financial'] ) ),
					array( 'icon' => 'DOC', 'title' => 'Alte documente', 'description' => 'Documente publice din categoria oficială existentă.', 'url' => $this->link( home_url( '/ro/category/alte-documente/' ) ) ),
				) ),
			) ),
		), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->section( $seed . '-registers-heading', 'Arhivă dinamică', 'Registre, hotărâri și documente publicate', '', '', '', 'light', '#ffffff' );
		$data[] = $this->container( $seed . '-registers', array(
			$this->widget( $seed . '-registers-widget', 'porumbesti-document-library', array(
				'kicker' => 'Monitorul Oficial Local', 'title' => 'Documente publicate', 'description' => '', 'source' => 'legacy_posts',
				'legacy_keywords' => 'hotarari,dispozitii,documente,financiare,alte-documente,statut,regulamente', 'count' => 18, 'columns' => '3', 'show_filters' => 'yes', 'show_search' => 'yes',
			) ),
		), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->section( $seed . '-recent-heading', 'Noutăți utile', 'Hotărâri 2026 publicate recent', '', 'Vezi detalii', $this->routes()['council'], 'dark', '#172c3a' );
		$data[] = $this->container( $seed . '-recent', array(
			$this->widget( $seed . '-recent-widget', 'porumbesti-news-grid', array(
				'post_type' => 'post', 'category' => 'hotarari-ale-consiului-local-ro', 'language' => 'ro', 'count' => 3, 'columns' => '3', 'orderby' => 'date',
				'show_excerpt' => 'yes', 'show_category' => 'yes', 'show_date' => 'yes', 'empty_text' => 'Nu există hotărâri publicate pentru această selecție.', 'read_more_text' => 'Vezi documentul →',
			) ),
		), array( 'background_background' => 'classic', 'background_color' => '#172c3a', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false ) ) );
		$data = array_merge( $data, $this->original_content_sections( $page, 'ro', $seed ) );
		$data[] = $this->redesign_page_end( $seed );
		return $data;
	}

	private function contact_ro_data( \WP_Post $page ): array {
		$seed = 'contact-' . $page->ID;
		$data = $this->redesign_page_start(
			$page,
			$seed,
			'Contactați-ne',
			'Primăria Comunei Porumbești vă stă la dispoziție în programul de lucru cu publicul.',
			'',
			$this->design_media( '2018/07/hatter-13.jpg', 'contact-page-hero' )
		);
		$data[] = $this->container( $seed . '-details', array(
			$this->widget( $seed . '-details-widget', 'porumbesti-contact-details', array(
				'kicker' => 'Locație', 'title' => 'Primăria Comunei Porumbești', 'description' => 'România, jud. Satu Mare, com. Porumbești, sat Porumbești, nr. 17C, cod 447152.',
				'address' => 'România, jud. Satu Mare, com. Porumbești, sat Porumbești, nr. 17C, cod 447152', 'address_code' => 'AD',
				'phone' => '0361 525 288', 'phone_secondary' => '0361 525 288', 'fax' => '0361 525 288', 'email' => 'primar@primariaporumbesti.ro', 'registration_label' => 'CIF', 'registration_value' => '17530869', 'hours' => '', 'hours_code' => 'OR',
				'map_embed' => $this->link( self::MAP_EMBED_URL ), 'map_title' => 'Primăria Comunei Porumbești pe hartă',
			) ),
		), array( 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '32', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->container( $seed . '-form', array(
			$this->widget( $seed . '-form-widget', 'porumbesti-contact-form', array(
				'kicker' => 'Mesaj rapid', 'title' => 'Trimiteți un mesaj', 'description' => 'Completați câmpurile obligatorii pentru a trimite mesajul direct către primărie.',
				'recipient' => 'primar@primariaporumbesti.ro', 'language' => 'ro', 'name_label' => 'Nume', 'email_label' => 'Email', 'subject_label' => 'Subiect', 'message_label' => 'Mesaj',
				'button_text' => 'Trimite mesajul', 'privacy_text' => 'Datele sunt folosite exclusiv pentru a răspunde solicitării.',
			) ),
		), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '64', 'right' => '0', 'bottom' => '64', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->container( $seed . '-quick-links', array(
			$this->widget( $seed . '-quick-links-widget', 'porumbesti-services-grid', array(
				'columns' => '4',
				'items_list' => $this->repeater( $seed . '-quick-link-items', array(
					array( 'icon' => 'TEL', 'title' => 'Telefon', 'description' => '0361 525 288', 'url' => $this->link( 'tel:0361525288' ) ),
					array( 'icon' => 'FAX', 'title' => 'Fax', 'description' => '0361 525 288', 'url' => $this->link( get_permalink( $page ) ) ),
					array( 'icon' => '@', 'title' => 'Email oficial', 'description' => 'primar@primariaporumbesti.ro', 'url' => $this->link( 'mailto:primar@primariaporumbesti.ro' ) ),
					array( 'icon' => 'AD', 'title' => 'Sediu', 'description' => 'Porumbești, nr. 17C, 447152', 'url' => $this->link( self::MAP_URL ) ),
				) ),
			) ),
		), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data = array_merge( $data, $this->original_content_sections( $page, 'ro', $seed ) );
		$data[] = $this->redesign_page_end( $seed );
		return $data;
	}

	private function generic_page_data( \WP_Post $page, string $type, string $language = 'ro' ): array {
		if ( 'ro' === $language ) {
			$redesign = $this->specialized_ro_page_data( $page );
			if ( $redesign ) {
				return $redesign;
			}
		}
		$routes = $this->routes( $language );
		$copy = $this->interface_copy( $language );
		$seed = 'page-' . $page->ID;
		$labels = 'hu' === $language ? array(
			'administration' => array( 'Polgármesteri Hivatal', 'A helyi közigazgatás, a vezetőség és az ügyfélkapcsolatok információi.' ),
			'documents'      => array( 'Közérdekű adatok', 'Kökényesd Község dokumentumai, felhívásai és nyilvános információi.' ),
			'community'      => array( 'Kökényesd Község', 'Hasznos információk a községről és a helyi közszolgáltatásokról.' ),
			'galleries'      => array( 'Galéria', 'Képek Kökényesd Község életéből és történetéből.' ),
		) : array(
			'administration' => array( 'Administrație', 'Informații despre administrația locală, conducere și relația cu cetățenii.' ),
			'documents'      => array( 'Transparență', 'Documente, anunțuri și informații publice ale Comunei Porumbești.' ),
			'community'      => array( 'Comuna Porumbești', 'Informații utile despre comunitate și serviciile publice locale.' ),
			'galleries'      => array( 'Galerie', 'Imagini din viața și istoria Comunei Porumbești.' ),
		);
		$label = $labels[ $type ] ?? $labels['community'];
		$page_identity = strtolower( remove_accents( $page->post_name . ' ' . $page->post_title ) );
		$is_contact_page = (bool) preg_match( '/contact|elerhet/', $page_identity );
		$excerpt = trim( wp_strip_all_tags( (string) $page->post_excerpt ) );
		$description = $excerpt;
		$source_content = $this->original_page_content( $page );
		$same_slug_content = $this->same_slug_post_content( $page, $language );
		if ( $same_slug_content ) {
			$source_content = $same_slug_content;
		}
		if ( 'sitemap' === sanitize_title( $page->post_name ) ) {
			$source_content = $this->sitemap_content( $language );
		}
		$media_items = $this->legacy_media_items( $page, $source_content );
		$normalized_content = $this->normalize_legacy_content( $source_content );
		$unplaced_media = $this->unplaced_media_items( $media_items, $normalized_content, $this->media() );
		$legacy_queries = $this->legacy_post_queries( $source_content );

		$data = array(
			$this->container(
				$seed . '-header',
				array(
					$this->widget( $seed . '-header-widget', 'porumbesti-site-header', $this->header_settings( $page, $language, $routes, $seed . '-header' ) ),
					$this->widget( $seed . '-search', 'porumbesti-search-box', $this->search_settings( $language ) ),
				),
				array( 'content_width' => 'full' )
			),
			$this->container(
				$seed . '-hero',
				array( $this->widget( $seed . '-hero-widget', 'porumbesti-page-hero', array(
					'kicker' => $label[0],
					'title' => get_the_title( $page ),
					'description' => $description,
					'background' => $this->design_media( '2018/07/hatter-13.jpg', 'generic-page-hero' ),
					'parent_label' => $copy['home'],
					'parent_link' => $this->link( $routes[ 'home_' . $language ] ),
					'current_label' => get_the_title( $page ),
				) ) ),
				array( 'content_width' => 'full' )
			),
			$this->container(
				$seed . '-content',
				array( $this->widget( $seed . '-content-widget', 'porumbesti-content-media', array(
					'kicker' => '',
					'title' => '',
					'description' => '',
					'content' => $normalized_content,
					'image' => array( 'id' => 0, 'url' => '' ),
					'image_side' => 'right',
				) ) ),
				array( 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) )
			),
		);
		if ( $is_contact_page ) {
			array_pop( $data );
			$is_hungarian = 'hu' === $language;
			$data[] = $this->container(
				$seed . '-contact-details',
				array( $this->widget( $seed . '-contact-details-widget', 'porumbesti-contact-details', array(
					'kicker'      => $is_hungarian ? 'Elérhetőség' : 'Contact',
					'title'       => $is_hungarian ? 'Kökényesd Község Polgármesteri Hivatala' : 'Primăria Comunei Porumbești',
					'description' => $is_hungarian ? 'Hivatali elérhetőségek és ügyfélfogadási információk.' : 'Date de contact și program de lucru pentru cetățeni.',
					'address'     => $is_hungarian ? 'Romania, Jud. Satu Mare, Com. Porumbesti, Sat. Porumbesti, Nr 17C, Cod. 447152' : 'România, jud. Satu Mare, com. Porumbești, sat Porumbești, nr. 17C, cod 447152',
					'address_code' => $is_hungarian ? 'Cim' : 'LOC',
					'phone'       => $is_hungarian ? '0361 525288' : '0361 525 288',
					'phone_secondary' => $is_hungarian ? '' : '0361 525 288',
					'fax'         => $is_hungarian ? '0361 525288' : '0361 525 288',
					'email'       => 'primar@primariaporumbesti.ro',
					'registration_label' => 'CIF',
					'registration_value' => '17530869',
					'hours'       => '',
					'hours_code'  => $is_hungarian ? 'IDŐ' : 'ORĂ',
					'map_embed'   => $this->link( self::MAP_EMBED_URL ),
					'map_title'   => $is_hungarian ? 'Térkép' : 'Hartă',
				) ) ),
				array( 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '32', 'left' => '0', 'isLinked' => false ) )
			);
			$data[] = $this->container(
				$seed . '-contact-form',
				array( $this->widget( $seed . '-contact-form-widget', 'porumbesti-contact-form', array(
					'kicker'        => $is_hungarian ? 'Üzenet' : 'Mesaj',
					'title'         => $is_hungarian ? 'Írjon nekünk' : 'Trimiteți-ne un mesaj',
					'description'   => $is_hungarian ? 'Az űrlap kötelező mezőinek kitöltése után üzenete közvetlenül a hivatalhoz érkezik.' : 'Completați câmpurile obligatorii pentru a trimite mesajul direct către primărie.',
					'recipient'     => 'primar@primariaporumbesti.ro',
					'language'      => $language,
					'name_label'    => $is_hungarian ? 'Név' : 'Nume și prenume',
					'email_label'   => 'Email',
					'subject_label' => $is_hungarian ? 'Tárgy' : 'Subiect',
					'message_label' => $is_hungarian ? 'Üzenet' : 'Mesaj',
					'button_text'   => $is_hungarian ? 'Üzenet küldése' : 'Trimite mesajul',
					'privacy_text'  => $is_hungarian ? 'Az adatokat kizárólag a megkeresés megválaszolására használjuk.' : 'Datele sunt folosite exclusiv pentru a răspunde solicitării.',
				) ) ),
				array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) )
			);
		}

		if ( 'galleries' === $type ) {
			$gallery_items = $this->gallery_items( $page, '', $source_content );
			if ( $gallery_items ) {
				$data[] = $this->container( $seed . '-gallery', array( $this->widget( $seed . '-gallery-widget', 'porumbesti-photo-gallery', array( 'kicker' => 'hu' === $language ? 'Galéria' : 'Galerie', 'title' => get_the_title( $page ), 'description' => $description, 'items_list' => $this->repeater( $seed . '-images', $gallery_items ), 'columns' => '3' ) ) ), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
			}
		}
		foreach ( $legacy_queries as $query_index => $query ) {
			$data[] = $this->container( $seed . '-legacy-posts-' . $query_index, array( $this->widget( $seed . '-legacy-posts-widget-' . $query_index, 'porumbesti-news-grid', array(
				'post_type' => 'post', 'category' => $query['category'], 'language' => $language, 'count' => $query['count'], 'columns' => $query['columns'], 'orderby' => $query['orderby'], 'show_excerpt' => $query['show_excerpt'], 'show_category' => 'yes', 'show_date' => 'yes', 'empty_text' => $copy['empty_posts'], 'read_more_text' => $copy['read_more'],
			) ) ), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		}
		if ( 'galleries' !== $type && $unplaced_media ) {
			$data[] = $this->container( $seed . '-media', array( $this->widget( $seed . '-media-widget', 'porumbesti-photo-gallery', array( 'kicker' => 'hu' === $language ? 'Média' : 'Media', 'title' => 'hu' === $language ? 'Képek és médiatartalmak' : 'Imagini și materiale media', 'description' => $description, 'items_list' => $this->repeater( $seed . '-media-items', $unplaced_media ), 'columns' => count( $unplaced_media ) > 2 ? '3' : '2' ) ) ), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		}
		$data[] = $this->container( $seed . '-footer', array(
			$this->widget( $seed . '-footer-widget', 'porumbesti-site-footer', $this->footer_settings( $language, $routes, $seed . '-footer' ) ),
			$this->widget( $seed . '-accessibility', 'porumbesti-accessibility', $this->accessibility_settings( $language ) ),
		), array( 'content_width' => 'full' ) );

		return $data;
	}

	private function home_ro_data( \WP_Post $page ): array {
		$menu_id = $this->menu_id();
		$routes  = $this->routes();
		$uploads = wp_get_upload_dir();
		$cta_image = $this->design_media( '2019/03/sisop.jpg', 'sgg-transparency' );
		$hero_image = $this->design_media( '2018/07/hatter-13.jpg', 'homepage-hero' );
		$community_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/07/hatter-13.jpg', 'homepage-community' ) : null;
		$community_image = $community_item ? $community_item['image'] : $this->media();
		$mayor_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/07/Toth-Zoltan-200x200.jpg', 'homepage-mayor' ) : null;
		$vice_mayor_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/06/vice-300x300-200x200.png', 'homepage-vice-mayor' ) : null;
		$secretary_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/06/secretar-300x300-200x200.png', 'homepage-secretary' ) : null;
		$mayor_image = $mayor_item ? $mayor_item['image'] : $this->media();
		$leadership_items = array(
			array( 'icon' => 'PR', 'image' => $mayor_image, 'title' => 'Tóth Zoltán · Primar', 'description' => 'Conducerea executivă a Comunei Porumbești.', 'url' => $this->link( $routes['mayor'] ) ),
			array( 'icon' => 'VP', 'image' => $vice_mayor_item ? $vice_mayor_item['image'] : $this->media(), 'title' => 'Simon Ilie · Viceprimar', 'description' => 'Conducerea executivă a administrației locale.', 'url' => $this->link( $routes['vice_mayor'] ) ),
			array( 'icon' => 'SG', 'image' => $secretary_item ? $secretary_item['image'] : $this->media(), 'title' => 'Csorba Levente · Secretar general', 'description' => 'Secretarul general al Comunei Porumbești.', 'url' => $this->link( $routes['secretary'] ) ),
		);
		$mayor_message = $this->homepage_mayor_message( $page, 'ro' );
		$community_content = '<p>Descoperiți istoria comunei, prezentarea localității, personalitățile și activitatea sportivă locală.</p>'
			. '<div class="porumbesti-home-link-list">'
			. '<a href="' . esc_url( $routes['history'] ) . '">Istoria comunei <span>→</span></a>'
			. '<a href="' . esc_url( $routes['location'] ) . '">Prezentarea comunei <span>→</span></a>'
			. '<a href="' . esc_url( $routes['tourism'] ) . '">Asociația Sportivă Ugocea Porumbești <span>→</span></a>'
			. '<a href="' . esc_url( $routes['galleries'] ) . '">Galeria foto <span>→</span></a>'
			. '</div>';

		$data = array(
			$this->container(
				'header',
				array(
					$this->widget(
						'header-widget',
						'porumbesti-site-header',
						array(
							'official_text'  => 'Site oficial al Primăriei Comunei Porumbești, județul Satu Mare, România',
							'trust_text'     => is_ssl() ? 'Conexiune securizată' : '',
							'mail_url'       => $this->link( 'mailto:primar@primariaporumbesti.ro' ),
							'logo'           => $this->brand_logo(),
							'brand_title'    => 'Comuna Porumbești',
							'brand_subtitle' => 'Primăria · Kökényesd Község',
							'home_url'       => $this->link( $routes['home_ro'] ),
							'menu_id'        => $menu_id,
							'cta_text'       => 'Monitorul Oficial',
							'cta_link'       => $this->link( $routes['monitor'] ),
							'porumbesti_sticky'   => 'yes',
							'language_items' => $this->repeater( 'lang', array(
								array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( $routes['home_ro'] ) ),
								array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( $routes['home_hu'] ) ),
							) ),
						)
					),
					$this->widget( 'search-modal', 'porumbesti-search-box' ),
				),
				array( 'content_width' => 'full' )
			),
			$this->container(
				'hero',
				array(
					$this->widget(
						'hero-widget',
						'porumbesti-home-hero',
						array(
							'eyebrow'        => 'Informații oficiale pentru cetățeni',
							'title'          => 'Servicii publice transparente pentru Comuna Porumbești.',
							'description'    => 'Portal modern pentru documente, formulare, hotărâri, anunțuri oficiale și informații utile pentru cetățeni.',
							'background'     => $hero_image,
							'primary_text'   => 'Vezi documentele',
							'primary_link'   => $this->link( $routes['public_info'] ),
							'secondary_text' => 'Contact rapid',
							'secondary_link' => $this->link( $routes['contact'] ),
							'show_search'    => 'yes',
							'updates_title'  => 'Informații utile astăzi',
							'updates_items'  => $this->repeater( 'home-status-ro', array(
								array( 'day' => 'TEL', 'title' => 'Contact rapid', 'meta' => '0361 525 288', 'url' => $this->link( $routes['contact'] ) ),
								array( 'day' => 'MAIL', 'title' => 'Email oficial', 'meta' => 'primar@primariaporumbesti.ro', 'url' => $this->link( 'mailto:primar@primariaporumbesti.ro' ) ),
								array( 'day' => 'SED', 'title' => 'Sediul Primăriei', 'meta' => 'Porumbești nr. 17C', 'url' => $this->link( $routes['contact'] ) ),
							) ),
						)
					),
				),
				array( 'content_width' => 'full' )
			),
			$this->section( 'services-head', 'Acces rapid', 'Servicii frecvente', '', 'Toate informațiile', $routes['public_info'], 'light', '#ffffff' ),
			$this->container(
				'services',
				array(
					$this->widget(
						'services-widget',
						'porumbesti-services-grid',
						array(
							'columns'    => '4',
							'items_list' => $this->repeater( 'services', array(
								array( 'icon' => 'MOL', 'title' => 'Monitorul Oficial Local', 'description' => 'Hotărâri, dispoziții și documente publice.', 'url' => $this->link( $routes['monitor'] ) ),
								array( 'icon' => 'PDF', 'title' => 'Formulare tipizate', 'description' => 'Cereri și documente administrative utile.', 'url' => $this->link( $routes['forms'] ) ),
								array( 'icon' => 'TEL', 'title' => 'Telefoane utile', 'description' => 'Date de contact pentru serviciile publice.', 'url' => $this->link( $routes['phones'] ) ),
								array( 'icon' => 'LEG', 'title' => 'Legislație', 'description' => 'Acte normative și informații administrative.', 'url' => $this->link( $routes['legislation'] ) ),
								array( 'icon' => 'AN', 'title' => 'Anunțuri', 'description' => 'Comunicări actuale pentru locuitori.', 'url' => $this->link( $routes['announcements'] ) ),
								array( 'icon' => 'PO', 'title' => 'Portal online', 'description' => 'Acces la portalul de servicii.', 'url' => $this->link( $routes['portal'] ) ),
								array( 'icon' => 'CL', 'title' => 'Consiliul Local', 'description' => 'Componență și hotărâri.', 'url' => $this->link( $routes['council'] ) ),
								array( 'icon' => 'FOT', 'title' => 'Galeria foto', 'description' => 'Imagini din viața comunității.', 'url' => $this->link( $routes['galleries'] ) ),
							) ),
						)
					),
				),
				array(
					'background_background' => 'classic',
					'background_color' => '#ffffff',
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->section( 'news-head', 'Ultimele actualizări', 'Anunțuri oficiale', '', 'Toate anunțurile', $routes['announcements'] ),
			$this->container(
				'news',
				array(
					$this->widget(
						'news-widget',
						'porumbesti-news-grid',
						array(
							'post_type'    => 'post',
							'category'     => 'anunturi',
							'language'     => 'ro',
							'count'        => 6,
							'columns'      => '3',
							'orderby'      => 'date',
							'show_excerpt' => 'yes',
							'show_category'=> 'yes',
							'show_date'    => 'yes',
						)
					),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->section( 'decisions-head', 'Consiliul Local', 'Hotărâri recente', '', 'Arhiva H.C.L.', $routes['decisions'], 'dark', '#172c3a' ),
			$this->container(
				'decisions',
				array(
					$this->widget(
						'decisions-widget',
						'porumbesti-news-grid',
						array(
							'post_type'    => 'post',
							'category'     => 'hotarari-ale-consiului-local-ro',
							'language'     => 'ro',
							'count'        => 6,
							'columns'      => '3',
							'orderby'      => 'date',
							'show_excerpt' => 'yes',
							'show_category'=> 'yes',
							'show_date'    => 'yes',
							'empty_text'   => 'Nu există hotărâri publicate.',
							'read_more_text' => 'Vezi documentul →',
						)
					),
				),
				array(
					'background_background' => 'classic',
					'background_color' => '#172c3a',
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->section( 'leadership-head', 'Administrație locală', 'Conducere', '', 'Conducerea Primăriei', $routes['mayor'] ),
			$this->container(
				'leadership',
				array(
					$this->widget( 'leadership-widget', 'porumbesti-services-grid', array( 'columns' => '3', 'items_list' => $this->repeater( 'home-leadership-ro', $leadership_items ) ) ),
				),
				array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) )
			),
			$this->container(
				'community',
				array(
					$this->widget( 'community-widget', 'porumbesti-content-media', array(
						'kicker' => 'Comuna',
						'title' => 'Istorie, cultură și natură în inima județului Satu Mare',
						'description' => '',
						'content' => $community_content,
						'image' => $community_image,
						'image_side' => 'right',
					) ),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->container(
				'mayor-message',
				array(
					$this->widget( 'mayor-message-widget', 'porumbesti-content-media', array(
						'kicker' => 'Stimați vizitatori!',
						'title' => 'Mesajul primarului',
						'description' => '',
						'content' => $mayor_message,
						'image' => $mayor_image,
						'image_side' => 'left',
					) ),
				),
				array( 'background_background' => 'classic', 'background_color' => '#f4f7f9', 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) )
			),
			$this->section( 'monitor-head', 'Monitorul Oficial Local', 'Documente publice într-un singur loc', '', 'Deschide MOL', $routes['monitor'], 'light', '#ffffff' ),
			$this->container(
				'monitor-services',
				array(
					$this->widget( 'monitor-services-widget', 'porumbesti-services-grid', array(
						'columns' => '3',
						'items_list' => $this->repeater( 'monitor-services', array(
							array( 'icon' => 'CL', 'title' => 'Hotărârile autorității deliberative', 'description' => 'Arhivă pentru hotărârile Consiliului Local.', 'url' => $this->link( $routes['deliberative'] ) ),
							array( 'icon' => 'PR', 'title' => 'Dispoziții autoritatea executivă', 'description' => 'Documente emise de conducerea executivă.', 'url' => $this->link( $routes['executive'] ) ),
							array( 'icon' => 'FIN', 'title' => 'Documente și informații financiare', 'description' => 'Buget, execuție și date financiare publice.', 'url' => $this->link( $routes['financial'] ) ),
						) ),
					) ),
				),
				array(
					'background_background' => 'classic',
					'background_color' => '#ffffff',
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->container(
				'cta',
				array(
					$this->widget(
						'cta-widget',
						'porumbesti-cta-banner',
						array(
							'kicker'      => 'Transparență administrativă',
							'title'       => 'Guvernare transparentă, deschisă și participativă',
							'description' => 'Acces clar la documente, decizii și informații publice pentru o administrație responsabilă.',
							'image'       => $cta_image,
							'button_text' => 'Detalii proiect SGG',
							'button_link' => $this->link( self::TRANSPARENCY_URL ),
						)
					),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '48', 'right' => '0', 'bottom' => '48', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->container(
				'footer',
				array(
					$this->widget( 'footer-widget', 'porumbesti-site-footer', $this->footer_settings( 'ro', $routes, 'home-footer' ) ),
					$this->widget( 'accessibility-widget', 'porumbesti-accessibility', array( 'title' => 'Accesibilitate', 'position' => 'right' ) ),
				),
				array( 'content_width' => 'full' )
			),
		);
		// The homepage already presents the relevant legacy material in dedicated,
		// curated sections. The original source remains stored in SOURCE_META for
		// recovery, but rendering it here would duplicate the whole page before the footer.
		return $data;
	}

	private function home_hu_data( \WP_Post $page ): array {
		$routes = $this->routes( 'hu' );
		$copy = $this->interface_copy( 'hu' );
		$uploads = wp_get_upload_dir();
		$hero_image = $this->design_media( '2018/07/hatter-13.jpg', 'homepage-hu-hero' );
		$community_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/07/hatter-13.jpg', 'homepage-community' ) : null;
		$community_image = $community_item ? $community_item['image'] : $this->media();
		$cta_image = $this->design_media( '2019/03/sisop.jpg', 'sgg-transparency' );
		$mayor_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/07/Toth-Zoltan-200x200.jpg', 'homepage-hu-mayor' ) : null;
		$vice_mayor_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/06/vice-300x300-200x200.png', 'homepage-hu-vice-mayor' ) : null;
		$secretary_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/06/secretar-300x300-200x200.png', 'homepage-hu-secretary' ) : null;
		$mayor_image = $mayor_item ? $mayor_item['image'] : $this->media();
		$leadership_items = array(
			array( 'icon' => 'PM', 'image' => $mayor_image, 'title' => 'Tóth Zoltán · Polgármester', 'description' => 'Kökényesd község végrehajtó vezetője.', 'url' => $this->link( $routes['mayor'] ) ),
			array( 'icon' => 'AP', 'image' => $vice_mayor_item ? $vice_mayor_item['image'] : $this->media(), 'title' => 'Simon Ilie · Alpolgármester', 'description' => 'A helyi közigazgatás végrehajtó vezetősége.', 'url' => $this->link( $routes['vice_mayor'] ) ),
			array( 'icon' => 'J', 'image' => $secretary_item ? $secretary_item['image'] : $this->media(), 'title' => 'Csorba Levente · Jegyző', 'description' => 'Kökényesd község főjegyzője.', 'url' => $this->link( $routes['secretary'] ) ),
		);
		$mayor_message = $this->homepage_mayor_message( $page, 'hu' );
		$community_content = '<p>Ismerje meg Kökényesd Község történetét, jelentős személyiségeit és helyi sportéletét.</p><div class="porumbesti-home-link-list">'
			. '<a href="' . esc_url( $routes['history'] ) . '">Községünk története <span>→</span></a>'
			. '<a href="' . esc_url( $routes['monuments'] ) . '">Nagyjaink <span>→</span></a>'
			. '<a href="' . esc_url( $routes['tourism'] ) . '">A kökényesdi Ugocsa csapatának története <span>→</span></a>'
			. '<a href="' . esc_url( $routes['galleries'] ) . '">Fotógaléria <span>→</span></a></div>';

		$data = array(
			$this->container( 'hu-header', array(
				$this->widget( 'hu-header-widget', 'porumbesti-site-header', $this->header_settings( $page, 'hu', $routes, 'hu-home-header' ) ),
				$this->widget( 'hu-search-modal', 'porumbesti-search-box', $this->search_settings( 'hu' ) ),
			), array( 'content_width' => 'full' ) ),
			$this->container( 'hu-hero', array(
				$this->widget( 'hu-hero-widget', 'porumbesti-home-hero', array(
					'eyebrow' => 'Hivatalos információk a lakosságnak',
					'title' => 'Isten hozta Önöket Kökényesd Község hivatalos honlapján!',
					'description' => 'Közérdekű tájékoztatás, hivatali ügyintézés, felhívások, dokumentumok és közösségi hírek egy helyen.',
					'background' => $hero_image,
					'primary_text' => 'Közérdekű információk', 'primary_link' => $this->link( $routes['public_info'] ),
					'secondary_text' => 'Elérhetőség', 'secondary_link' => $this->link( $routes['contact'] ), 'show_search' => 'yes',
					'search_label' => $copy['search'], 'search_placeholder' => $copy['search_placeholder'], 'search_button' => $copy['search_button'], 'search_language' => 'hu',
					'updates_title' => 'Mai hasznos információk', 'updates_items' => $this->repeater( 'home-status-hu', array(
						array( 'day' => 'TEL', 'title' => 'Gyors kapcsolat', 'meta' => '0361 525 288', 'url' => $this->link( $routes['contact'] ) ),
						array( 'day' => 'MAIL', 'title' => 'Hivatalos e-mail', 'meta' => 'primar@primariaporumbesti.ro', 'url' => $this->link( 'mailto:primar@primariaporumbesti.ro' ) ),
						array( 'day' => 'CÍM', 'title' => 'Polgármesteri Hivatal', 'meta' => 'Kökényesd 17C.', 'url' => $this->link( $routes['contact'] ) ),
					) ),
				) ),
			), array( 'content_width' => 'full' ) ),
			$this->section( 'hu-services-head', 'Gyors elérés', 'Közérdekű ügyek', '', 'Minden felhívás', $routes['announcements'], 'light', '#ffffff' ),
			$this->container( 'hu-services', array(
				$this->widget( 'hu-services-widget', 'porumbesti-services-grid', array( 'columns' => '4', 'items_list' => $this->repeater( 'hu-services', array(
					array( 'icon' => 'KÖZ', 'title' => 'Helyi hivatalos közlöny', 'description' => 'Határozatok, rendelkezések és nyilvános dokumentumok.', 'url' => $this->link( $routes['monitor'] ) ),
					array( 'icon' => 'PDF', 'title' => 'Formanyomtatványok', 'description' => 'Hivatali kérelmek és letölthető űrlapok.', 'url' => $this->link( $routes['forms'] ) ),
					array( 'icon' => 'TEL', 'title' => 'Hasznos telefonszámok', 'description' => 'Gyors kapcsolat a közszolgáltatásokhoz.', 'url' => $this->link( $routes['phones'] ) ),
					array( 'icon' => 'JOG', 'title' => 'Jogalkotás', 'description' => 'Jogszabályok és hivatali tájékoztatás.', 'url' => $this->link( $routes['legislation'] ) ),
					array( 'icon' => 'EL', 'title' => 'Elérhetőség', 'description' => 'Telefonszámok, cím és ügyfélfogadás.', 'url' => $this->link( $routes['contact'] ) ),
					array( 'icon' => 'HT', 'title' => 'Helyi tanács', 'description' => 'A helyi tanács szerkezete és határozatai.', 'url' => $this->link( $routes['council'] ) ),
					array( 'icon' => 'KK', 'title' => 'Községünk', 'description' => 'Történet, személyiségek és sport.', 'url' => $this->link( $routes['history'] ) ),
					array( 'icon' => 'FOT', 'title' => 'Galéria', 'description' => 'Képek a község eseményeiről és mindennapjairól.', 'url' => $this->link( $routes['galleries'] ) ),
				) ) ) ),
			), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ) ) ),
			$this->container( 'hu-community', array(
				$this->widget( 'hu-community-widget', 'porumbesti-content-media', array( 'kicker' => 'Községünk', 'title' => 'Történet, kultúra és természeti értékek Szatmár szívében', 'description' => '', 'content' => $community_content, 'image' => $community_image, 'image_side' => 'right' ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) ),
			$this->section( 'hu-announcements-head', 'Közérdekű', 'Felhívások', '', 'Minden felhívás', $routes['announcements'] ),
			$this->container( 'hu-announcements', array(
				$this->widget( 'hu-announcements-widget', 'porumbesti-news-grid', array( 'post_type' => 'post', 'category' => 'felhivasok', 'language' => 'hu', 'count' => 6, 'columns' => '3', 'orderby' => 'date', 'show_excerpt' => 'yes', 'show_category' => 'yes', 'show_date' => 'yes', 'empty_text' => $copy['empty_posts'], 'read_more_text' => $copy['read_more'] ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ) ) ),
			$this->section( 'hu-decisions-head', 'Helyi tanács', 'Legutóbbi határozatok', 'A magyar felület a hiteles román dokumentumokat jelöli.', 'Határozatok archívuma', $routes['decisions'], 'dark', '#172c3a' ),
			$this->container( 'hu-decisions', array(
				$this->widget( 'hu-decisions-widget', 'porumbesti-news-grid', array( 'post_type' => 'post', 'category' => 'hotarari-ale-consiului-local-ro', 'language' => 'ro', 'count' => 6, 'columns' => '3', 'orderby' => 'date', 'show_excerpt' => 'yes', 'show_category' => 'yes', 'show_date' => 'yes', 'empty_text' => 'Jelenleg nincs közzétett határozat.', 'read_more_text' => 'RO · hiteles dokumentum →' ) ),
			), array( 'background_background' => 'classic', 'background_color' => '#172c3a', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false ) ) ),
			$this->section( 'hu-leadership-head', 'Helyi közigazgatás', 'Vezetőség', '', 'A hivatal vezetősége', $routes['mayor'] ),
			$this->container( 'hu-leadership', array(
				$this->widget( 'hu-leadership-widget', 'porumbesti-services-grid', array( 'columns' => '3', 'items_list' => $this->repeater( 'home-leadership-hu', $leadership_items ) ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) ),
			$this->container( 'hu-mayor-message', array(
				$this->widget( 'hu-mayor-message-widget', 'porumbesti-content-media', array( 'kicker' => 'Tisztelt Látogatók!', 'title' => 'A polgármester köszöntője', 'description' => '', 'content' => $mayor_message, 'image' => $mayor_image, 'image_side' => 'left' ) ),
			), array( 'background_background' => 'classic', 'background_color' => '#f4f7f9', 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) ),
			$this->section( 'hu-monitor-head', 'Önkormányzati átláthatóság', 'Helyi hivatalos közlöny', '', 'Közlöny megnyitása', $routes['monitor'], 'light', '#ffffff' ),
			$this->container( 'hu-monitor', array(
				$this->widget( 'hu-monitor-widget', 'porumbesti-services-grid', array( 'columns' => '3', 'items_list' => $this->repeater( 'hu-monitor-items', array(
					array( 'icon' => 'HT', 'title' => 'A tanácsadó hatóság rendelkezései', 'description' => 'A helyi tanács határozatainak archívuma.', 'url' => $this->link( $routes['deliberative'] ) ),
					array( 'icon' => 'VH', 'title' => 'A végrehajtó hatóság rendelkezései', 'description' => 'A végrehajtó vezetőség által kiadott dokumentumok.', 'url' => $this->link( $routes['executive'] ) ),
					array( 'icon' => 'PÉN', 'title' => 'Pénzügyi dokumentumok és információk', 'description' => 'Költségvetési és pénzügyi adatok.', 'url' => $this->link( $routes['financial'] ) ),
				) ) ) ),
			), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) ),
			$this->container( 'hu-cta', array(
				$this->widget( 'hu-cta-widget', 'porumbesti-cta-banner', array( 'kicker' => 'Közigazgatási átláthatóság', 'title' => 'Átlátható, nyitott és részvételi kormányzás', 'description' => 'Közvetlen hozzáférés a dokumentumokhoz, döntésekhez és közérdekű információkhoz.', 'image' => $cta_image, 'button_text' => 'SGG projekt részletei', 'button_link' => $this->link( self::TRANSPARENCY_URL ) ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '48', 'right' => '0', 'bottom' => '48', 'left' => '0', 'isLinked' => false ) ) ),
			$this->container( 'hu-footer', array(
				$this->widget( 'hu-footer-widget', 'porumbesti-site-footer', $this->footer_settings( 'hu', $routes, 'hu-home-footer' ) ),
				$this->widget( 'hu-accessibility-widget', 'porumbesti-accessibility', $this->accessibility_settings( 'hu' ) ),
			), array( 'content_width' => 'full' ) ),
		);
		// Keep the Hungarian homepage focused on its curated bilingual sections.
		// The untouched source backup remains available for recovery and auditing.
		return $data;
	}

	private function mayor_ro_data( \WP_Post $page ): array {
		$seed = 'mayor-' . $page->ID;
		$routes = $this->routes();
		$source_content = $this->original_page_content( $page );
		$media_items = $this->legacy_media_items( $page, $source_content );
		$mayor_photo = $media_items ? $media_items[0]['image'] : $this->media();
		$normalized_content = $this->normalize_legacy_content( $source_content );
		$profile_bio = $this->integrated_profile_content( $normalized_content, $mayor_photo, array( get_the_title( $page ), 'Primar', 'Tóth Zoltán' ) );
		$data = $this->redesign_page_start(
			$page,
			$seed,
			'Conducerea Primăriei',
			'Profilul conducerii și acces rapid către departamentele administrative ale Comunei Porumbești.',
			'',
			$this->design_media( '2018/07/hatter-13.jpg', 'mayor-page-hero' )
		);
		$data[] = $this->container( $seed . '-profile', array(
			$this->widget( $seed . '-profile-widget', 'porumbesti-person-profile', array(
				'photo' => $mayor_photo, 'role' => 'Primar', 'name' => 'Tóth Zoltán', 'subtitle' => 'Primarul Comunei Porumbești',
				'bio' => $profile_bio, 'phone' => '0361 525 288', 'email' => 'primar@primariaporumbesti.ro', 'office' => '',
			) ),
		), array( 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '32', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->container( $seed . '-schedule', array(
			$this->widget( $seed . '-schedule-widget', 'porumbesti-schedule-grid', array(
				'kicker' => 'Program audiențe', 'title' => 'Acces la conducere', 'description' => '',
				'items_list' => $this->repeater( $seed . '-schedule-items', array(
					array( 'icon' => 'PR', 'title' => 'Primar', 'time' => 'Program disponibil la sediul Primăriei' ),
					array( 'icon' => 'VP', 'title' => 'Viceprimar', 'time' => 'Program disponibil la sediul Primăriei' ),
					array( 'icon' => 'SG', 'title' => 'Secretar General', 'time' => 'Program disponibil la sediul Primăriei' ),
					array( 'icon' => 'GH', 'title' => 'Ghișeu', 'time' => 'Informații la 0361 525 288' ),
				) ),
			) ),
			$this->widget( $seed . '-links-widget', 'porumbesti-link-list', array(
				'title' => 'Conducere',
				'items_list' => $this->repeater( $seed . '-link-items', array(
					array( 'icon' => 'CP', 'label' => 'Cuvântul primarului', 'meta' => 'Mesaj publicat', 'url' => $this->link( $this->page_url( array( 'cuvantul-primarului' ), $routes['mayor'] ) ) ),
					array( 'icon' => 'ROF', 'label' => 'Regulamentul de organizare', 'meta' => 'Primăria Comunei Porumbești', 'url' => $this->link( $this->page_url( array( 'regulamentul-de-organizare-si-functionare-al-primariei-comunei-porumbesti' ), $routes['departments'] ) ) ),
				) ),
			) ),
		), array( 'gap' => array( 'unit' => 'px', 'size' => 32, 'sizes' => array() ), 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->section( $seed . '-departments-heading', 'Departamente', 'Servicii administrative', '', 'Contact departamente', $routes['contact'], 'light', '#ffffff' );
		$data[] = $this->container( $seed . '-departments', array(
			$this->widget( $seed . '-departments-widget', 'porumbesti-services-grid', array(
				'columns' => '3',
				'items_list' => $this->repeater( $seed . '-department-items', array(
					array( 'icon' => 'TAX', 'title' => 'Taxe și Impozite Locale', 'description' => 'Informații fiscale și comunicări pentru contribuabili.', 'url' => $this->link( $routes['taxes'] ) ),
					array( 'icon' => 'AGR', 'title' => 'Registru Agricol', 'description' => 'Servicii pentru terenuri, gospodării și evidențe agricole.', 'url' => $this->link( $routes['agricultural'] ) ),
					array( 'icon' => 'URB', 'title' => 'Urbanism', 'description' => 'Certificate de urbanism și autorizații de construire.', 'url' => $this->link( $routes['urbanism'] ) ),
					array( 'icon' => 'SOC', 'title' => 'Asistența Socială', 'description' => 'Sprijin pentru persoane și familii vulnerabile.', 'url' => $this->link( $this->page_url( array( 'asistenta-sociala' ), $routes['departments'] ) ) ),
					array( 'icon' => 'SC', 'title' => 'Stare Civilă', 'description' => 'Acte, certificate și proceduri de stare civilă.', 'url' => $this->link( $this->page_url( array( 'stare-civila' ), $routes['departments'] ) ) ),
					array( 'icon' => 'FIN', 'title' => 'Contabilitate', 'description' => 'Date financiare și gestiune bugetară locală.', 'url' => $this->link( $this->page_url( array( 'contabilitate' ), $routes['departments'] ) ) ),
				) ),
			) ),
		), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		$data[] = $this->redesign_page_end( $seed );
		return $data;
	}

	private function mayor_hu_data( \WP_Post $page ): array {
		$routes = $this->routes( 'hu' );
		$source_content = $this->original_page_content( $page );
		$media_items = $this->legacy_media_items( $page, $source_content );
		$mayor_photo = $media_items ? $media_items[0]['image'] : $this->media();
		$normalized_content = $this->normalize_legacy_content( $source_content );
		$profile_bio = $this->integrated_profile_content( $normalized_content, $mayor_photo, array( get_the_title( $page ), 'Polgármester', 'Primar', 'Tóth Zoltán' ) );
		$data = array(
			$this->container( 'mayor-hu-header', array(
				$this->widget( 'mayor-hu-header-widget', 'porumbesti-site-header', $this->header_settings( $page, 'hu', $routes, 'mayor-hu-header' ) ),
				$this->widget( 'mayor-hu-search-modal', 'porumbesti-search-box', $this->search_settings( 'hu' ) ),
			), array( 'content_width' => 'full' ) ),
			$this->container( 'mayor-hu-hero', array(
				$this->widget( 'mayor-hu-hero-widget', 'porumbesti-page-hero', array( 'kicker' => 'Polgármesteri Hivatal', 'title' => get_the_title( $page ), 'description' => '', 'background' => $this->design_media( '2018/07/hatter-13.jpg', 'mayor-hu-page-hero' ), 'parent_label' => 'Kezdőlap', 'parent_link' => $this->link( $routes['home_hu'] ), 'current_label' => get_the_title( $page ) ) ),
			), array( 'content_width' => 'full' ) ),
			$this->container( 'mayor-hu-profile', array(
				$this->widget( 'mayor-hu-profile-widget', 'porumbesti-person-profile', array( 'photo' => $mayor_photo, 'role' => 'Polgármester', 'name' => 'Tóth Zoltán', 'subtitle' => 'Kökényesd község polgármestere', 'bio' => $profile_bio, 'phone' => '0361 525 288', 'email' => 'primar@primariaporumbesti.ro', 'office' => 'Időpont-egyeztetés: 0361 525 288' ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '70', 'right' => '0', 'bottom' => '32', 'left' => '0', 'isLinked' => false ) ) ),
		);
		$data[] = $this->container( 'mayor-hu-footer', array(
			$this->widget( 'mayor-hu-footer-widget', 'porumbesti-site-footer', $this->footer_settings( 'hu', $routes, 'mayor-hu-footer' ) ),
			$this->widget( 'mayor-hu-accessibility-widget', 'porumbesti-accessibility', $this->accessibility_settings( 'hu' ) ),
		), array( 'content_width' => 'full' ) );
		return $data;
	}

	private function section( string $seed, string $kicker, string $title, string $description = '', string $button = '', string $url = '', string $theme = 'light', string $background = '' ): array {
		return $this->container(
			$seed,
			array(
				$this->widget(
					$seed . '-widget',
					'porumbesti-section-heading',
					array(
						'kicker'      => $kicker,
						'title'       => $title,
						'description' => $description,
						'theme'       => $theme,
						'button_text' => $button,
						'button_link' => $this->link( $url ),
					)
				),
			),
			array(
				'background_background' => $background ? 'classic' : '',
				'background_color' => $background,
				'padding' => array( 'unit' => 'px', 'top' => '80', 'right' => '0', 'bottom' => '30', 'left' => '0', 'isLinked' => false ),
			)
		);
	}
}
