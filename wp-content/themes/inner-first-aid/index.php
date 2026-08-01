<?php
/**
 * Main template (fallback).
 *
 * @package inner-first-aid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="ifa-fallback" style="padding: 160px 0 80px; text-align: center;">
	<div class="ifa-container">
		<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
		<p>
			<?php
			if ( ifa_is_sl() ) {
				echo esc_html__( 'Ko te boli - tu je korak za korakom.', 'inner-first-aid' );
			} else {
				echo esc_html__( 'When it hurts - here is step by step.', 'inner-first-aid' );
			}
			?>
		</p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=ifa-builder' ) ); ?>">
			<?php echo esc_html__( 'Build the pages with Elementor', 'inner-first-aid' ); ?>
		</a></p>
	</div>
</section>

<?php
get_footer();
