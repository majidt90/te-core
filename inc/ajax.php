<?php
/**
 * Cart mutations and read-only product endpoints.
 *
 * Cart writes go through WooCommerce’s own cart so totals, stock, and sessions stay exact.
 * Search and cards are public catalog data.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wc_ajax_te_core_add', 'te_core_ajax_add' );
add_action( 'wc_ajax_nopriv_te_core_add', 'te_core_ajax_add' );
add_action( 'wc_ajax_te_core_qty', 'te_core_ajax_qty' );
add_action( 'wc_ajax_nopriv_te_core_qty', 'te_core_ajax_qty' );
add_action( 'wc_ajax_te_core_remove', 'te_core_ajax_remove' );
add_action( 'wc_ajax_nopriv_te_core_remove', 'te_core_ajax_remove' );
add_action( 'wc_ajax_te_core_coupon', 'te_core_ajax_coupon' );
add_action( 'wc_ajax_nopriv_te_core_coupon', 'te_core_ajax_coupon' );
add_action( 'rest_api_init', 'te_core_rest_routes' );

/**
 * Verify the storefront nonce.
 *
 * @return void
 */
function te_core_verify_ajax() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'te_core' ) ) {
		wp_send_json_error(
			array( 'message' => __( 'The page expired. Reload and try again.', 'te-core' ) ),
			403
		);
	}
}

/**
 * Add a simple product. Variable products are refused so options are not skipped.
 *
 * @return void
 */
function te_core_ajax_add() {
	te_core_verify_ajax();
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Cart is not available.', 'te-core' ) ), 400 );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$qty        = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
	$qty        = max( 1, min( 99, $qty ) );
	$product    = wc_get_product( $product_id );

	if ( ! $product || ! $product->is_visible() ) {
		wp_send_json_error( array( 'message' => __( 'Product not found.', 'te-core' ) ), 404 );
	}
	if ( ! $product->is_type( 'simple' ) ) {
		wp_send_json_success(
			array(
				'redirect' => $product->get_permalink(),
			)
		);
	}
	if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		wp_send_json_error( array( 'message' => __( 'This product cannot be added right now.', 'te-core' ) ), 400 );
	}

	$added = WC()->cart->add_to_cart( $product_id, $qty );
	if ( ! $added ) {
		$message = te_core_first_notice();
		wp_send_json_error(
			array( 'message' => $message ? $message : __( 'Could not add this product.', 'te-core' ) ),
			400
		);
	}
	wp_send_json_success( te_core_cart_payload() );
}

/**
 * Set a line quantity.
 *
 * @return void
 */
function te_core_ajax_qty() {
	te_core_verify_ajax();
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Cart is not available.', 'te-core' ) ), 400 );
	}
	$key = isset( $_POST['key'] ) ? wc_clean( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
	$qty = min( 99, $qty );
	if ( '' === $key || ! isset( WC()->cart->get_cart()[ $key ] ) ) {
		wp_send_json_error( array( 'message' => __( 'That item is no longer in the cart.', 'te-core' ) ), 404 );
	}
	WC()->cart->set_quantity( $key, $qty, true );
	wp_send_json_success( te_core_cart_payload() );
}

/**
 * Remove a line.
 *
 * @return void
 */
function te_core_ajax_remove() {
	te_core_verify_ajax();
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Cart is not available.', 'te-core' ) ), 400 );
	}
	$key = isset( $_POST['key'] ) ? wc_clean( wp_unslash( $_POST['key'] ) ) : '';
	if ( '' === $key ) {
		wp_send_json_error( array( 'message' => __( 'That item is no longer in the cart.', 'te-core' ) ), 404 );
	}
	WC()->cart->remove_cart_item( $key );
	wp_send_json_success( te_core_cart_payload() );
}

/**
 * Apply or remove a coupon through WooCommerce. Invalid codes fail with its notice.
 *
 * @return void
 */
