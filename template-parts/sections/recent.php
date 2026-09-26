<?php
/**
 * Recently viewed. Filled by the browser after a product is opened.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args = $args ?? array();
?>
<section class="te-section te-recent <?php echo esc_attr( te_core_width_class( $args ) ); ?>" id="te-<?php echo esc_attr( $args['uid'] ?? 'recent' ); ?>" data-te-recent data-count="<?php echo esc_attr( (string) te_core_field( $args, 'count' ) ); ?>" hidden>
	<div class="te-container">
		<?php
		te_core_section_head(
			(string) te_core_field( $args, 'kicker' ),
			(string) te_core_field( $args, 'title' )
		);
		?>
		<div data-te-recent-body></div>
	</div>
</section>
