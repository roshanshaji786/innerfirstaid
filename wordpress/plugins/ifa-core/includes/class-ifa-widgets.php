<?php
/**
 * Elementor widget registration.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Widgets
 */
class IFA_Widgets {

	/**
	 * Singleton.
	 *
	 * @var IFA_Widgets|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return IFA_Widgets
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
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_categories' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );

		// The theme self-hosts Inter; do not pull Google Fonts on the front end
		// (faster, GDPR-friendly, works on private/offline hosts).
		add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );
	}

	/**
	 * Register the "Inner First Aid" category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elements manager.
	 */
	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'ifa',
			array(
				'title' => __( 'Inner First Aid', 'ifa-core' ),
				'icon'  => 'eicon-heart',
			)
		);
	}

	/**
	 * Register widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		$files = array(
			'hero',
			'lead-form',
			'social-proof',
			'programs',
			'how-it-works',
			'pricing',
			'lang-switch',
		);

		foreach ( $files as $file ) {
			$path = IFA_CORE_DIR . 'includes/widgets/class-ifa-widget-' . $file . '.php';
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}

		$widgets = array(
			'IFA_Widget_Hero',
			'IFA_Widget_Lead_Form',
			'IFA_Widget_Social_Proof',
			'IFA_Widget_Programs',
			'IFA_Widget_How_It_Works',
			'IFA_Widget_Pricing',
			'IFA_Widget_Lang_Switch',
		);

		foreach ( $widgets as $class ) {
			if ( class_exists( $class ) ) {
				$widgets_manager->register( new $class() );
			}
		}
	}
}
