<?php
/**
 * Lumora Child theme setup.
 *
 * @package LumoraChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LUMORA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue parent + child styles, brand stylesheet and Google Fonts.
 *
 * Fonts are loaded via Google Fonts (no plugin needed):
 * - Cormorant Garamond (headings, serif)
 * - DM Sans (body / navigation, sans-serif)
 */
function lumora_child_enqueue_assets() {
	// Parent Hello Elementor reset/theme styles (handles registered by parent).
	wp_enqueue_style(
		'hello-elementor',
		get_template_directory_uri() . '/assets/css/reset.css',
		[],
		'3.5.1'
	);

	wp_enqueue_style(
		'hello-elementor-theme-style',
		get_template_directory_uri() . '/assets/css/theme.css',
		[ 'hello-elementor' ],
		'3.5.1'
	);

	// Child theme header stylesheet (required by WordPress, minimal rules).
	wp_enqueue_style(
		'lumora-child-style',
		get_stylesheet_uri(),
		[ 'hello-elementor-theme-style' ],
		LUMORA_CHILD_VERSION
	);

	// Google Fonts: Cormorant Garamond + DM Sans with sensible fallbacks in CSS.
	wp_enqueue_style(
		'lumora-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap',
		[],
		null
	);

	// Brand stylesheet (versioned, tracked in git).
	wp_enqueue_style(
		'lumora-brand',
		get_stylesheet_directory_uri() . '/assets/css/lumora.css',
		[ 'lumora-child-style', 'lumora-fonts' ],
		LUMORA_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'lumora_child_enqueue_assets', 20 );

/**
 * Preconnect to Google Fonts for performance.
 */
function lumora_child_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = 'https://fonts.gstatic.com';
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'lumora_child_resource_hints', 10, 2 );

/**
 * Canvas pages (Elementor Canvas) should not output theme header/footer.
 * Elementor Canvas template already bypasses them; this is a safety net
 * so the in-page Elementor header/footer is the single source of truth.
 */
function lumora_child_hide_theme_header_footer( $display ) {
	if ( is_page_template( 'elementor_canvas' ) ) {
		return false;
	}
	return $display;
}
add_filter( 'hello_elementor_header_footer', 'lumora_child_hide_theme_header_footer' );
