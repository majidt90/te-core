<?php
/**
 * Latest posts.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args  = $args ?? array();
$count = max( 2, min( 6, (int) te_core_field( $args, 'count' ) ) );
$query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( ! $query->have_posts() ) {
	return;
}
te_core_section_open( $args, 'te-journal' );
te_core_section_head(
	(string) te_core_field( $args, 'kicker' ),
	(string) te_core_field( $args, 'title' ),
	get_permalink( get_option( 'page_for_posts' ) ) ?: '',
	get_option( 'page_for_posts' ) ? __( 'All notes', 'te-core' ) : ''
);
echo '<div class="te-postgrid">';
while ( $query->have_posts() ) {
	$query->the_post();
	echo '<article class="te-post">';
	if ( has_post_thumbnail() ) {
		echo '<a class="te-post__media" href="' . esc_url( get_permalink() ) . '" tabindex="-1" aria-hidden="true">';
		the_post_thumbnail( 'medium_large' );
		echo '</a>';
	}
	echo '<p class="te-card__cat">' . esc_html( get_the_date() ) . '</p>';
	echo '<h3 class="te-card__title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
	echo '</article>';
}
echo '</div>';
wp_reset_postdata();
te_core_section_close();
