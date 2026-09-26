<?php
/**
 * Storefront markup shared by the loop, rails, and search.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product card. Hooks stay available for plugins; default WC wrappers are rebuilt here.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function te_core_product_card( $product = null ) {
	if ( ! $product && isset( $GLOBALS['product'] ) ) {
		$product = $GLOBALS['product'];
	}
	if ( ! is_a( $product, 'WC_Product' ) || ! $product->is_visible() ) {
		return;
	}

	$permalink = $product->get_permalink();
	$on_sale   = $product->is_on_sale();
	?>
	<article class="te-card">
		<div class="te-card__media">
			<?php
			/**
			 * Plugins may inject before the card media.
			 * The theme removes the default link-open callback so it is not duplicated.
			 */
			do_action( 'woocommerce_before_shop_loop_item' );
			?>
			<a class="te-card__link" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
				<?php echo woocommerce_get_product_thumbnail( 'te-card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php te_core_card_alt_image( $product ); ?>
			</a>
			<?php te_core_sale_badge( $product ); ?>
			<?php if ( ! $product->is_in_stock() ) : ?>
				<span class="te-badge te-badge--muted"><?php esc_html_e( 'Out of stock', 'te-core' ); ?></span>
			<?php endif; ?>
			<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
			<?php if ( te_core_on( 'quick_view' ) || te_core_on( 'show_wishlist' ) || te_core_on( 'show_compare' ) ) : ?>
				<div class="te-card__tools">
					<?php if ( te_core_on( 'show_compare' ) ) : ?>
						<button type="button" class="te-iconbtn te-compare-btn" data-te-compare="<?php echo esc_attr( (string) $product->get_id() ); ?>" aria-pressed="false">
							<?php te_core_icon( 'scale' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Compare', 'te-core' ); ?></span>
						</button>
					<?php endif; ?>
					<?php if ( te_core_on( 'show_wishlist' ) ) : ?>
						<button type="button" class="te-iconbtn te-wish" data-te-wish="<?php echo esc_attr( (string) $product->get_id() ); ?>" aria-pressed="false">
							<?php te_core_icon( 'heart' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Save', 'te-core' ); ?></span>
						</button>
					<?php endif; ?>
					<?php if ( te_core_on( 'quick_view' ) ) : ?>
						<button type="button" class="te-iconbtn te-quick" data-te-quick="<?php echo esc_attr( (string) $product->get_id() ); ?>">
							<?php te_core_icon( 'search' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Quick view', 'te-core' ); ?></span>
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="te-card__body">
			<?php do_action( 'woocommerce_shop_loop_item_title' ); ?>
			<?php if ( te_core_on( 'show_card_category' ) ) : ?>
				<?php te_core_card_category( $product ); ?>
			<?php endif; ?>
			<h3 class="te-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
			</h3>
			<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
			<?php if ( te_core_on( 'show_rating' ) ) : ?>
				<?php woocommerce_template_loop_rating(); ?>
			<?php endif; ?>
			<div class="te-card__price<?php echo $on_sale ? ' is-sale' : ''; ?>">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>
			<?php te_core_low_stock_badge( $product ); ?>
			<div class="te-card__actions">
				<?php woocommerce_template_loop_add_to_cart(); ?>
				<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
			</div>
		</div>
	</article>
	<?php
}

/**
 * First visible category, linked.
 *
 * @param WC_Product $product Product.
 * @return void
 */
function te_core_card_category( $product ) {
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}
	$term = $terms[0];
	$link = get_term_link( $term );
	if ( is_wp_error( $link ) ) {
		return;
	}
	echo '<p class="te-card__cat"><a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a></p>';
}

/**
 * Sale badge. Percent when the math is real, otherwise the text label.
 *
 * @param WC_Product $product Product.
 * @return void
 */
function te_core_sale_badge( $product ) {
	if ( ! $product->is_on_sale() ) {
		return;
	}
	$label = __( 'Sale', 'te-core' );
	if ( 'percent' === te_core_get( 'sale_badge' ) ) {
		$percent = te_core_discount_percent( $product );
		if ( $percent > 0 ) {
			$label = sprintf(
				/* translators: %s: discount percent */
				__( '−%s%%', 'te-core' ),
				te_core_digits( (string) $percent )
			);
		}
	}
	echo '<span class="te-badge">' . esc_html( $label ) . '</span>';
}

/**
 * Discount percent, or 0 when it cannot be stated exactly.
 *
 * @param WC_Product $product Product.
 * @return int
 */
function te_core_discount_percent( $product ) {
	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();
	if ( $product->is_type( 'variable' ) ) {
		$regular = (float) $product->get_variation_regular_price( 'max' );
		$sale    = (float) $product->get_variation_sale_price( 'min' );
	}
	if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) {
		return 0;
	}
	return (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
}

/**
 * Query products for a rail. Featured falls back to newest so the section is not a lie.
 *
 * @param string $source   Source key.
 * @param int    $count    Limit.
 * @param int    $category Category ID.
 * @return array{products:array<int,WC_Product>,source:string}
 */
function te_core_query_products( $source, $count, $category = 0 ) {
	$result = array(
		'products' => array(),
		'source'   => $source,
	);
	if ( ! function_exists( 'wc_get_products' ) ) {
		return $result;
	}
	$count = max( 1, min( 16, (int) $count ) );
	$args  = array(
		'status'     => 'publish',
		'limit'      => $count,
		'visibility' => 'catalog',
		'return'     => 'objects',
	);

	switch ( $source ) {
		case 'sale':
			$ids = wc_get_product_ids_on_sale();
			$args['include'] = $ids ? $ids : array( 0 );
			break;
		case 'newest':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'bestselling':
			$args['orderby'] = 'popularity';
			$args['order']   = 'DESC';
			break;
		case 'top_rated':
			$args['orderby'] = 'rating';
			$args['order']   = 'DESC';
			break;
		case 'category':
			$term = get_term( (int) $category, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$args['category'] = array( $term->slug );
			}
			break;
		case 'featured':
		default:
			$args['featured'] = true;
			$source           = 'featured';
			break;
	}

	$products = wc_get_products( $args );
	if ( 'featured' === $source && ! $products ) {
		unset( $args['featured'] );
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
		$products        = wc_get_products( $args );
		$source          = 'newest';
	}

	$result['products'] = is_array( $products ) ? $products : array();
	$result['source']   = $source;
	return $result;
}

/**
 * Print a product grid.
 *
 * @param array<int,WC_Product> $products Products.
 * @return void
 */
function te_core_render_products( $products ) {
	if ( ! $products ) {
		return;
	}
	global $post, $product;
	echo '<ul class="products te-products">';
	foreach ( $products as $item ) {
		if ( ! is_a( $item, 'WC_Product' ) ) {
			continue;
		}
		$product = $item;
		$post    = get_post( $item->get_id() );
		if ( $post ) {
			setup_postdata( $post );
		}
		echo '<li ';
		wc_product_class( 'te-card-item', $item );
		echo '>';
		te_core_product_card( $item );
		echo '</li>';
	}
	echo '</ul>';
	wp_reset_postdata();
}

/**
 * Section heading row.
 *
 * @param string $kicker    Eyebrow.
 * @param string $title     Title.
 * @param string $link      URL.
 * @param string $link_text Link text.
 * @param string $heading   h1 or h2.
 * @return void
 */
function te_core_section_head( $kicker, $title, $link = '', $link_text = '', $heading = 'h2' ) {
	if ( '' === $title && '' === $kicker ) {
		return;
	}
	$tag = ( 'h1' === $heading ) ? 'h1' : 'h2';
	echo '<header class="te-section__head">';
	echo '<div>';
	if ( '' !== $kicker ) {
		echo '<p class="te-kicker">' . esc_html( $kicker ) . '</p>';
	}
	if ( '' !== $title ) {
		echo '<' . $tag . ' class="te-section__title">' . esc_html( $title ) . '</' . $tag . '>';
	}
	echo '</div>';
	if ( $link && $link_text ) {
		echo '<a class="te-link" href="' . esc_url( $link ) . '">' . esc_html( $link_text ) . '</a>';
	}
	echo '</header>';
}

/**
 * Width token. Unknown values stay contained so a bad save cannot break the grid.
 *
 * @param array $args Section args.
 * @return string
 */
function te_core_width_class( $args ) {
	$width = (string) te_core_field( $args, 'width' );
	if ( ! in_array( $width, array( 'contained', 'wide', 'full' ), true ) ) {
		$width = 'contained';
	}
	return 'is-' . $width;
}

/**
 * Open a homepage section.
 *
 * @param array  $args  Section args.
 * @param string $class Extra class.
 * @return void
 */
function te_core_section_open( $args, $class ) {
	$id = ! empty( $args['uid'] ) ? ' id="te-' . esc_attr( $args['uid'] ) . '"' : '';
	echo '<section class="te-section ' . esc_attr( trim( $class . ' ' . te_core_width_class( $args ) ) ) . '"' . $id . '>';
	echo '<div class="te-container">';
}

/**
 * Close a homepage section.
 *
 * @return void
 */
function te_core_section_close() {
	echo '</div></section>';
}

/**
 * Catalog filters. Query-string based, so they work with no script.
 *
 * @return void
 */
function te_core_render_filters() {
	if ( ! function_exists( 'is_shop' ) ) {
		return;
	}
	$min     = isset( $_GET['min_price'] ) ? sanitize_text_field( wp_unslash( $_GET['min_price'] ) ) : '';
	$max     = isset( $_GET['max_price'] ) ? sanitize_text_field( wp_unslash( $_GET['max_price'] ) ) : '';
	$instock = ! empty( $_GET['instock'] );
	$action  = te_core_filter_action();
	$current = is_product_category() ? get_queried_object_id() : 0;
	$cats    = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => 24,
		)
	);
	?>
	<div class="te-filters__head">
		<h2><?php esc_html_e( 'Filter', 'te-core' ); ?></h2>
		<button type="button" class="te-iconbtn te-filters__close" data-te-close-filters>
			<?php te_core_icon( 'close' ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Close filters', 'te-core' ); ?></span>
		</button>
	</div>
	<?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
		<div class="te-filter">
			<h3><?php esc_html_e( 'Department', 'te-core' ); ?></h3>
			<ul class="te-filter__list">
				<li>
					<a href="<?php echo esc_url( te_core_shop_url() ); ?>" <?php echo is_shop() ? 'aria-current="page"' : ''; ?>>
						<?php esc_html_e( 'All products', 'te-core' ); ?>
					</a>
				</li>
				<?php foreach ( $cats as $cat ) : ?>
					<?php $link = get_term_link( $cat ); ?>
					<?php if ( is_wp_error( $link ) ) { continue; } ?>
					<li>
						<a href="<?php echo esc_url( $link ); ?>" <?php echo ( (int) $current === (int) $cat->term_id ) ? 'aria-current="page"' : ''; ?>>
							<?php echo esc_html( $cat->name ); ?>
							<span><?php echo esc_html( te_core_digits( (string) $cat->count ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<form class="te-filter" method="get" action="<?php echo esc_url( $action ); ?>">
		<h3><?php esc_html_e( 'Price', 'te-core' ); ?></h3>
		<div class="te-filter__price">
			<label>
				<span class="screen-reader-text"><?php esc_html_e( 'Minimum price', 'te-core' ); ?></span>
				<input type="number" inputmode="numeric" min="0" name="min_price" value="<?php echo esc_attr( $min ); ?>" placeholder="<?php esc_attr_e( 'Min', 'te-core' ); ?>">
			</label>
			<label>
				<span class="screen-reader-text"><?php esc_html_e( 'Maximum price', 'te-core' ); ?></span>
				<input type="number" inputmode="numeric" min="0" name="max_price" value="<?php echo esc_attr( $max ); ?>" placeholder="<?php esc_attr_e( 'Max', 'te-core' ); ?>">
			</label>
		</div>
		<label class="te-check">
			<input type="checkbox" name="instock" value="1" <?php checked( $instock ); ?>>
			<span><?php esc_html_e( 'In stock only', 'te-core' ); ?></span>
		</label>
		<?php te_core_preserve_query( array( 'min_price', 'max_price', 'instock', 'paged' ) ); ?>
		<button class="te-btn te-btn--primary" type="submit"><?php esc_html_e( 'Apply', 'te-core' ); ?></button>
	</form>
	<?php if ( is_active_sidebar( 'shop' ) ) : ?>
		<div class="te-filter te-filter--widgets">
			<?php dynamic_sidebar( 'shop' ); ?>
		</div>
	<?php endif; ?>
	<?php
}

/**
 * Current catalog URL without paging.
 *
 * @return string
 */
function te_core_filter_action() {
	if ( is_product_taxonomy() ) {
		$link = get_term_link( get_queried_object() );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return te_core_shop_url();
}

/**
 * Keep unrelated query args when the filter form submits.
 *
 * @param array $drop Args to drop.
 * @return void
 */
function te_core_preserve_query( $drop ) {
	foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key = sanitize_key( $key );
		if ( in_array( $key, $drop, true ) || is_array( $value ) ) {
			continue;
		}
		echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( sanitize_text_field( wp_unslash( $value ) ) ) . '">';
	}
}

/**
 * Search placeholder.
 *
 * @return string
 */
function te_core_search_placeholder() {
	$custom = te_core_get( 'search_placeholder' );
	if ( is_string( $custom ) && '' !== $custom ) {
		return $custom;
	}
	if ( class_exists( 'WooCommerce' ) ) {
		return __( 'Search products and departments', 'te-core' );
	}
	return __( 'Search', 'te-core' );
}

/**
 * Cart count for the header.
 *
 * @return int
 */
function te_core_cart_count() {
	return te_core_ensure_cart_count();
}

/**
 * Posted search string.
 *
 * @return string
 */
function te_core_search_query() {
	return get_search_query();
}

/**
 * First letter, safe for Persian and Latin.
 *
 * @param string $name Name.
 * @return string
 */
function te_core_monogram( $name ) {
	$name = trim( wp_strip_all_tags( $name ) );
	if ( '' === $name ) {
		return '·';
	}
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $name, 0, 1 );
	}
	return substr( $name, 0, 1 );
}

/**
 * Cart count, loading the session if WooCommerce has not yet.
 *
 * @return int
 */
/**
 * Live catalog facts when the hero has no image. No invented numbers.
 *
 * @return void
 */
function te_core_hero_facts() {
	$facts = array();
	if ( post_type_exists( 'product' ) ) {
		$counts  = wp_count_posts( 'product' );
		$facts[] = array(
			'value' => isset( $counts->publish ) ? (int) $counts->publish : 0,
			'label' => __( 'Products', 'te-core' ),
		);
		$cats = wp_count_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
			)
		);
		if ( ! is_wp_error( $cats ) ) {
			$facts[] = array(
				'value' => (int) $cats,
				'label' => __( 'Departments', 'te-core' ),
			);
		}
	}
	$facts[] = array(
		'value' => null,
		'label' => __( 'Prices confirmed at checkout', 'te-core' ),
	);
	echo '<ul class="te-facts">';
	foreach ( $facts as $fact ) {
		echo '<li>';
		if ( null !== $fact['value'] ) {
			echo '<strong>' . esc_html( te_core_digits( number_format_i18n( $fact['value'] ) ) ) . '</strong>';
		}
		echo '<span>' . esc_html( $fact['label'] ) . '</span>';
		echo '</li>';
	}
	echo '</ul>';
}

