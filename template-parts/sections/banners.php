<?php
/**
 * Promo tiles.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args  = $args ?? array();
$tiles = array();
for ( $i = 1; $i <= 3; $i++ ) {
	if ( isset( $args[ "b{$i}_enabled" ] ) && ! $args[ "b{$i}_enabled" ] ) {
		continue;
	}
	$title = (string) te_core_field( $args, "b{$i}_title" );
	if ( '' === $title ) {
		continue;
	}
	$tiles[] = array(
		'title' => $title,
		'text'  => (string) te_core_field( $args, "b{$i}_text" ),
		'url'   => (string) te_core_field( $args, "b{$i}_url" ),
		'image' => (int) te_core_field( $args, "b{$i}_image" ),
	);
}
if ( ! $tiles ) {
	return;
}
te_core_section_open( $args, 'te-banners' );
if ( te_core_field( $args, 'title' ) ) {
	echo '<h2 class="te-section__title">' . esc_html( te_core_field( $args, 'title' ) ) . '</h2>';
}
echo '<ul class="te-bannergrid">';
foreach ( $tiles as $index => $tile ) {
	$url   = $tile['url'] ? $tile['url'] : te_core_shop_url();
	$image = te_core_media( $tile['image'], 'medium_large' );
	echo '<li><a class="te-banner te-banner--' . esc_attr( (string) ( $index + 1 ) ) . '" href="' . esc_url( $url ) . '">';
	if ( $image ) {
		echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '<span><strong>' . esc_html( $tile['title'] ) . '</strong>';
	if ( $tile['text'] ) {
		echo '<em>' . esc_html( $tile['text'] ) . '</em>';
	}
	echo '</span></a></li>';
}
echo '</ul>';
te_core_section_close();
