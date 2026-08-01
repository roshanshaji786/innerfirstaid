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
		add_settings_section( 'ifa_analytics', __( 'Analytics (loaded only after cookie consent)', 'ifa-core' ), '__return_false', 'ifa-settings' );
		add_settings_section( 'ifa_leads', __( 'Leads', 'ifa-core' ), '__return_false', 'ifa-settings' );
		add_settings_section( 'ifa_branding', __( 'Header & footer texts', 'ifa-core' ), '__return_false', 'ifa-settings' );
		add_settings_section( 'ifa_cookie', __( 'Cookie banner', 'ifa-core' ), '__return_false', 'ifa-settings' );
		add_settings_section( 'ifa_links', __( 'Page URLs', 'ifa-core' ), '__return_false', 'ifa-settings' );

		$this->add_field( 'ifa_payments', 'stripe_en', __( 'Stripe link — English program', 'ifa-core' ), 'url' );
		$this->add_field( 'ifa_payments', 'stripe_sl_f', __( 'Stripe link — Slovenian (female)', 'ifa-core' ), 'url' );
		$this->add_field( 'ifa_payments', 'stripe_sl_m', __( 'Stripe link — Slovenian (male)', 'ifa-core' ), 'url' );

		$this->add_field( 'ifa_analytics', 'ga_id', __( 'Google Analytics 4 ID (e.g. G-XXXXXXXXXX)', 'ifa-core' ), 'text' );
		$this->add_field( 'ifa_analytics', 'pixel_id', __( 'Meta Pixel ID (e.g. 1234567890)', 'ifa-core' ), 'text' );

		$this->add_field( 'ifa_leads', 'lead_notify_email', __( 'Email to notify on new lead (optional)', 'ifa-core' ), 'email' );

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

		printf(
			'<input type="%s" name="%s" value="%s" class="regular-text" />',
			esc_attr( 'email' === $type ? 'email' : 'text' ),
			esc_attr( $name ),
			esc_attr( $val )
		);
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
		);
		foreach ( $texts as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = sanitize_text_field( $input[ $k ] );
			}
		}

		$urls = array( 'stripe_en', 'stripe_sl_f', 'stripe_sl_m', 'en_url', 'sl_url', 'privacy_url', 'terms_url' );
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
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Inner First Aid settings', 'ifa-core' ); ?></h1>
			<?php settings_errors(); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION );
				do_settings_sections( 'ifa-settings' );
				submit_button();
				?>
			</form>
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
