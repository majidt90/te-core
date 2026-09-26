<?php
/**
 * Storefront features that sit on the existing catalog, not beside it.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'te_core_view_boot', 1 );
add_action( 'wp_footer', 'te_core_back_to_top', 30 );
add_action( 'woocommerce_single_product_summary', 'te_core_product_meta_tools', 36 );
add_action( 'woocommerce_after_add_to_cart_button', 'te_core_delivery_line', 12 );
add_action( 'woocommerce_single_product_summary', 'te_core_contact_line', 37 );
add_action( 'woocommerce_single_product_summary', 'te_core_restock_form', 38 );
add_filter( 'woocommerce_product_tabs', 'te_core_delivery_tab' );
add_action( 'woocommerce_after_single_product', 'te_core_product_recent', 15 );
add_action( 'woocommerce_before_shop_loop', 'te_core_view_toggle', 21 );
add_action( 'woocommerce_after_shop_loop', 'te_core_load_more', 15 );
add_action( 'woocommerce_check_cart_items', 'te_core_enforce_min_order' );
add_action( 'woocommerce_after_customer_login_form', 'te_core_track_form' );
add_action( 'woocommerce_account_dashboard', 'te_core_track_form' );
add_shortcode( 'te_core_track', 'te_core_track_shortcode' );
add_action( 'admin_post_nopriv_te_core_note', 'te_core_handle_note' );
add_action( 'admin_post_te_core_note', 'te_core_handle_note' );
add_action( 'admin_post_nopriv_te_core_restock', 'te_core_handle_restock' );
add_action( 'admin_post_te_core_restock', 'te_core_handle_restock' );
add_action( 'admin_post_nopriv_te_core_track', 'te_core_handle_track' );
add_action( 'admin_post_te_core_track', 'te_core_handle_track' );
add_action( 'add_meta_boxes', 'te_core_restock_box' );

/**
 * Avoid a flash of the grid when this device last chose the list.
 *
 * @return void
 */
function te_core_view_boot() {
	if ( ! te_core_on( 'catalog_view' ) || ! te_core_is_catalog() ) {
		return;
	}
	echo "<script>try{if(localStorage.getItem('te_core_view')==='list')document.documentElement.classList.add('te-view-list');}catch(e){}</script>\n";
}

/**
 * Shop, department, and product-search requests. Not the homepage grids.
 *
 * @return bool
 */
function te_core_is_catalog() {
	if ( ! function_exists( 'is_shop' ) ) {
		return false;
	}
	if ( is_shop() || is_product_taxonomy() ) {
		return true;
	}
	if ( ! is_search() ) {
		return false;
	}
	$type = get_query_var( 'post_type' );
	return 'product' === $type || ( is_array( $type ) && in_array( 'product', $type, true ) );
}

/**
 * Quiet return after a long page.
 *
 * @return void
 */
function te_core_back_to_top() {
	if ( ! te_core_on( 'back_to_top' ) ) {
		return;
	}
	echo '<button type="button" class="te-top" data-te-top hidden>';
	te_core_icon( 'up' );
	echo '<span class="screen-reader-text">' . esc_html__( 'Back to top', 'te-core' ) . '</span></button>';
}

/**
 * A status line after a form redirect.
 *
 * @return void
 */
