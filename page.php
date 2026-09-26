<?php
/**
 * Page.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="te-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class(); ?>>
			<header class="te-pagehead">
				<div class="te-container te-narrow">
					<h1><?php the_title(); ?></h1>
				</div>
			</header>
			<div class="te-content te-container">
				<?php the_content(); ?>
				<?php if ( function_exists( 'te_core_saved_page' ) ) { te_core_saved_page(); } ?>
				<?php
				wp_link_pages(
					array(
						'before' => '<nav class="te-pages">' . esc_html__( 'Pages:', 'te-core' ),
						'after'  => '</nav>',
					)
				);
				?>
			</div>
		</article>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
