<?php
/**
 * Built-in Brevo (Sendinblue) mailer — no extra SMTP plugin needed.
 *
 * When configured, ALL WordPress emails (guide PDFs, lead notifications,
 * password resets, etc.) are sent directly through the Brevo REST API:
 * https://api.brevo.com/v3/smtp/email
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Brevo
 */
class IFA_Brevo {

	const API = 'https://api.brevo.com/v3';

	/**
	 * Singleton.
	 *
	 * @var IFA_Brevo|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return IFA_Brevo
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
		// Route every wp_mail() through Brevo when configured.
		add_filter( 'pre_wp_mail', array( $this, 'route_mail' ), 10, 2 );
		add_action( 'admin_post_ifa_brevo_test', array( $this, 'handle_test' ) );
	}

	/**
	 * Configured API key.
	 *
	 * @return string
	 */
	public static function api_key() {
		return trim( (string) ifa_get_option( 'brevo_api_key', '' ) );
	}

	/**
	 * Sender email (falls back to admin email).
	 *
	 * @return string
	 */
	public static function sender_email() {
		$e = trim( (string) ifa_get_option( 'brevo_sender_email', '' ) );
		return '' !== $e ? $e : (string) get_option( 'admin_email' );
	}

	/**
	 * Sender name.
	 *
	 * @return string
	 */
	public static function sender_name() {
		$n = trim( (string) ifa_get_option( 'brevo_sender_name', '' ) );
		return '' !== $n ? $n : (string) get_bloginfo( 'name' );
	}

	/**
	 * Is Brevo configured (API key present)?
	 *
	 * @return bool
	 */
	public static function is_configured() {
		return '' !== self::api_key();
	}

	/**
	 * pre_wp_mail filter: send through Brevo when configured.
	 *
	 * @param mixed $null Null (short-circuit value).
	 * @param array $atts wp_mail attributes (to, subject, message, headers, attachments).
	 * @return mixed
	 */
	public function route_mail( $null, $atts ) {
		if ( ! self::is_configured() ) {
			return $null; // not configured — let normal wp_mail run.
		}
		return self::send( $atts );
	}

	/**
	 * Send an email via the Brevo API.
	 *
	 * @param array $atts Mail attributes.
	 * @return bool
	 */
	public static function send( $atts ) {
		$to          = isset( $atts['to'] ) ? $atts['to'] : '';
		$subject     = isset( $atts['subject'] ) ? (string) $atts['subject'] : '';
		$message     = isset( $atts['message'] ) ? (string) $atts['message'] : '';
		$attachments = isset( $atts['attachments'] ) ? (array) $atts['attachments'] : array();

		$tos = is_array( $to ) ? $to : array( $to );

		$payload = array(
			'sender'      => array(
				'email' => self::sender_email(),
				'name'  => self::sender_name(),
			),
			'to'          => array(),
			'subject'     => $subject,
			'htmlContent' => $message,
		);

		foreach ( $tos as $t ) {
			if ( is_string( $t ) && is_email( $t ) ) {
				$payload['to'][] = array( 'email' => $t );
			}
		}
		if ( empty( $payload['to'] ) ) {
			self::log( 'error', 'No valid recipient' );
			return false;
		}

		if ( $attachments ) {
			$payload['attachment'] = array();
			foreach ( $attachments as $path ) {
				if ( is_string( $path ) && file_exists( $path ) && is_readable( $path ) ) {
					$mime = wp_check_filetype( $path );
					$payload['attachment'][] = array(
						'name'    => basename( $path ),
						'content' => base64_encode( (string) file_get_contents( $path ) ),
					);
				}
			}
		}

		$response = wp_remote_post(
			self::API . '/smtp/email',
			array(
				'timeout' => 30,
				'headers' => array(
					'api-key'       => self::api_key(),
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			self::log( 'error', 'Network error: ' . $response->get_error_message() );
			return false;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		$ok   = in_array( $code, array( 200, 201 ), true );

		if ( $ok ) {
			// Brevo returns messageIds like "<abc@host>" — strip the angle brackets
			// before sanitizing (sanitize_text_field would treat them as tags).
			$raw        = isset( $body['messageId'] ) ? (string) $body['messageId'] : '';
			$message_id = sanitize_text_field( str_replace( array( '<', '>' ), '', $raw ) );
			update_option( 'ifa_brevo_last_message_id', $message_id );
			self::log( 'ok', 'Accepted by Brevo — messageId: ' . $message_id );
		} else {
			$detail = isset( $body['message'] ) ? $body['message'] : '';
			if ( isset( $body['code'] ) ) {
				$detail .= ' [' . $body['code'] . ']';
			}
			self::log( 'error', 'Brevo rejected (' . $code . '): ' . $detail );
			// Remember the last rejection so the settings page can explain it.
			update_option(
				'ifa_brevo_last_error',
				array(
					'code'   => $code,
					'detail' => $detail,
					'time'   => current_time( 'mysql' ),
				)
			);
		}

		return $ok;
	}

	/**
	 * Write to the plugin error log.
	 *
	 * @param string $level Level.
	 * @param string $msg   Message.
	 */
	private static function log( $level, $msg ) {
		$dir = WP_CONTENT_DIR . '/ifa-logs';
		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir, 0755, true );
		}
		$line = '[' . gmdate( 'Y-m-d H:i:s' ) . '] [' . strtoupper( $level ) . '] ' . $msg . "\n";
		@file_put_contents( $dir . '/brevo.log', $line, FILE_APPEND );
	}

	/**
	 * Admin-post handler: full Brevo verification + test send.
	 */
	public function handle_test() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied', 'ifa-core' ) );
		}
		check_admin_referer( 'ifa_brevo_test' );

