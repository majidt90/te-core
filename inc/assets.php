<?php
/**
 * Front and editor assets. Nothing is enqueued “just in case”.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'te_core_enqueue', 20 );
add_action( 'wp_enqueue_scripts', 'te_core_dequeue', 100 );
add_action( 'wp_head', 'te_core_preload_font', 2 );
add_filter( 'woocommerce_get_price_html', 'te_core_filter_price_html', 20 );
add_filter( 'wc_price', 'te_core_filter_price_html', 20 );
add_filter( 'gettext', 'te_core_filter_gettext_digits', 20, 3 );

/**
 * Styles and the one storefront script.
 *
 * @return void
 */
function te_core_enqueue() {
	wp_enqueue_style(
		'te-core',
		TE_CORE_URI . '/assets/css/theme.css',
		array(),
		te_core_asset_ver( '/assets/css/theme.css' )
	);
	wp_add_inline_style( 'te-core', te_core_inline_tokens() );

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style(
			'te-core-woo',
			TE_CORE_URI . '/assets/css/woocommerce.css',
			array( 'te-core' ),
			te_core_asset_ver( '/assets/css/woocommerce.css' )
		);
	}

	wp_enqueue_script(
		'te-core',
		TE_CORE_URI . '/assets/js/theme.js',
		array(),
		te_core_asset_ver( '/assets/js/theme.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	$cart_count = 0;
	if ( function_exists( 'WC' ) && WC()->cart ) {
		$cart_count = WC()->cart->get_cart_contents_count();
	}

	wp_localize_script(
		'te-core',
		'teCore',
		array(
			'nonce'         => wp_create_nonce( 'te_core' ),
			'persianDigits' => te_core_persian_digits_enabled(),
			'productId'     => is_singular( 'product' ) ? get_queried_object_id() : 0,
			'i18n'          => array(
				'added'    => __( 'Added to cart', 'te-core' ),
				'error'    => __( 'Could not update the cart.', 'te-core' ),
				'empty'    => __( 'Nothing matched.', 'te-core' ),
				'search'   => __( 'Search results', 'te-core' ),
				'viewAll'  => __( 'View all results', 'te-core' ),
				'loading'  => __( 'Loading', 'te-core' ),
				'close'    => __( 'Close', 'te-core' ),
				'removed'       => __( 'Removed', 'te-core' ),
				'compareFull'   => __( 'Compare holds four products.', 'te-core' ),
				'compareAdded'  => __( 'Added to compare', 'te-core' ),
				'compareEmpty'  => __( 'Nothing to compare yet.', 'te-core' ),
				'recent'        => __( 'Recent searches', 'te-core' ),
				'coupon'        => __( 'Coupon applied', 'te-core' ),
				'copied'        => __( 'Link copied', 'te-core' ),
				'skuCopied'     => __( 'Copied', 'te-core' ),
				'loadingMore'   => __( 'Loading', 'te-core' ),
				'savedEmpty'    => __( 'Nothing saved yet.', 'te-core' ),
			),
			'features'      => array(
				'predictive'   => te_core_on( 'show_search' ) && te_core_on( 'predictive_search' ),
				'ajaxCart'     => te_core_on( 'ajax_cart' ) && class_exists( 'WooCommerce' ),
				'quickView'    => te_core_on( 'quick_view' ) && class_exists( 'WooCommerce' ),
				'wishlist'     => te_core_on( 'show_wishlist' ),
				'recent'       => true,
				'compare'      => te_core_on( 'show_compare' ) && class_exists( 'WooCommerce' ),
				'buybar'       => te_core_on( 'sticky_buybar' ) && function_exists( 'is_product' ) && is_product(),
				'recentSearch' => te_core_on( 'show_search' ) && te_core_on( 'recent_search' ),
				'coupon'       => te_core_on( 'show_coupon' ) && class_exists( 'WooCommerce' ),
			),
			'endpoints'     => array(
				'add'     => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( 'te_core_add' ) : '',
				'qty'     => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( 'te_core_qty' ) : '',
				'remove'  => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( 'te_core_remove' ) : '',
				'coupon'  => ( class_exists( 'WC_AJAX' ) && te_core_on( 'show_coupon' ) ) ? WC_AJAX::get_endpoint( 'te_core_coupon' ) : '',
				'search'  => rest_url( 'te-core/v1/search' ),
				'cards'   => rest_url( 'te-core/v1/cards' ),
				'quick'   => rest_url( 'te-core/v1/quick-view' ),
				'compare' => te_core_on( 'show_compare' ) ? rest_url( 'te-core/v1/compare' ) : '',
			),
			'cartCount'     => $cart_count,
		)
	);
}

/**
 * Tokens that must match the settings screen.
 *
 * @return string
 */
function te_core_inline_tokens() {
	$accent = sanitize_hex_color( (string) te_core_get( 'accent_color' ) );
	if ( ! $accent ) {
		$accent = '#c2410c';
	}
	$ratio = (string) te_core_get( 'image_ratio' );
	if ( ! in_array( $ratio, array( '1/1', '4/5', '3/4' ), true ) ) {
		$ratio = '1/1';
	}
	$fit   = ( 'cover' === te_core_get( 'image_fit' ) ) ? 'cover' : 'contain';
	$width = (int) te_core_get( 'content_width' );
	$cols  = (int) te_core_get( 'shop_columns' );

	return sprintf(
		':root{--te-accent:%1$s;--te-width:%2$dpx;--te-cols-desktop:%3$d;--te-ratio:%4$s;--te-fit:%5$s;}',
		$accent,
		$width,
		$cols,
		$ratio,
		$fit
	);
}

/**
 * Drop known storefront costs. Cart fragments stay available as an explicit opt-in.
 *
 * @return void
 */
function te_core_dequeue() {
	if ( te_core_on( 'disable_emoji' ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}
	if ( ! te_core_on( 'cart_fragments' ) ) {
		wp_dequeue_script( 'wc-cart-fragments' );
	}
	if ( te_core_on( 'ajax_cart' ) ) {
		wp_dequeue_script( 'wc-add-to-cart' );
	}
}

/**
 * Preload the single variable font.
 *
 * @return void
 */
function te_core_preload_font() {
	if ( ! te_core_on( 'preload_font' ) ) {
		return;
	}
	$url = TE_CORE_URI . '/assets/fonts/vazirmatn-wght.woff2';
	echo '<link rel="preload" href="' . esc_url( $url ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}

/**
 * Persian digits in prices. Admin screens stay untouched.
 *
 * @param string $html Price HTML.
 * @return string
 */
function te_core_filter_price_html( $html ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $html;
	}
	return te_core_decode_marks( te_core_digits( $html ) );
}

/**
 * Persian digits in translated storefront strings that contain numbers.
 *
 * @param string $translated Translated text.
 * @param string $text       Original text.
 * @param string $domain     Text domain.
 * @return string
 */
function te_core_filter_gettext_digits( $translated, $text, $domain ) {
	unset( $text );
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $translated;
	}
	if ( 'woocommerce' !== $domain && 'te-core' !== $domain ) {
		return $translated;
	}
	if ( ! preg_match( '/\d/', $translated ) || false !== strpos( $translated, '%' ) ) {
		return $translated;
	}
	return te_core_digits( $translated );
}
