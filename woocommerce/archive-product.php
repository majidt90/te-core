<?php
/**
 * Product archives.
 *
 * Based on WooCommerce archive-product.php 8.6.0. Hooks are preserved.
 * The sidebar action is called inside the filter column so plugins can still attach.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package TE_Core
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );
do_action( 'woocommerce_shop_loop_header' );

$show_filters = te_core_on( 'show_filters' );
?>
<div class="te-catalog <?php echo $show_filters ? '' : 'te-catalog--plain'; ?>">
	<?php if ( $show_filters ) : ?>
		<aside class="te-filters" id="te-filters">
			<?php
			remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
			add_action( 'woocommerce_sidebar', 'te_core_render_filters', 10 );
			do_action( 'woocommerce_sidebar' );
			?>
		</aside>
		<div class="te-filters-backdrop" data-te-close-filters hidden></div>
	<?php endif; ?>
	<div class="te-catalog__main">
		<?php if ( woocommerce_product_loop() ) : ?>
			<?php do_action( 'woocommerce_before_shop_loop' ); ?>
			<?php woocommerce_product_loop_start(); ?>
			<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				endwhile;
				?>
			<?php endif; ?>
			<?php woocommerce_product_loop_end(); ?>
			<?php do_action( 'woocommerce_after_shop_loop' ); ?>
		<?php else : ?>
			<?php do_action( 'woocommerce_no_products_found' ); ?>
		<?php endif; ?>
	</div>
</div>
<?php
do_action( 'woocommerce_after_main_content' );
get_footer( 'shop' );
