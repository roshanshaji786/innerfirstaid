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
		add_action( 'admin_post_ifa_test_guide', array( $this, 'handle_test_guide' ) );
	}

	/**
	 * Admin-post handler: send the guide email to a chosen address to verify
	 * that the mailer (FluentSMTP/Brevo) is actually delivering.
	 */
	public function handle_test_guide() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied', 'ifa-core' ) );
		}
		check_admin_referer( 'ifa_test_guide' );

		$to = isset( $_POST['test_email'] ) ? sanitize_email( wp_unslash( $_POST['test_email'] ) ) : '';
		if ( ! is_email( $to ) ) {
			$to = get_option( 'admin_email' );
		}
		$lang = isset( $_POST['test_lang'] ) && 'sl' === $_POST['test_lang'] ? 'sl' : 'en';

		$sent   = $this->send_guide( $to, $lang );
		$status = get_option( 'ifa_guide_last_send', array() );

		set_transient(
			'ifa_test_guide_result',
			array(
				'ok'    => $sent,
				'to'    => $to,
				'lang'  => $lang,
				'time'  => current_time( 'mysql' ),
				'info'  => isset( $status['info'] ) ? $status['info'] : '',
			),
			120
		);

		wp_safe_redirect( admin_url( 'admin.php?page=ifa-settings' ) );
		exit;
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
			consent VARCHAR(20) NOT NULL DEFAULT 'yes',
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
		// Verify the nonce from $_POST directly (robust even when a host's
		// request_order config omits POST from $_REQUEST).
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ifa_lead_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
		}

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

		// Consent checkbox (documents opt-in; required when enabled).
		$consent = ( isset( $_POST['consent'] ) && 'yes' === $_POST['consent'] ) ? 'yes' : 'no';
		if ( '1' === ifa_get_option( 'lead_consent_enabled', '1' ) && 'yes' !== $consent ) {
			wp_send_json_error( array( 'message' => 'Consent required' ), 400 );
		}

		// reCAPTCHA v2 — verified server-side when configured.
		if ( '' !== ifa_get_option( 'recaptcha_secret_key', '' ) ) {
			$token = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
			if ( ! self::verify_recaptcha( $token ) ) {
				wp_send_json_error( array( 'message' => 'Captcha verification failed' ), 400 );
			}
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
			'email'   => strtolower( $email ),
			'lang'    => $lang,
			'source'  => $source,
			'consent' => $consent,
			'ip'      => $ip,
		) );

		if ( false === $inserted ) {
			// Duplicate email is not an error — the lead already exists.
			$this->notify( strtolower( $email ), $lang, $source, true );
			$this->send_guide( strtolower( $email ), $lang );
			wp_send_json_success( array( 'message' => 'exists' ), 200 );
		}

		$this->notify( strtolower( $email ), $lang, $source, false );
		$this->send_guide( strtolower( $email ), $lang );
		wp_send_json_success( array( 'message' => 'created' ), 201 );
	}

	/**
	 * Send the free guide (PDF) to the lead automatically.
	 *
	 * @param string $email Lead email.
	 * @param string $lang  Language.
	 * @return bool Whether wp_mail accepted the send.
	 */
	public function send_guide( $email, $lang ) {
		$suffix = 'sl' === $lang ? 'sl' : 'en';

		$subject = ifa_get_option( 'guide_subject_' . $suffix, '' );
		if ( '' === $subject ) {
			$subject = 'sl' === $suffix
				? 'Tvoj brezplacni vodic: 3 napake, ki podaljsajo bolecino'
				: 'Your free guide: 3 mistakes that prolong the pain';
		}

		$message = ifa_get_option( 'guide_message_' . $suffix, '' );
		if ( '' === $message ) {
			$message = 'sl' === $suffix
				? "Zivjo,\n\nhvala, da si se prijavil/a. V prilogi je tvoj brezplacni vodic.\n\nLep pozdrav,\nekipa Inner First Aid"
				: "Hi,\n\nthanks for signing up. Your free guide is attached.\n\nBest,\nthe Inner First Aid team";
		}
		$message = nl2br( esc_html( $message ) );

		// Resolve the PDF path from the configured URL (per-language file,
		// falling back to the single default).
		$attachment  = '';
		$attach_name = '';
		$pdf_url     = ifa_get_option( 'guide_pdf_url_' . $suffix, '' );
		if ( '' === $pdf_url ) {
			$pdf_url = ifa_get_option( 'guide_pdf_url', '' );
		}
		if ( '' !== $pdf_url ) {
			$upload_dir = wp_get_upload_dir();
			$base       = isset( $upload_dir['baseurl'] ) ? trailingslashit( $upload_dir['baseurl'] ) : '';
			$path       = isset( $upload_dir['basedir'] ) ? trailingslashit( $upload_dir['basedir'] ) : '';
			$http_scheme = is_ssl() ? 'https://' : 'http://';
			// Normalize scheme differences (http vs https) between the saved URL and the uploads base.
			if ( $base && 0 !== strpos( $pdf_url, $base ) && $base !== str_replace( $http_scheme, $http_scheme, $base ) ) {
				$alt_base = str_replace( 'http://', 'https://', $base );
				if ( 0 === strpos( $pdf_url, $alt_base ) ) {
					$base = $alt_base;
				}
			}
			if ( $base && 0 === strpos( $pdf_url, $base ) ) {
				$attachment = $path . ltrim( substr( $pdf_url, strlen( $base ) ), '/' );
				if ( ! file_exists( $attachment ) ) {
					$attachment = '';
				}
			}
			// If the path is outside uploads (e.g. custom folder), fall back to download.
			if ( '' === $attachment && filter_var( $pdf_url, FILTER_VALIDATE_URL ) ) {
				$tmp = download_url( $pdf_url );
				if ( ! is_wp_error( $tmp ) ) {
					$attachment  = $tmp;
					$attach_name = basename( parse_url( $pdf_url, PHP_URL_PATH ) );
				}
			}
		}

		$to      = $email;
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$attachments = $attachment ? array( $attachment ) : array();

		// Capture the actual From address WordPress uses (with FluentSMTP active,
		// this may be rewritten — good for diagnosing mailer routing).
		$GLOBALS['ifa_captured_from'] = '';
		add_filter(
			'wp_mail_from',
			function ( $f ) {
				$GLOBALS['ifa_captured_from'] = $f;
				return $f;
			}
		);

		$sent = wp_mail( $to, $subject, $message, $headers, $attachments );

		// Keep a short delivery log for the admin (useful to debug mailers).
		$message_id = class_exists( 'IFA_Brevo' ) ? (string) get_option( 'ifa_brevo_last_message_id', '' ) : '';
		update_option(
			'ifa_guide_last_send',
			array(
				'ok'        => (bool) $sent,
				'to'        => $to,
				'lang'      => $lang,
				'time'      => current_time( 'mysql' ),
				'attach'    => $attachment ? basename( $attachment ) : 'none',
				'from'      => isset( $GLOBALS['ifa_captured_from'] ) ? $GLOBALS['ifa_captured_from'] : '',
				'message_id'=> $message_id,
				'info'      => $sent ? ( $message_id ? 'Accepted by Brevo — messageId: ' . $message_id : 'wp_mail accepted (check your SMTP provider dashboard for delivery)' ) : 'wp_mail returned false — the email was NOT handed to the mailer',
			)
		);

		return $sent;
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
				'consent'    => isset( $data['consent'] ) ? $data['consent'] : 'yes',
				'ip'         => substr( $data['ip'], 0, 64 ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $ok ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Verify a Google reCAPTCHA v2 token server-side.
	 *
	 * @param string $token The g-recaptcha-response value.
	 * @return bool
	 */
	private static function verify_recaptcha( $token ) {
		$secret = ifa_get_option( 'recaptcha_secret_key', '' );
		if ( '' === $secret ) {
			return true; // not configured — skip.
		}
		if ( '' === $token ) {
			return false;
		}
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$res = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'timeout' => 15,
				'body'    => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => $ip,
				),
			)
		);
		if ( is_wp_error( $res ) ) {
			return false;
		}
		$body = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		return ! empty( $body['success'] );
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

		$guide_ok = '' !== ifa_get_option( 'guide_pdf_url', '' );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Leads', 'ifa-core' ); ?></h1>
			<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'ifa-core' ); ?></a>

			<div class="notice <?php echo $guide_ok ? 'notice-success' : 'notice-warning'; ?>" style="margin:14px 0 8px;">
				<p>
					<strong><?php esc_html_e( 'Free guide auto-delivery', 'ifa-core' ); ?>:</strong>
					<?php if ( $guide_ok ) : ?>
						<?php esc_html_e( 'Active — every new lead automatically receives the guide PDF by email.', 'ifa-core' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Not configured yet.', 'ifa-core' ); ?>
						<?php
						printf(
							/* translators: %s: settings URL. */
							wp_kses_post( 'Upload your guide PDF in <a href="%s">Settings → Inner First Aid</a> and the email will be sent automatically on every signup.' ),
							esc_url( admin_url( 'options-general.php?page=ifa-settings' ) )
						);
						?>
					<?php endif; ?>
				</p>
			</div>
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
						<th><?php esc_html_e( 'Consent', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'IP', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Date', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'ifa-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( ! $rows ) : ?>
					<tr><td colspan="8"><?php esc_html_e( 'No leads yet.', 'ifa-core' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row->id ); ?></td>
							<td><?php echo esc_html( $row->email ); ?></td>
							<td><?php echo esc_html( $row->lang ); ?></td>
							<td><?php echo esc_html( $row->source ); ?></td>
							<td><?php echo 'yes' === $row->consent ? esc_html__( 'Yes', 'ifa-core' ) : esc_html__( 'No', 'ifa-core' ); ?></td>
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
		fputcsv( $out, array( 'id', 'email', 'lang', 'source', 'consent', 'ip', 'created_at' ) );
		foreach ( $rows as $row ) {
			fputcsv( $out, array( $row->id, $row->email, $row->lang, $row->source, isset( $row->consent ) ? $row->consent : 'yes', $row->ip, $row->created_at ) );
		}
		fclose( $out );
		exit;
	}
}
