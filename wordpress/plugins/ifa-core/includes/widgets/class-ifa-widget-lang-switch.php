<?php
/**
 * IFA Language Switcher widget — EN / SL links.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Widget_Lang_Switch
 */
class IFA_Widget_Lang_Switch extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ifa-lang-switch';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Language switcher', 'ifa-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-global-settings';
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
			'current_label',
			array(
				'label'   => __( 'Current language label', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'EN',
			)
		);

		$this->add_control(
			'other_label',
			array(
				'label'   => __( 'Other language label', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'SL',
			)
		);

		$this->add_control(
			'other_url',
			array(
				'label'   => __( 'Other language URL', 'ifa-core' ),
				'type'    => \Elementor\Controls_Manager::URL,
				'default' => array( 'url' => '' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$url   = isset( $s['other_url']['url'] ) ? $s['other_url']['url'] : '';
		$label = ! empty( $s['current_label'] ) ? $s['current_label'] : 'EN';
		$other = ! empty( $s['other_label'] ) ? $s['other_label'] : 'SL';
		?>
		<div class="ifa-langwidget">
			<span class="ifa-langwidget__current"><?php echo esc_html( $label ); ?></span>
			<?php if ( $url ) : ?>
				<a class="ifa-langwidget__other" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $other ); ?></a>
			<?php endif; ?>
		</div>
		<?php
	}
}
