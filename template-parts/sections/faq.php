<?php
/**
 * Questions. Empty pairs are skipped.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args  = $args ?? array();
$items = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$q = trim( (string) te_core_field( $args, 'q' . $i ) );
	$a = trim( (string) te_core_field( $args, 'a' . $i ) );
	if ( '' === $q || '' === $a ) {
		continue;
	}
	$items[] = array( $q, $a );
}
if ( ! $items ) {
	return;
}
te_core_section_open( $args, 'te-faq' );
te_core_section_head( (string) te_core_field( $args, 'kicker' ), (string) te_core_field( $args, 'title' ) );
echo '<div class="te-faq__list">';
foreach ( $items as $item ) {
	echo '<details><summary>' . esc_html( $item[0] ) . '</summary><p>' . esc_html( $item[1] ) . '</p></details>';
}
echo '</div>';
te_core_section_close();
