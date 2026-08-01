<?php
/**
 * IFA Social Proof widget — trust bar with title/subtitle items.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Widget_Social_Proof
 */
class IFA_Widget_Social_Proof extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ifa-social-proof';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Social proof bar', 'ifa-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-bullet-list';
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

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Psychologist verified',
			)
		);

		$repeater->add_control(
			'subtitle',
			array(
				'label'   => __( 'Subtitle', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Evidence-based content',
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'ifa-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => 'Psychologist verified', 'subtitle' => 'Evidence-based content' ),
					array( 'title' => '21-day program', 'subtitle' => 'One email per day' ),
					array( 'title' => 'Anonymous', 'subtitle' => 'Nobody knows. Only you.' ),
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
				'default' => '#0d2e1c',
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'   => __( 'Text color', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$items = isset( $s['items'] ) && is_array( $s['items'] ) ? $s['items'] : array();
		$bg   = ! empty( $s['bg_color'] ) ? sanitize_hex_color( $s['bg_color'] ) : '#0d2e1c';
		$fg   = ! empty( $s['text_color'] ) ? sanitize_hex_color( $s['text_color'] ) : '#ffffff';

		if ( ! $items ) {
			return;
		}
		?>
		<div class="ifa-social" style="background-color:<?php echo esc_attr( $bg ); ?>;color:<?php echo esc_attr( $fg ); ?>;">
			<div class="ifa-container ifa-social__grid">
				<?php foreach ( $items as $item ) : ?>
					<?php if ( ! empty( $item['title'] ) ) : ?>
						<div class="ifa-social__item">
							<strong class="ifa-social__title"><?php echo esc_html( $item['title'] ); ?></strong>
							<?php if ( ! empty( $item['subtitle'] ) ) : ?>
								<span class="ifa-social__sub"><?php echo esc_html( $item['subtitle'] ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
