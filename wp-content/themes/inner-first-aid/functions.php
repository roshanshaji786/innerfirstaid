<?php
/**
 * Inner First Aid theme functions.
 *
 * @package inner-first-aid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IFA_THEME_VERSION', '1.0.0' );
define( 'IFA_THEME_DIR', get_template_directory() );
define( 'IFA_THEME_URI', get_template_directory_uri() );

/**
 * Option helper fallback (normally provided by the IFA Core plugin).
 *
 * @param string $key     Option key.
 * @param mixed  $default Default.
 * @return mixed
 */
if ( ! function_exists( 'ifa_get_option' ) ) {
	function ifa_get_option( $key, $default = '' ) {
		$all = get_option( 'ifa_settings', array() );
		return isset( $all[ $key ] ) ? $all[ $key ] : $default;
	}
}

/**
 * Theme setup.
 */
function ifa_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'inner-first-aid' ),
		)
	);
}
add_action( 'after_setup_theme', 'ifa_theme_setup' );

/**
 * Enqueue styles and scripts.
 */
function ifa_theme_assets() {
	wp_enqueue_style( 'ifa-theme-fonts', IFA_THEME_URI . '/assets/css/fonts.css', array(), IFA_THEME_VERSION );
	wp_enqueue_style( 'ifa-theme', IFA_THEME_URI . '/assets/css/main.css', array( 'ifa-theme-fonts' ), IFA_THEME_VERSION );

	wp_enqueue_script( 'ifa-theme', IFA_THEME_URI . '/assets/js/main.js', array(), IFA_THEME_VERSION, true );

	wp_localize_script(
		'ifa-theme',
		'IFA_THEME',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ifa_theme_assets' );

/**
 * Elementor compatibility: keep default templates.
 */
function ifa_theme_register_elementor_locations( $elementor_theme_manager ) {
	// Intentionally minimal: we only need content templates.
}
add_action( 'elementor/theme/register_locations', 'ifa_theme_register_elementor_locations' );

/**
 * Add body classes: language + page flag.
 *
 * @param array $classes Body classes.
 * @return array
 */
function ifa_theme_body_classes( $classes ) {
	$classes[] = ifa_is_sl() ? 'lang-sl' : 'lang-en';
	$classes[] = is_page() ? 'is-page' : '';
	return $classes;
}
add_filter( 'body_class', 'ifa_theme_body_classes' );

/**
 * Detect Slovenian content: any page under /sl.
 * (Defined by the IFA Core plugin; fallback for theme-only installs.)
 *
 * @return bool
 */
if ( ! function_exists( 'ifa_is_sl' ) ) {
	function ifa_is_sl() {
		if ( function_exists( 'ifa_get_lang' ) ) {
			return 'sl' === ifa_get_lang();
		}
		$path = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return 0 === strpos( $path, '/sl' ) && ! is_admin();
	}
}

/**
 * Set html lang attribute.
 *
 * @param string $lang Full language attribute string.
 * @return string
 */
function ifa_theme_html_lang( $lang ) {
	return ifa_is_sl() ? 'lang="sl-SI"' : 'lang="en-US"';
}
add_filter( 'language_attributes', 'ifa_theme_html_lang' );

/**
 * SEO title: on the static front page use the page title (WP 7 defaults to the site name).
 *
 * @param array $parts Document title parts.
 * @return array
 */
function ifa_theme_document_title_parts( $parts ) {
	if ( is_front_page() && is_page() && ! is_paged() ) {
		$parts['title'] = get_the_title();
	}
	return $parts;
}
add_filter( 'document_title_parts', 'ifa_theme_document_title_parts' );

/**
 * Excerpt length for SEO description.
 *
 * @param int $length Length.
 * @return int
 */
function ifa_theme_excerpt_length( $length ) {
	return 24;
}
add_filter( 'excerpt_length', 'ifa_theme_excerpt_length' );
