<?php
/**
 * IFA How It Works widget — numbered steps.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Widget_How_It_Works
 */
class IFA_Widget_How_It_Works extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ifa-how-it-works';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'How it works', 'ifa-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-number-field';
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
				'default' => 'How it works',
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'num',
			array(
				'label'   => __( 'Number', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '1',
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Enter your email',
			)
		);

		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => 'Get the free guide instantly.',
			)
		);

		$this->add_control(
			'steps',
			array(
				'label'       => __( 'Steps', 'ifa-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ num }}}. {{{ title }}}',
				'default'     => array(
					array( 'num' => '1', 'title' => 'Enter your email', 'text' => 'Get the free guide instantly.' ),
					array( 'num' => '2', 'title' => 'Read it', 'text' => "5 minutes. 3 mistakes you're probably making." ),
					array( 'num' => '3', 'title' => 'Start the 21-day program', 'text' => 'EUR 39.90. One email per day. Step by step.' ),
				),
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
				'default' => '#f8fdf9',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$steps = isset( $s['steps'] ) && is_array( $s['steps'] ) ? $s['steps'] : array();
		$bg    = ! empty( $s['bg_color'] ) ? sanitize_hex_color( $s['bg_color'] ) : '#f8fdf9';

		if ( ! $steps ) {
			return;
		}
		?>
		<div class="ifa-steps" style="background-color:<?php echo esc_attr( $bg ); ?>;">
			<div class="ifa-container">
				<?php if ( ! empty( $s['label'] ) ) : ?>
					<span class="ifa-section-label"><?php echo esc_html( $s['label'] ); ?></span>
				<?php endif; ?>

				<div class="ifa-steps__row">
					<?php foreach ( $steps as $i => $step ) : ?>
						<div class="ifa-steps__item">
							<div class="ifa-steps__num"><?php echo esc_html( $step['num'] ); ?></div>
							<div class="ifa-steps__content">
								<h3 class="ifa-steps__title"><?php echo esc_html( $step['title'] ); ?></h3>
								<p class="ifa-steps__text"><?php echo esc_html( $step['text'] ); ?></p>
							</div>
							<?php if ( $i < count( $steps ) - 1 ) : ?>
								<span class="ifa-steps__divider" aria-hidden="true"></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
