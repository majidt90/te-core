<?php
/**
 * Reassurance row.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args  = $args ?? array();
$items = array();
for ( $i = 1; $i <= 4; $i++ ) {
	if ( empty( $args[ "item_{$i}_enabled" ] ) && array_key_exists( "item_{$i}_enabled", $args ) && ! $args[ "item_{$i}_enabled" ] ) {
		continue;
	}
	if ( isset( $args[ "item_{$i}_enabled" ] ) && ! $args[ "item_{$i}_enabled" ] ) {
		continue;
	}
	$title = (string) te_core_field( $args, "item_{$i}_title" );
	if ( '' === $title ) {
		continue;
	}
	$items[] = array(
		'icon'  => (string) te_core_field( $args, "item_{$i}_icon" ),
		'title' => $title,
		'text'  => (string) te_core_field( $args, "item_{$i}_text" ),
	);
}
if ( ! $items ) {
	return;
}
te_core_section_open( $args, 'te-trust' );
if ( te_core_field( $args, 'title' ) ) {
	echo '<h2 class="te-section__title">' . esc_html( te_core_field( $args, 'title' ) ) . '</h2>';
}
echo '<ul class="te-trust__list">';
foreach ( $items as $item ) {
	echo '<li>';
	te_core_icon( $item['icon'] ? $item['icon'] : 'check' );
	echo '<div><strong>' . esc_html( $item['title'] ) . '</strong>';
	if ( $item['text'] ) {
		echo '<p>' . esc_html( $item['text'] ) . '</p>';
	}
	echo '</div></li>';
}
echo '</ul>';
te_core_section_close();
