<?php
/**
 * Plugin Name: Inner First Aid Core
 * Description: Core functionality for Inner First Aid: Elementor widgets, lead capture (with admin list & CSV export), Stripe payment links, cookie consent with analytics, multi-language (EN/SL) and one-click page builder.
 * Version: 1.0.1
 * Author: Inner First Aid
 * Text Domain: ifa-core
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Graceful PHP version check — never fatal on old hosts.
 */
if ( version_compare( PHP_VERSION, '7.2', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p><strong>Inner First Aid Core:</strong> ';
			echo esc_html( sprintf( 'This plugin requires PHP 7.2 or newer. Your server runs PHP %s. Please ask your host to update PHP.', PHP_VERSION ) );
			echo '</p></div>';
		}
	);
	return;
}

define( 'IFA_CORE_VERSION', '1.0.1' );
define( 'IFA_CORE_FILE', __FILE__ );
define( 'IFA_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'IFA_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once IFA_CORE_DIR . 'includes/helpers.php';
require_once IFA_CORE_DIR . 'includes/class-ifa-settings.php';
require_once IFA_CORE_DIR . 'includes/class-ifa-leads.php';
require_once IFA_CORE_DIR . 'includes/class-ifa-consent.php';
require_once IFA_CORE_DIR . 'includes/class-ifa-builder.php';
require_once IFA_CORE_DIR . 'includes/class-ifa-widgets.php';

/**
 * Activation: safe defaults + lazy table creation.
 * All DB work is deferred so activation never fails on restrictive hosts.
 */
function ifa_core_activate() {
	try {
		IFA_Settings::defaults();
	} catch ( Exception $e ) {
		// Never break activation because of settings.
	}
}
register_activation_hook( __FILE__, 'ifa_core_activate' );

/**
 * Create the leads table lazily (first admin page load or first lead).
 */
function ifa_core_maybe_create_tables() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	try {
		IFA_Leads::maybe_create_table();
	} catch ( Exception $e ) {
		// Fail soft; the table is re-checked on every lead insert.
	}
}
add_action( 'admin_init', 'ifa_core_maybe_create_tables' );

/**
 * Bootstrap.
 */
function ifa_core_init() {
	if ( ! class_exists( 'IFA_Settings' ) ) {
		return;
	}

	IFA_Settings::instance();
	IFA_Leads::instance();
	IFA_Consent::instance();
	IFA_Builder::instance();

	if ( did_action( 'elementor/loaded' ) ) {
		if ( class_exists( 'IFA_Widgets' ) ) {
			IFA_Widgets::instance();
		}
	} elseif ( class_exists( 'IFA_Widgets' ) ) {
		add_action( 'elementor/loaded', array( 'IFA_Widgets', 'instance' ) );
	}

	// Admin notice if Elementor is missing.
	add_action(
		'admin_notices',
		function () {
			if ( did_action( 'elementor/loaded' ) || ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			echo '<div class="notice notice-warning"><p><strong>Inner First Aid Core:</strong> ';
			echo esc_html__( 'Please install and activate the free Elementor plugin for the editable front end.', 'ifa-core' );
			echo '</p></div>';
		}
	);
}
add_action( 'plugins_loaded', 'ifa_core_init', 20 );

/**
 * Enqueue front-end assets (widget styles + behaviour).
 */
function ifa_core_frontend_assets() {
	if ( ! did_action( 'elementor/loaded' ) && ! is_admin() ) {
		return;
	}

	wp_register_style( 'ifa-widgets', IFA_CORE_URL . 'assets/css/widgets.css', array(), IFA_CORE_VERSION );
	wp_register_script( 'ifa-frontend', IFA_CORE_URL . 'assets/js/frontend.js', array(), IFA_CORE_VERSION, true );

	if ( did_action( 'elementor/loaded' ) && \Elementor\Plugin::$instance->frontend ) {
		$post_id = get_the_ID();
		// Enqueue on Elementor pages only (cheap check).
		if ( $post_id && \Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id ) ) {
			wp_enqueue_style( 'ifa-widgets' );
			wp_enqueue_script( 'ifa-frontend' );
		}
	}

	wp_localize_script(
		'ifa-frontend',
		'IFA_FRONTEND',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ifa_lead_nonce' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ifa_core_frontend_assets' );
