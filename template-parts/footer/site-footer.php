<?php
/**
 * Site footer.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="te-footer">
	<div class="te-container te-footer__grid">
		<div class="te-footer__brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="te-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<span class="te-logo__mark" aria-hidden="true">TE</span>
					<span class="te-logo__name"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
			<?php te_core_store_details(); ?>
			<?php if ( get_bloginfo( 'description' ) ) : ?>
				<p><?php bloginfo( 'description' ); ?></p>
			<?php endif; ?>
			<?php if ( te_core_get( 'payment_note' ) ) : ?>
				<p class="te-footer__note"><?php echo esc_html( te_core_get( 'payment_note' ) ); ?></p>
			<?php endif; ?>
			<?php te_core_social_links(); ?>
		</div>
		<?php
		$areas = array( 'footer-1', 'footer-2', 'footer-3' );
		$any   = false;
		if ( te_core_on( 'footer_widgets' ) ) {
			foreach ( $areas as $area ) {
				if ( is_active_sidebar( $area ) ) {
					$any = true;
					echo '<div class="te-footer__col">';
					dynamic_sidebar( $area );
					echo '</div>';
				}
			}
		}
		if ( ! $any ) {
			te_core_footer_fallback();
		}
		?>
	</div>
	<div class="te-container te-footer__legal">
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: year, 2: site name */
					__( '© %1$s %2$s', 'te-core' ),
					te_core_digits( gmdate( 'Y' ) ),
					get_bloginfo( 'name' )
				)
			);
			?>
			<?php if ( te_core_get( 'footer_note' ) ) : ?>
				<span><?php echo esc_html( te_core_get( 'footer_note' ) ); ?></span>
			<?php endif; ?>
		</p>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'legal',
				'container'      => 'nav',
				'container_class'=> 'te-legal',
				'menu_class'     => 'te-legal__list',
				'fallback_cb'    => false,
				'depth'          => 1,
			)
		);
		?>
	</div>
</footer>
<?php if ( te_core_on( 'mobile_dock' ) ) : ?>
	<nav class="te-dock" aria-label="<?php esc_attr_e( 'Mobile', 'te-core' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>
			<?php te_core_icon( 'home' ); ?>
			<span><?php esc_html_e( 'Home', 'te-core' ); ?></span>
		</a>
		<button type="button" data-te-focus="te-q">
			<?php te_core_icon( 'search' ); ?>
			<span><?php esc_html_e( 'Search', 'te-core' ); ?></span>
		</button>
		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<button type="button" data-te-dialog="te-cart">
				<?php te_core_icon( 'bag' ); ?>
				<span><?php esc_html_e( 'Cart', 'te-core' ); ?></span>
			</button>
		<?php endif; ?>
		<a href="<?php echo esc_url( te_core_account_url() ); ?>">
			<?php te_core_icon( 'user' ); ?>
			<span><?php esc_html_e( 'Account', 'te-core' ); ?></span>
		</a>
	</nav>
<?php endif; ?>
<?php

/**
 * Footer columns when no widgets are assigned.
 *
 * @return void
 */
function te_core_footer_fallback() {
	echo '<div class="te-footer__col">';
	echo '<h2 class="te-widget__title">' . esc_html__( 'Catalog', 'te-core' ) . '</h2>';
	echo '<ul>';
	if ( class_exists( 'WooCommerce' ) ) {
		echo '<li><a href="' . esc_url( te_core_shop_url() ) . '">' . esc_html__( 'All products', 'te-core' ) . '</a></li>';
		echo '<li><a href="' . esc_url( te_core_source_url( 'sale' ) ) . '">' . esc_html__( 'On sale', 'te-core' ) . '</a></li>';
		echo '<li><a href="' . esc_url( te_core_account_url() ) . '">' . esc_html__( 'Account', 'te-core' ) . '</a></li>';
	}
		echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'te-core' ) . '</a></li>';
		if ( te_core_on( 'order_lookup' ) && function_exists( 'wc_get_page_permalink' ) ) {
			$account = wc_get_page_permalink( 'myaccount' );
			if ( $account ) {
				echo '<li><a href="' . esc_url( $account . '#te-track' ) . '">' . esc_html__( 'Track an order', 'te-core' ) . '</a></li>';
			}
		}
	echo '</ul></div>';

	echo '<div class="te-footer__col">';
	echo '<h2 class="te-widget__title">' . esc_html__( 'Pages', 'te-core' ) . '</h2>';
	wp_nav_menu(
		array(
			'theme_location' => 'footer',
			'container'      => false,
			'fallback_cb'    => 'te_core_footer_pages',
			'depth'          => 1,
		)
	);
	echo '</div>';
}

/**
 * Page list fallback for the footer menu.
 *
 * @return void
 */
function te_core_footer_pages() {
	$exclude = array( (int) get_option( 'page_on_front' ) );
	if ( function_exists( 'wc_get_page_id' ) ) {
		foreach ( array( 'shop', 'cart', 'checkout', 'myaccount' ) as $key ) {
			$exclude[] = (int) wc_get_page_id( $key );
		}
	}
	$exclude = array_filter( array_unique( $exclude ) );
	wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 1,
			'number'   => 6,
			'exclude'  => implode( ',', $exclude ),
		)
	);
}
