<?php
/**
 * Title: Quiet intro
 * Slug: te-core/quiet-intro
 * Categories: te-core, text
 * Keywords: about, intro
 * Description: A narrow introduction for a page.
 *
 * @package TE_Core
 */

?>
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"textColor":"accent","fontSize":"small"} -->
	<p class="has-accent-color has-text-color has-small-font-size"><?php echo esc_html__( 'A note', 'te-core' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html__( 'Chosen, not dumped.', 'te-core' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p><?php echo esc_html__( 'A mixed catalog only works when finding is faster than scrolling. Write the promise you can keep.', 'te-core' ); ?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