function te_core_ensure_cart_count() {
	if ( ! function_exists( 'WC' ) ) {
		return 0;
	}
	if ( null === WC()->cart && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}
	if ( ! WC()->cart ) {
		return 0;
	}
	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * Second gallery image. Omitted unless the setting is on and a file exists.
 *
 * @param WC_Product $product Product.
 * @return void
 */
function te_core_card_alt_image( $product ) {
	if ( ! te_core_on( 'hover_image' ) ) {
		return;
	}
	$ids = $product->get_gallery_image_ids();
	if ( empty( $ids[0] ) ) {
		return;
	}
	echo wp_get_attachment_image(
		(int) $ids[0],
		'te-card',
		false,
		array(
			'class'         => 'te-card__alt',
			'alt'           => '',
			'loading'       => 'lazy',
			'decoding'      => 'async',
			'fetchpriority' => 'low',
		)
	);
}

/**
 * Honest low-stock line. Empty unless stock is managed and inside the limit.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function te_core_low_stock_text( $product ) {
	if ( ! te_core_on( 'low_stock' ) || ! $product->managing_stock() || ! $product->is_in_stock() ) {
		return '';
	}
	$qty = $product->get_stock_quantity();
	if ( null === $qty || $qty < 1 || $qty > (int) te_core_get( 'low_stock_qty' ) ) {
		return '';
	}
	return sprintf(
		/* translators: %s: remaining quantity */
		__( 'Only %s left', 'te-core' ),
		te_core_digits( (string) $qty )
	);
}

