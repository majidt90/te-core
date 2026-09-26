<?php
/**
 * WooCommerce integration. Hooks first; templates only where the markup must change.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'te_core_wc_hooks', 20 );
add_filter( 'loop_shop_columns', 'te_core_loop_columns' );
add_filter( 'loop_shop_per_page', 'te_core_loop_per_page' );
add_filter( 'woocommerce_breadcrumb_defaults', 'te_core_breadcrumbs' );
add_filter( 'woocommerce_loop_add_to_cart_args', 'te_core_loop_cart_args', 10, 2 );
add_action( 'woocommerce_product_query', 'te_core_product_query' );
add_filter( 'woocommerce_output_related_products_args', 'te_core_related_args' );
add_action( 'woocommerce_after_add_to_cart_button', 'te_core_buybox_note' );
add_action( 'wp', 'te_core_single_sidebar' );

/**
 * Replace WC wrappers and the loop chrome that would fight the card.
 *
 * @return void
 */
function te_core_wc_hooks() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	add_action( 'woocommerce_before_main_content', 'te_core_wrapper_open', 10 );
	add_action( 'woocommerce_after_main_content', 'te_core_wrapper_close', 10 );

	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
	remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

	remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
	add_action( 'woocommerce_no_products_found', 'te_core_no_products' );

	add_action( 'woocommerce_before_shop_loop', 'te_core_filter_toggle', 19 );
}

/**
 * Opening wrapper.
 *
 * @return void
 */
function te_core_wrapper_open() {
	echo '<main id="primary" class="te-main te-shop"><div class="te-container">';
}

/**
 * Closing wrapper.
 *
 * @return void
 */
function te_core_wrapper_close() {
	echo '</div></main>';
}

/**
 * Columns from the same setting the grid uses.
 *
 * @return int
 */
function te_core_loop_columns() {
	return (int) te_core_get( 'shop_columns' );
}

/**
 * Products per page.
 *
 * @return int
 */
function te_core_loop_per_page() {
	return (int) te_core_get( 'products_per_page' );
}

/**
 * Breadcrumb markup that stays valid as a list in both directions.
 *
 * @param array $defaults Defaults.
 * @return array
 */
function te_core_breadcrumbs( $defaults ) {
	$defaults['delimiter']   = '';
	$defaults['wrap_before'] = '<nav class="te-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'te-core' ) . '"><ol>';
	$defaults['wrap_after']  = '</ol></nav>';
	$defaults['before']      = '<li>';
	$defaults['after']       = '</li>';
	return $defaults;
}

/**
 * Mark simple products so the script can intercept them. The href remains a real add-to-cart URL.
 *
 * @param array      $args    Button args.
 * @param WC_Product $product Product.
 * @return array
 */
function te_core_loop_cart_args( $args, $product ) {
	$args['class'] .= ' te-btn te-btn--cart';
	if ( te_core_on( 'ajax_cart' ) && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
		$args['class'] .= ' te-ajax-cart';
	}
	return $args;
}

/**
 * Accurate catalog filters: te_source, stock, and WooCommerce’s own price args.
 *
 * @param WP_Query $q Query.
 * @return void
 */
function te_core_product_query( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	$source = isset( $_GET['te_source'] ) ? sanitize_key( wp_unslash( $_GET['te_source'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tax    = (array) $q->get( 'tax_query' );

	if ( 'featured' === $source ) {
		$tax[] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => array( 'featured' ),
			'operator' => 'IN',
		);
	}
	if ( 'sale' === $source && function_exists( 'wc_get_product_ids_on_sale' ) ) {
		$ids = wc_get_product_ids_on_sale();
		$q->set( 'post__in', $ids ? $ids : array( 0 ) );
	}
	if ( ! empty( $_GET['instock'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tax[] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => array( 'outofstock' ),
			'operator' => 'NOT IN',
		);
	}
	if ( count( $tax ) > ( $q->get( 'tax_query' ) ? count( (array) $q->get( 'tax_query' ) ) : 0 ) ) {
		$q->set( 'tax_query', $tax );
	}
}

/**
 * Related products follow the desktop column count, capped at 4.
 *
 * @param array $args Args.
 * @return array
 */
function te_core_related_args( $args ) {
	$cols                 = min( 4, (int) te_core_get( 'shop_columns' ) );
	$args['posts_per_page'] = $cols;
	$args['columns']        = $cols;
	return $args;
}

/**
 * A short, honest note under the buy button. Not a fake shipping promise.
 *
 * @return void
 */
function te_core_buybox_note() {
	echo '<p class="te-buybox-note">' . esc_html__( 'Shipping and payment are confirmed at checkout.', 'te-core' ) . '</p>';
}

/**
 * Shop filters belong on archives, not beside the buy box.
 *
 * @return void
 */
function te_core_single_sidebar() {
	if ( function_exists( 'is_product' ) && is_product() ) {
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	}
}

/**
 * Mobile filter button. Desktop uses the aside directly.
 *
 * @return void
 */
function te_core_filter_toggle() {
	if ( ! te_core_on( 'show_filters' ) ) {
		return;
	}
	echo '<button type="button" class="te-btn te-btn--ghost te-filter-open" data-te-open-filters>';
	te_core_icon( 'filter' );
	echo '<span>' . esc_html__( 'Filter', 'te-core' ) . '</span></button>';
}

/**
 * Empty catalog state.
 *
 * @return void
 */
function te_core_no_products() {
	echo '<div class="te-empty">';
	echo '<p class="te-kicker">' . esc_html__( 'Catalog', 'te-core' ) . '</p>';
	echo '<h2>' . esc_html__( 'Nothing in this view.', 'te-core' ) . '</h2>';
	echo '<p>' . esc_html__( 'Clear a filter, or search the whole catalog.', 'te-core' ) . '</p>';
	get_search_form();
	echo '</div>';
}