function te_core_flash() {
	$key = isset( $_GET['te_note'] ) ? sanitize_key( wp_unslash( $_GET['te_note'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$map = array(
		'restock'     => __( 'We’ll email the shop.', 'te-core' ),
		'restock_dup' => __( 'We already have that note.', 'te-core' ),
		'note'        => __( 'Note sent.', 'te-core' ),
		'note_fail'   => __( 'Could not send the note.', 'te-core' ),
		'limit'       => __( 'Too many notes. Try again in a minute.', 'te-core' ),
		'expired'     => __( 'The page expired. Reload and try again.', 'te-core' ),
		'stock'       => __( 'This product is available now.', 'te-core' ),
	);
	if ( ! isset( $map[ $key ] ) ) {
		return;
	}
	echo '<div class="te-flash" role="status">' . esc_html( $map[ $key ] ) . '</div>';
}

/**
 * SKU, with a copy button, and a share control.
 *
 * @return void
 */
function te_core_product_meta_tools() {
	$product = $GLOBALS['product'] ?? null;
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	if ( te_core_on( 'show_sku' ) && $product->get_sku() ) {
		$sku = $product->get_sku();
		echo '<p class="te-sku"><span>' . esc_html__( 'SKU', 'te-core' ) . '</span> <code>' . esc_html( $sku ) . '</code>';
		echo '<button type="button" data-te-copy="' . esc_attr( $sku ) . '">';
		te_core_icon( 'copy' );
		echo '<span>' . esc_html__( 'Copy', 'te-core' ) . '</span></button></p>';
	}
	if ( te_core_on( 'share_product' ) ) {
		echo '<p class="te-share"><button type="button" data-te-share data-url="' . esc_url( $product->get_permalink() ) . '" data-title="' . esc_attr( $product->get_name() ) . '">';
		te_core_icon( 'share' );
		echo '<span>' . esc_html__( 'Share', 'te-core' ) . '</span></button></p>';
	}
}

/**
 * Merchant delivery sentence. The generic checkout note stays above it.
 *
 * @return void
 */
function te_core_delivery_line() {
	$note = trim( (string) te_core_get( 'delivery_note' ) );
	if ( '' === $note ) {
		return;
	}
	$short = te_core_limit_text( $note, 180 );
	if ( $short !== $note ) {
		$short .= '…';
	}
	echo '<p class="te-buybox-note">' . esc_html( $short ) . '</p>';
}

/**
 * Product tab for the same note, when it is set.
 *
 * @param array $tabs Tabs.
 * @return array
 */
function te_core_delivery_tab( $tabs ) {
	if ( '' === trim( (string) te_core_get( 'delivery_note' ) ) ) {
		return $tabs;
	}
	$tabs['te_core_delivery'] = array(
		'title'    => __( 'Delivery', 'te-core' ),
		'priority' => 25,
		'callback' => 'te_core_delivery_tab_content',
	);
	return $tabs;
}

/**
 * Delivery tab body.
 *
 * @return void
 */
function te_core_delivery_tab_content() {
	echo '<div class="te-lead">' . wp_kses_post( wpautop( esc_html( (string) te_core_get( 'delivery_note' ) ) ) ) . '</div>';
}

/**
 * Contact link on the product, when a URL is set.
 *
 * @return void
 */
function te_core_contact_line() {
	$url = te_core_get( 'contact_url' );
	if ( ! is_string( $url ) || '' === $url ) {
		return;
	}
	echo '<p class="te-ask"><a class="te-btn te-btn--ghost" href="' . esc_url( $url ) . '">' . esc_html( te_core_contact_label() ) . '</a></p>';
}

/**
 * Contact label, translated when the merchant left it blank.
 *
 * @return string
 */
function te_core_contact_label() {
	$label = te_core_get( 'contact_label' );
	if ( is_string( $label ) && '' !== $label ) {
		return $label;
	}
	return __( 'Ask the shop', 'te-core' );
}

/**
 * Header icon for the same contact link.
 *
 * @return void
 */
function te_core_header_contact() {
	$url = te_core_get( 'contact_url' );
	if ( ! is_string( $url ) || '' === $url ) {
		return;
	}
	echo '<a class="te-iconbtn" href="' . esc_url( $url ) . '">';
	te_core_icon( 'headset' );
	echo '<span class="screen-reader-text">' . esc_html( te_core_contact_label() ) . '</span></a>';
}

/**
 * Recently viewed under the product. The script fills it.
 *
 * @return void
 */
function te_core_product_recent() {
	if ( ! te_core_on( 'product_recent' ) || ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	echo '<section class="te-product-recent" data-te-recent data-count="4" hidden>';
	echo '<h2>' . esc_html__( 'Recently viewed', 'te-core' ) . '</h2>';
	echo '<div data-te-recent-body></div></section>';
}

/**
 * Grid / list switch. The list class is applied before paint when remembered.
 *
 * @return void
 */
function te_core_view_toggle() {
	if ( ! te_core_on( 'catalog_view' ) || ! te_core_is_catalog() ) {
		return;
	}
	echo '<div class="te-view" role="group" aria-label="' . esc_attr__( 'Catalog view', 'te-core' ) . '">';
	echo '<button type="button" class="te-view__btn" data-te-view="grid" aria-pressed="true">' . esc_html__( 'Grid', 'te-core' ) . '</button>';
	echo '<button type="button" class="te-view__btn" data-te-view="list" aria-pressed="false">' . esc_html__( 'List', 'te-core' ) . '</button>';
	echo '</div>';
}

/**
 * Next-page button. Hidden until JavaScript unhides it.
 *
 * @return void
 */
function te_core_load_more() {
	if ( ! te_core_on( 'load_more' ) || ! te_core_is_catalog() ) {
		return;
	}
	global $wp_query;
	if ( ! $wp_query instanceof WP_Query ) {
		return;
	}
	$max   = (int) $wp_query->max_num_pages;
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	if ( $paged >= $max ) {
		return;
	}
	$url = get_pagenum_link( $paged + 1 );
	if ( ! $url ) {
		return;
	}
	echo '<div class="te-more" data-te-more hidden><button type="button" class="te-btn te-btn--ghost" data-te-more-btn data-url="' . esc_url( $url ) . '">' . esc_html__( 'Load more', 'te-core' ) . '</button></div>';
}

/**
 * Attribute terms as real filter links. WooCommerce reads filter_{attribute}.
 *
 * @return void
 */
function te_core_attribute_filters() {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return;
	}
	$shown = 0;
	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		if ( $shown >= 4 ) {
			break;
		}
		$name = $attribute->attribute_name;
		$tax  = wc_attribute_taxonomy_name( $name );
		if ( ! taxonomy_exists( $tax ) ) {
			continue;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => true,
				'number'     => 8,
			)
		);
		if ( ! $terms || is_wp_error( $terms ) ) {
			continue;
		}
		++$shown;
		$key     = 'filter_' . $name;
		$current = isset( $_GET[ $key ] ) ? array_filter( array_map( 'sanitize_title', explode( ',', sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="te-filter"><h3>' . esc_html( wc_attribute_label( $tax ) ) . '</h3><ul class="te-filter__list">';
		foreach ( $terms as $term ) {
			$on    = in_array( $term->slug, $current, true );
			$slugs = $on ? array_values( array_diff( $current, array( $term->slug ) ) ) : array_merge( $current, array( $term->slug ) );
			$drop  = $slugs ? array() : array( $key, 'query_type_' . $name );
			$set   = $slugs ? array( $key => implode( ',', $slugs ), 'query_type_' . $name => 'or' ) : array();
			echo '<li><a href="' . esc_url( te_core_query_url( $drop, $set ) ) . '"' . ( $on ? ' aria-current="true"' : '' ) . '>';
			echo '<span>' . esc_html( $term->name ) . '</span>';
			echo '<span>' . esc_html( te_core_digits( (string) $term->count ) ) . '</span></a></li>';
		}
		echo '</ul></div>';
	}
}

/**
 * Block checkout below the merchant’s minimum. 0 means no limit.
 *
 * @return void
 */
function te_core_enforce_min_order() {
	$min = (float) te_core_get( 'min_order' );
	if ( $min <= 0 || ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	if ( te_core_cart_goods_total() + 0.001 >= $min ) {
		return;
	}
	wc_add_notice(
		sprintf(
			/* translators: %s: minimum order amount */
			__( 'Minimum order is %s.', 'te-core' ),
			wp_strip_all_tags( wc_price( $min ) )
		),
		'error'
	);
}

/**
 * The same limit, inside the cart drawer.
 *
 * @return void
 */
function te_core_min_order_note() {
	$min = (float) te_core_get( 'min_order' );
	if ( $min <= 0 || ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$left = $min - te_core_cart_goods_total();
	if ( $left <= 0.001 ) {
		return;
	}
	echo '<p class="te-min">' . wp_kses_post(
		sprintf(
			/* translators: %s: amount still needed */
			__( '%s more to reach the minimum order.', 'te-core' ),
			wc_price( $left )
		)
	) . '</p>';
}

/**
 * One cross-sell that is not already in the cart.
 *
 * @return void
 */
function te_core_cart_cross_sell() {
	if ( ! te_core_on( 'cart_cross_sell' ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$in   = array();
	$want = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		$product = $item['data'] ?? null;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			continue;
		}
		$in[] = $product->get_id();
		if ( $product->get_parent_id() ) {
			$in[] = $product->get_parent_id();
		}
		$want = array_merge( $want, $product->get_cross_sell_ids() );
	}
	foreach ( array_unique( array_map( 'absint', $want ) ) as $id ) {
		if ( in_array( $id, $in, true ) ) {
			continue;
		}
		$product = wc_get_product( $id );
		if ( ! $product || ! $product->is_visible() || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			continue;
		}
		echo '<div class="te-cross"><a class="te-cross__media" href="' . esc_url( $product->get_permalink() ) . '">';
		echo $product->get_image( 'thumbnail', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</a><div><p class="te-kicker">' . esc_html__( 'You might also need', 'te-core' ) . '</p>';
		echo '<a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $product->get_name() ) . '</a>';
		echo '<span class="te-mini__price">' . wp_kses_post( $product->get_price_html() ) . '</span></div>';
		$previous            = $GLOBALS['product'] ?? null;
		$GLOBALS['product']  = $product;
		woocommerce_template_loop_add_to_cart();
		$GLOBALS['product'] = $previous;
		echo '</div>';
		return;
	}
}

/**
 * Footer social links. Empty settings are omitted.
 *
 * @return void
 */
function te_core_social_links() {
	$links = array(
		'telegram'  => array( te_core_get( 'social_telegram' ), __( 'Telegram', 'te-core' ) ),
		'instagram' => array( te_core_get( 'social_instagram' ), __( 'Instagram', 'te-core' ) ),
		'whatsapp'  => array( te_core_get( 'social_whatsapp' ), __( 'WhatsApp', 'te-core' ) ),
	);
	$ready = array();
	foreach ( $links as $link ) {
		if ( is_string( $link[0] ) && '' !== $link[0] ) {
			$ready[] = $link;
		}
	}
	if ( ! $ready ) {
		return;
	}
	echo '<ul class="te-social">';
	foreach ( $ready as $link ) {
		echo '<li><a href="' . esc_url( $link[0] ) . '" rel="noopener noreferrer">' . esc_html( $link[1] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * A few in-stock products. Labeled as suggestions, not as search hits.
 *
 * @return void
 */
function te_core_suggest_products() {
	if ( ! te_core_on( 'suggest_empty' ) || ! function_exists( 'wc_get_products' ) ) {
		return;
	}
	$products = wc_get_products(
		array(
			'status'       => 'publish',
			'limit'        => 4,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'stock_status' => 'instock',
			'visibility'   => 'catalog',
		)
	);
	if ( ! $products ) {
		return;
	}
	echo '<div class="te-suggest-block"><h2>' . esc_html__( 'Try one of these', 'te-core' ) . '</h2>';
	te_core_render_products( $products );
	echo '</div>';
}

/**
 * Restock request. Only for a product that is actually out of stock.
 *
 * @return void
 */
function te_core_restock_form() {
	if ( ! te_core_on( 'stock_alert' ) ) {
		return;
	}
	$product = $GLOBALS['product'] ?? null;
	if ( ! is_a( $product, 'WC_Product' ) || $product->is_in_stock() ) {
		return;
	}
	echo '<form class="te-noteform" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="te_core_restock">';
	echo '<input type="hidden" name="product_id" value="' . esc_attr( (string) $product->get_id() ) . '">';
	wp_nonce_field( 'te_core_restock', 'te_core_restock_nonce' );
	echo '<label class="screen-reader-text" for="te-restock-email">' . esc_html__( 'Your email', 'te-core' ) . '</label>';
	echo '<input id="te-restock-email" type="email" name="te_email" required maxlength="80" autocomplete="email" placeholder="' . esc_attr__( 'Your email', 'te-core' ) . '">';
	echo '<label class="te-hp" for="te-restock-company">' . esc_html__( 'Leave this field empty', 'te-core' ) . '</label>';
	echo '<input class="te-hp" id="te-restock-company" type="text" name="te_company" tabindex="-1" autocomplete="off">';
	echo '<button class="te-btn te-btn--ghost" type="submit">' . esc_html__( 'Tell me when it’s back', 'te-core' ) . '</button></form>';
}

/**
 * Shop-note form used by the homepage section.
 *
 * @param string $button Button label.
 * @return void
 */
function te_core_note_form( $button ) {
	static $note_form = 0;
	++$note_form;
	$id = 'te-note-email-' . $note_form;
	echo '<form class="te-noteform" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="te_core_note">';
	wp_nonce_field( 'te_core_note', 'te_core_note_nonce' );
	echo '<label class="screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html__( 'Your email', 'te-core' ) . '</label>';
	echo '<input id="' . esc_attr( $id ) . '" type="email" name="te_email" required maxlength="80" autocomplete="email" placeholder="' . esc_attr__( 'Your email', 'te-core' ) . '">';
	echo '<label class="te-hp" for="te-note-company-' . esc_attr( (string) $note_form ) . '">' . esc_html__( 'Leave this field empty', 'te-core' ) . '</label>';
	echo '<input class="te-hp" id="te-note-company-' . esc_attr( (string) $note_form ) . '" type="text" name="te_company" tabindex="-1" autocomplete="off">';
	echo '<button class="te-btn te-btn--primary" type="submit">' . esc_html( $button ? $button : __( 'Send the note', 'te-core' ) ) . '</button></form>';
}

/**
 * Order lookup. Email must match the billing email.
 *
 * @return void
 */
function te_core_track_form() {
	if ( ! te_core_on( 'order_lookup' ) || ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	static $shown = false;
	if ( $shown ) {
		return;
	}
	$shown = true;
	echo '<form class="te-track" id="te-track" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<h2>' . esc_html__( 'Track an order', 'te-core' ) . '</h2>';
	echo '<p>' . esc_html__( 'Order number and the email used at checkout. Both have to match.', 'te-core' ) . '</p>';
	te_core_track_result();
	echo '<input type="hidden" name="action" value="te_core_track">';
	wp_nonce_field( 'te_core_track', 'te_core_track_nonce' );
	echo '<label for="te-track-order">' . esc_html__( 'Order number', 'te-core' ) . '</label>';
	echo '<input id="te-track-order" type="text" name="te_order" required maxlength="32" inputmode="numeric" autocomplete="off">';
	echo '<label for="te-track-email">' . esc_html__( 'Email', 'te-core' ) . '</label>';
	echo '<input id="te-track-email" type="email" name="te_email" required maxlength="80" autocomplete="email">';
	echo '<label class="te-hp" for="te-track-company">' . esc_html__( 'Leave this field empty', 'te-core' ) . '</label>';
	echo '<input class="te-hp" id="te-track-company" type="text" name="te_company" tabindex="-1" autocomplete="off">';
	echo '<button class="te-btn te-btn--primary" type="submit">' . esc_html__( 'Look up', 'te-core' ) . '</button></form>';
}

/**
 * Shortcode wrapper. The form prints itself.
 *
 * @return string
 */
function te_core_track_shortcode() {
	ob_start();
	te_core_track_form();
	return (string) ob_get_clean();
}

/**
 * Result of the last lookup, stored briefly and then deleted.
 *
 * @return void
 */
function te_core_track_result() {
	$token = isset( $_GET['te_track'] ) ? sanitize_key( wp_unslash( $_GET['te_track'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( '' === $token ) {
		return;
	}
	$data = get_transient( 'te_core_track_' . $token );
	delete_transient( 'te_core_track_' . $token );
	if ( ! is_array( $data ) || empty( $data['ok'] ) ) {
		echo '<p class="te-track__miss">' . esc_html__( 'We could not find that order.', 'te-core' ) . '</p>';
		return;
	}
	echo '<dl class="te-track__result">';
	echo '<div><dt>' . esc_html__( 'Status', 'te-core' ) . '</dt><dd>' . esc_html( (string) $data['status'] ) . '</dd></div>';
	echo '<div><dt>' . esc_html__( 'Placed', 'te-core' ) . '</dt><dd>' . esc_html( (string) $data['date'] ) . '</dd></div>';
	if ( ! empty( $data['items'] ) && is_array( $data['items'] ) ) {
		echo '<div><dt>' . esc_html__( 'Items', 'te-core' ) . '</dt><dd>' . esc_html( implode( ', ', array_map( 'strval', $data['items'] ) ) ) . '</dd></div>';
	}
	echo '</dl>';
}

/**
 * Shared redirect after a public form.
 *
 * @param string $code Note code.
 * @return void
 */
function te_core_redirect_note( $code ) {
	$ref = wp_get_referer();
	if ( ! $ref ) {
		$ref = home_url( '/' );
	}
	wp_safe_redirect( add_query_arg( 'te_note', sanitize_key( $code ), remove_query_arg( array( 'te_note', 'te_track' ), $ref ) ) );
	exit;
}

/**
 * Small per-address limit. Not a substitute for a real firewall.
 *
 * @param string $bucket Bucket.
 * @param int    $limit  Hits.
 * @param int    $window Seconds.
 * @return bool
 */
function te_core_rate_ok( $bucket, $limit = 5, $window = 60 ) {
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key   = 'te_rl_' . md5( $bucket . '|' . $ip );
	$count = (int) get_transient( $key );
	if ( $count >= $limit ) {
		return false;
	}
	set_transient( $key, $count + 1, $window );
	return true;
}

/**
 * Reject a filled honeypot.
 *
 * @return bool
 */
function te_core_honeypot_clear() {
	$company = isset( $_POST['te_company'] ) ? trim( (string) wp_unslash( $_POST['te_company'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	return '' === $company;
}

/**
 * Email the shop. The address is not stored.
 *
 * @return void
 */
function te_core_handle_note() {
	$nonce = isset( $_POST['te_core_note_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['te_core_note_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'te_core_note' ) || ! te_core_honeypot_clear() ) {
		te_core_redirect_note( 'expired' );
	}
	if ( ! te_core_rate_ok( 'note', 4, MINUTE_IN_SECONDS ) ) {
		te_core_redirect_note( 'limit' );
	}
	$email = isset( $_POST['te_email'] ) ? sanitize_email( wp_unslash( $_POST['te_email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		te_core_redirect_note( 'note_fail' );
	}
	/**
	 * Fires when a visitor sends a shop note. Plugins may forward it.
	 *
	 * @param string $email Email address.
	 */
	do_action( 'te_core_shop_note', $email );
	$sent = wp_mail(
		get_option( 'admin_email' ),
		sprintf(
			/* translators: %s: site name */
			__( 'Note from the shop form — %s', 'te-core' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		),
		sprintf(
			/* translators: %s: email address */
			__( 'A visitor asked to be written back at %s. This address was not stored.', 'te-core' ),
			$email
		)
	);
	te_core_redirect_note( $sent ? 'note' : 'note_fail' );
}

/**
 * Email the shop about an out-of-stock product. Deduped per product.
 *
 * @return void
 */
function te_core_handle_restock() {
	$nonce = isset( $_POST['te_core_restock_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['te_core_restock_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'te_core_restock' ) || ! te_core_honeypot_clear() ) {
		te_core_redirect_note( 'expired' );
	}
	if ( ! te_core_rate_ok( 'restock', 4, MINUTE_IN_SECONDS ) ) {
		te_core_redirect_note( 'limit' );
	}
	$id      = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$email   = isset( $_POST['te_email'] ) ? sanitize_email( wp_unslash( $_POST['te_email'] ) ) : '';
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;
	if ( ! $product || ! is_email( $email ) ) {
		te_core_redirect_note( 'note_fail' );
	}
	if ( $product->is_in_stock() ) {
		te_core_redirect_note( 'stock' );
	}
	$saved = get_post_meta( $id, '_te_core_restock', true );
	$saved = is_array( $saved ) ? $saved : array();
	$key   = strtolower( $email );
	if ( in_array( $key, $saved, true ) ) {
		te_core_redirect_note( 'restock_dup' );
	}
	$saved[] = $key;
	update_post_meta( $id, '_te_core_restock', array_slice( $saved, -50 ) );
	wp_mail(
		get_option( 'admin_email' ),
		sprintf(
			/* translators: %s: product name */
			__( 'Restock note — %s', 'te-core' ),
			$product->get_name()
		),
		sprintf(
			/* translators: 1: email, 2: product name, 3: URL */
			__( '%1$s asked to hear when %2$s is back. %3$s', 'te-core' ),
			$email,
			$product->get_name(),
			$product->get_permalink()
		)
	);
	te_core_redirect_note( 'restock' );
}

/**
 * Look up an order only when the email matches.
 *
 * @return void
 */
function te_core_handle_track() {
	$nonce = isset( $_POST['te_core_track_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['te_core_track_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'te_core_track' ) || ! te_core_honeypot_clear() ) {
		te_core_redirect_note( 'expired' );
	}
	if ( ! te_core_rate_ok( 'track', 8, 10 * MINUTE_IN_SECONDS ) ) {
		te_core_redirect_note( 'limit' );
	}
	$number = isset( $_POST['te_order'] ) ? sanitize_text_field( wp_unslash( $_POST['te_order'] ) ) : '';
	$email  = isset( $_POST['te_email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['te_email'] ) ) ) : '';
	$order  = te_core_find_order( $number );
	$bill   = $order ? strtolower( sanitize_email( $order->get_billing_email() ) ) : '';
	$match  = '' !== $email && '' !== $bill && hash_equals( hash( 'sha256', $bill ), hash( 'sha256', $email ) );
	$token  = strtolower( wp_generate_password( 12, false, false ) );
	$data   = array( 'ok' => false );
	if ( $order && $match ) {
		$items = array();
		foreach ( $order->get_items() as $item ) {
			$items[] = $item->get_name();
			if ( count( $items ) >= 6 ) {
				break;
			}
		}
		$date = $order->get_date_created();
		$data = array(
			'ok'     => true,
			'status' => wc_get_order_status_name( $order->get_status() ),
			'date'   => $date ? wc_format_datetime( $date ) : '',
			'items'  => $items,
		);
	}
	set_transient( 'te_core_track_' . $token, $data, 2 * MINUTE_IN_SECONDS );
	$ref = wp_get_referer();
	if ( ! $ref ) {
		$ref = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
	}
	wp_safe_redirect( add_query_arg( 'te_track', $token, remove_query_arg( array( 'te_note', 'te_track' ), $ref ) ) . '#te-track' );
	exit;
}

/**
 * Show collected restock addresses on the product, only when there are some.
 *
 * @return void
 */
function te_core_restock_box() {
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$saved   = $post_id ? get_post_meta( $post_id, '_te_core_restock', true ) : array();
	if ( ! is_array( $saved ) || ! $saved ) {
		return;
	}
	add_meta_box(
		'te_core_restock',
		__( 'Restock notes', 'te-core' ),
		'te_core_restock_box_html',
		'product',
		'side',
		'low'
	);
}

/**
 * Restock meta box.
 *
 * @param WP_Post $post Product.
 * @return void
 */
function te_core_restock_box_html( $post ) {
	$saved = get_post_meta( $post->ID, '_te_core_restock', true );
	if ( ! is_array( $saved ) || ! $saved ) {
		echo '<p>' . esc_html__( 'No notes yet.', 'te-core' ) . '</p>';
		return;
	}
	echo '<ul>';
	foreach ( $saved as $email ) {
		echo '<li>' . esc_html( (string) $email ) . '</li>';
	}
	echo '</ul>';
}

/**
 * Find an order by its displayed number or its ID. Email is checked by the caller.
 *
 * @param string $number Order number.
 * @return WC_Order|null
 */
function te_core_find_order( $number ) {
	$number = trim( (string) $number );
	if ( '' === $number || ! function_exists( 'wc_get_order' ) ) {
		return null;
	}
	$digits = preg_replace( '/\D+/', '', $number );
	if ( $digits ) {
		$order = wc_get_order( absint( $digits ) );
		if ( $order ) {
			$shown = (string) $order->get_order_number();
			if ( $shown === $number || $shown === $digits || (string) $order->get_id() === $digits ) {
				return $order;
			}
		}
	}
	if ( function_exists( 'wc_get_orders' ) ) {
		$found = wc_get_orders(
			array(
				'limit'      => 1,
				'meta_key'   => '_order_number', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $number, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $found ) {
			return $found[0];
		}
	}
	return null;
}
