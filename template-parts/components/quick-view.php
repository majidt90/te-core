<?php
/**
 * Quick view body.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? ( $GLOBALS['product'] ?? null );
if ( ! is_a( $product, 'WC_Product' ) ) {
	return;
}
?>
<div class="te-quick">
	<div class="te-quick__media">
		<?php echo $product->get_image( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<div class="te-quick__body">
		<?php te_core_card_category( $product ); ?>
		<h3><?php echo esc_html( $product->get_name() ); ?></h3>
		<?php woocommerce_template_loop_rating(); ?>
		<div class="te-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<div class="te-quick__excerpt"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
		<?php if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
			<a class="te-btn te-btn--primary te-ajax-cart" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-product_id="<?php echo esc_attr( (string) $product->get_id() ); ?>">
				<?php echo esc_html( $product->add_to_cart_text() ); ?>
			</a>
		<?php else : ?>
			<a class="te-btn te-btn--primary" href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php esc_html_e( 'View options', 'te-core' ); ?>
			</a>
		<?php endif; ?>
		<p><a class="te-link" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'Full details', 'te-core' ); ?></a></p>
	</div>
</div>
