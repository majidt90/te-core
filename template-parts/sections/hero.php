<?php
/**
 * Hero section.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args       = $args ?? array();
$align      = ( 'center' === te_core_field( $args, 'align' ) ) ? 'is-center' : '';
$cta        = te_core_smart_url( (string) te_core_field( $args, 'cta_url' ), 'shop' );
$secondary  = (string) te_core_field( $args, 'secondary_url' );
$sec_label  = (string) te_core_field( $args, 'secondary_label' );
if ( '' === $secondary && '' !== $sec_label ) {
	$secondary = te_core_source_url( 'sale' );
}
$image = te_core_media(
	(int) te_core_field( $args, 'image' ),
	'large',
	array(
		'loading'       => 'eager',
		'fetchpriority' => 'high',
		'class'         => 'te-hero__img',
	)
);
?>
<section class="te-section te-hero <?php echo esc_attr( trim( $align . ' ' . te_core_width_class( $args ) ) ); ?>" id="te-<?php echo esc_attr( $args['uid'] ?? 'hero' ); ?>">
	<div class="te-container te-hero__grid">
		<div class="te-hero__copy">
			<?php if ( te_core_field( $args, 'kicker' ) ) : ?>
				<p class="te-kicker"><?php echo esc_html( te_core_field( $args, 'kicker' ) ); ?></p>
			<?php endif; ?>
			<h1><?php echo esc_html( te_core_field( $args, 'title' ) ); ?></h1>
			<?php if ( te_core_field( $args, 'text' ) ) : ?>
				<p class="te-lead"><?php echo esc_html( te_core_field( $args, 'text' ) ); ?></p>
			<?php endif; ?>
			<div class="te-actions">
				<?php if ( te_core_field( $args, 'cta_label' ) ) : ?>
					<a class="te-btn te-btn--primary" href="<?php echo esc_url( $cta ); ?>"><?php echo esc_html( te_core_field( $args, 'cta_label' ) ); ?></a>
				<?php endif; ?>
				<?php if ( $sec_label ) : ?>
					<a class="te-btn te-btn--ghost" href="<?php echo esc_url( $secondary ); ?>"><?php echo esc_html( $sec_label ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="te-hero__visual">
			<?php if ( $image ) : ?>
				<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<?php te_core_hero_facts(); ?>
			<?php endif; ?>
		</div>
	</div>
</section>
