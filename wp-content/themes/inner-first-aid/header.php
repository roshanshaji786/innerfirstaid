<?php
/**
 * Header template.
 *
 * @package inner-first-aid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ifa_cta      = ifa_is_sl() ? ifa_get_option( 'header_cta_sl', 'Zacni brezplacno' ) : ifa_get_option( 'header_cta_en', 'Get started free' );
$ifa_logo_txt = ifa_get_option( 'header_logo_text', 'innerfirstaid.com' );
$ifa_sl_url   = ifa_get_option( 'sl_url', home_url( '/sl/' ) );
$ifa_en_url   = ifa_get_option( 'en_url', home_url( '/' ) );
$ifa_is_sl    = ifa_is_sl();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header id="ifa-header" class="ifa-header" data-ifa-header>
	<div class="ifa-container ifa-header__inner">
		<a href="<?php echo esc_url( $ifa_is_sl ? $ifa_sl_url : $ifa_en_url ); ?>" class="ifa-logo" aria-label="<?php echo esc_attr( $ifa_logo_txt ); ?> — <?php echo esc_attr__( 'Home', 'inner-first-aid' ); ?>">
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false">
				<circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2"/>
				<path d="M10 16C10 12 13 9 16 9C19 9 22 12 22 16C22 20 19 23 16 23C13 23 10 20 10 16Z" fill="currentColor" opacity="0.2"/>
				<path d="M16 12V20M12 16H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
			<span class="ifa-logo__text"><?php echo esc_html( $ifa_logo_txt ); ?></span>
		</a>

		<button class="ifa-nav-toggle" data-ifa-nav-toggle aria-label="<?php echo esc_attr__( 'Toggle menu', 'inner-first-aid' ); ?>" aria-expanded="false" aria-controls="ifa-nav">
			<svg class="ifa-icon-burger" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
				<path d="M4 7H20M4 12H20M4 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
			<svg class="ifa-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
				<path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</button>

		<nav id="ifa-nav" class="ifa-nav" data-ifa-nav aria-label="<?php echo esc_attr__( 'Main navigation', 'inner-first-aid' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'ifa-menu',
						'depth'          => 1,
					)
				);
			}
			?>
			<span class="ifa-lang-switch" role="navigation" aria-label="<?php echo esc_attr__( 'Language', 'inner-first-aid' ); ?>">
				<?php if ( $ifa_is_sl ) : ?>
					<a href="<?php echo esc_url( $ifa_en_url ); ?>" class="ifa-lang-link" data-ifa-lang="en">EN</a>
					<span class="ifa-lang-sep" aria-hidden="true">|</span>
					<span class="ifa-lang-link is-current" aria-current="true">SL</span>
				<?php else : ?>
					<span class="ifa-lang-link is-current" aria-current="true">EN</span>
					<span class="ifa-lang-sep" aria-hidden="true">|</span>
					<a href="<?php echo esc_url( $ifa_sl_url ); ?>" class="ifa-lang-link" data-ifa-lang="sl">SL</a>
				<?php endif; ?>
			</span>
			<a href="#programs" class="ifa-btn" data-ifa-cta><?php echo esc_html( $ifa_cta ); ?></a>
		</nav>
	</div>
</header>

<main id="main" class="ifa-main">