function te_core_ajax_coupon() {
	te_core_verify_ajax();
	if ( ! te_core_on( 'show_coupon' ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Cart is not available.', 'te-core' ) ), 400 );
	}
	$posted = isset( $_POST['coupon'] ) ? wp_unslash( $_POST['coupon'] ) : '';
	$raw    = is_string( $posted ) ? wc_format_coupon_code( $posted ) : '';
	$code = function_exists( 'mb_substr' ) ? mb_substr( $raw, 0, 40 ) : substr( $raw, 0, 40 );
	if ( '' === $code ) {
		wp_send_json_error( array( 'message' => __( 'Enter a coupon code.', 'te-core' ) ), 400 );
	}
	if ( ! empty( $_POST['remove'] ) ) {
		WC()->cart->remove_coupon( $code );
		wp_send_json_success( te_core_cart_payload() );
	}
	if ( ! WC()->cart->apply_coupon( $code ) ) {
		$message = te_core_first_notice();
		wp_send_json_error(
			array( 'message' => $message ? $message : __( 'Coupon was not applied.', 'te-core' ) ),
			400
		);
	}
	wp_send_json_success( te_core_cart_payload() );
}

/**
 * First WooCommerce notice, then clear the queue so it is not printed twice.
 *
 * @return string
 */
function te_core_first_notice() {
	$notices = wc_get_notices( 'error' );
	wc_clear_notices();
	if ( empty( $notices[0]['notice'] ) ) {
		return '';
	}
	return wp_strip_all_tags( $notices[0]['notice'] );
}

/**
 * Cart payload rendered by the theme, so currency format stays WooCommerce’s.
 *
 * @return array<string,mixed>
 */
function te_core_cart_payload() {
	wc_clear_notices();
	$count = WC()->cart->get_cart_contents_count();
	ob_start();
	get_template_part( 'template-parts/cart/mini-cart' );
	$html = ob_get_clean();
	return array(
		'count'   => $count,
		'display' => te_core_digits( (string) $count ),
		'total'   => wp_strip_all_tags( WC()->cart->get_cart_total() ),
		'html'    => $html,
	);
}

/**
 * Public catalog routes.
 *
 * @return void
 */
function te_core_rest_routes() {
	register_rest_route(
		'te-core/v1',
		'/search',
		array(
			'methods'             => 'GET',
			'callback'            => 'te_core_rest_search',
			'permission_callback' => '__return_true',
			'args'                => array(
				'q' => array(
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
	register_rest_route(
		'te-core/v1',
		'/cards',
		array(
			'methods'             => 'GET',
			'callback'            => 'te_core_rest_cards',
			'permission_callback' => '__return_true',
			'args'                => array(
				'ids' => array(
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
	if ( te_core_on( 'show_compare' ) ) {
		register_rest_route(
			'te-core/v1',
			'/compare',
			array(
				'methods'             => 'GET',
				'callback'            => 'te_core_rest_compare',
				'permission_callback' => '__return_true',
				'args'                => array(
					'ids' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}
	register_rest_route(
		'te-core/v1',
		'/quick-view',
		array(
			'methods'             => 'GET',
			'callback'            => 'te_core_rest_quick',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}

/**
 * Predictive search: products, a SKU match, and departments.
 *
 * @param WP_REST_Request $request Request.
 * @return array<string,mixed>
 */
function te_core_rest_search( $request ) {
	$q = trim( (string) $request->get_param( 'q' ) );
	if ( function_exists( 'mb_substr' ) ) {
		$q = mb_substr( $q, 0, 80 );
	} else {
		$q = substr( $q, 0, 80 );
	}
	$payload = array(
		'products'   => array(),
		'categories' => array(),
		'view_all'   => add_query_arg(
			array(
				's'         => $q,
				'post_type' => class_exists( 'WooCommerce' ) ? 'product' : 'post',
			),
			home_url( '/' )
		),
	);
	if ( strlen( $q ) < 2 ) {
		return $payload;
	}

	if ( class_exists( 'WooCommerce' ) ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				's'              => $q,
				'posts_per_page' => 6,
				'no_found_rows'  => true,
			)
		);
		$ids = wp_list_pluck( $query->posts, 'ID' );
		wp_reset_postdata();

		if ( count( $ids ) < 6 ) {
			$ids = array_values( array_unique( array_merge( $ids, te_core_sku_ids( $q, 6 - count( $ids ) ) ) ) );
		}
		foreach ( array_slice( $ids, 0, 6 ) as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}
			$image = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
			$payload['products'][] = array(
				'id'    => $product->get_id(),
				'name'  => $product->get_name(),
				'url'   => $product->get_permalink(),
				'price' => wp_strip_all_tags( $product->get_price_html() ),
				'image' => $image ? $image : '',
			);
		}

		$cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'name__like' => $q,
				'number'     => 3,
			)
		);
		if ( $cats && ! is_wp_error( $cats ) ) {
			foreach ( $cats as $cat ) {
				$link = get_term_link( $cat );
				if ( is_wp_error( $link ) ) {
					continue;
				}
				$payload['categories'][] = array(
					'name'  => $cat->name,
					'url'   => $link,
					'count' => te_core_digits( (string) $cat->count ),
				);
			}
		}
		return $payload;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			's'              => $q,
			'posts_per_page' => 6,
			'no_found_rows'  => true,
		)
	);
	foreach ( $query->posts as $post ) {
		$payload['products'][] = array(
			'id'    => $post->ID,
			'name'  => get_the_title( $post ),
			'url'   => get_permalink( $post ),
			'price' => '',
			'image' => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: '',
		);
	}
	wp_reset_postdata();
	return $payload;
}

/**
 * Partial SKU matches. Prepared, limited, catalog-only.
 *
 * @param string $q     Query.
 * @param int    $limit Limit.
 * @return array<int,int>
 */
function te_core_sku_ids( $q, $limit ) {
	global $wpdb;
	$limit = max( 1, min( 6, (int) $limit ) );
	$ids   = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value LIKE %s LIMIT %d",
			'%' . $wpdb->esc_like( $q ) . '%',
			$limit
		)
	);
	return array_map( 'absint', $ids );
}

/**
 * Render cards for wishlist and recently viewed.
 *
 * @param WP_REST_Request $request Request.
 * @return array<string,string>
 */
function te_core_rest_cards( $request ) {
	$ids = array_slice(
		array_filter( array_map( 'absint', explode( ',', (string) $request->get_param( 'ids' ) ) ) ),
		0,
		12
	);
	if ( ! $ids || ! function_exists( 'wc_get_product' ) ) {
		return array( 'html' => '' );
	}
	ob_start();
	echo '<ul class="products te-products">';
	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );
		if ( ! $product || ! $product->is_visible() ) {
			continue;
		}
		$GLOBALS['product'] = $product;
		echo '<li ';
		wc_product_class( 'te-card-item', $product );
		echo '>';
		te_core_product_card( $product );
		echo '</li>';
	}
	echo '</ul>';
	return array( 'html' => ob_get_clean() );
}

/**
 * Compare table. Built only when the dialog asks for it.
 *
 * @param WP_REST_Request $request Request.
 * @return array<string,string>
 */
function te_core_rest_compare( $request ) {
	$ids = array_slice(
		array_filter( array_map( 'absint', explode( ',', (string) $request->get_param( 'ids' ) ) ) ),
		0,
		4
	);
	return array( 'html' => te_core_compare_html( $ids ) );
}

/**
 * Quick view HTML.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function te_core_rest_quick( $request ) {
	$id      = absint( $request->get_param( 'id' ) );
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;
	if ( ! $product || ! $product->is_visible() ) {
		return new WP_Error( 'te_core_not_found', __( 'Product not found.', 'te-core' ), array( 'status' => 404 ) );
	}
	$GLOBALS['product'] = $product;
	$post               = get_post( $id );
	if ( $post ) {
		setup_postdata( $post );
	}
	ob_start();
	get_template_part( 'template-parts/components/quick-view', null, array( 'product' => $product ) );
	$html = ob_get_clean();
	wp_reset_postdata();
	return rest_ensure_response( array( 'html' => $html ) );
}
