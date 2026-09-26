<?php
/**
 * Department grid.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args  = $args ?? array();
$count = (int) te_core_field( $args, 'count' );
if ( ! taxonomy_exists( 'product_cat' ) ) {
	return;
}
$terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
		'number'     => max( 2, min( 12, $count ) ),
		'orderby'    => 'menu_order',
	)
);
if ( ! $terms || is_wp_error( $terms ) ) {
	return;
}
te_core_section_open( $args, 'te-cats' );
te_core_section_head( (string) te_core_field( $args, 'kicker' ), (string) te_core_field( $args, 'title' ) );
echo '<ul class="te-catgrid">';
foreach ( $terms as $term ) {
	$link = get_term_link( $term );
	if ( is_wp_error( $link ) ) {
		continue;
	}
	$thumb = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
	echo '<li><a class="te-cat" href="' . esc_url( $link ) . '">';
	echo '<span class="te-cat__media">';
	if ( $thumb ) {
		echo wp_get_attachment_image( $thumb, 'te-card', false, array( 'alt' => '' ) );
	} else {
		echo '<span class="te-mono" aria-hidden="true">' . esc_html( te_core_monogram( $term->name ) ) . '</span>';
	}
	echo '</span>';
	echo '<span class="te-cat__name">' . esc_html( $term->name ) . '</span>';
	echo '<span class="te-cat__count">' . esc_html( te_core_digits( sprintf( _n( '%s item', '%s items', $term->count, 'te-core' ), number_format_i18n( $term->count ) ) ) ) . '</span>';
	echo '</a></li>';
}
echo '</ul>';
te_core_section_close();
