<?php
/**
 * Comments.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="te-comments te-container te-narrow">
	<?php if ( have_comments() ) : ?>
		<h2>
			<?php
			printf(
				esc_html(
					sprintf(
						/* translators: %s: comment count */
						_n( '%s comment', '%s comments', get_comments_number(), 'te-core' ),
						te_core_digits( number_format_i18n( get_comments_number() ) )
					)
				)
			);
			?>
		</h2>
		<ol class="te-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size'=> 40,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>
	<?php comment_form(); ?>
</section>
