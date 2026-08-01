<?php
/**
 * 404 template.
 *
 * @package inner-first-aid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="ifa-404" style="padding: 180px 0 100px; text-align: center;">
	<div class="ifa-container">
		<h1><?php echo ifa_is_sl() ? esc_html__( 'Strani ni bilo mogoce najti', 'inner-first-aid' ) : esc_html__( 'Page not found', 'inner-first-aid' ); ?></h1>
		<p><a href="<?php echo esc_url( home_url( ifa_is_sl() ? '/sl/' : '/' ) ); ?>">
			<?php echo ifa_is_sl() ? esc_html__( 'Nazaj na domov', 'inner-first-aid' ) : esc_html__( 'Back to home', 'inner-first-aid' ); ?>
		</a></p>
	</div>
</section>

<?php
get_footer();
