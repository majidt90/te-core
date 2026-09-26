<?php
/**
 * Product card inside loops.
 *
 * Based on WooCommerce content-product.php 9.4.0.
 * Visibility rules are unchanged. Markup is the shared TE Core card.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package TE_Core
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'te-card-item', $product ); ?>>
	<?php
	do_action( 'te_core_before_product_card', $product );
	te_core_product_card( $product );
	do_action( 'te_core_after_product_card', $product );
	?>
</li>
