<?php
/**
 * 404.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="te-main">
	<div class="te-container te-empty">
		<p class="te-kicker">404</p>
		<h1><?php esc_html_e( 'This page is not in the catalog.', 'te-core' ); ?></h1>
		<p><?php esc_html_e( 'Search, or go back to the front.', 'te-core' ); ?></p>
		<?php get_search_form(); ?>
		<p><a class="te-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'te-core' ); ?></a></p>
	</div>
</main>
<?php
get_footer();
