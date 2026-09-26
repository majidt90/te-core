<?php
/**
 * Dialogs: cart, wishlist, departments, quick view.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<dialog class="te-dialog te-dialog--drawer" id="te-cart" aria-labelledby="te-cart-title">
		<div class="te-dialog__bar">
			<h2 id="te-cart-title"><?php esc_html_e( 'Cart', 'te-core' ); ?></h2>
			<button type="button" class="te-iconbtn" data-te-close>
				<?php te_core_icon( 'close' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'te-core' ); ?></span>
			</button>
		</div>
		<div class="te-dialog__body" data-te-cart-body>
			<?php get_template_part( 'template-parts/cart/mini-cart' ); ?>
		</div>
	</dialog>
<?php endif; ?>

<?php if ( te_core_on( 'show_compare' ) && class_exists( 'WooCommerce' ) ) : ?>
	<dialog class="te-dialog te-dialog--compare" id="te-compare" aria-labelledby="te-compare-title">
		<div class="te-dialog__bar">
			<h2 id="te-compare-title"><?php esc_html_e( 'Compare', 'te-core' ); ?></h2>
			<button type="button" class="te-iconbtn" data-te-close>
				<?php te_core_icon( 'close' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'te-core' ); ?></span>
			</button>
		</div>
		<div class="te-dialog__body" data-te-compare-body>
			<p class="te-empty-inline"><?php esc_html_e( 'Nothing to compare yet.', 'te-core' ); ?></p>
		</div>
	</dialog>
<?php endif; ?>

<?php if ( te_core_on( 'show_wishlist' ) ) : ?>
	<dialog class="te-dialog te-dialog--drawer" id="te-wishlist" aria-labelledby="te-wish-title">
		<div class="te-dialog__bar">
			<h2 id="te-wish-title"><?php esc_html_e( 'Saved', 'te-core' ); ?></h2>
			<button type="button" class="te-iconbtn" data-te-close>
				<?php te_core_icon( 'close' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'te-core' ); ?></span>
			</button>
		</div>
		<div class="te-dialog__body" data-te-wish-body>
			<p class="te-empty-inline"><?php esc_html_e( 'Nothing saved yet.', 'te-core' ); ?></p>
		</div>
	</dialog>
<?php endif; ?>

<?php if ( te_core_on( 'quick_view' ) && class_exists( 'WooCommerce' ) ) : ?>
	<dialog class="te-dialog te-dialog--quick" id="te-quick" aria-labelledby="te-quick-title">
		<div class="te-dialog__bar">
			<h2 id="te-quick-title"><?php esc_html_e( 'Quick view', 'te-core' ); ?></h2>
			<button type="button" class="te-iconbtn" data-te-close>
				<?php te_core_icon( 'close' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'te-core' ); ?></span>
			</button>
		</div>
		<div class="te-dialog__body" data-te-quick-body></div>
	</dialog>
<?php endif; ?>

<?php if ( te_core_on( 'show_category_panel' ) && class_exists( 'WooCommerce' ) ) : ?>
	<dialog class="te-dialog te-dialog--panel" id="te-categories" aria-labelledby="te-cat-title">
		<div class="te-dialog__bar">
			<h2 id="te-cat-title"><?php esc_html_e( 'Departments', 'te-core' ); ?></h2>
			<button type="button" class="te-iconbtn" data-te-close>
				<?php te_core_icon( 'close' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'te-core' ); ?></span>
			</button>
		</div>
		<div class="te-dialog__body">
			<?php te_core_category_panel(); ?>
		</div>
	</dialog>
<?php endif; ?>
<?php

/**
 * Department panel: parents and a short list of children.
 *
 * @return void
 */
function te_core_category_panel() {
	$parents = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'orderby'    => 'menu_order',
			'exclude'    => te_core_hidden_term_ids(),
		)
	);
	if ( ! $parents || is_wp_error( $parents ) ) {
		echo '<p>' . esc_html__( 'No departments yet.', 'te-core' ) . '</p>';
		return;
	}
	echo '<ul class="te-panel">';
	foreach ( $parents as $parent ) {
		$link = get_term_link( $parent );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$thumb = absint( get_term_meta( $parent->term_id, 'thumbnail_id', true ) );
		echo '<li class="te-panel__item">';
		echo '<a class="te-panel__parent" href="' . esc_url( $link ) . '">';
		if ( $thumb ) {
			echo wp_get_attachment_image( $thumb, 'thumbnail', false, array( 'alt' => '' ) );
		} else {
			echo '<span class="te-mono" aria-hidden="true">' . esc_html( te_core_monogram( $parent->name ) ) . '</span>';
		}
		echo '<span>' . esc_html( $parent->name ) . '</span></a>';
		$children = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => $parent->term_id,
				'number'     => 6,
			)
		);
		if ( $children && ! is_wp_error( $children ) ) {
			echo '<ul>';
			foreach ( $children as $child ) {
				$child_link = get_term_link( $child );
				if ( is_wp_error( $child_link ) ) {
					continue;
				}
				echo '<li><a href="' . esc_url( $child_link ) . '">' . esc_html( $child->name ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</li>';
	}
	echo '</ul>';
}
