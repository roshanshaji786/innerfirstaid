<?php
/**
 * Built-in Custom SMTP mailer — uses the hosting email account (e.g. cPanel).
 *
 * When configured, WordPress uses PHPMailer with the given SMTP server for ALL
 * emails (guide PDFs, lead notifications, system mail). No extra plugin needed.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Smtp
 */
class IFA_Smtp {

	/**
	 * Singleton.
	 *
	 * @var IFA_Smtp|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return IFA_Smtp
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
		add_action( 'phpmailer_init', array( $this, 'configure' ) );
		add_action( 'admin_post_ifa_smtp_test', array( $this, 'handle_test' ) );
	}

	/**
	 * Host.
	 *
	 * @return string
	 */
	public static function host() {
		return trim( (string) ifa_get_option( 'smtp_host', '' ) );
	}

	/**
	 * Is Custom SMTP configured?
	 *
	 * @return bool
	 */
	public static function is_configured() {
		return '' !== self::host();
	}

	/**
	 * Configure PHPMailer with the SMTP settings (runs for every wp_mail).
	 *
	 * @param PHPMailer $phpmailer PHPMailer instance.
	 */
	public function configure( $phpmailer ) {
		if ( ! self::is_configured() ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host       = self::host();
		$phpmailer->Port       = (int) ifa_get_option( 'smtp_port', 465 );
		$phpmailer->SMTPAuth   = true;
		$phpmailer->Username   = trim( (string) ifa_get_option( 'smtp_username', '' ) );
		$phpmailer->Password   = (string) ifa_get_option( 'smtp_password', '' );

		$enc = ifa_get_option( 'smtp_encryption', 'ssl' );
		if ( 'none' === $enc ) {
			$phpmailer->SMTPSecure = '';
		} elseif ( 'tls' === $enc ) {
			$phpmailer->SMTPSecure = 'tls';
		} else {
			$phpmailer->SMTPSecure = 'ssl';
		}

		$from_email = trim( (string) ifa_get_option( 'smtp_from_email', '' ) );
		if ( '' !== $from_email && is_email( $from_email ) ) {
			$phpmailer->From = $from_email;
		}
		$from_name = trim( (string) ifa_get_option( 'smtp_from_name', '' ) );
		if ( '' !== $from_name ) {
			$phpmailer->FromName = $from_name;
		}

		// Friendly errors instead of silent failures; fail fast (default 300s is too long).
		$phpmailer->SMTPDebug = 0;
		$phpmailer->Timeout   = 15;
	}

	/**
	 * Send a test email through wp_mail (which now uses the SMTP config).
	 *
	 * @param string $to Recipient.
	 * @return array{ok: bool, detail: string}
	 */
	public static function send_test( $to ) {
		$GLOBALS['ifa_smtp_result'] = '';
		add_action(
			'wp_mail_failed',
			function ( $wp_error ) {
				$GLOBALS['ifa_smtp_result'] = $wp_error->get_error_message();
			}
		);
		add_action(
			'wp_mail_succeeded',
			function () {
				$GLOBALS['ifa_smtp_result'] = 'SUCCESS';
			}
		);

		$sent = wp_mail(
			$to,
			'Inner First Aid — SMTP test ' . gmdate( 'Y-m-d H:i' ),
			'<p>This is a test email sent from your WordPress site through the custom SMTP server (<strong>' . esc_html( self::host() ) . '</strong>).</p><p>If you can read this, email delivery is working.</p>',
			array( 'Content-Type: text/html; charset=UTF-8' )
		);

		$detail = isset( $GLOBALS['ifa_smtp_result'] ) ? $GLOBALS['ifa_smtp_result'] : '';
		if ( $sent ) {
			return array(
				'ok'     => true,
				'detail' => 'wp_mail succeeded. ' . ( 'SUCCESS' === $detail ? 'PHPMailer confirmed the send.' : 'SMTP server accepted the email.' ),
			);
		}
		return array(
			'ok'     => false,
			'detail' => 'Email FAILED: ' . ( '' !== $detail ? $detail : 'the SMTP server rejected the connection. Check host/port/encryption/username/password.' ),
		);
	}

	/**
	 * Admin-post handler: SMTP test.
	 */
	public function handle_test() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied', 'ifa-core' ) );
		}
		check_admin_referer( 'ifa_smtp_test' );

		$to = isset( $_POST['test_email'] ) ? sanitize_email( wp_unslash( $_POST['test_email'] ) ) : '';
		if ( ! is_email( $to ) ) {
			$to = (string) get_option( 'admin_email' );
		}

		$result = self::send_test( $to );
		set_transient( 'ifa_smtp_test_result', $result, 300 );

		wp_safe_redirect( admin_url( 'options-general.php?page=ifa-settings' ) );
		exit;
	}
}
