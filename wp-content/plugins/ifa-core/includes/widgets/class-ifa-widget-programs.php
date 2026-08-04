<?php
/**
 * IFA Programs widget — two program cards with Stripe CTA / gender picker.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Widget_Programs
 */
class IFA_Widget_Programs extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ifa-programs';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Program cards', 'ifa-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
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
			'label',
			array(
				'label'   => __( 'Section label', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Programs',
			)
		);

		$this->add_control(
			'card1_title',
			array(
				'label'   => __( 'Card 1 — title', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'After a breakup',
			)
		);

		$this->add_control(
			'card1_text',
			array(
				'label'   => __( 'Card 1 — text', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => "When thoughts of them won't go away.",
			)
		);

		$this->add_control(
			'card1_mode',
			array(
				'label'   => __( 'Card 1 — button mode', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'direct',
				'options' => array(
					'direct' => __( 'Direct link', 'ifa-core' ),
					'gender' => __( 'Gender picker (female / male)', 'ifa-core' ),
				),
			)
		);

		$this->add_control(
			'card1_btn',
			array(
				'label'   => __( 'Card 1 — button text', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Start program ->',
			)
		);

		$this->add_control(
			'card1_btn_female',
			array(
				'label'       => __( 'Card 1 — female button (gender mode)', 'ifa-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'For women',
				'condition'   => array( 'card1_mode' => 'gender' ),
			)
		);

		$this->add_control(
			'card1_btn_male',
			array(
				'label'       => __( 'Card 1 — male button (gender mode)', 'ifa-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'For men',
				'condition'   => array( 'card1_mode' => 'gender' ),
			)
		);

		$this->add_control(
			'card1_link_source',
			array(
				'label'   => __( 'Card 1 — payment link source', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'site_default',
				'options' => array(
					'site_default' => __( 'Use site default (Settings > Inner First Aid)', 'ifa-core' ),
					'custom'       => __( 'Use a custom link below', 'ifa-core' ),
				),
			)
		);

		$this->add_control(
			'card1_custom_link',
			array(
				'label'     => __( 'Card 1 — custom Stripe link', 'ifa-core' ),
				'type'      => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://buy.stripe.com/...',
				'condition' => array( 'card1_link_source' => 'custom' ),
			)
		);

		$this->add_control(
			'card2_title',
			array(
				'label'   => __( 'Card 2 — title', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Insomnia',
			)
		);

		$this->add_control(
			'card2_text',
			array(
				'label'   => __( 'Card 2 — text', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => 'Your path to restful sleep.',
			)
		);

		$this->add_control(
			'card2_btn',
			array(
				'label'   => __( 'Card 2 — button text', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Coming soon',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Resolve the card-1 payment URL.
	 *
	 * @return string
	 */
	private function card1_url() {
		$s = $this->get_settings_for_display();

		if ( isset( $s['card1_link_source'] ) && 'custom' === $s['card1_link_source'] ) {
			$url = isset( $s['card1_custom_link']['url'] ) ? $s['card1_custom_link']['url'] : '';
			return ifa_is_valid_stripe_url( $url ) ? $url : '';
		}

		$lang = ifa_get_lang();
		if ( 'sl' === $lang ) {
			// Gender mode prefers the female link as the default.
			$url = ifa_stripe_url( 'stripe_sl_f' );
			if ( '' === $url ) {
				$url = ifa_stripe_url( 'stripe_en' );
			}
			return $url;
		}

		return ifa_stripe_url( 'stripe_en' );
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$card1_url = $this->card1_url();
		$lang      = ifa_get_lang();
		$gender    = isset( $s['card1_mode'] ) && 'gender' === $s['card1_mode'];

		// Per-gender links (SL gender mode).
		$url_f = ifa_stripe_url( 'stripe_sl_f' );
		$url_m = ifa_stripe_url( 'stripe_sl_m' );
		?>
		<div class="ifa-programs" data-ifa-section="programs" id="programs">
			<div class="ifa-container">
				<?php if ( ! empty( $s['label'] ) ) : ?>
					<span class="ifa-section-label"><?php echo esc_html( $s['label'] ); ?></span>
				<?php endif; ?>

				<div class="ifa-programs__grid">
					<article class="ifa-card ifa-card--active">
						<span class="ifa-card__bar" aria-hidden="true"></span>
						<div class="ifa-card__body">
							<svg class="ifa-card__icon" viewBox="0 0 48 48" fill="none" aria-hidden="true">
								<path d="M12 34C21 33 33 25 38 10C23 12 14 21 12 34Z" fill="currentColor" opacity="0.22"/>
								<path d="M12 36C19 28 27 22 38 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
								<path d="M14 36C20 38 29 36 35 29" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
							</svg>
							<h3 class="ifa-card__title"><?php echo esc_html( $s['card1_title'] ); ?></h3>
							<p class="ifa-card__text"><?php echo esc_html( $s['card1_text'] ); ?></p>

							<?php if ( $gender ) : ?>
								<div class="ifa-card__gender" data-ifa-gender>
									<button type="button" class="ifa-btn ifa-card__gender-toggle" data-ifa-gender-toggle>
										<?php echo esc_html( $s['card1_btn'] ); ?>
									</button>
									<div class="ifa-card__gender-options" data-ifa-gender-options hidden>
										<?php if ( $url_f ) : ?>
											<a class="ifa-btn" href="<?php echo esc_url( $url_f ); ?>"><?php echo esc_html( $s['card1_btn_female'] ); ?></a>
										<?php else : ?>
											<button type="button" class="ifa-btn is-disabled" disabled><?php echo esc_html( $s['card1_btn_female'] ); ?></button>
										<?php endif; ?>
										<?php if ( $url_m ) : ?>
											<a class="ifa-btn" href="<?php echo esc_url( $url_m ); ?>"><?php echo esc_html( $s['card1_btn_male'] ); ?></a>
										<?php else : ?>
											<button type="button" class="ifa-btn is-disabled" disabled><?php echo esc_html( $s['card1_btn_male'] ); ?></button>
										<?php endif; ?>
									</div>
								</div>
							<?php elseif ( $card1_url ) : ?>
								<a class="ifa-btn ifa-card__cta" href="<?php echo esc_url( $card1_url ); ?>"><?php echo esc_html( $s['card1_btn'] ); ?></a>
							<?php else : ?>
								<button type="button" class="ifa-btn is-disabled" disabled><?php echo esc_html( $s['card1_btn'] ); ?></button>
							<?php endif; ?>
						</div>
					</article>

					<article class="ifa-card ifa-card--inactive">
						<span class="ifa-card__bar" aria-hidden="true"></span>
						<div class="ifa-card__body">
							<svg class="ifa-card__icon" viewBox="0 0 48 48" fill="none" aria-hidden="true">
								<path d="M31 8C24 11 19 18 19 26C19 34 25 40 33 41C30 44 26 46 21 46C11 46 3 38 3 28C3 18 11 10 21 10C25 10 28 11 31 8Z" fill="currentColor" opacity="0.28"/>
								<path d="M34 14L35.5 17L39 17.5L36.5 20L37 23.5L34 22L31 23.5L31.5 20L29 17.5L32.5 17L34 14Z" fill="currentColor"/>
							</svg>
							<h3 class="ifa-card__title"><?php echo esc_html( $s['card2_title'] ); ?></h3>
							<p class="ifa-card__text"><?php echo esc_html( $s['card2_text'] ); ?></p>
							<button type="button" class="ifa-btn is-disabled" disabled><?php echo esc_html( $s['card2_btn'] ); ?></button>
						</div>
					</article>
				</div>
			</div>
		</div>
		<?php
	}
}
