<?php
/**
 * A note emailed to the shop. Nothing is stored as a list.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$args = $args ?? array();
te_core_section_open( $args, 'te-shopnote' );
?>
<div class="te-narrow">
	<?php if ( te_core_field( $args, 'kicker' ) ) : ?>
		<p class="te-kicker"><?php echo esc_html( te_core_field( $args, 'kicker' ) ); ?></p>
	<?php endif; ?>
	<?php if ( te_core_field( $args, 'title' ) ) : ?>
		<h2 class="te-section__title"><?php echo esc_html( te_core_field( $args, 'title' ) ); ?></h2>
	<?php endif; ?>
	<?php if ( te_core_field( $args, 'text' ) ) : ?>
		<p><?php echo esc_html( te_core_field( $args, 'text' ) ); ?></p>
	<?php endif; ?>
	<?php te_core_note_form( (string) te_core_field( $args, 'button' ) ); ?>
</div>
<?php
te_core_section_close();
