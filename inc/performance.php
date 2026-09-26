<?php
/**
 * Small, explicit performance choices.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'before_woocommerce_init', 'te_core_declare_wc_compat' );
add_filter( 'woocommerce_enqueue_styles', 'te_core_strip_wc_styles' );
add_action( 'wp_head', 'te_core_website_schema', 20 );
add_filter( 'wp_get_attachment_image_attributes', 'te_core_image_attributes', 10, 3 );
add_filter( 'woocommerce_placeholder_img', 'te_core_placeholder_alt' );

/**
 * High-performance order storage and block checkout stay compatible.
 *
 * @return void
 */
function te_core_declare_wc_compat() {
	if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		return;
	}
	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', TE_CORE_FILE, true );
	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', TE_CORE_FILE, true );
}

/**
 * Classic WooCommerce CSS is replaced by assets/css/woocommerce.css.
 * Block checkout keeps its own stylesheet.
 *
 * @param array $styles WC styles.
 * @return array
 */
function te_core_strip_wc_styles( $styles ) {
	unset( $styles['woocommerce-general'], $styles['woocommerce-layout'], $styles['woocommerce-smallscreen'] );
	return $styles;
}

/**
 * Sitelinks search box. Skipped when WooCommerce already prints website data.
 *
 * @return void
 */
function te_core_website_schema() {
	if ( ! is_front_page() ) {
		return;
	}
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return;
	}
	$data = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => home_url( '/?s={search_term_string}&post_type=product' ),
			'query-input' => 'required name=search_term_string',
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

/**
 * Reserve the first row of images and lazy-load the rest.
 *
 * @param array        $attr       Attributes.
 * @param WP_Post      $attachment Attachment.
 * @param string|array $size       Size.
 * @return array
 */
function te_core_image_attributes( $attr, $attachment, $size ) {
	unset( $attachment );
	$src   = isset( $attr['src'] ) ? (string) $attr['src'] : '';
	$class = isset( $attr['class'] ) ? (string) $attr['class'] : '';
	if ( ! is_admin() && ( false !== strpos( $src, 'woocommerce-placeholder' ) || false !== strpos( $class, 'woocommerce-placeholder' ) ) ) {
		$attr['alt'] = '';
	}
	if ( is_admin() ) {
		return $attr;
	}
	$size_name = is_array( $size ) ? '' : (string) $size;
	if ( ! in_array( $size_name, array( 'te-card', 'woocommerce_thumbnail', 'woocommerce_single' ), true ) ) {
		return $attr;
	}
	static $count = 0;
	++$count;
	$eager = $count <= 4 && ( is_front_page() || ( function_exists( 'is_shop' ) && is_shop() ) );
	if ( $eager ) {
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';
	} else {
		$attr['loading'] = 'lazy';
	}
	$attr['decoding'] = 'async';
	return $attr;
}

/**
 * Placeholder images are decorative. The translated alt is not a product name.
 *
 * @param string $html Image HTML.
 * @return string
 */
function te_core_placeholder_alt( $html ) {
	if ( ! is_string( $html ) ) {
		return $html;
	}
	$clean = preg_replace( '/ alt="[^"]*"/', ' alt=""', $html, 1 );
	return is_string( $clean ) ? $clean : $html;
}
