<?php
/**
 * Merchant-written quotes. Not product reviews.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args  = $args ?? array();
$items = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$quote = trim( (string) te_core_field( $args, 'quote_' . $i ) );
	if ( '' === $quote ) {
		continue;
	}
	$items[] = array(
		'quote' => $quote,
		'name'  => trim( (string) te_core_field( $args, 'name_' . $i ) ),
	);
}
if ( ! $items ) {
	return;
}
te_core_section_open( $args, 'te-quotes' );
if ( te_core_field( $args, 'title' ) ) {
	echo '<h2 class="te-section__title">' . esc_html( te_core_field( $args, 'title' ) ) . '</h2>';
}
echo '<ul class="te-quotes__list">';
foreach ( $items as $item ) {
	echo '<li><blockquote><p>' . esc_html( $item['quote'] ) . '</p>';
	if ( '' !== $item['name'] ) {
		echo '<footer>' . esc_html( $item['name'] ) . '</footer>';
	}
	echo '</blockquote></li>';
}
echo '</ul>';
te_core_section_close();