/**
 * Print the low-stock line when it is real.
 *
 * @param WC_Product $product Product.
 * @return void
 */
function te_core_low_stock_badge( $product ) {
	$label = te_core_low_stock_text( $product );
	if ( '' === $label ) {
		return;
	}
	echo '<p class="te-low">' . esc_html( $label ) . '</p>';
}

/**
 * First four visible attributes, in the buy box.
 *
 * @return void
 */
function te_core_product_specs() {
	if ( ! te_core_on( 'show_specs' ) || ! function_exists( 'wc_get_product' ) ) {
		return;
	}
	$product = $GLOBALS['product'] ?? null;
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	$rows = array();
	foreach ( $product->get_attributes() as $attr ) {
		if ( ! is_a( $attr, 'WC_Product_Attribute' ) || ! $attr->get_visible() ) {
			continue;
		}
		$value = $product->get_attribute( $attr->get_name() );
		if ( '' === $value ) {
			continue;
		}
		$rows[] = array( wc_attribute_label( $attr->get_name() ), $value );
		if ( count( $rows ) >= 4 ) {
			break;
		}
	}
	if ( ! $rows ) {
		return;
	}
	echo '<dl class="te-specs">';
	foreach ( $rows as $row ) {
		echo '<div><dt>' . esc_html( $row[0] ) . '</dt><dd>' . esc_html( $row[1] ) . '</dd></div>';
	}
	echo '</dl>';
}

