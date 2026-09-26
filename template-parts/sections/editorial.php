<?php
/**
 * Editorial block.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args = $args ?? array();
if ( '' === (string) te_core_field( $args, 'title' ) && '' === (string) te_core_field( $args, 'text' ) ) {
	return;
}
$url = (string) te_core_field( $args, 'cta_url' );
?>
<section class="te-section te-editorial" id="te-<?php echo esc_attr( $args['uid'] ?? 'note' ); ?>">
	<div class="te-container te-narrow">
		<?php if ( te_core_field( $args, 'kicker' ) ) : ?>
			<p class="te-kicker"><?php echo esc_html( te_core_field( $args, 'kicker' ) ); ?></p>
		<?php endif; ?>
		<?php if ( te_core_field( $args, 'title' ) ) : ?>
			<h2 class="te-section__title"><?php echo esc_html( te_core_field( $args, 'title' ) ); ?></h2>
		<?php endif; ?>
		<?php if ( te_core_field( $args, 'text' ) ) : ?>
			<div class="te-lead"><?php echo wp_kses_post( wpautop( te_core_field( $args, 'text' ) ) ); ?></div>
		<?php endif; ?>
		<?php if ( te_core_field( $args, 'cta_label' ) && $url ) : ?>
			<a class="te-link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( te_core_field( $args, 'cta_label' ) ); ?></a>
		<?php endif; ?>
	</div>
</section>
