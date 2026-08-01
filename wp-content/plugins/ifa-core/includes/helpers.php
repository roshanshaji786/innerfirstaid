<?php
/**
 * Shared helpers for Inner First Aid Core.
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
function ifa_get_option( $key, $default = '' ) {
	$all = get_option( 'ifa_settings', array() );
	return isset( $all[ $key ] ) ? $all[ $key ] : $default;
}

/**
 * Update a single plugin option.
 *
 * @param string $key   Option key.
 * @param mixed  $value Value.
 * @return bool
 */
function ifa_update_option( $key, $value ) {
	$all       = get_option( 'ifa_settings', array() );
	$all[ $key ] = $value;
	return update_option( 'ifa_settings', $all );
}

/**
 * Current site language: 'en' or 'sl'.
 *
 * @return string
 */
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

/**
 * Is the current language Slovenian?
 *
 * @return bool
 */
function ifa_is_sl() {
	return 'sl' === ifa_get_lang();
}

/**
 * Get a valid Stripe payment URL from settings, or ''.
 *
 * @param string $key Option key.
 * @return string
 */
function ifa_stripe_url( $key ) {
	$url = trim( (string) ifa_get_option( $key, '' ) );
	if ( '' === $url || 'placeholder' === $url || false !== strpos( $url, 'REPLACE' ) ) {
		return '';
	}
	if ( ! preg_match( '#^https://buy\.stripe\.com/.*#', $url ) ) {
		return '';
	}
	return $url;
}

/**
 * Front-end URL for a page, per language.
 *
 * @param string $lang 'en'|'sl'.
 * @return string
 */
function ifa_home_url( $lang ) {
	if ( 'sl' === $lang ) {
		$url = ifa_get_option( 'sl_url', '' );
		return $url ? $url : home_url( '/sl/' );
	}
	$url = ifa_get_option( 'en_url', '' );
	return $url ? $url : home_url( '/' );
}
