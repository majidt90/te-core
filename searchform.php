<?php
/**
 * Search form.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;
?>
<form class="te-search te-search--page" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="te-search-page"><?php esc_html_e( 'Search', 'te-core' ); ?></label>
	<input id="te-search-page" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( te_core_search_placeholder() ); ?>">
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<button class="te-btn te-btn--primary" type="submit"><?php esc_html_e( 'Search', 'te-core' ); ?></button>
</form>
