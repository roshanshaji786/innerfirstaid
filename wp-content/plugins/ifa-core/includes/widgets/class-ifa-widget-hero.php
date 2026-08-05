<?php
/**
 * IFA Hero widget — full-height hero with lead-capture form.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Widget_Hero
 */
class IFA_Widget_Hero extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ifa-hero';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Hero + Lead form', 'ifa-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-banner';
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
			'badge',
			array(
				'label'   => __( 'Badge', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'The international platform for psychological first aid',
			)
		);

		$this->add_control(
			'headline',
			array(
				'label'   => __( 'Headline', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => 'When it hurts - here is step by step.',
			)
		);

		$this->add_control(
			'subheadline',
			array(
				'label'   => __( 'Subheadline', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => 'Free guide: 3 mistakes that prolong the pain. Enter your email and receive it now.',
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
			'form_note',
			array(
				'label'   => __( 'Note under the form', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Free | Anonymous | No credit card',
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

		$this->start_controls_section(
			'background',
			array(
				'label' => __( 'Background', 'ifa-core' ),
			)
		);

		$this->add_control(
			'bg_image',
			array(
				'label'   => __( 'Background image', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'   => __( 'Overlay color', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#0a2312',
			)
		);

		$this->add_control(
			'overlay_opacity',
			array(
				'label'   => __( 'Overlay opacity', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'default' => array(
					'size' => 0.62,
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$lang      = 'sl' === $s['lang'] ? 'sl' : 'en';
		$image_url = ! empty( $s['bg_image']['url'] ) ? $s['bg_image']['url'] : '';
		$opacity   = isset( $s['overlay_opacity']['size'] ) ? (float) $s['overlay_opacity']['size'] : 0.62;
		$opacity   = max( 0, min( 1, $opacity ) );
		$overlay   = ! empty( $s['overlay_color'] ) ? sanitize_hex_color( $s['overlay_color'] ) : '#0a2312';

		$instance = $this->get_id();
		?>
		<section class="ifa-hero" data-ifa-hero>
			<?php if ( $image_url ) : ?>
				<div class="ifa-hero__bg" style="background-image:url('<?php echo esc_url( $image_url ); ?>');"></div>
			<?php endif; ?>
			<?php if ( $overlay ) : ?>
				<div class="ifa-hero__overlay" style="background-color:<?php echo esc_attr( $overlay ); ?>;opacity:<?php echo esc_attr( $opacity ); ?>;"></div>
			<?php endif; ?>

			<div class="ifa-container ifa-hero__inner">
				<?php if ( ! empty( $s['badge'] ) ) : ?>
					<span class="ifa-hero__badge"><?php echo esc_html( $s['badge'] ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $s['headline'] ) ) : ?>
					<h1 class="ifa-hero__title"><?php echo esc_html( $s['headline'] ); ?></h1>
				<?php endif; ?>

				<?php if ( ! empty( $s['subheadline'] ) ) : ?>
					<p class="ifa-hero__sub"><?php echo esc_html( $s['subheadline'] ); ?></p>
				<?php endif; ?>

				<form class="ifa-lead-form" data-ifa-lead-form data-ifa-lang="<?php echo esc_attr( $lang ); ?>" data-ifa-source="hero" data-ifa-success="<?php echo esc_attr( $s['success_text'] ); ?>" data-ifa-error="<?php echo esc_attr( $s['error_text'] ); ?>" data-ifa-invalid="<?php echo esc_attr( $s['invalid_text'] ); ?>" novalidate>
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
					<?php if ( '1' === ifa_get_option( 'lead_consent_enabled', '1' ) ) : ?>
						<label class="ifa-consent">
							<input type="checkbox" name="consent" value="yes" required>
							<span><?php echo esc_html( ifa_get_option( 'lead_consent_label_' . $lang, '' ) ); ?></span>
						</label>
					<?php endif; ?>
					<?php $rc_site = ifa_get_option( 'recaptcha_site_key', '' ); ?>
					<?php if ( '' !== $rc_site ) : ?>
						<div class="ifa-recaptcha" data-ifa-recaptcha data-sitekey="<?php echo esc_attr( $rc_site ); ?>"></div>
					<?php endif; ?>
					<input type="text" name="company_website" class="ifa-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
					<span class="ifa-lead-form__status" data-ifa-lead-status role="status" aria-live="polite"></span>
				</form>

				<?php if ( ! empty( $s['form_note'] ) ) : ?>
					<p class="ifa-hero__note"><?php echo esc_html( $s['form_note'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