/**
 * Whether a query arg is a catalog filter, not sort or search.
 *
 * @param string $key Query key.
 * @return bool
 */
function te_core_is_filter_arg( $key ) {
	if ( in_array( $key, array( 'min_price', 'max_price', 'instock', 'te_source', 'rating_filter' ), true ) ) {
		return true;
	}
	return 0 === strpos( $key, 'filter_' ) || 0 === strpos( $key, 'query_type_' );
}

/**
 * Current catalog query, paging stripped.
 *
 * @return array<string,string>
 */
function te_core_catalog_args() {
	$args = array();
	foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( is_array( $value ) ) {
			continue;
		}
		$key = sanitize_key( $key );
		if ( '' === $key || 'paged' === $key ) {
			continue;
		}
		$args[ $key ] = sanitize_text_field( wp_unslash( $value ) );
	}
	return $args;
}

/**
 * Catalog URL with some query args removed.
 *
 * @param array<int,string> $drop Keys to drop.
 * @param array<string,string|null> $set Keys to set. Null removes.
 * @return string
 */
function te_core_query_url( $drop, $set = array() ) {
	$args = te_core_catalog_args();
	foreach ( $drop as $key ) {
		unset( $args[ $key ] );
	}
	foreach ( $set as $key => $value ) {
		if ( null === $value || '' === $value ) {
			unset( $args[ $key ] );
		} else {
			$args[ $key ] = $value;
		}
	}
	$base = te_core_filter_action();
	return $args ? add_query_arg( $args, $base ) : $base;
}

