<?php
/**
 * Settings page: Settings > Inner First Aid.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Settings
 */
class IFA_Settings {

	/**
	 * Option name.
	 */
	const OPTION = 'ifa_settings';

	/**
	 * Singleton.
	 *
	 * @var IFA_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return IFA_Settings
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
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Defaults.
	 */
	public static function defaults() {
		$current = get_option( self::OPTION, array() );
		$defaults = array(
			'stripe_en'              => '',
			'stripe_sl_f'            => '',
			'stripe_sl_m'            => '',
			'ga_id'                  => '',
			'pixel_id'               => '',
			'lead_notify_email'      => '',
			'brevo_api_key'          => '',
			'brevo_sender_email'     => '',
			'brevo_sender_name'      => '',
			'recaptcha_site_key'     => '',
			'recaptcha_secret_key'   => '',
			'lead_consent_enabled'   => '1',
			'lead_consent_label_en'  => 'I agree to receive the free guide and occasional emails. See the Privacy Policy.',
			'lead_consent_label_sl'  => 'Strinjam se, da prejmem brezplačni vodnik in občasna sporočila. Glej Politiko zasebnosti.',
			'guide_pdf_url'          => '',
			'guide_pdf_url_en'       => '',
			'guide_pdf_url_sl'       => '',
			'guide_subject_en'       => 'Your free guide: 3 mistakes that prolong the pain',
			'guide_subject_sl'       => 'Tvoj brezplacni vodic: 3 napake, ki podaljsajo bolecino',
			'guide_message_en'       => "Hi,\n\nthanks for signing up. Your free guide is attached.\n\nBest,\nthe Inner First Aid team",
			'guide_message_sl'       => "Zivjo,\n\nhvala, da si se prijavil/a. V prilogi je tvoj brezplacni vodic.\n\nLep pozdrav,\nekipa Inner First Aid",
			'cookie_text_en'         => 'We use cookies to improve your experience and analyze site traffic.',
			'cookie_text_sl'         => 'Uporabljamo piskotke za izboljsanje uporabniske izkusnje.',
			'cookie_accept_en'       => 'Accept',
			'cookie_accept_sl'       => 'Sprejmi',
			'cookie_decline_en'      => 'Decline',
			'cookie_decline_sl'      => 'Zavrni',
			'header_cta_en'          => 'Get started free',
			'header_cta_sl'          => 'Zacni brezplacno',
			'header_logo_text'       => 'innerfirstaid.com',
			'footer_copy_en'         => 'Copyright 2026 Inner First Aid',
			'footer_copy_sl'         => 'Copyright 2026 Inner First Aid',
			'footer_disclaimer_en'   => 'This program is for educational purposes only. It is not a substitute for professional support.',
			'footer_disclaimer_sl'   => 'Ta program je za izobrazevalne namene. Ni nadomestek za strokovno pomoc.',
			'footer_privacy_en'      => 'Privacy Policy',
			'footer_privacy_sl'      => 'Politika zasebnosti',
			'footer_terms_en'        => 'Terms of Service',
			'footer_terms_sl'        => 'Pogoji uporabe',
			'en_url'                 => '',
			'sl_url'                 => '',
			'privacy_url'            => '',
			'terms_url'              => '',
		);
		$merged = array_merge( $defaults, is_array( $current ) ? $current : array() );
		update_option( self::OPTION, $merged );
	}

