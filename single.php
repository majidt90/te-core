<?php
/**
 * Single post.
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
					<p class="te-kicker"><?php echo esc_html( get_the_date() ); ?></p>
					<h1><?php the_title(); ?></h1>
				</div>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="te-container te-single-media">
					<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
				</div>
			<?php endif; ?>
			<div class="te-content te-container te-narrow">
				<?php the_content(); ?>
				<?php the_tags( '<p class="te-tags">', ' ', '</p>' ); ?>
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