/**
 * Active filters as links. No script: each link drops one argument.
 *
 * @return void
 */
function te_core_filter_chips() {
	$args = te_core_catalog_args();
	$any  = false;
	foreach ( array_keys( $args ) as $key ) {
		if ( te_core_is_filter_arg( $key ) ) {
			$any = true;
			break;
		}
	}
	if ( ! $any ) {
		return;
	}

	$chips = array();
	if ( isset( $args['min_price'] ) && '' !== $args['min_price'] ) {
		$chips[] = array(
			'label' => sprintf(
				/* translators: %s: minimum price */
				__( 'Min %s', 'te-core' ),
				te_core_digits( $args['min_price'] )
			),
			'url'   => te_core_query_url( array( 'min_price' ) ),
		);
	}
	if ( isset( $args['max_price'] ) && '' !== $args['max_price'] ) {
		$chips[] = array(
			'label' => sprintf(
				/* translators: %s: maximum price */
				__( 'Max %s', 'te-core' ),
				te_core_digits( $args['max_price'] )
			),
			'url'   => te_core_query_url( array( 'max_price' ) ),
		);
	}
	if ( ! empty( $args['instock'] ) ) {
		$chips[] = array(
			'label' => __( 'In stock only', 'te-core' ),
			'url'   => te_core_query_url( array( 'instock' ) ),
		);
	}
	$sources = array(
		'featured' => __( 'Featured', 'te-core' ),
		'sale'     => __( 'On sale', 'te-core' ),
	);
	if ( isset( $args['te_source'], $sources[ $args['te_source'] ] ) ) {
		$chips[] = array(
			'label' => $sources[ $args['te_source'] ],
			'url'   => te_core_query_url( array( 'te_source' ) ),
		);
	}
	if ( ! empty( $args['rating_filter'] ) ) {
		$chips[] = array(
			'label' => sprintf(
				/* translators: %s: minimum rating */
				__( 'Rated %s', 'te-core' ),
				te_core_digits( $args['rating_filter'] )
			),
			'url'   => te_core_query_url( array( 'rating_filter' ) ),
		);
	}
	foreach ( $args as $key => $value ) {
		if ( 0 !== strpos( $key, 'filter_' ) || '' === $value ) {
			continue;
		}
		$attr  = substr( $key, 7 );
		$tax   = function_exists( 'wc_attribute_taxonomy_name' ) ? wc_attribute_taxonomy_name( $attr ) : $attr;
		$label = function_exists( 'wc_attribute_label' ) ? wc_attribute_label( $tax ) : $attr;
		$slugs = array_filter( array_map( 'sanitize_title', explode( ',', $value ) ) );
		foreach ( $slugs as $slug ) {
			$term = taxonomy_exists( $tax ) ? get_term_by( 'slug', $slug, $tax ) : null;
			$name = ( $term && ! is_wp_error( $term ) ) ? $term->name : $slug;
			$left = array_values( array_diff( $slugs, array( $slug ) ) );
			$drop = $left ? array() : array( $key, 'query_type_' . $attr );
			$set  = $left ? array( $key => implode( ',', $left ) ) : array();
			$chips[] = array(
				'label' => sprintf(
					/* translators: 1: attribute label, 2: term name */
					__( '%1$s: %2$s', 'te-core' ),
					$label,
					$name
				),
				'url'   => te_core_query_url( $drop, $set ),
			);
		}
	}
	if ( ! $chips ) {
		return;
	}

	$clear = te_core_catalog_args();
	foreach ( array_keys( $clear ) as $key ) {
		if ( te_core_is_filter_arg( $key ) ) {
			unset( $clear[ $key ] );
		}
	}
	$clear_url = $clear ? add_query_arg( $clear, te_core_filter_action() ) : te_core_filter_action();

	echo '<nav class="te-active" aria-label="' . esc_attr__( 'Active filters', 'te-core' ) . '"><ul>';
	foreach ( $chips as $chip ) {
		echo '<li><a href="' . esc_url( $chip['url'] ) . '"><span>' . esc_html( $chip['label'] ) . '</span><span aria-hidden="true">×</span><span class="screen-reader-text">' . esc_html__( 'Remove', 'te-core' ) . '</span></a></li>';
	}
	echo '<li class="te-active__clear"><a href="' . esc_url( $clear_url ) . '">' . esc_html__( 'Clear all', 'te-core' ) . '</a></li>';
	echo '</ul></nav>';
}

