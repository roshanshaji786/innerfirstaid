<?php
/**
 * Cookie consent banner + consent-gated analytics (GA4, Meta Pixel).
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Consent
 */
class IFA_Consent {

	/**
	 * Singleton.
	 *
	 * @var IFA_Consent|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return IFA_Consent
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
		add_action( 'wp_footer', array( $this, 'render_banner' ), 99 );
	}

	/**
	 * Render the cookie banner markup (shown by JS after a short delay).
	 */
	public function render_banner() {
		$lang   = ifa_get_lang();
		$suffix = 'sl' === $lang ? 'sl' : 'en';

		$text   = ifa_get_option( 'cookie_text_' . $suffix, '' );
		$accept = ifa_get_option( 'cookie_accept_' . $suffix, 'Accept' );
		$decline = ifa_get_option( 'cookie_decline_' . $suffix, 'Decline' );

		if ( '' === $text ) {
			$text = 'sl' === $suffix
				? 'Uporabljamo piskotke za izboljsanje uporabniske izkusnje.'
				: 'We use cookies to improve your experience and analyze site traffic.';
		}

		$ga_id    = trim( ifa_get_option( 'ga_id', '' ) );
		$pixel_id = trim( ifa_get_option( 'pixel_id', '' ) );
		$has_ga   = ( '' !== $ga_id && 'G-XXXXXXXXXX' !== $ga_id && 'placeholder' !== $ga_id );
		$has_px   = ( '' !== $pixel_id && 'XXXXXXXXXXXXXXXX' !== $pixel_id && 'placeholder' !== $pixel_id );

		printf(
			'<div class="ifa-cookie" data-ifa-cookie data-ifa-cookie-text="%1$s" data-ifa-cookie-accept="%2$s" data-ifa-cookie-decline="%3$s" data-ifa-ga="%4$s" data-ifa-pixel="%5$s" hidden>',
			esc_attr( $text ),
			esc_attr( $accept ),
			esc_attr( $decline ),
			esc_attr( $has_ga ? $ga_id : '' ),
			esc_attr( $has_px ? $pixel_id : '' )
		);
		echo '<div class="ifa-cookie__inner">';
		echo '<p class="ifa-cookie__text"></p>';
		echo '<div class="ifa-cookie__actions">';
		echo '<button type="button" class="ifa-cookie__decline" data-ifa-cookie-decline-btn></button>';
		echo '<button type="button" class="ifa-btn ifa-cookie__accept" data-ifa-cookie-accept-btn></button>';
		echo '</div></div></div>';
	}
}
