<?php
/**
 * Name row.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args  = $args ?? array();
$lines = preg_split( '/\r\n|\r|\n/', (string) te_core_field( $args, 'names' ) );
$names = array();
if ( is_array( $lines ) ) {
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$names[] = $line;
		}
	}
}
if ( ! $names ) {
	return;
}
te_core_section_open( $args, 'te-brands' );
if ( te_core_field( $args, 'title' ) ) {
	echo '<h2 class="te-section__title">' . esc_html( te_core_field( $args, 'title' ) ) . '</h2>';
}
echo '<ul>';
foreach ( $names as $name ) {
	echo '<li>' . esc_html( $name ) . '</li>';
}
echo '</ul>';
te_core_section_close();
