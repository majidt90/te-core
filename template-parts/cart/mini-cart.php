<?php
/**
 * Mini cart. Rendered on load and again after each cart mutation.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
	return;
}

$cart = WC()->cart->get_cart();
if ( ! $cart ) {
	echo '<div class="te-empty-inline">';
	echo '<p>' . esc_html__( 'Your cart is empty.', 'te-core' ) . '</p>';
	echo '<a class="te-btn te-btn--primary" href="' . esc_url( te_core_shop_url() ) . '">' . esc_html__( 'Browse the catalog', 'te-core' ) . '</a>';
	echo '</div>';
	return;
}
?>
<ul class="te-mini">
	<?php foreach ( $cart as $key => $item ) : ?>
		<?php
		$product = $item['data'] ?? null;
		if ( ! $product || ! $product->exists() ) {
			continue;
		}
		$qty = (int) $item['quantity'];
		?>
		<li class="te-mini__item">
			<a class="te-mini__media" href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php echo $product->get_image( 'thumbnail', array( 'class' => 'te-mini__img' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<div class="te-mini__body">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
				<span class="te-mini__price"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $product, $qty ) ); ?></span>
				<div class="te-qty" data-key="<?php echo esc_attr( $key ); ?>" data-qty="<?php echo esc_attr( (string) $qty ); ?>">
					<button type="button" data-te-qty="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'te-core' ); ?>"><?php te_core_icon( 'minus' ); ?></button>
					<span><?php echo esc_html( te_core_digits( (string) $qty ) ); ?></span>
					<button type="button" data-te-qty="1" aria-label="<?php esc_attr_e( 'Increase quantity', 'te-core' ); ?>"><?php te_core_icon( 'plus' ); ?></button>
				</div>
			</div>
			<button type="button" class="te-iconbtn" data-te-remove="<?php echo esc_attr( $key ); ?>">
				<?php te_core_icon( 'trash' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Remove', 'te-core' ); ?></span>
			</button>
		</li>
	<?php endforeach; ?>
</ul>
<div class="te-mini__foot">
	<p>
		<span><?php esc_html_e( 'Subtotal', 'te-core' ); ?></span>
		<strong><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></strong>
	</p>
	<a class="te-btn te-btn--ghost" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View cart', 'te-core' ); ?></a>
	<a class="te-btn te-btn--primary" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'te-core' ); ?></a>
</div>
