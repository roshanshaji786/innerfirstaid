<?php
/**
 * Lead capture: custom table, AJAX endpoint, rate limiting, admin list + CSV export.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Leads
 */
class IFA_Leads {

	/**
	 * Table name (without prefix).
	 */
	const TABLE = 'ifa_leads';

	/**
	 * Singleton.
	 *
	 * @var IFA_Leads|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return IFA_Leads
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'wp_ajax_ifa_submit_lead', array( $this, 'handle_submit' ) );
		add_action( 'wp_ajax_nopriv_ifa_submit_lead', array( $this, 'handle_submit' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_ifa_export_leads', array( $this, 'export_csv' ) );
		add_action( 'admin_post_ifa_delete_lead', array( $this, 'delete_lead' ) );
	}

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE;
	}

	/**
	 * Create the table (dbDelta).
	 */
	public static function maybe_create_table() {
		global $wpdb;

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			email VARCHAR(191) NOT NULL,
			lang VARCHAR(10) NOT NULL DEFAULT 'en',
			source VARCHAR(64) NOT NULL DEFAULT 'landing_page',
			ip VARCHAR(64) NOT NULL DEFAULT '',
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * AJAX submit handler.
	 */
	public function handle_submit() {
		check_ajax_referer( 'ifa_lead_nonce', 'nonce' );

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$lang  = isset( $_POST['lang'] ) && 'sl' === $_POST['lang'] ? 'sl' : 'en';
		$source = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : 'landing_page';
		$honeypot = isset( $_POST['company_website'] ) ? wp_unslash( $_POST['company_website'] ) : '';

		// Honeypot: bots fill the invisible field.
		if ( '' !== $honeypot ) {
			wp_send_json_success( array( 'message' => 'ok' ), 200 );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'Invalid email' ), 400 );
		}

		// Rate limit: max 10 submissions per hour per IP.
		$ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
		$rl_key  = 'ifa_rl_' . md5( $ip );
		$count   = (int) get_transient( $rl_key );
		if ( $count >= 10 ) {
			wp_send_json_error( array( 'message' => 'Too many attempts' ), 429 );
		}
		set_transient( $rl_key, $count + 1, HOUR_IN_SECONDS );

		$inserted = $this->insert( array(
			'email'  => strtolower( $email ),
			'lang'   => $lang,
			'source' => $source,
			'ip'     => $ip,
		) );

		if ( false === $inserted ) {
			// Duplicate email is not an error — the lead already exists.
			$this->notify( strtolower( $email ), $lang, $source, true );
			wp_send_json_success( array( 'message' => 'exists' ), 200 );
		}

