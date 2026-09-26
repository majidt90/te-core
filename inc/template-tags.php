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
			</a>
			<?php te_core_sale_badge( $product ); ?>
			<?php if ( ! $product->is_in_stock() ) : ?>
				<span class="te-badge te-badge--muted"><?php esc_html_e( 'Out of stock', 'te-core' ); ?></span>
			<?php endif; ?>
			<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
			<?php if ( te_core_on( 'quick_view' ) || te_core_on( 'show_wishlist' ) ) : ?>
				<div class="te-card__tools">
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
 * Open a homepage section.
 *
 * @param array  $args  Section args.
 * @param string $class Extra class.
 * @return void
 */
function te_core_section_open( $args, $class ) {
	$id = ! empty( $args['uid'] ) ? ' id="te-' . esc_attr( $args['uid'] ) . '"' : '';
	echo '<section class="te-section ' . esc_attr( $class ) . '"' . $id . '>';
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
