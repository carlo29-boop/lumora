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

	// Hero slider behaviour (vanilla JS, footer-loaded, no dependencies).
	wp_enqueue_script(
		'lumora-slider',
		get_stylesheet_directory_uri() . '/assets/js/lumora-slider.js',
		[],
		LUMORA_CHILD_VERSION,
		true
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

/**
 * Live-Link-safe frontend asset URLs.
 *
 * WordPress and Elementor render absolute media/script/style URLs built from
 * the DB siteurl (http://localhost:10003). Those URLs can never load for a
 * visitor opening the site through a Local Live Link public URL, because
 * `localhost` does not resolve outside this machine.
 *
 * These filters rewrite same-site frontend URLs to root-relative form, which
 * browsers resolve against whatever host served the page — local or Live
 * Link — without changing any markup, design, or stored content. Admin
 * screens, feeds and REST responses keep absolute URLs. External hosts
 * (e.g. Google Fonts) are never touched.
 */
function lumora_use_relative_frontend_urls() {
	if ( is_admin() ) {
		return false;
	}
	if ( is_feed() ) {
		return false;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}
	return true;
}

/**
 * Strip the local host from a same-site URL, keeping path + query.
 *
 * @param string $url Absolute URL.
 * @return string Root-relative URL when the host is this site's local host, else unchanged.
 */
function lumora_maybe_relative_url( $url ) {
	if ( ! is_string( $url ) || '' === $url || '/' === $url[0] ) {
		return $url;
	}
	$url_host = wp_parse_url( $url, PHP_URL_HOST );
	if ( ! $url_host ) {
		return $url;
	}
	$home_host  = wp_parse_url( home_url(), PHP_URL_HOST );
	$local_hosts = array_filter( [ $home_host, 'localhost', 'lumora.local' ] );
	$match       = false;
	foreach ( $local_hosts as $host ) {
		if ( 0 === strcasecmp( (string) $url_host, (string) $host ) ) {
			$match = true;
			break;
		}
	}
	if ( ! $match ) {
		return $url;
	}
	$path     = wp_parse_url( $url, PHP_URL_PATH );
	$query    = wp_parse_url( $url, PHP_URL_QUERY );
	$fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );
	if ( ! $path ) {
		return $url;
	}
	$relative = $path;
	if ( $query ) {
		$relative .= '?' . $query;
	}
	if ( $fragment ) {
		$relative .= '#' . $fragment;
	}
	return $relative;
}

/**
 * Relativize attachment <img> src output (covers Elementor image widgets,
 * which render by attachment ID through wp_get_attachment_image()).
 */
function lumora_relative_attachment_image_src( $image, $attachment_id, $size, $icon ) {
	if ( ! lumora_use_relative_frontend_urls() || ! is_array( $image ) || empty( $image[0] ) ) {
		return $image;
	}
	$image[0] = lumora_maybe_relative_url( $image[0] );
	return $image;
}
add_filter( 'wp_get_attachment_image_src', 'lumora_relative_attachment_image_src', 10, 4 );

/**
 * Relativize every candidate in responsive srcset output.
 */
function lumora_relative_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
	if ( ! lumora_use_relative_frontend_urls() || ! is_array( $sources ) ) {
		return $sources;
	}
	foreach ( $sources as $width => $source ) {
		if ( isset( $source['url'] ) ) {
			$sources[ $width ]['url'] = lumora_maybe_relative_url( $source['url'] );
		}
	}
	return $sources;
}
add_filter( 'wp_calculate_image_srcset', 'lumora_relative_image_srcset', 10, 5 );

/**
 * Relativize enqueued script/style URLs so CSS/JS also load under Live Link.
 */
function lumora_relative_loader_src( $src, $handle ) {
	if ( ! lumora_use_relative_frontend_urls() || ! is_string( $src ) ) {
		return $src;
	}
	return lumora_maybe_relative_url( $src );
}
add_filter( 'script_loader_src', 'lumora_relative_loader_src', 10, 2 );
add_filter( 'style_loader_src', 'lumora_relative_loader_src', 10, 2 );
