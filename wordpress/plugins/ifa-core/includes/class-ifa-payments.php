<?php
/**
 * Lemon Squeezy webhook: delivers the paid program after a checkout.
 *
 * Flow: buyer pays on a Lemon Squeezy checkout link -> Lemon Squeezy calls
 * /wp-json/ifa/v1/lemonsqueezy-webhook -> we verify the signature, record the
 * order, add the buyer to the program's Brevo list (which starts the 21-day
 * email automation in Brevo) and send a welcome email.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Payments
 */
class IFA_Payments {

	/**
	 * Orders table (without prefix).
	 */
	const TABLE = 'ifa_orders';

	/**
	 * Program slugs. Checkout links carry them as checkout[custom][program].
	 */
	const PROGRAMS = array( 'en', 'sl_f', 'sl_m' );

	/**
	 * Singleton.
	 *
	 * @var IFA_Payments|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return IFA_Payments
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
	 * Public webhook URL to paste into Lemon Squeezy.
	 *
	 * @return string
	 */
	public static function webhook_url() {
		return rest_url( 'ifa/v1/lemonsqueezy-webhook' );
	}

	/**
	 * Language of a program slug.
	 *
	 * @param string $program Program slug.
	 * @return string
	 */
	public static function program_lang( $program ) {
		return 0 === strpos( $program, 'sl' ) ? 'sl' : 'en';
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
			order_ref VARCHAR(191) NOT NULL,
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
			UNIQUE KEY order_ref (order_ref),
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
			'/lemonsqueezy-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_webhook' ),
				'permission_callback' => '__return_true', // Authenticated by the X-Signature header.
			)
		);
	}

	/**
	 * Verify a Lemon Squeezy X-Signature header: hex HMAC-SHA256 of the raw body.
	 *
	 * @param string $payload   Raw request body.
	 * @param string $signature X-Signature header.
	 * @param string $secret    Webhook signing secret.
	 * @return bool
	 */
	public static function verify_signature( $payload, $signature, $secret ) {
		if ( '' === $secret || '' === $signature ) {
			return false;
		}
		$expected = hash_hmac( 'sha256', $payload, $secret );
		return hash_equals( $expected, strtolower( trim( $signature ) ) );
	}

	/**
	 * Webhook handler.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function handle_webhook( $request ) {
		$payload   = (string) $request->get_body();
		$signature = (string) $request->get_header( 'x_signature' );
		$secret    = trim( (string) ifa_get_option( 'lemonsqueezy_webhook_secret', '' ) );

		if ( ! self::verify_signature( $payload, $signature, $secret ) ) {
			self::log_event( 'rejected', 'Invalid or missing X-Signature' );
			return new WP_REST_Response( array( 'error' => 'invalid signature' ), 400 );
		}

		$event = json_decode( $payload, true );
		if ( ! is_array( $event ) || empty( $event['meta']['event_name'] ) || empty( $event['data']['id'] ) ) {
			self::log_event( 'rejected', 'Malformed event' );
			return new WP_REST_Response( array( 'error' => 'malformed event' ), 400 );
		}

		$type = (string) $event['meta']['event_name'];

		try {
			switch ( $type ) {
				case 'order_created':
					$status = isset( $event['data']['attributes']['status'] ) ? (string) $event['data']['attributes']['status'] : '';
					$result = 'paid' === $status ? $this->fulfill( $event ) : 'waiting (status: ' . $status . ')';
					break;
				case 'order_refunded':
					$result = $this->refund( $event );
					break;
				default:
					$result = 'ignored';
			}
		} catch ( Exception $e ) {
			// A 500 makes Lemon Squeezy retry later.
			self::log_event( 'error', $type . ': ' . $e->getMessage() );
			return new WP_REST_Response( array( 'error' => 'processing failed' ), 500 );
		}

		self::log_event( 'ok', $type . ': ' . $result );
		return new WP_REST_Response( array( 'received' => true ), 200 );
	}

	/**
	 * Which program an order bought, from the checkout link's custom data.
	 *
	 * @param array $event Webhook event.
	 * @return string Program slug, or '' when unknown.
	 */
	public static function resolve_program( $event ) {
		$program = isset( $event['meta']['custom_data']['program'] ) ? (string) $event['meta']['custom_data']['program'] : '';
		return in_array( $program, self::PROGRAMS, true ) ? $program : '';
	}

	/**
	 * Record a paid order and deliver the program (idempotent per order).
	 *
	 * @param array $event Webhook event.
	 * @return string Result summary for the log.
	 * @throws Exception When the order cannot be stored.
	 */
	private function fulfill( $event ) {
		global $wpdb;

		$attr      = (array) $event['data']['attributes'];
		$order_ref = (string) $event['data']['id'];

		self::maybe_create_table();
		$table = self::table();

		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE order_ref = %s", $order_ref ) );
		if ( $existing ) {
			return 'already delivered (order #' . $existing . ')';
		}

		$email   = isset( $attr['user_email'] ) ? sanitize_email( $attr['user_email'] ) : '';
		$name    = isset( $attr['user_name'] ) ? sanitize_text_field( $attr['user_name'] ) : '';
		$program = self::resolve_program( $event );
		$lang    = self::program_lang( $program );

		$ok = $wpdb->insert(
			$table,
			array(
				'order_ref'  => $order_ref,
				'email'      => $email,
				'name'       => $name,
				'program'    => $program,
				'amount'     => isset( $attr['total'] ) ? (int) $attr['total'] : 0,
				'currency'   => isset( $attr['currency'] ) ? strtoupper( (string) $attr['currency'] ) : '',
				'status'     => 'paid',
				'livemode'   => empty( $attr['test_mode'] ) ? 1 : 0,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s' )
		);
		if ( ! $ok ) {
			// Lost a race with a parallel retry of the same event.
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE order_ref = %s", $order_ref ) ) ) {
				return 'already delivered';
			}
			throw new Exception( 'Could not store order: ' . $wpdb->last_error );
		}
		$order_id = (int) $wpdb->insert_id;

		$notes = array();
		if ( '' === $program ) {
			$notes[] = 'unknown program in checkout link (check manually)';
		}
		if ( ! is_email( $email ) ) {
			$notes[] = 'no buyer email';
		} else {
			$notes[] = $this->add_to_brevo_list( $email, $name, $program );
			$notes[] = $this->send_welcome( $email, $lang ) ? 'welcome sent' : 'welcome not sent';
		}
		$this->notify_admin( $email, $name, $program, $attr );

		$delivery = implode( '; ', $notes );
		$wpdb->update( $table, array( 'delivery' => substr( $delivery, 0, 255 ) ), array( 'id' => $order_id ), array( '%s' ), array( '%d' ) );

		return 'order #' . $order_id . ' ' . $program . ' — ' . $delivery;
	}

	/**
	 * Mark a refunded order and take the buyer off the program list.
	 *
	 * @param array $event Webhook event.
	 * @return string
	 */
	private function refund( $event ) {
		global $wpdb;

		$order_ref = (string) $event['data']['id'];

		self::maybe_create_table();
		$table = self::table();
		$order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE order_ref = %s", $order_ref ) );
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
	 * @param string $program Program slug.
	 * @return int
	 */
	public static function list_id( $program ) {
		if ( '' === $program ) {
			return 0;
		}
		return absint( ifa_get_option( 'brevo_list_' . $program, 0 ) );
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
	 * @param string $program Program slug.
	 * @return string Result note.
	 */
	private function add_to_brevo_list( $email, $name, $program ) {
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
	 * @param string $program Program slug.
	 * @param array  $attr    Order attributes.
	 */
	private function notify_admin( $email, $name, $program, $attr ) {
		$to = ifa_get_option( 'lead_notify_email', '' );
		if ( ! is_email( $to ) ) {
			return;
		}
		$amount = isset( $attr['total'] ) ? number_format( $attr['total'] / 100, 2 ) : '?';
		$cur    = isset( $attr['currency'] ) ? strtoupper( $attr['currency'] ) : '';
		$mode   = empty( $attr['test_mode'] ) ? '' : ' [TEST]';
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
			'ifa_payments_last_event',
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
			<p><?php esc_html_e( 'Paid Lemon Squeezy orders received by the webhook (latest 200). Lemon Squeezy stays the source of truth for payments.', 'ifa-core' ); ?></p>
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
