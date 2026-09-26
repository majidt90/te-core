<?php
/**
 * Site header.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

$announce = te_core_on( 'announcement_enabled' ) ? trim( (string) te_core_get( 'announcement_text' ) ) : '';
$count    = te_core_cart_count();
?>
<?php if ( '' !== $announce ) : ?>
	<div class="te-announce">
		<div class="te-container te-announce__inner">
			<?php if ( te_core_get( 'announcement_url' ) ) : ?>
				<a href="<?php echo esc_url( te_core_get( 'announcement_url' ) ); ?>"><?php echo esc_html( $announce ); ?></a>
			<?php else : ?>
				<p><?php echo esc_html( $announce ); ?></p>
			<?php endif; ?>
			<?php if ( te_core_on( 'announcement_dismiss' ) ) : ?>
				<button type="button" class="te-iconbtn te-announce__close" data-te-dismiss="announce">
					<?php te_core_icon( 'close' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss announcement', 'te-core' ); ?></span>
				</button>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<header class="te-header" data-te-header>
	<div class="te-container te-header__bar">
		<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<a class="te-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="te-logo__mark" aria-hidden="true">TE</span>
				<span class="te-logo__name"><?php bloginfo( 'name' ); ?></span>
			</a>
		<?php endif; ?>

		<?php if ( te_core_on( 'show_search' ) ) : ?>
			<form class="te-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="te-q"><?php esc_html_e( 'Search', 'te-core' ); ?></label>
				<input
					id="te-q"
					class="te-search__input"
					type="search"
					name="s"
					value="<?php echo esc_attr( get_search_query() ); ?>"
					placeholder="<?php echo esc_attr( te_core_search_placeholder() ); ?>"
					autocomplete="off"
					role="combobox"
					aria-expanded="false"
					aria-controls="te-suggest"
					aria-autocomplete="list"
				>
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
				<button class="te-search__submit" type="submit">
					<?php te_core_icon( 'search' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'te-core' ); ?></span>
				</button>
				<div id="te-suggest" class="te-suggest" role="listbox" hidden></div>
			</form>
		<?php endif; ?>

		<div class="te-tools">
			<?php if ( te_core_on( 'show_account' ) ) : ?>
				<a class="te-iconbtn" href="<?php echo esc_url( te_core_account_url() ); ?>">
					<?php te_core_icon( 'user' ); ?>
					<span class="screen-reader-text">
						<?php echo is_user_logged_in() ? esc_html__( 'Account', 'te-core' ) : esc_html__( 'Sign in', 'te-core' ); ?>
					</span>
				</a>
			<?php endif; ?>
			<?php if ( te_core_on( 'show_wishlist' ) ) : ?>
				<button type="button" class="te-iconbtn" data-te-dialog="te-wishlist" aria-controls="te-wishlist" aria-expanded="false">
					<?php te_core_icon( 'heart' ); ?>
					<span class="te-count" data-te-wish-count hidden>0</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Saved', 'te-core' ); ?></span>
				</button>
			<?php endif; ?>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<button type="button" class="te-iconbtn" data-te-dialog="te-cart" aria-controls="te-cart" aria-expanded="false">
					<?php te_core_icon( 'bag' ); ?>
					<span class="te-count" data-te-cart-count <?php echo $count ? '' : 'hidden'; ?>><?php echo esc_html( te_core_digits( (string) $count ) ); ?></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Cart', 'te-core' ); ?></span>
				</button>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( te_core_on( 'show_departments' ) ) : ?>
		<div class="te-dept">
			<div class="te-container te-dept__row">
				<?php if ( te_core_on( 'show_category_panel' ) && class_exists( 'WooCommerce' ) ) : ?>
					<button type="button" class="te-dept__btn" data-te-dialog="te-categories" aria-controls="te-categories" aria-expanded="false">
						<?php te_core_icon( 'grid' ); ?>
						<span><?php esc_html_e( 'Departments', 'te-core' ); ?></span>
					</button>
				<?php endif; ?>
				<nav class="te-chips" aria-label="<?php esc_attr_e( 'Departments', 'te-core' ); ?>">
					<?php
					if ( 'menu' === te_core_get( 'department_source' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'header',
								'container'      => false,
								'menu_class'     => 'te-nav',
								'fallback_cb'    => false,
								'depth'          => 2,
							)
						);
					} else {
						te_core_department_chips();
					}
					?>
				</nav>
			</div>
		</div>
	<?php endif; ?>
</header>
<?php

/**
 * Department chips from product categories.
 *
 * @return void
 */
function te_core_department_chips() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'header',
				'container'      => false,
				'menu_class'     => 'te-nav',
				'fallback_cb'    => false,
				'depth'          => 1,
			)
		);
		return;
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => 20,
			'orderby'    => 'menu_order',
		)
	);
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}
	echo '<ul class="te-nav">';
	foreach ( $terms as $term ) {
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$current = is_tax( 'product_cat', $term->term_id ) ? ' aria-current="page"' : '';
		echo '<li><a href="' . esc_url( $link ) . '"' . $current . '>' . esc_html( $term->name ) . '</a></li>';
	}
	echo '</ul>';
}
