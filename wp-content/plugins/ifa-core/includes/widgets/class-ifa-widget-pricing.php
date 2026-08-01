<?php
/**
 * IFA Pricing widget — CTA band with old/new price and Stripe button.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Widget_Pricing
 */
class IFA_Widget_Pricing extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ifa-pricing';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Pricing / CTA', 'ifa-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-price-list';
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
			'title',
			array(
				'label'   => __( 'Title', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '21-day breakup recovery program',
			)
		);

		$this->add_control(
			'price_old',
			array(
				'label'   => __( 'Old price', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'EUR 110.90',
			)
		);

		$this->add_control(
			'price_new',
			array(
				'label'   => __( 'New price', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'EUR 39.90',
			)
		);

		$this->add_control(
			'price_note',
			array(
				'label'   => __( 'Price note', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '| Instant access',
			)
		);

		$this->add_control(
			'btn_text',
			array(
				'label'   => __( 'Button text', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Start now ->',
			)
		);

		$this->add_control(
			'note',
			array(
				'label'   => __( 'Note', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Anonymous | Instant access | No risk',
			)
		);

		$this->add_control(
			'link_source',
			array(
				'label'   => __( 'Payment link source', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'site_default',
				'options' => array(
					'site_default' => __( 'Use site default (Settings > Inner First Aid)', 'ifa-core' ),
					'custom'       => __( 'Use a custom link below', 'ifa-core' ),
				),
			)
		);

		$this->add_control(
			'custom_link',
			array(
				'label'       => __( 'Custom Stripe link', 'ifa-core' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://buy.stripe.com/...',
				'condition'   => array( 'link_source' => 'custom' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'Style', 'ifa-core' ),
			)
		);

		$this->add_control(
			'bg_color',
			array(
				'label'   => __( 'Background color', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#1a4d2e',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$url = '';
		if ( isset( $s['link_source'] ) && 'custom' === $s['link_source'] ) {
			$url = isset( $s['custom_link']['url'] ) ? $s['custom_link']['url'] : '';
			if ( '' !== $url && ! preg_match( '#^https://buy\.stripe\.com/.*#', $url ) ) {
				$url = '';
			}
		} else {
			$url = ifa_stripe_url( 'sl' === ifa_get_lang() ? 'stripe_sl_f' : 'stripe_en' );
			if ( '' === $url ) {
				$url = ifa_stripe_url( 'stripe_en' );
			}
		}

		$bg = ! empty( $s['bg_color'] ) ? sanitize_hex_color( $s['bg_color'] ) : '#1a4d2e';
		?>
		<div class="ifa-pricing" style="background-color:<?php echo esc_attr( $bg ); ?>;">
			<div class="ifa-container ifa-pricing__inner">
				<h2 class="ifa-pricing__title"><?php echo esc_html( $s['title'] ); ?></h2>

				<div class="ifa-pricing__prices">
					<?php if ( ! empty( $s['price_old'] ) ) : ?>
						<span class="ifa-pricing__old"><?php echo esc_html( $s['price_old'] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $s['price_new'] ) ) : ?>
						<span class="ifa-pricing__new"><?php echo esc_html( $s['price_new'] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $s['price_note'] ) ) : ?>
						<span class="ifa-pricing__note"><?php echo esc_html( $s['price_note'] ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( $url ) : ?>
					<a class="ifa-btn ifa-pricing__btn" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $s['btn_text'] ); ?></a>
				<?php else : ?>
					<button type="button" class="ifa-btn ifa-pricing__btn is-disabled" disabled><?php echo esc_html( $s['btn_text'] ); ?></button>
				<?php endif; ?>

				<?php if ( ! empty( $s['note'] ) ) : ?>
					<p class="ifa-pricing__note-small"><?php echo esc_html( $s['note'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
