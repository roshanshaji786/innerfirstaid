<?php
/**
 * Main template (fallback).
 * Shown only if the landing pages have not been built yet.
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
		<h1 style="font-size:38px;font-weight:600;margin:0 0 12px;">
			<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
		</h1>
		<p style="font-size:18px;color:#4b5563;margin:0 0 28px;">
			<?php
			if ( ifa_is_sl() ) {
				echo esc_html__( 'Ko te boli - tu je korak za korakom.', 'inner-first-aid' );
			} else {
				echo esc_html__( 'When it hurts - here is step by step.', 'inner-first-aid' );
			}
			?>
		</p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=ifa-settings' ) ); ?>"
				class="ifa-btn" style="display:inline-block;">
				<?php echo esc_html__( 'Build the pages with Elementor', 'inner-first-aid' ); ?>
			</a>
		</p>
		<p style="color:#9ca3af;font-size:13px;margin-top:12px;">
			<?php echo esc_html__( 'One click — creates the EN + SL landing pages, Privacy and Terms.', 'inner-first-aid' ); ?>
		</p>
	</div>
</section>

<?php
get_footer();
