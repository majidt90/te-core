<?php
/**
 * Site front. Sections come from the registry; a static page’s content follows.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="te-main te-front">
	<?php if ( ! te_core_front_has_hero() ) : ?>
		<h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>
	<?php endif; ?>
	<?php te_core_render_sections(); ?>
	<?php if ( is_home() ) : ?>
		<?php get_template_part( 'template-parts/content/loop' ); ?>
	<?php elseif ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			if ( get_the_content() ) :
				?>
				<article <?php post_class( 'te-container te-content te-front-page' ); ?>>
					<?php the_content(); ?>
				</article>
				<?php
			endif;
		endwhile;
		?>
	<?php endif; ?>
</main>
<?php
get_footer();
