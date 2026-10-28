<?php
/**
 * Stripe webhook: delivers the paid program after a Payment Link checkout.
 *
 * Flow: buyer pays on a Stripe Payment Link -> Stripe calls
 * /wp-json/ifa/v1/stripe-webhook -> we verify the signature, record the
 * order, add the buyer to the program's Brevo list (which starts the
 * 21-day email automation in Brevo) and send a welcome email.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Stripe
 */
class IFA_Stripe {

	/**
	 * Orders table (without prefix).
	 */
	const TABLE = 'ifa_orders';

	/**
	 * Allowed signature age in seconds (Stripe's default tolerance).
	 */
	const TOLERANCE = 300;

	/**
	 * Program keys = the Payment Link option keys.
	 */
	const PROGRAMS = array( 'stripe_en', 'stripe_sl_f', 'stripe_sl_m' );

	/**
	 * Singleton.
	 *
	 * @var IFA_Stripe|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return IFA_Stripe
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
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ), 20 );
	}

	/**
	 * Public webhook URL to paste into Stripe.
	 *
	 * @return string
	 */
	public static function webhook_url() {
		return rest_url( 'ifa/v1/stripe-webhook' );
	}

	/**
	 * Language of a program key.
	 *
	 * @param string $program Program key.
	 * @return string
	 */
	public static function program_lang( $program ) {
		return 0 === strpos( $program, 'stripe_sl' ) ? 'sl' : 'en';
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
			session_id VARCHAR(191) NOT NULL,
			payment_intent VARCHAR(191) NOT NULL DEFAULT '',
			email VARCHAR(191) NOT NULL DEFAULT '',
			name VARCHAR(191) NOT NULL DEFAULT '',
			program VARCHAR(32) NOT NULL DEFAULT '',
			amount BIGINT(20) NOT NULL DEFAULT 0,
			currency VARCHAR(10) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'paid',
			livemode TINYINT(1) NOT NULL DEFAULT 0,
			delivery VARCHAR(255) NOT NULL DEFAULT '',
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY session_id (session_id),
			KEY payment_intent (payment_intent),
			KEY email (email)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Register the REST route.
	 */
	public function register_route() {
		register_rest_route(
			'ifa/v1',
			'/stripe-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_webhook' ),
				'permission_callback' => '__return_true', // Authenticated by the Stripe signature.
			)
		);
	}

	/**
	 * Verify a Stripe-Signature header.
	 *
	 * @param string $payload Raw request body.
	 * @param string $header  Stripe-Signature header.
	 * @param string $secret  Endpoint signing secret (whsec_...).
	 * @return bool
	 */
	public static function verify_signature( $payload, $header, $secret ) {
		if ( '' === $secret || '' === $header ) {
			return false;
		}

		$timestamp  = 0;
		$signatures = array();
		foreach ( explode( ',', $header ) as $part ) {
			$kv = explode( '=', trim( $part ), 2 );
			if ( 2 !== count( $kv ) ) {
				continue;
			}
			if ( 't' === $kv[0] ) {
				$timestamp = (int) $kv[1];
			} elseif ( 'v1' === $kv[0] ) {
				$signatures[] = $kv[1];
			}
		}

		if ( ! $timestamp || empty( $signatures ) || abs( time() - $timestamp ) > self::TOLERANCE ) {
			return false;
		}

		$expected = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );
		foreach ( $signatures as $sig ) {
			if ( hash_equals( $expected, $sig ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Webhook handler.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function handle_webhook( $request ) {
		$payload = (string) $request->get_body();
		$header  = (string) $request->get_header( 'stripe_signature' );
		$secret  = trim( (string) ifa_get_option( 'stripe_webhook_secret', '' ) );

		if ( ! self::verify_signature( $payload, $header, $secret ) ) {
			self::log_event( 'rejected', 'Invalid or missing Stripe signature' );
			return new WP_REST_Response( array( 'error' => 'invalid signature' ), 400 );
		}

		$event = json_decode( $payload, true );
		if ( ! is_array( $event ) || empty( $event['type'] ) || ! isset( $event['data']['object'] ) ) {
			self::log_event( 'rejected', 'Malformed event' );
			return new WP_REST_Response( array( 'error' => 'malformed event' ), 400 );
		}

		$type   = (string) $event['type'];
		$object = (array) $event['data']['object'];

		try {
			switch ( $type ) {
				case 'checkout.session.completed':
					// Card payments are paid now; bank methods (SEPA etc.) may
					// still be pending and arrive as async_payment_succeeded.
					if ( isset( $object['payment_status'] ) && 'unpaid' === $object['payment_status'] ) {
						$result = 'waiting for async payment';
					} else {
						$result = $this->fulfill( $object );
					}
					break;
				case 'checkout.session.async_payment_succeeded':
					$result = $this->fulfill( $object );
					break;
				case 'charge.refunded':
					$result = $this->refund( $object );
					break;
				default:
					$result = 'ignored';
			}
		} catch ( Exception $e ) {
			// A 500 makes Stripe retry later.
			self::log_event( 'error', $type . ': ' . $e->getMessage() );
			return new WP_REST_Response( array( 'error' => 'processing failed' ), 500 );
		}

		self::log_event( 'ok', $type . ': ' . $result );
		return new WP_REST_Response( array( 'received' => true ), 200 );
	}

	/**
	 * Which program a Checkout Session bought.
	 * Payment Link metadata "ifa_program" wins, then the client_reference_id
	 * that ifa_stripe_url() adds to every button link.
	 *
	 * @param array $session Checkout Session.
	 * @return string
	 */
	public static function resolve_program( $session ) {
		$candidates = array();
		if ( isset( $session['metadata']['ifa_program'] ) ) {
			$candidates[] = (string) $session['metadata']['ifa_program'];
		}
		if ( isset( $session['client_reference_id'] ) ) {
			$candidates[] = (string) $session['client_reference_id'];
		}
		foreach ( $candidates as $c ) {
			$c = 0 === strpos( $c, 'stripe_' ) ? $c : 'stripe_' . $c; // Accept "en" or "stripe_en".
			if ( in_array( $c, self::PROGRAMS, true ) ) {
				return $c;
			}
		}
		return 'stripe_en';
	}

	/**
	 * Record a paid order and deliver the program (idempotent per session).
	 *
	 * @param array $session Checkout Session.
	 * @return string Result summary for the log.
	 * @throws Exception When the order cannot be stored.
	 */
	private function fulfill( $session ) {
		global $wpdb;

		$session_id = isset( $session['id'] ) ? (string) $session['id'] : '';
		if ( '' === $session_id ) {
			throw new Exception( 'Session without id' );
		}

		self::maybe_create_table();
		$table = self::table();

		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE session_id = %s", $session_id ) );
		if ( $existing ) {
			return 'already delivered (order #' . $existing . ')';
		}

		$details = isset( $session['customer_details'] ) ? (array) $session['customer_details'] : array();
		$email   = isset( $details['email'] ) ? sanitize_email( $details['email'] ) : '';
		if ( '' === $email && isset( $session['customer_email'] ) ) {
			$email = sanitize_email( $session['customer_email'] );
		}
		$name    = isset( $details['name'] ) ? sanitize_text_field( $details['name'] ) : '';
		$program = self::resolve_program( $session );
		$lang    = self::program_lang( $program );

		$ok = $wpdb->insert(
			$table,
			array(
				'session_id'     => $session_id,
				'payment_intent' => isset( $session['payment_intent'] ) ? (string) $session['payment_intent'] : '',
				'email'          => $email,
				'name'           => $name,
				'program'        => $program,
				'amount'         => isset( $session['amount_total'] ) ? (int) $session['amount_total'] : 0,
				'currency'       => isset( $session['currency'] ) ? strtoupper( (string) $session['currency'] ) : '',
				'status'         => 'paid',
				'livemode'       => ! empty( $session['livemode'] ) ? 1 : 0,
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s' )
		);
		if ( ! $ok ) {
			// Lost a race with a parallel retry of the same event.
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE session_id = %s", $session_id ) ) ) {
				return 'already delivered';
			}
			throw new Exception( 'Could not store order: ' . $wpdb->last_error );
		}
		$order_id = (int) $wpdb->insert_id;

		$notes = array();
		if ( ! is_email( $email ) ) {
			$notes[] = 'no buyer email';
		} else {
			$notes[] = $this->add_to_brevo_list( $email, $name, $program, $lang );
			$notes[] = $this->send_welcome( $email, $lang ) ? 'welcome sent' : 'welcome not sent';
		}
		$this->notify_admin( $email, $name, $program, $session );

		$delivery = implode( '; ', $notes );
		$wpdb->update( $table, array( 'delivery' => substr( $delivery, 0, 255 ) ), array( 'id' => $order_id ), array( '%s' ), array( '%d' ) );

		return 'order #' . $order_id . ' ' . $program . ' — ' . $delivery;
	}

	/**
	 * Mark a fully refunded order and take the buyer off the program list.
	 *
	 * @param array $charge Charge object.
	 * @return string
	 */
	private function refund( $charge ) {
		global $wpdb;

		if ( empty( $charge['refunded'] ) || empty( $charge['payment_intent'] ) ) {
			return 'partial refund, no change';
		}

		self::maybe_create_table();
		$table = self::table();
		$order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE payment_intent = %s", (string) $charge['payment_intent'] ) );
		if ( ! $order ) {
			return 'no matching order';
		}

		$wpdb->update( $table, array( 'status' => 'refunded' ), array( 'id' => $order->id ), array( '%s' ), array( '%d' ) );

		$list = self::list_id( $order->program );
		if ( $list && class_exists( 'IFA_Brevo' ) && IFA_Brevo::is_configured() && is_email( $order->email ) ) {
			wp_remote_post(
				IFA_Brevo::API . '/contacts/lists/' . $list . '/contacts/remove',
				array(
					'timeout' => 15,
					'headers' => self::brevo_headers(),
					'body'    => wp_json_encode( array( 'emails' => array( $order->email ) ) ),
				)
			);
		}
		return 'order #' . $order->id . ' refunded';
	}

	/**
	 * Brevo list id configured for a program.
	 *
	 * @param string $program Program key.
	 * @return int
	 */
	public static function list_id( $program ) {
		return absint( ifa_get_option( 'brevo_list_' . substr( $program, 7 ), 0 ) );
	}

	/**
	 * Headers for Brevo API calls.
	 *
	 * @return array
	 */
	private static function brevo_headers() {
		return array(
			'api-key'      => IFA_Brevo::api_key(),
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		);
	}

	/**
	 * Add (or update) the buyer in the program's Brevo list.
	 *
	 * @param string $email   Buyer email.
	 * @param string $name    Buyer name.
	 * @param string $program Program key.
	 * @param string $lang    Language.
	 * @return string Result note.
	 */
	private function add_to_brevo_list( $email, $name, $program, $lang ) {
		$list = self::list_id( $program );
		if ( ! $list ) {
			return 'no Brevo list set';
		}
		if ( ! class_exists( 'IFA_Brevo' ) || ! IFA_Brevo::is_configured() ) {
			return 'Brevo API key missing';
		}

		$attributes = array();
		if ( '' !== $name ) {
			$parts                   = explode( ' ', $name, 2 );
			$attributes['FIRSTNAME'] = $parts[0];
			if ( isset( $parts[1] ) ) {
				$attributes['LASTNAME'] = $parts[1];
			}
		}

		$body = array(
			'email'         => $email,
			'listIds'       => array( $list ),
			'updateEnabled' => true,
		);
		if ( $attributes ) {
			$body['attributes'] = $attributes;
		}

		$res = wp_remote_post(
			IFA_Brevo::API . '/contacts',
			array(
				'timeout' => 15,
				'headers' => self::brevo_headers(),
				'body'    => wp_json_encode( $body ),
			)
		);
		if ( is_wp_error( $res ) ) {
			return 'Brevo error: ' . $res->get_error_message();
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		if ( $code >= 200 && $code < 300 ) {
			return 'added to Brevo list ' . $list;
		}

		// Brevo rejects some attribute names on accounts without them; retry bare.
		if ( $attributes ) {
			unset( $body['attributes'] );
			$res  = wp_remote_post(
				IFA_Brevo::API . '/contacts',
				array(
					'timeout' => 15,
					'headers' => self::brevo_headers(),
					'body'    => wp_json_encode( $body ),
				)
			);
			$code = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
			if ( $code >= 200 && $code < 300 ) {
				return 'added to Brevo list ' . $list;
			}
		}
		return 'Brevo HTTP ' . $code;
	}

	/**
	 * Send the buyer a purchase confirmation / welcome email.
	 *
	 * @param string $email Buyer email.
	 * @param string $lang  Language.
	 * @return bool
	 */
	private function send_welcome( $email, $lang ) {
		$subject = trim( (string) ifa_get_option( 'welcome_subject_' . $lang, '' ) );
		if ( '' === $subject ) {
			$subject = 'sl' === $lang ? 'Dobrodosel/a v 21-dnevnem programu' : 'Welcome to the 21-day program';
		}
		$message = trim( (string) ifa_get_option( 'welcome_message_' . $lang, '' ) );
		if ( '' === $message ) {
			$message = 'sl' === $lang
				? "Zivjo,\n\nhvala za nakup. Tvoj 21-dnevni program se zacne zdaj: naslednjih 21 dni bos vsak dan prejel/a eno sporocilo.\n\nLep pozdrav,\nekipa Inner First Aid"
				: "Hi,\n\nthank you for your purchase. Your 21-day program starts now: you will receive one email per day for the next 21 days.\n\nBest,\nthe Inner First Aid team";
		}
		return wp_mail( $email, $subject, nl2br( esc_html( $message ) ), array( 'Content-Type: text/html; charset=UTF-8' ) );
	}

	/**
	 * Tell the site owner about a sale.
	 *
	 * @param string $email   Buyer email.
	 * @param string $name    Buyer name.
	 * @param string $program Program key.
	 * @param array  $session Checkout Session.
	 */
	private function notify_admin( $email, $name, $program, $session ) {
		$to = ifa_get_option( 'lead_notify_email', '' );
		if ( ! is_email( $to ) ) {
			return;
		}
		$amount = isset( $session['amount_total'] ) ? number_format( $session['amount_total'] / 100, 2 ) : '?';
		$cur    = isset( $session['currency'] ) ? strtoupper( $session['currency'] ) : '';
		$mode   = ! empty( $session['livemode'] ) ? '' : ' [TEST]';
		wp_mail(
			$to,
			'New Inner First Aid order' . $mode . ': ' . $amount . ' ' . $cur,
			esc_html( sprintf( 'Buyer: %s <%s> — program: %s — amount: %s %s', $name, $email, $program, $amount, $cur ) )
		);
	}

	/**
	 * Keep the last webhook result for the settings page.
	 *
	 * @param string $status ok|rejected|error.
	 * @param string $detail Detail.
	 */
	private static function log_event( $status, $detail ) {
		update_option(
			'ifa_stripe_last_event',
			array(
				'status' => $status,
				'detail' => substr( $detail, 0, 300 ),
				'time'   => current_time( 'mysql' ),
			),
			false
		);
	}

	/**
	 * Register the Orders admin page under IFA Leads.
	 */
	public function register_menu() {
		add_submenu_page(
			'ifa-leads',
			__( 'IFA Orders', 'ifa-core' ),
			__( 'Orders', 'ifa-core' ),
			'manage_options',
			'ifa-orders',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render the Orders admin page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::maybe_create_table();

		global $wpdb;
		$table = self::table();
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 200" );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Orders', 'ifa-core' ); ?></h1>
			<p><?php esc_html_e( 'Paid Stripe checkouts received by the webhook (latest 200). Stripe stays the source of truth for payments.', 'ifa-core' ); ?></p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>ID</th>
						<th><?php esc_html_e( 'Date', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Buyer', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Program', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Status', 'ifa-core' ); ?></th>
						<th><?php esc_html_e( 'Delivery', 'ifa-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr><td colspan="7"><?php esc_html_e( 'No orders yet.', 'ifa-core' ); ?></td></tr>
					<?php endif; ?>
					<?php foreach ( $rows as $r ) : ?>
						<tr>
							<td><?php echo (int) $r->id; ?></td>
							<td><?php echo esc_html( $r->created_at ); ?></td>
							<td><?php echo esc_html( trim( $r->name . ' <' . $r->email . '>' ) ); ?></td>
							<td><?php echo esc_html( $r->program ); ?></td>
							<td><?php echo esc_html( number_format( $r->amount / 100, 2 ) . ' ' . $r->currency ); ?></td>
							<td><?php echo esc_html( $r->status . ( $r->livemode ? '' : ' (test)' ) ); ?></td>
							<td><?php echo esc_html( $r->delivery ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
