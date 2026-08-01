<?php
/**
 * Export the built pages as importable Elementor templates.
 *
 * Run inside a WordPress install with pages built by IFA_Builder:
 *
 *   php scripts/export-templates.php /path/to/wordpress
 *
 * Writes JSON files into elementor-templates/ which can be imported via
 * Elementor > Templates > Import Templates.
 */

if ( PHP_SAPI !== 'cli' && ! isset( $_SERVER['REQUEST_METHOD'] ) ) {
	exit;
}

$wp_path = isset( $argv[1] ) ? rtrim( $argv[1], '/' ) : '';

if ( ! $wp_path || ! file_exists( $wp_path . '/wp-load.php' ) ) {
	fwrite( STDERR, "Usage: php export-templates.php /path/to/wordpress\n" );
	exit( 1 );
}

$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require $wp_path . '/wp-load.php';

$out_dir = dirname( __DIR__ ) . '/elementor-templates';
if ( ! is_dir( $out_dir ) ) {
	mkdir( $out_dir, 0755, true );
}

$pages = array(
	'home-en'      => 'home',
	'home-sl'      => 'sl',
	'privacy-en'   => 'privacy',
	'terms-en'     => 'terms',
);

foreach ( $pages as $label => $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! $page ) {
		fwrite( STDERR, "Page '$slug' not found — run the builder first.\n" );
		continue;
	}

	$data = get_post_meta( $page->ID, '_elementor_data', true );
	$tree = json_decode( $data, true );
	if ( ! is_array( $tree ) ) {
		fwrite( STDERR, "No Elementor data on '$slug'.\n" );
		continue;
	}

	$template = array(
		'version'       => '0.4',
		'title'         => $label,
		'type'          => 'page',
		'content'       => $tree,
		'page_settings' => array(),
		'metadata'      => array(
			'source' => 'Inner First Aid builder',
		),
	);

	$file = $out_dir . '/' . $label . '.json';
	file_put_contents( $file, wp_json_encode( $template, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	echo "Exported $file\n";
}

echo "Done.\n";
