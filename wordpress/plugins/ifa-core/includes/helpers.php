<?php
/**
 * Shared helpers for Inner First Aid Core.
 * All functions are guarded so a name collision with another theme/plugin
 * can never cause a fatal error.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a single plugin option.
 *
 * @param string $key     Option key (without prefix).
 * @param mixed  $default Default value.
 * @return mixed
 */
if ( ! function_exists( 'ifa_get_option' ) ) {
	function ifa_get_option( $key, $default = '' ) {
		$all = get_option( 'ifa_settings', array() );
		$val = isset( $all[ $key ] ) ? $all[ $key ] : $default;
		// Empty stored values fall back to the default (keeps links working
		// after site moves / when the user clears a field).
		return '' === $val ? $default : $val;
	}
}

/**
 * Update a single plugin option.
 *
 * @param string $key   Option key.
 * @param mixed  $value Value.
 * @return bool
 */
if ( ! function_exists( 'ifa_update_option' ) ) {
	function ifa_update_option( $key, $value ) {
		$all         = get_option( 'ifa_settings', array() );
		$all[ $key ] = $value;
		return update_option( 'ifa_settings', $all );
	}
}

/**
 * Current site language: 'en' or 'sl'.
 *
 * @return string
 */
if ( ! function_exists( 'ifa_get_lang' ) ) {
	function ifa_get_lang() {
		$path = '';
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$path = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		} elseif ( isset( $_SERVER['PHP_SELF'] ) ) {
			$path = esc_url_raw( wp_unslash( $_SERVER['PHP_SELF'] ) );
		}

		if ( preg_match( '#^/(sl)(/|$)#', $path ) ) {
			return 'sl';
		}

		$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		if ( $home && '/' !== $home && 0 === strpos( $path, $home . 'sl/' ) ) {
			return 'sl';
		}

		return 'en';
	}
}

/**
 * Is the current language Slovenian?
 *
 * @return bool
 */
if ( ! function_exists( 'ifa_is_sl' ) ) {
	function ifa_is_sl() {
		return 'sl' === ifa_get_lang();
	}
}

/**
 * Is a URL a usable Stripe payment link?
 * Accepts Payment Links (buy.stripe.com/...) and hosted Checkout sessions
 * (checkout.stripe.com/c/pay/...).
 *
 * @param string $url URL to check.
 * @return bool
 */
if ( ! function_exists( 'ifa_is_valid_stripe_url' ) ) {
	function ifa_is_valid_stripe_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url || 'placeholder' === $url || false !== strpos( $url, 'REPLACE' ) ) {
			return false;
		}
		return (bool) preg_match( '#^https://(buy\.stripe\.com/|checkout\.stripe\.com/c/pay/).*#', $url );
	}
}

/**
 * Get a valid Stripe payment URL from settings, or ''.
 *
 * @param string $key Option key.
 * @return string
 */
if ( ! function_exists( 'ifa_stripe_url' ) ) {
	function ifa_stripe_url( $key ) {
		$url = trim( (string) ifa_get_option( $key, '' ) );
		if ( ! ifa_is_valid_stripe_url( $url ) ) {
			return '';
		}
		return $url;
	}
}

/**
 * Front-end URL for a page, per language.
 *
 * @param string $lang 'en'|'sl'.
 * @return string
 */
if ( ! function_exists( 'ifa_home_url' ) ) {
	function ifa_home_url( $lang ) {
		if ( 'sl' === $lang ) {
			$url = ifa_get_option( 'sl_url', '' );
			return $url ? $url : home_url( '/sl/' );
		}
		$url = ifa_get_option( 'en_url', '' );
		return $url ? $url : home_url( '/' );
	}
}
