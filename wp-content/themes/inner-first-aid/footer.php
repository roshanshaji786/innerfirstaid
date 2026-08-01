<?php
/**
 * Footer template.
 *
 * @package inner-first-aid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ifa_is_sl       = ifa_is_sl();
$ifa_logo_txt    = ifa_get_option( 'header_logo_text', 'innerfirstaid.com' );
$ifa_copy        = ifa_get_option( 'footer_copy_' . ( $ifa_is_sl ? 'sl' : 'en' ), $ifa_is_sl ? 'Copyright 2026 Inner First Aid' : 'Copyright 2026 Inner First Aid' );
$ifa_disclaimer  = ifa_get_option( 'footer_disclaimer_' . ( $ifa_is_sl ? 'sl' : 'en' ), $ifa_is_sl ? 'Ta program je za izobrazevalne namene. Ni nadomestek za strokovno pomoc.' : 'This program is for educational purposes only. It is not a substitute for professional support.' );
$ifa_privacy_txt = ifa_get_option( 'footer_privacy_' . ( $ifa_is_sl ? 'sl' : 'en' ), $ifa_is_sl ? 'Politika zasebnosti' : 'Privacy Policy' );
$ifa_terms_txt   = ifa_get_option( 'footer_terms_' . ( $ifa_is_sl ? 'sl' : 'en' ), $ifa_is_sl ? 'Pogoji uporabe' : 'Terms of Service' );
$ifa_privacy_url = ifa_get_option( 'privacy_url', home_url( '/privacy/' ) );
$ifa_terms_url   = ifa_get_option( 'terms_url', home_url( '/terms/' ) );
$ifa_home_url    = $ifa_is_sl ? ifa_get_option( 'sl_url', home_url( '/sl/' ) ) : ifa_get_option( 'en_url', home_url( '/' ) );
?>
</main><!-- #main -->

<footer class="ifa-footer">
	<div class="ifa-container">
		<div class="ifa-footer__top">
			<a href="<?php echo esc_url( $ifa_home_url ); ?>" class="ifa-footer__logo"><?php echo esc_html( $ifa_logo_txt ); ?></a>
			<span class="ifa-footer__copy"><?php echo esc_html( $ifa_copy ); ?></span>
		</div>
		<div class="ifa-footer__links">
			<a href="<?php echo esc_url( $ifa_privacy_url ); ?>"><?php echo esc_html( $ifa_privacy_txt ); ?></a>
			<span aria-hidden="true">|</span>
			<a href="<?php echo esc_url( $ifa_terms_url ); ?>"><?php echo esc_html( $ifa_terms_txt ); ?></a>
		</div>
		<p class="ifa-footer__disclaimer"><?php echo esc_html( $ifa_disclaimer ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