/**
 * Goods total the free-shipping meter compares. No shipping calculation.
 *
 * @return float
 */
function te_core_cart_goods_total() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0.0;
	}
	$cart   = WC()->cart;
	$amount = (float) $cart->get_subtotal();
	if ( $cart->display_prices_including_tax() ) {
		$amount += (float) $cart->get_subtotal_tax();
	}
	$amount -= (float) $cart->get_discount_total();
	if ( $cart->display_prices_including_tax() ) {
		$amount -= (float) $cart->get_discount_tax();
	}
	return max( 0, $amount );
}

/**
 * Free-shipping progress. Hidden when the threshold is 0.
 *
 * @return void
 */
function te_core_shipping_meter() {
	$min = (float) te_core_get( 'free_shipping_min' );
	if ( $min <= 0 || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$unlocked = false;
	foreach ( WC()->cart->get_coupons() as $coupon ) {
		if ( is_a( $coupon, 'WC_Coupon' ) && $coupon->get_free_shipping() ) {
			$unlocked = true;
			break;
		}
	}
	$spent = te_core_cart_goods_total();
	$left  = $unlocked ? 0 : max( 0, $min - $spent );
	$pct   = $unlocked ? 100 : min( 100, ( $spent / $min ) * 100 );
	echo '<div class="te-meter">';
	if ( $left <= 0 ) {
		echo '<p>' . esc_html__( 'Free shipping unlocked', 'te-core' ) . '</p>';
	} else {
		echo '<p>' . wp_kses_post(
			sprintf(
				/* translators: %s: remaining amount */
				__( '%s more for free shipping', 'te-core' ),
				te_core_digits( wc_price( $left ) )
			)
		) . '</p>';
	}
	echo '<div class="te-meter__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . esc_attr( (string) (int) round( $pct ) ) . '" aria-label="' . esc_attr__( 'Free shipping', 'te-core' ) . '">';
	echo '<span style="width:' . esc_attr( (string) round( $pct, 1 ) ) . '%"></span>';
	echo '</div></div>';
}

/**
 * Compare table for at most four visible products.
 *
 * @param array<int,int> $ids Product IDs.
 * @return string
 */
function te_core_compare_html( $ids ) {
	$products = array();
	foreach ( array_slice( array_values( array_unique( array_map( 'absint', $ids ) ) ), 0, 4 ) as $id ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;
		if ( $product && $product->is_visible() ) {
			$products[] = $product;
		}
	}
	if ( ! $products ) {
		return '<p class="te-empty-inline">' . esc_html__( 'Nothing to compare yet.', 'te-core' ) . '</p>';
	}

	$labels = array();
	foreach ( $products as $product ) {
		foreach ( $product->get_attributes() as $attr ) {
			if ( ! is_a( $attr, 'WC_Product_Attribute' ) || ! $attr->get_visible() ) {
				continue;
			}
			$name = $attr->get_name();
			if ( ! isset( $labels[ $name ] ) ) {
				$labels[ $name ] = wc_attribute_label( $name );
			}
			if ( count( $labels ) >= 8 ) {
				break 2;
			}
		}
	}

	ob_start();
	echo '<div class="te-compare"><table>';
	echo '<thead><tr><th scope="col"><span class="screen-reader-text">' . esc_html__( 'Product', 'te-core' ) . '</span></th>';
	foreach ( $products as $product ) {
		echo '<th scope="col">';
		echo '<button type="button" class="te-iconbtn" data-te-compare-remove="' . esc_attr( (string) $product->get_id() ) . '">';
		te_core_icon( 'close' );
		echo '<span class="screen-reader-text">' . esc_html__( 'Remove from compare', 'te-core' ) . '</span></button>';
		echo '<a href="' . esc_url( $product->get_permalink() ) . '">';
		echo $product->get_image( 'thumbnail', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span>' . esc_html( $product->get_name() ) . '</span></a>';
		echo '</th>';
	}
	echo '</tr></thead><tbody>';
	echo '<tr><th scope="row">' . esc_html__( 'Price', 'te-core' ) . '</th>';
	foreach ( $products as $product ) {
		echo '<td>' . wp_kses_post( $product->get_price_html() ) . '</td>';
	}
	echo '</tr>';
	echo '<tr><th scope="row">' . esc_html__( 'Stock', 'te-core' ) . '</th>';
	foreach ( $products as $product ) {
		echo '<td>' . esc_html( te_core_compare_stock( $product ) ) . '</td>';
	}
	echo '</tr>';
	foreach ( $labels as $name => $label ) {
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th>';
		foreach ( $products as $product ) {
			$value = $product->get_attribute( $name );
			echo '<td>' . ( '' !== $value ? esc_html( $value ) : '<span class="te-muted">—</span>' ) . '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table></div>';
	return (string) ob_get_clean();
}

/**
 * Stock cell for compare. Quantity only when WooCommerce is tracking it.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function te_core_compare_stock( $product ) {
	if ( ! $product->is_in_stock() ) {
		return __( 'Out of stock', 'te-core' );
	}
	if ( $product->managing_stock() ) {
		$qty = $product->get_stock_quantity();
		if ( null !== $qty ) {
			return sprintf(
				/* translators: %s: stock quantity */
				__( '%s in stock', 'te-core' ),
				te_core_digits( (string) $qty )
			);
		}
	}
	return __( 'In stock', 'te-core' );
}
