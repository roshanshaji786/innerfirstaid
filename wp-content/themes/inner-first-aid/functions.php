<?php
/**
 * Inner First Aid theme functions.
 *
 * @package inner-first-aid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IFA_THEME_VERSION', '1.0.1' );
define( 'IFA_THEME_DIR', get_template_directory() );
define( 'IFA_THEME_URI', get_template_directory_uri() );

/**
 * Option helper fallback (normally provided by the IFA Core plugin).
 *
 * @param string $key     Option key.
 * @param mixed  $default Default.
 * @return mixed
 */
if ( ! function_exists( 'ifa_get_option' ) ) {
	function ifa_get_option( $key, $default = '' ) {
		$all = get_option( 'ifa_settings', array() );
		return isset( $all[ $key ] ) ? $all[ $key ] : $default;
	}
}

/**
 * Theme setup.
 */
function ifa_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'inner-first-aid' ),
		)
	);
}
add_action( 'after_setup_theme', 'ifa_theme_setup' );

/**
 * Enqueue styles and scripts.
 */
function ifa_theme_assets() {
	wp_enqueue_style( 'ifa-theme-fonts', IFA_THEME_URI . '/assets/css/fonts.css', array(), IFA_THEME_VERSION );
	wp_enqueue_style( 'ifa-theme', IFA_THEME_URI . '/assets/css/main.css', array( 'ifa-theme-fonts' ), IFA_THEME_VERSION );

	wp_enqueue_script( 'ifa-theme', IFA_THEME_URI . '/assets/js/main.js', array(), IFA_THEME_VERSION, true );

	wp_localize_script(
		'ifa-theme',
		'IFA_THEME',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ifa_theme_assets' );

/**
 * Elementor compatibility: keep default templates.
 */
function ifa_theme_register_elementor_locations( $elementor_theme_manager ) {
	// Intentionally minimal: we only need content templates.
}
add_action( 'elementor/theme/register_locations', 'ifa_theme_register_elementor_locations' );

/**
 * Add body classes: language + page flag.
 *
 * @param array $classes Body classes.
 * @return array
 */
function ifa_theme_body_classes( $classes ) {
	$classes[] = ifa_is_sl() ? 'lang-sl' : 'lang-en';
	$classes[] = is_page() ? 'is-page' : '';
	return $classes;
}
add_filter( 'body_class', 'ifa_theme_body_classes' );

/**
 * Detect Slovenian content: any page under /sl.
 * (Defined by the IFA Core plugin; fallback for theme-only installs.)
 *
 * @return bool
 */
if ( ! function_exists( 'ifa_is_sl' ) ) {
	function ifa_is_sl() {
		if ( function_exists( 'ifa_get_lang' ) ) {
			return 'sl' === ifa_get_lang();
		}
		$path = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return 0 === strpos( $path, '/sl' ) && ! is_admin();
	}
}

/**
 * Set html lang attribute.
 *
 * @param string $lang Full language attribute string.
 * @return string
 */
function ifa_theme_html_lang( $lang ) {
	return ifa_is_sl() ? 'lang="sl-SI"' : 'lang="en-US"';
}
add_filter( 'language_attributes', 'ifa_theme_html_lang' );

/**
 * SEO title: on the static front page use the page title (WP 7 defaults to the site name).
 *
 * @param array $parts Document title parts.
 * @return array
 */
function ifa_theme_document_title_parts( $parts ) {
	if ( is_front_page() && is_page() && ! is_paged() ) {
		$parts['title'] = get_the_title();
	}
	return $parts;
}
add_filter( 'document_title_parts', 'ifa_theme_document_title_parts' );

/**
 * Excerpt length for SEO description.
 *
 * @param int $length Length.
 * @return int
 */
function ifa_theme_excerpt_length( $length ) {
	return 24;
}
add_filter( 'excerpt_length', 'ifa_theme_excerpt_length' );

/* ==========================================================================
 * Performance & SEO optimizations
 * ========================================================================== */

/**
 * Remove unnecessary <head> output: emoji scripts, oEmbed, generator tag,
 * RSD/WLW, shortlinks, extra feeds, REST/Resource-Hints links.
 */
function ifa_theme_clean_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_resource_hints', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'init', 'ifa_theme_clean_head' );

/**
 * Disable embeds entirely (we never use them).
 */
function ifa_theme_disable_embeds() {
	wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'ifa_theme_disable_embeds' );

/**
 * Drop jQuery Migrate on the front end (Elementor does not need it).
 *
 * @param WP_Scripts $scripts Scripts object.
 */
function ifa_theme_remove_jquery_migrate( $scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$script = $scripts->registered['jquery'];
		if ( $script->deps ) {
			$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
		}
	}
}
add_action( 'wp_default_scripts', 'ifa_theme_remove_jquery_migrate' );

/**
 * Basic security headers — works on any host (no .htaccess needed).
 */
function ifa_theme_security_headers() {
	if ( headers_sent() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: DENY' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
}
add_action( 'template_redirect', 'ifa_theme_security_headers' );

/**
 * Disable pingbacks (anti-spam) and their header.
 */
function ifa_theme_disable_pingbacks() {
	add_filter( 'xmlrpc_methods', function ( $methods ) {
		unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	} );
	add_filter( 'wp_headers', function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	} );
}
add_action( 'init', 'ifa_theme_disable_pingbacks' );

/**
 * SEO meta description (per language), used when no plugin provides one.
 */
function ifa_theme_meta_description() {
	if ( is_front_page() || ( is_page() && 'sl' === ifa_get_lang() && 0 === strpos( get_the_permalink(), home_url( '/sl/' ) ) ) ) {
		$desc = ifa_is_sl()
			? 'Mednarodna platforma za psiholosko prvo pomoc. Brezplacni vodic: 3 napake, ki podaljsajo bolecino.'
			: 'The international platform for psychological first aid. Free guide: 3 mistakes that prolong the pain.';
	} else {
		$desc = wp_strip_all_tags( get_the_excerpt() );
	}
	$desc = trim( $desc );
	if ( '' !== $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}
}

/**
 * Open Graph + Twitter meta tags.
 */
function ifa_theme_og_tags() {
	$title = wp_get_document_title();
	$desc  = ifa_is_sl()
		? 'Mednarodna platforma za psiholosko prvo pomoc. Brezplacni vodic: 3 napake, ki podaljsajo bolecino.'
		: 'The international platform for psychological first aid. Free guide: 3 mistakes that prolong the pain.';

	$image = '';
	$hero_id = (int) get_option( 'ifa_hero_image_id', 0 );
	if ( $hero_id ) {
		$image = wp_get_attachment_image_url( $hero_id, 'large' );
	}
	if ( ! $image ) {
		$image = IFA_THEME_URI . '/assets/img/hero.jpg';
	}

	?>
	<meta property="og:type" content="website">
	<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $desc ); ?>">
	<meta property="og:url" content="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>">
	<?php if ( $image ) : ?>
		<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
	<?php endif; ?>
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $desc ); ?>">
	<?php if ( $image ) : ?>
		<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
	<?php endif; ?>
	<?php
}

