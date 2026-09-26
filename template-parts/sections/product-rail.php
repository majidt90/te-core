<?php
/**
 * Product rail.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args   = $args ?? array();
$query  = te_core_query_products(
	(string) te_core_field( $args, 'source' ),
	(int) te_core_field( $args, 'count' ),
	(int) te_core_field( $args, 'category' )
);
if ( ! $query['products'] ) {
	return;
}
$title = (string) te_core_field( $args, 'title' );
if ( '' === $title ) {
	$titles = array(
		'featured'    => __( 'Selected for you', 'te-core' ),
		'sale'        => __( 'Today’s deals', 'te-core' ),
		'newest'      => __( 'New in the catalog', 'te-core' ),
		'bestselling' => __( 'Best selling', 'te-core' ),
		'top_rated'   => __( 'Top rated', 'te-core' ),
		'category'    => __( 'From the department', 'te-core' ),
	);
	$title = $titles[ $query['source'] ] ?? __( 'Products', 'te-core' );
}
$link = te_core_source_url( $query['source'], (int) te_core_field( $args, 'category' ) );
te_core_section_open( $args, 'te-rail' );
te_core_section_head(
	(string) te_core_field( $args, 'kicker' ),
	$title,
	$link,
	(string) te_core_field( $args, 'link_label' )
);
te_core_render_products( $query['products'] );
te_core_section_close();
