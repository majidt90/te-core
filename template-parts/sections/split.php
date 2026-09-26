<?php
/**
 * Split promo.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args  = $args ?? array();
$image = te_core_media( (int) te_core_field( $args, 'image' ), 'large' );
$class = te_core_field( $args, 'image_end' ) ? 'is-end' : '';
$url   = (string) te_core_field( $args, 'cta_url' );
te_core_section_open( $args, 'te-split ' . $class );
echo '<div class="te-split__grid">';
echo '<div class="te-split__copy">';
if ( te_core_field( $args, 'kicker' ) ) {
	echo '<p class="te-kicker">' . esc_html( te_core_field( $args, 'kicker' ) ) . '</p>';
}
echo '<h2 class="te-section__title">' . esc_html( te_core_field( $args, 'title' ) ) . '</h2>';
if ( te_core_field( $args, 'text' ) ) {
	echo '<div class="te-lead">' . wp_kses_post( wpautop( te_core_field( $args, 'text' ) ) ) . '</div>';
}
if ( te_core_field( $args, 'cta_label' ) && $url ) {
	echo '<a class="te-btn te-btn--primary" href="' . esc_url( $url ) . '">' . esc_html( te_core_field( $args, 'cta_label' ) ) . '</a>';
}
echo '</div>';
echo '<div class="te-split__visual">';
echo $image ? $image : '<div class="te-split__fallback" aria-hidden="true"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '</div></div>';
te_core_section_close();