	/**
	 * Register the admin menu page.
	 */
	public function register_menu() {
		add_options_page(
			__( 'Inner First Aid', 'ifa-core' ),
			__( 'Inner First Aid', 'ifa-core' ),
			'manage_options',
			'ifa-settings',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register settings + fields.
	 */
	public function register_settings() {
		register_setting(
			self::OPTION,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
			)
		);

		add_settings_section( 'ifa_payments', __( 'Payments (Stripe)', 'ifa-core' ), array( $this, 'section_payments' ), 'ifa-settings' );
		add_settings_section( 'ifa_brevo', __( 'Email delivery (Brevo — built-in)', 'ifa-core' ), array( $this, 'section_brevo' ), 'ifa-settings' );
		add_settings_section( 'ifa_spam', __( 'Form protection (reCAPTCHA) & consent', 'ifa-core' ), array( $this, 'section_spam' ), 'ifa-settings' );
		add_settings_section( 'ifa_analytics', __( 'Analytics (loaded only after cookie consent)', 'ifa-core' ), '__return_false', 'ifa-settings' );
		add_settings_section( 'ifa_leads', __( 'Leads & free guide', 'ifa-core' ), array( $this, 'section_leads' ), 'ifa-settings' );
		add_settings_section( 'ifa_branding', __( 'Header & footer texts', 'ifa-core' ), '__return_false', 'ifa-settings' );
		add_settings_section( 'ifa_cookie', __( 'Cookie banner', 'ifa-core' ), '__return_false', 'ifa-settings' );
		add_settings_section( 'ifa_links', __( 'Page URLs', 'ifa-core' ), '__return_false', 'ifa-settings' );

		$this->add_field( 'ifa_payments', 'stripe_en', __( 'Stripe link — English program', 'ifa-core' ), 'url' );
		$this->add_field( 'ifa_payments', 'stripe_sl_f', __( 'Stripe link — Slovenian (female)', 'ifa-core' ), 'url' );
		$this->add_field( 'ifa_payments', 'stripe_sl_m', __( 'Stripe link — Slovenian (male)', 'ifa-core' ), 'url' );

		$this->add_field( 'ifa_brevo', 'brevo_api_key', __( 'Brevo SMTP API key (Settings → SMTP & API, starts with xkeysib-)', 'ifa-core' ), 'password' );
		$this->add_field( 'ifa_brevo', 'brevo_sender_email', __( 'Brevo sender email (must be verified in Brevo → Senders)', 'ifa-core' ), 'email' );
		$this->add_field( 'ifa_brevo', 'brevo_sender_name', __( 'Brevo sender name (shown as the From name)', 'ifa-core' ), 'text' );

		$this->add_field( 'ifa_spam', 'recaptcha_site_key', __( 'reCAPTCHA v2 Site key (google.com/recaptcha/admin → create → v2 "I\'m not a robot")', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_spam', 'recaptcha_secret_key', __( 'reCAPTCHA v2 Secret key', 'ifa-core' ), 'password' );
		$this->add_field( 'ifa_spam', 'lead_consent_enabled', __( 'Show consent checkbox on the lead form', 'ifa-core' ), 'checkbox' );
		$this->add_field( 'ifa_spam', 'lead_consent_label_en', __( 'Consent label (EN)', 'ifa-core' ), 'textarea' );
		$this->add_field( 'ifa_spam', 'lead_consent_label_sl', __( 'Consent label (SL)', 'ifa-core' ), 'textarea' );

		$this->add_field( 'ifa_analytics', 'ga_id', __( 'Google Analytics 4 ID (e.g. G-XXXXXXXXXX)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_analytics', 'pixel_id', __( 'Meta Pixel ID (e.g. 1234567890)', 'ifa-core' ), 'text' );

		$this->add_field( 'ifa_leads', 'lead_notify_email', __( 'Email to notify on new lead (optional)', 'ifa-core' ), 'email' );

		// Free guide delivery.
		$this->add_field( 'ifa_leads', 'guide_subject_en', __( 'Guide email subject (EN)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_leads', 'guide_subject_sl', __( 'Guide email subject (SL)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_leads', 'guide_message_en', __( 'Guide email message (EN)', 'ifa-core' ), 'textarea' );
		$this->add_field( 'ifa_leads', 'guide_message_sl', __( 'Guide email message (SL)', 'ifa-core' ), 'textarea' );
		$this->add_field( 'ifa_leads', 'guide_pdf_url_en', __( 'Guide PDF URL — English (upload the PDF in Media → Library, copy its URL, paste here)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_leads', 'guide_pdf_url_sl', __( 'Guide PDF URL — Slovenian (leave empty to reuse the English one)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_leads', 'guide_pdf_url', __( 'Fallback guide PDF URL (used when a language has no specific file)', 'ifa-core' ), 'text' );

		$this->add_field( 'ifa_branding', 'header_logo_text', __( 'Logo text', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_branding', 'header_cta_en', __( 'Header button (EN)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_branding', 'header_cta_sl', __( 'Header button (SL)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_branding', 'footer_copy_en', __( 'Footer copyright (EN)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_branding', 'footer_copy_sl', __( 'Footer copyright (SL)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_branding', 'footer_disclaimer_en', __( 'Footer disclaimer (EN)', 'ifa-core' ), 'textarea' );
		$this->add_field( 'ifa_branding', 'footer_disclaimer_sl', __( 'Footer disclaimer (SL)', 'ifa-core' ), 'textarea' );
		$this->add_field( 'ifa_branding', 'footer_privacy_en', __( 'Privacy link label (EN)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_branding', 'footer_privacy_sl', __( 'Privacy link label (SL)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_branding', 'footer_terms_en', __( 'Terms link label (EN)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_branding', 'footer_terms_sl', __( 'Terms link label (SL)', 'ifa-core' ), 'text' );

		$this->add_field( 'ifa_cookie', 'cookie_text_en', __( 'Banner text (EN)', 'ifa-core' ), 'textarea' );
		$this->add_field( 'ifa_cookie', 'cookie_text_sl', __( 'Banner text (SL)', 'ifa-core' ), 'textarea' );
		$this->add_field( 'ifa_cookie', 'cookie_accept_en', __( 'Accept label (EN)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_cookie', 'cookie_accept_sl', __( 'Accept label (SL)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_cookie', 'cookie_decline_en', __( 'Decline label (EN)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_cookie', 'cookie_decline_sl', __( 'Decline label (SL)', 'ifa-core' ), 'text' );

		$this->add_field( 'ifa_links', 'en_url', __( 'English home URL (leave empty — auto-uses the site home)', 'ifa-core' ), 'url' );
		$this->add_field( 'ifa_links', 'sl_url', __( 'Slovenian home URL (leave empty — auto-uses /sl/)', 'ifa-core' ), 'url' );
		$this->add_field( 'ifa_links', 'privacy_url', __( 'Privacy policy URL (leave empty — auto-uses /privacy/)', 'ifa-core' ), 'url' );
		$this->add_field( 'ifa_links', 'terms_url', __( 'Terms URL (leave empty — auto-uses /terms/)', 'ifa-core' ), 'url' );
	}

	/**
	 * Add a settings field.
	 *
	 * @param string $section Section id.
	 * @param string $key     Option key.
	 * @param string $label   Label.
	 * @param string $type    Field type.
	 */
	private function add_field( $section, $key, $label, $type ) {
		add_settings_field(
			$key,
			$label,
			array( $this, 'render_field' ),
			'ifa-settings',
			$section,
			array( 'key' => $key, 'type' => $type )
		);
	}

	/**
	 * Render a field.
	 *
	 * @param array $args Field args.
	 */
	public function render_field( $args ) {
		$key  = $args['key'];
		$type = isset( $args['type'] ) ? $args['type'] : 'text';
		$val  = ifa_get_option( $key, '' );
		$name = self::OPTION . '[' . esc_attr( $key ) . ']';

		if ( 'textarea' === $type ) {
			printf(
				'<textarea name="%s" rows="3" class="large-text">%s</textarea>',
				esc_attr( $name ),
				esc_textarea( $val )
			);
			return;
		}

		if ( 'checkbox' === $type ) {
			printf(
				'<input type="checkbox" name="%s" value="1" %s/>',
				esc_attr( $name ),
				checked( '1', $val, false )
			);
			return;
		}

		$input_type = 'text';
		if ( 'email' === $type ) {
			$input_type = 'email';
		} elseif ( 'password' === $type ) {
			$input_type = 'password';
		}

		printf(
			'<input type="%s" name="%s" value="%s" class="regular-text" %s/>',
			esc_attr( $input_type ),
			esc_attr( $name ),
			esc_attr( $val ),
			'password' === $type ? 'autocomplete="new-password" ' : ''
		);

		// Live status for Stripe link fields.
		if ( in_array( $key, array( 'stripe_en', 'stripe_sl_f', 'stripe_sl_m' ), true ) ) {
			if ( '' !== $val && function_exists( 'ifa_is_valid_stripe_url' ) ) {
				if ( ifa_is_valid_stripe_url( $val ) ) {
					echo ' <span style="color:#1a7f37;font-weight:600;">✔ ' . esc_html__( 'active — buttons are live', 'ifa-core' ) . '</span>';
				} else {
					echo ' <span style="color:#b35900;font-weight:600;">⚠ ' . esc_html__( 'not accepted — must start with https://buy.stripe.com/ or https://checkout.stripe.com/c/pay/', 'ifa-core' ) . '</span>';
				}
			} elseif ( '' === $val ) {
				echo ' <span style="color:#9ca3af;">— ' . esc_html__( 'empty (buttons stay disabled)', 'ifa-core' ) . '</span>';
			}
		}

		// Live status for the Brevo API key field.
		if ( 'brevo_api_key' === $key ) {
			if ( '' === $val ) {
				echo ' <span style="color:#9ca3af;">— ' . esc_html__( 'empty (emails use the default WordPress mailer)', 'ifa-core' ) . '</span>';
			} elseif ( strlen( $val ) < 30 || 0 !== strpos( $val, 'xkeysib-' ) ) {
				echo ' <span style="color:#b3261e;font-weight:600;">✖ ' . esc_html__( 'invalid format — must start with xkeysib- and be ~70 characters. Re-copy the key from Brevo → Settings → SMTP & API.', 'ifa-core' ) . '</span>';
			} else {
				echo ' <span style="color:#1a7f37;font-weight:600;">✔ ' . esc_html__( 'format OK (length ' . strlen( $val ) . ') — click "Verify Brevo" to confirm', 'ifa-core' ) . '</span>';
			}
		}
	}

	/**
	 * Form protection section help.
	 */
	public function section_spam() {
		echo '<p class="description">' . esc_html__( 'Protects the lead forms from bots (required by Brevo for transactional sending). Google reCAPTCHA v2 checkbox is verified on the server for every submission. The consent checkbox documents opt-in for every lead.', 'ifa-core' ) . '</p>';
		echo '<ol class="description" style="list-style:decimal;margin-left:1.2em;">';
		echo '<li>' . esc_html__( 'Go to google.com/recaptcha/admin → Create → reCAPTCHA v2 → "I\'m not a robot" Checkbox.', 'ifa-core' ) . '</li>';
		echo '<li>' . esc_html__( 'Add the domain (e.g. innerfirstaid.com) → Submit → copy the Site key and Secret key.', 'ifa-core' ) . '</li>';
		echo '<li>' . esc_html__( 'Paste them below and save. The forms will then show the checkbox + captcha automatically.', 'ifa-core' ) . '</li>';
		echo '</ol>';
	}

	/**
	 * Brevo section help.
	 */
	public function section_brevo() {
		echo '<p class="description">' . esc_html__( 'When the API key is set, ALL site emails (guide PDFs, lead notifications, WordPress system emails) are sent directly through Brevo — no separate SMTP plugin needed. You can deactivate FluentSMTP.', 'ifa-core' ) . '</p>';
		echo '<ol class="description" style="list-style:decimal;margin-left:1.2em;">';
		echo '<li>' . esc_html__( 'Brevo dashboard → Settings → SMTP & API → copy the "SMTP API key" (starts with xkeysib-).', 'ifa-core' ) . '</li>';
		echo '<li>' . esc_html__( 'Paste it below, plus a sender email that is verified in Brevo → Settings → Senders.', 'ifa-core' ) . '</li>';
		echo '<li>' . esc_html__( 'Save, then use the "Verify Brevo connection" button — it checks the key, the sender and sends a real test email to your inbox.', 'ifa-core' ) . '</li>';
		echo '</ol>';
	}

	/**
	 * Leads & free guide section help.
	 */
	public function section_leads() {
		echo '<p class="description">' . esc_html__( 'The guide email (with the PDF attached) is sent automatically to every lead after they submit the form — in their language.', 'ifa-core' ) . '</p>';
		echo '<ol class="description" style="list-style:decimal;margin-left:1.2em;">';
		echo '<li>' . esc_html__( 'Upload the guide PDFs: Media → Add New (or drag & drop).', 'ifa-core' ) . '</li>';
		echo '<li>' . esc_html__( 'Open each file in the Media Library and copy its URL (ends with .pdf).', 'ifa-core' ) . '</li>';
		echo '<li>' . esc_html__( 'Paste the English PDF into "Guide PDF URL — English" and the Slovenian one into "Guide PDF URL — Slovenian", then save. Emails with the attachment are sent automatically from now on.', 'ifa-core' ) . '</li>';
		echo '</ol>';
		echo '<p class="description">' . esc_html__( 'Tip: install a free SMTP plugin (WP Mail SMTP / FluentSMTP) on the host so the emails land in the inbox, not spam.', 'ifa-core' ) . '</p>';
	}

	/**
	 * Payments section help.
	 */
	public function section_payments() {
		echo '<p class="description">' . esc_html__( 'Create Payment Links in your Stripe Dashboard and paste them here. Buttons stay disabled until a valid https://buy.stripe.com/ link is set.', 'ifa-core' ) . '</p>';
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize( $input ) {
		$all  = get_option( self::OPTION, array() );
		$out  = is_array( $all ) ? $all : array();

		$texts = array(
			'header_logo_text', 'header_cta_en', 'header_cta_sl',
			'footer_copy_en', 'footer_copy_sl', 'footer_disclaimer_en', 'footer_disclaimer_sl',
			'footer_privacy_en', 'footer_privacy_sl', 'footer_terms_en', 'footer_terms_sl',
			'cookie_text_en', 'cookie_text_sl', 'cookie_accept_en', 'cookie_accept_sl',
			'cookie_decline_en', 'cookie_decline_sl', 'ga_id', 'pixel_id',
			'guide_subject_en', 'guide_subject_sl', 'guide_message_en', 'guide_message_sl',
		);
		foreach ( $texts as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = sanitize_text_field( $input[ $k ] );
			}
		}

		// Brevo API key: keep the full key (may contain spaces? no — but preserve exact value).
		if ( isset( $input['brevo_api_key'] ) ) {
			$out['brevo_api_key'] = trim( sanitize_text_field( $input['brevo_api_key'] ) );
		}
		if ( isset( $input['brevo_sender_email'] ) ) {
			$out['brevo_sender_email'] = sanitize_email( $input['brevo_sender_email'] );
		}
		if ( isset( $input['brevo_sender_name'] ) ) {
			$out['brevo_sender_name'] = sanitize_text_field( $input['brevo_sender_name'] );
		}

		// reCAPTCHA + consent.
		if ( isset( $input['recaptcha_site_key'] ) ) {
			$out['recaptcha_site_key'] = trim( sanitize_text_field( $input['recaptcha_site_key'] ) );
		}
		if ( isset( $input['recaptcha_secret_key'] ) ) {
			$out['recaptcha_secret_key'] = trim( sanitize_text_field( $input['recaptcha_secret_key'] ) );
		}
		if ( isset( $input['lead_consent_enabled'] ) ) {
			$out['lead_consent_enabled'] = '1';
		} else {
			$out['lead_consent_enabled'] = '0';
		}
		foreach ( array( 'lead_consent_label_en', 'lead_consent_label_sl' ) as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = sanitize_text_field( $input[ $k ] );
			}
		}

		$urls = array( 'stripe_en', 'stripe_sl_f', 'stripe_sl_m', 'en_url', 'sl_url', 'privacy_url', 'terms_url', 'guide_pdf_url', 'guide_pdf_url_en', 'guide_pdf_url_sl' );
		foreach ( $urls as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = esc_url_raw( trim( $input[ $k ] ) );
			}
		}

		if ( isset( $input['lead_notify_email'] ) ) {
			$out['lead_notify_email'] = sanitize_email( $input['lead_notify_email'] );
		}

		return $out;
	}

	/**
	 * Render the settings page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show the result of a manual test guide send.
		$test_result = get_transient( 'ifa_test_guide_result' );

		// Show the result of a Brevo connection verification.
		$brevo_result = get_transient( 'ifa_brevo_test_result' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Inner First Aid settings', 'ifa-core' ); ?></h1>
			<?php settings_errors(); ?>

			<?php if ( is_array( $brevo_result ) ) : ?>
				<div class="notice notice-info" style="margin:12px 0;">
					<p><strong><?php esc_html_e( 'Brevo connection check', 'ifa-core' ); ?></strong></p>
					<ul style="margin:0 0 6px 18px;list-style:disc;">
						<?php foreach ( $brevo_result as $r ) : ?>
							<li style="margin:3px 0;">
								<span style="font-weight:600;color:<?php echo 'ok' === $r['type'] ? '#1a7f37' : ( 'error' === $r['type'] ? '#b3261e' : '#555' ); ?>;">
									<?php echo 'ok' === $r['type'] ? '✔' : ( 'error' === $r['type'] ? '✖' : '•' ); ?>
									<?php echo esc_html( $r['label'] ); ?>:
								</span>
								<?php echo esc_html( $r['detail'] ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( is_array( $test_result ) ) : ?>
				<div class="notice <?php echo ! empty( $test_result['ok'] ) ? 'notice-success' : 'notice-error'; ?>">
					<p>
						<strong><?php esc_html_e( 'Test guide email', 'ifa-core' ); ?>:</strong>
						<?php echo ! empty( $test_result['ok'] ) ? esc_html__( 'wp_mail accepted the send.', 'ifa-core' ) : esc_html__( 'wp_mail FAILED — the email was not sent.', 'ifa-core' ); ?>
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: email, 2: language, 3: time. */
								__( 'To: %1$s (%2$s) at %3$s.', 'ifa-core' ),
								$test_result['to'],
								strtoupper( $test_result['lang'] ),
								$test_result['time']
							)
						);
						?>
					</p>
					<?php if ( ! empty( $test_result['info'] ) ) : ?>
						<p style="margin-top:4px;"><?php echo esc_html( $test_result['info'] ); ?></p>
					<?php endif; ?>
					<p style="margin:6px 0 0;color:#6b7280;">
						<?php esc_html_e( 'If wp_mail accepted but the email did not arrive, the problem is in the SMTP provider — check FluentSMTP → Email Logs and your Brevo/SMTP dashboard (the send must appear there).', 'ifa-core' ); ?>
					</p>
				</div>
			<?php endif; ?>
			<?php if ( isset( $_GET['ifa_built'] ) && '1' === $_GET['ifa_built'] ) : ?>
				<div class="notice notice-success">
					<p>
						<strong><?php esc_html_e( 'Pages built successfully!', 'ifa-core' ); ?></strong>
						<?php esc_html_e( 'Open Pages to see them, and edit any page with Elementor.', 'ifa-core' ); ?>
					</p>
				</div>
			<?php endif; ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION );
				do_settings_sections( 'ifa-settings' );
				submit_button();
				?>
			</form>

			<hr>
			<h2><?php esc_html_e( 'Verify Brevo connection', 'ifa-core' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Checks the API key, confirms your sender is verified, and sends a real test email to your inbox via the Brevo API.', 'ifa-core' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px;">
				<input type="hidden" name="action" value="ifa_brevo_test">
				<?php wp_nonce_field( 'ifa_brevo_test' ); ?>
				<input type="email" name="test_email" required placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="regular-text">
				<?php submit_button( __( 'Verify Brevo & send test email', 'ifa-core' ), 'primary', 'submit', false ); ?>
			</form>
			<?php if ( class_exists( 'IFA_Brevo' ) && IFA_Brevo::is_configured() ) : ?>
				<p class="description" style="margin-top:6px;">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: sender email, 2: last message id. */
							__( 'Brevo active — sender: %1$s. Last Brevo messageId: %2$s.', 'ifa-core' ),
							IFA_Brevo::sender_email(),
							get_option( 'ifa_brevo_last_message_id', '—' )
						)
					);
					?>
				</p>
			<?php endif; ?>

			<hr>
			<h2><?php esc_html_e( 'Test the guide email delivery', 'ifa-core' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Send the guide email right now to verify your mailer (FluentSMTP / Brevo) is working. Use your own inbox.', 'ifa-core' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px;">
				<input type="hidden" name="action" value="ifa_test_guide">
				<?php wp_nonce_field( 'ifa_test_guide' ); ?>
				<input type="email" name="test_email" required placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="regular-text">
				<select name="test_lang">
					<option value="en">English</option>
					<option value="sl">Slovenščina</option>
				</select>
				<?php submit_button( __( 'Send test guide email', 'ifa-core' ), 'secondary', 'submit', false ); ?>
			</form>
			<?php
			$last = get_option( 'ifa_guide_last_send', array() );
			if ( ! empty( $last ) ) :
				?>
				<p class="description" style="margin-top:8px;">
					<strong><?php esc_html_e( 'Last automatic guide send:', 'ifa-core' ); ?></strong>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: ok/failed, 2: to, 3: lang, 4: attachment, 5: from, 6: time. */
							__( '%1$s → %2$s (%3$s), attachment: %4$s, from: %5$s, at %6$s', 'ifa-core' ),
							! empty( $last['ok'] ) ? 'OK' : 'FAILED',
							isset( $last['to'] ) ? $last['to'] : '-',
							isset( $last['lang'] ) ? strtoupper( $last['lang'] ) : '-',
							isset( $last['attach'] ) ? $last['attach'] : 'none',
							isset( $last['from'] ) && '' !== $last['from'] ? $last['from'] : '(unknown)',
							isset( $last['time'] ) ? $last['time'] : '-'
						)
					);
					?>
				</p>
			<?php endif; ?>

			<hr>
			<h2><?php esc_html_e( 'Build the pages (Elementor)', 'ifa-core' ); ?></h2>
			<p>
				<?php esc_html_e( 'Creates the home pages (EN + SL), privacy and terms pages, and makes every page editable in the Elementor editor.', 'ifa-core' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ifa_build_pages">
				<?php wp_nonce_field( 'ifa_build_pages' ); ?>
				<?php submit_button( __( 'Build pages now', 'ifa-core' ), 'primary', 'submit', false ); ?>
			</form>
			<p class="description">
				<?php esc_html_e( 'Existing pages with the same slugs are updated in place — their Elementor content is replaced.', 'ifa-core' ); ?>
			</p>
		</div>
		<?php
	}
}
