<?php
/**
 * Inner First Aid Theme functions and definitions
 *
 * @package InnerFirstAid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Setup theme common features.
 */
function innerfirstaid_theme_setup() {
	// Let WordPress manage the document title
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails on posts and pages
	add_theme_support( 'post-thumbnails' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// HTML5 markup support for search, comments, etc.
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Register Primary Menu
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'innerfirstaid' ),
	) );
}
add_action( 'after_setup_theme', 'innerfirstaid_theme_setup' );

/**
 * Enqueue scripts and styles.
 */
function innerfirstaid_theme_scripts() {
	// Google Fonts - Inter
	wp_enqueue_style( 'innerfirstaid-font-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', array(), null );

	// Tailwind CSS via CDN for instant compatibility with layout utilities
	wp_enqueue_style( 'innerfirstaid-tailwind-cdn', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', array(), '2.2.19' );

	// Theme Main Stylesheet
	wp_enqueue_style( 'innerfirstaid-theme-style', get_stylesheet_uri(), array( 'innerfirstaid-tailwind-cdn' ), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'innerfirstaid_theme_scripts' );

/**
 * Declare Elementor support so that Elementor knows it can build on this theme seamlessly.
 */
function innerfirstaid_elementor_support() {
	add_theme_support( 'elementor' );
}
add_action( 'after_setup_theme', 'innerfirstaid_elementor_support' );

/**
 * Register Elementor Custom Theme locations (Header/Footer) if Elementor Pro is active.
 */
function innerfirstaid_register_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_locations();
}
add_action( 'elementor/theme/register_locations', 'innerfirstaid_register_elementor_locations' );

/**
 * Register WordPress Customizer options for setting Stripe Payment links dynamically.
 */
function innerfirstaid_customize_register( $wp_customize ) {
	// Stripe Links section
	$wp_customize->add_section( 'innerfirstaid_stripe_section', array(
		'title'       => esc_html__( 'Stripe Payment Links', 'innerfirstaid' ),
		'priority'    => 30,
		'description' => esc_html__( 'Configure your live Stripe Payment Link URLs here. These links populate your buttons dynamically across the site.', 'innerfirstaid' ),
	) );

	// English Stripe Link
	$wp_customize->add_setting( 'stripe_link_en', array(
		'default'           => 'https://buy.stripe.com/REPLACE_ENGLISH_LINK',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'stripe_link_en', array(
		'label'    => esc_html__( 'English Stripe Checkout Link', 'innerfirstaid' ),
		'section'  => 'innerfirstaid_stripe_section',
		'type'     => 'url',
	) );

	// Slovenian Stripe Link - Female
	$wp_customize->add_setting( 'stripe_link_sl_f', array(
		'default'           => 'https://buy.stripe.com/REPLACE_SLOVENIAN_FEMALE',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'stripe_link_sl_f', array(
		'label'    => esc_html__( 'Slovenian Stripe Link (Female)', 'innerfirstaid' ),
		'section'  => 'innerfirstaid_stripe_section',
		'type'     => 'url',
	) );

	// Slovenian Stripe Link - Male
	$wp_customize->add_setting( 'stripe_link_sl_m', array(
		'default'           => 'https://buy.stripe.com/REPLACE_SLOVENIAN_MALE',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'stripe_link_sl_m', array(
		'label'    => esc_html__( 'Slovenian Stripe Link (Male)', 'innerfirstaid' ),
		'section'  => 'innerfirstaid_stripe_section',
		'type'     => 'url',
	) );
}
add_action( 'customize_register', 'innerfirstaid_customize_register' );