/**
 * Is a dedicated SEO plugin active? If so, let it handle meta.
 *
 * @return bool
 */
function ifa_theme_has_seo_plugin() {
	return defined( 'RANK_MATH_VERSION' )
		|| defined( 'WPSEO_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' );
}

/**
 * Print SEO + OG meta in wp_head (skipped when an SEO plugin is active).
 */
function ifa_theme_head_meta() {
	if ( is_admin() || is_feed() || is_robots() || is_embed() || ifa_theme_has_seo_plugin() ) {
		return;
	}
	ifa_theme_meta_description();
	ifa_theme_og_tags();
}
add_action( 'wp_head', 'ifa_theme_head_meta', 5 );

/**
 * JSON-LD Organization schema.
 */
function ifa_theme_jsonld() {
	if ( is_admin() || is_feed() || ifa_theme_has_seo_plugin() ) {
		return;
	}
	$hero_id = (int) get_option( 'ifa_hero_image_id', 0 );
	$logo = $hero_id ? wp_get_attachment_image_url( $hero_id, 'large' ) : IFA_THEME_URI . '/assets/img/hero.jpg';
	?>
	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "Organization",
		"name": "<?php echo esc_js( get_bloginfo( 'name' ) ); ?>",
		"url": "<?php echo esc_url( home_url( '/' ) ); ?>",
		"logo": "<?php echo esc_url( $logo ); ?>",
		"description": "<?php echo esc_js( ifa_is_sl() ? 'Mednarodna platforma za psiholosko prvo pomoc.' : 'The international platform for psychological first aid.' ); ?>"
	}
	</script>
	<?php
}
add_action( 'wp_head', 'ifa_theme_jsonld', 7 );

/**
 * Preload the Inter font (main weight) for faster first paint.
 */
function ifa_theme_preload_fonts() {
	?>
	<link rel="preload" href="<?php echo esc_url( IFA_THEME_URI . '/assets/fonts/inter-latin-400-normal.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo esc_url( IFA_THEME_URI . '/assets/fonts/inter-latin-700-normal.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<?php
}
add_action( 'wp_head', 'ifa_theme_preload_fonts', 3 );

/**
 * Defer our front-end script (it has no dependencies).
 *
 * @param string $tag    Script tag.
 * @param string $handle Handle.
 * @return string
 */
function ifa_theme_defer_script( $tag, $handle ) {
	if ( 'ifa-theme' === $handle && false === strpos( $tag, 'defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'ifa_theme_defer_script', 10, 2 );