		$to = isset( $_POST['test_email'] ) ? sanitize_email( wp_unslash( $_POST['test_email'] ) ) : '';
		if ( ! is_email( $to ) ) {
			$to = (string) get_option( 'admin_email' );
		}

		$results = self::run_checks( $to );
		set_transient( 'ifa_brevo_test_result', $results, 300 );

		wp_safe_redirect( admin_url( 'options-general.php?page=ifa-settings' ) );
		exit;
	}

	/**
	 * Run the verification suite:
	 * 1. API key valid (GET /account)
	 * 2. Sender exists in the senders list (GET /senders)
	 * 3. Real test send via API (POST /smtp/email)
	 *
	 * @param string $to Recipient for the test send.
	 * @return array List of ['type' => ok|error|info, 'label' => string, 'detail' => string].
	 */
	public static function run_checks( $to ) {
		$out = array();

		// 1. API key.
		$key = self::api_key();

		// Sanity-check the key format before hitting the API.
		if ( strlen( $key ) < 30 || 0 !== strpos( $key, 'xkeysib-' ) ) {
			$out[] = array(
				'type'   => 'error',
				'label'  => 'API key',
				'detail' => 'The saved key looks wrong: it must start with "xkeysib-" and be about 70 characters long (saved length: ' . strlen( $key ) . '). You likely copied a partial key or the wrong value. Get it from Brevo → Settings → SMTP & API → "SMTP API key" and copy the WHOLE key.',
			);
			return $out;
		}

		$res = wp_remote_get(
			self::API . '/account',
			array(
				'timeout' => 20,
				'headers' => array(
					'api-key' => $key,
					'Accept'  => 'application/json',
				),
			)
		);
		if ( is_wp_error( $res ) ) {
			$out[] = array( 'type' => 'error', 'label' => 'API key', 'detail' => 'Network error: ' . $res->get_error_message() );
			return $out;
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		$body = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		if ( 200 !== $code ) {
			$msg = isset( $body['message'] ) ? (string) $body['message'] : '';
			$out[] = array(
				'type'   => 'error',
				'label'  => 'API key',
				'detail' => sprintf(
					'Brevo returned HTTP %1$d%2$s. This means the key is invalid or unauthorized for this account. Common causes: (1) you pasted the key from a DIFFERENT Brevo account, (2) the key was copied only partially (it must start with "xkeysib-" and be ~70 characters), (3) the key was regenerated after you pasted it. Fix: Brevo → Settings → SMTP & API → copy the current "SMTP API key" in full (Ctrl+C / Cmd+C), then paste it here again and Save.',
					$code,
					'' !== $msg ? ' — "' . $msg . '"' : ''
				),
			);
			return $out;
		}
		$out[] = array(
			'type'   => 'ok',
			'label'  => 'API key',
			'detail' => 'Valid — Brevo account: ' . ( isset( $body['email'] ) ? $body['email'] : '?' ),
		);

		// 2. Sender verified.
		$res = wp_remote_get(
			self::API . '/senders',
			array(
				'timeout' => 20,
				'headers' => array(
					'api-key' => self::api_key(),
					'Accept'  => 'application/json',
				),
			)
		);
		$senders = array();
		if ( ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res ) ) {
			$senders_body = json_decode( (string) wp_remote_retrieve_body( $res ), true );
			$senders      = isset( $senders_body['senders'] ) && is_array( $senders_body['senders'] ) ? $senders_body['senders'] : array();
		}
		$sender_email = self::sender_email();
		$found        = false;
		$sender_list  = array();
		foreach ( $senders as $s ) {
			$e = isset( $s['email'] ) ? $s['email'] : '';
			if ( '' !== $e ) {
				$sender_list[] = $e;
				if ( strtolower( $e ) === strtolower( $sender_email ) ) {
					$found = true;
				}
			}
		}
		if ( $found ) {
			$out[] = array(
				'type'   => 'ok',
				'label'  => 'Sender',
				'detail' => $sender_email . ' is in your Brevo senders list (verified).',
			);
		} else {
			$out[] = array(
				'type'   => 'error',
				'label'  => 'Sender',
				'detail' => '"' . $sender_email . '" is NOT in your Brevo senders list. Verified senders on this account: ' . ( $sender_list ? implode( ', ', $sender_list ) : '(none found)' ) . '. Set "Brevo sender email" to one of these.',
			);
			return $out;
		}

		// 3. Real test send.
		$test = self::send(
			array(
				'to'          => $to,
				'subject'     => 'Inner First Aid — Brevo test ' . gmdate( 'Y-m-d H:i' ),
				'message'     => '<p>This is a test email sent directly via the Brevo API from your WordPress site.</p><p>If you can read this, email delivery is working.</p>',
				'attachments' => array(),
			)
		);
		if ( $test ) {
			$out[] = array(
				'type'   => 'ok',
				'label'  => 'Test send',
				'detail' => 'Brevo ACCEPTED the email (messageId: ' . get_option( 'ifa_brevo_last_message_id', '' ) . '). It should now be in your inbox — it is also visible in Brevo → Activity → Email sending.',
			);
		} else {
			$err = get_option( 'ifa_brevo_last_error', array() );
			$out[] = array(
				'type'   => 'error',
				'label'  => 'Test send',
				'detail' => 'Brevo rejected the email (HTTP ' . ( isset( $err['code'] ) ? $err['code'] : '?' ) . '): ' . ( isset( $err['detail'] ) ? $err['detail'] : 'unknown' ),
			);
		}

		return $out;
	}
}
