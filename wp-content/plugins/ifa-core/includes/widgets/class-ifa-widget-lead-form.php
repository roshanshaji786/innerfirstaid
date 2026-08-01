<?php
/**
 * IFA Lead Form widget — standalone email capture form.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Widget_Lead_Form
 */
class IFA_Widget_Lead_Form extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ifa-lead-form';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Lead form', 'ifa-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'ifa' );
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content',
			array(
				'label' => __( 'Content', 'ifa-core' ),
			)
		);

		$this->add_control(
			'lang',
			array(
				'label'   => __( 'Language (stored with the lead)', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'en',
				'options' => array(
					'en' => 'English',
					'sl' => 'Slovenščina',
				),
			)
		);

		$this->add_control(
			'form_placeholder',
			array(
				'label'   => __( 'Email placeholder', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Enter your email',
			)
		);

		$this->add_control(
			'form_button',
			array(
				'label'   => __( 'Button text', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Get free guide ->',
			)
		);

		$this->add_control(
			'success_text',
			array(
				'label'   => __( 'Success message', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Sent! Check your inbox.',
			)
		);

		$this->add_control(
			'error_text',
			array(
				'label'   => __( 'Error message', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Something went wrong. Please check the email and try again.',
			)
		);

		$this->add_control(
			'invalid_text',
			array(
				'label'   => __( 'Invalid email message', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Please enter a valid email address.',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$s        = $this->get_settings_for_display();
		$lang     = 'sl' === $s['lang'] ? 'sl' : 'en';
		$instance = $this->get_id();
		?>
		<form class="ifa-lead-form" data-ifa-lead-form data-ifa-lang="<?php echo esc_attr( $lang ); ?>" data-ifa-source="widget" data-ifa-success="<?php echo esc_attr( $s['success_text'] ); ?>" data-ifa-error="<?php echo esc_attr( $s['error_text'] ); ?>" data-ifa-invalid="<?php echo esc_attr( $s['invalid_text'] ); ?>" novalidate>
			<label class="screen-reader-text" for="ifa-email-<?php echo esc_attr( $instance ); ?>">
				<?php echo esc_html( ! empty( $s['form_placeholder'] ) ? $s['form_placeholder'] : 'Email' ); ?>
			</label>
			<input
				id="ifa-email-<?php echo esc_attr( $instance ); ?>"
				class="ifa-lead-form__input"
				type="email"
				name="email"
				required
				autocomplete="email"
				placeholder="<?php echo esc_attr( $s['form_placeholder'] ); ?>"
			/>
			<button type="submit" class="ifa-btn ifa-lead-form__submit" data-ifa-lead-submit>
				<?php echo esc_html( $s['form_button'] ); ?>
			</button>
			<input type="text" name="company_website" class="ifa-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
			<span class="ifa-lead-form__status" data-ifa-lead-status role="status" aria-live="polite"></span>
		</form>
		<?php
	}
}