		$this->notify( strtolower( $email ), $lang, $source, false );
		wp_send_json_success( array( 'message' => 'created' ), 201 );
	}

	/**
	 * Insert a lead.
	 *
	 * @param array $data Lead data.
	 * @return int|false Insert id or false on duplicate/failure.
	 */
	public function insert( $data ) {
		global $wpdb;

		self::maybe_create_table();

		$existing = $wpdb->get_var(
			$wpdb->prepare( 'SELECT id FROM ' . self::table() . ' WHERE email = %s', $data['email'] )
		);
		if ( $existing ) {
			return false;
		}

		$ok = $wpdb->insert(
			self::table(),
			array(
				'email'      => $data['email'],
				'lang'       => $data['lang'],
				'source'     => substr( $data['source'], 0, 64 ),
				'ip'         => substr( $data['ip'], 0, 64 ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		return $ok ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Send optional admin notification.
	 *
	 * @param string $email   Lead email.
	 * @param string $lang    Language.
	 * @param string $source  Source.
	 * @param bool   $duplicate Whether it was a duplicate.
	 */
	private function notify( $email, $lang, $source, $duplicate ) {
		$to = ifa_get_option( 'lead_notify_email', '' );
		if ( ! is_email( $to ) ) {
			return;
		}
		$subject = $duplicate
			? sprintf( '[Inner First Aid] %s', __( 'Repeat lead', 'ifa-core' ) )
			: sprintf( '[Inner First Aid] %s', __( 'New lead', 'ifa-core' ) );
		$body = sprintf(
			"%s\n\nEmail: %s\nLang: %s\nSource: %s\nTime: %s\n\n%s",
			__( 'A new guide request was submitted:', 'ifa-core' ),
			$email,
			$lang,
			$source,
			current_time( 'mysql' ),
			admin_url( 'admin.php?page=ifa-leads' )
		);
		wp_mail( $to, $subject, $body );
	}

	/**
	 * Register the admin list page.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'IFA Leads', 'ifa-core' ),
			__( 'IFA Leads', 'ifa-core' ),
			'manage_options',
			'ifa-leads',
			array( $this, 'render_admin_page' ),
			'dashicons-email-alt',
			26
		);
	}

	/**
	 * Render the admin list page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::maybe_create_table();

		global $wpdb;
		$table = self::table();
		$per   = 50;
		$paged = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$pages = max( 1, (int) ceil( $total / $per ) );
		$paged = min( $paged, $pages );

		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $per, ( $paged - 1 ) * $per )
		);

		$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=ifa_export_leads' ), 'ifa_export_leads' );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Leads', 'ifa-core' ); ?></h1>
			<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'ifa-core' ); ?></a>
			<p>
				<?php
				printf(
					/* translators: %s: lead count. */
					esc_html__( '%s total email leads captured via the Elementor lead forms.', 'ifa-core' ),
					esc_html( number_format_i18n( $total ) )
				);
				?>
			</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>ID</th>
						<th><?php esc_html_e( 'Email', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Lang', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Source', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'IP', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Date', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'ifa-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( ! $rows ) : ?>
					<tr><td colspan="7"><?php esc_html_e( 'No leads yet.', 'ifa-core' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row->id ); ?></td>
							<td><?php echo esc_html( $row->email ); ?></td>
							<td><?php echo esc_html( $row->lang ); ?></td>
							<td><?php echo esc_html( $row->source ); ?></td>
							<td><?php echo esc_html( $row->ip ); ?></td>
							<td><?php echo esc_html( $row->created_at ); ?></td>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ifa_delete_lead&id=' . (int) $row->id ), 'ifa_delete_lead_' . (int) $row->id ) ); ?>"
									onclick="return confirm('<?php echo esc_js( __( 'Delete this lead?', 'ifa-core' ) ); ?>');">
									<?php esc_html_e( 'Delete', 'ifa-core' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
			<?php if ( $pages > 1 ) : ?>
				<div class="tablenav" style="margin-top:12px;">
					<div class="tablenav-pages">
						<?php
						echo wp_kses_post(
							paginate_links(
								array(
									'base'    => add_query_arg( 'paged', '%#%' ),
									'format'  => '',
									'current' => $paged,
									'total'   => $pages,
								)
							)
						);
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Delete a lead.
	 */
	public function delete_lead() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied', 'ifa-core' ) );
		}
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		if ( ! $id || ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? wp_unslash( $_GET['_wpnonce'] ) : '', 'ifa_delete_lead_' . $id ) ) {
			wp_die( esc_html__( 'Invalid request', 'ifa-core' ) );
		}
		global $wpdb;
		$wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
		wp_safe_redirect( admin_url( 'admin.php?page=ifa-leads' ) );
		exit;
	}

	/**
	 * Export all leads as CSV.
	 */
	public function export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied', 'ifa-core' ) );
		}
		if ( ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? wp_unslash( $_GET['_wpnonce'] ) : '', 'ifa_export_leads' ) ) {
			wp_die( esc_html__( 'Invalid request', 'ifa-core' ) );
		}

		global $wpdb;
		$rows = $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY id DESC' );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=ifa-leads-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'id', 'email', 'lang', 'source', 'ip', 'created_at' ) );
		foreach ( $rows as $row ) {
			fputcsv( $out, array( $row->id, $row->email, $row->lang, $row->source, $row->ip, $row->created_at ) );
		}
		fclose( $out );
		exit;
	}
}
