<?php
/**
 * Post loop.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;
?>
<main id="primary" class="te-main">
	<div class="te-container">
		<header class="te-pagehead te-pagehead--flush">
			<?php if ( is_search() ) : ?>
				<p class="te-kicker"><?php esc_html_e( 'Search', 'te-core' ); ?></p>
				<h1>
					<?php
					printf(
						/* translators: %s: search query */
						esc_html__( 'Results for “%s”', 'te-core' ),
						esc_html( get_search_query() )
					);
					?>
				</h1>
			<?php elseif ( is_home() && ! is_front_page() ) : ?>
				<h1><?php echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ); ?></h1>
			<?php elseif ( ! is_front_page() ) : ?>
				<?php the_archive_title( '<h1>', '</h1>' ); ?>
				<?php the_archive_description( '<div class="te-lead">', '</div>' ); ?>
			<?php endif; ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="te-postgrid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'te-post' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="te-post__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
								<?php the_post_thumbnail( 'medium_large' ); ?>
							</a>
						<?php endif; ?>
						<p class="te-card__cat"><?php echo esc_html( get_the_date() ); ?></p>
						<h2 class="te-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
					</article>
					<?php
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => __( 'Previous', 'te-core' ),
					'next_text' => __( 'Next', 'te-core' ),
				)
			);
			?>
		<?php else : ?>
			<div class="te-empty">
				<h2><?php esc_html_e( 'Nothing matched.', 'te-core' ); ?></h2>
				<?php get_search_form(); ?>
				<?php te_core_suggest_products(); ?>
			</div>
		<?php endif; ?>
	</div>
</main>
