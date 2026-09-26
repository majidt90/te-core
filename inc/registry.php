<?php
/**
 * The core registry.
 *
 * Settings and homepage sections are declared once. The settings screen,
 * the sanitizer, and the storefront all read this file. Adding a control
 * here is what makes it real — there is no second list to keep in sync.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme setting tabs, in screen order.
 *
 * @return array<string,string>
 */
function te_core_settings_tabs() {
	return array(
		'general'     => __( 'General', 'te-core' ),
		'header'      => __( 'Header', 'te-core' ),
		'homepage'    => __( 'Homepage', 'te-core' ),
		'shop'        => __( 'Shop', 'te-core' ),
		'performance' => __( 'Performance', 'te-core' ),
		'footer'      => __( 'Footer', 'te-core' ),
	);
}

/**
 * Every storefront setting.
 *
 * @return array<string,array<string,mixed>>
 */
function te_core_settings_schema() {
	$schema = array(
		'direction'            => array(
			'tab'         => 'general',
			'type'        => 'select',
			'label'       => __( 'Text direction', 'te-core' ),
			'description' => __( 'Automatic follows the site language. Force a direction only when the locale and the layout must differ.', 'te-core' ),
			'options'     => array(
				'auto' => __( 'Automatic (site language)', 'te-core' ),
				'rtl'  => __( 'Right to left', 'te-core' ),
				'ltr'  => __( 'Left to right', 'te-core' ),
			),
			'default'     => 'auto',
		),
		'persian_digits'       => array(
			'tab'         => 'general',
			'type'        => 'select',
			'label'       => __( 'Persian digits', 'te-core' ),
			'description' => __( 'Applied to prices, counts, and storefront text. Checkout inputs stay easy to type.', 'te-core' ),
			'options'     => array(
				'auto' => __( 'Automatic (Persian locales)', 'te-core' ),
				'on'   => __( 'On', 'te-core' ),
				'off'  => __( 'Off', 'te-core' ),
			),
			'default'     => 'auto',
		),
		'accent_color'         => array(
			'tab'         => 'general',
			'type'        => 'color',
			'label'       => __( 'Accent', 'te-core' ),
			'description' => __( 'Used for price signals, the cart count, and focus. Buttons stay ink so the accent stays rare.', 'te-core' ),
			'default'     => '#c2410c',
		),
		'content_width'        => array(
			'tab'         => 'general',
			'type'        => 'number',
			'label'       => __( 'Content width', 'te-core' ),
			'description' => __( 'Pixels. The catalog, header, and footer share this measure.', 'te-core' ),
			'min'         => 960,
			'max'         => 1600,
			'default'     => 1240,
		),
		'sticky_header'        => array(
			'tab'     => 'general',
			'type'    => 'checkbox',
			'label'   => __( 'Sticky header', 'te-core' ),
			'default' => 1,
		),
		'mobile_dock'          => array(
			'tab'         => 'general',
			'type'        => 'checkbox',
			'label'       => __( 'Mobile dock', 'te-core' ),
			'description' => __( 'Home, search, cart, and account within thumb reach. Hidden on larger screens.', 'te-core' ),
			'default'     => 1,
		),
		'announcement_enabled' => array(
			'tab'     => 'header',
			'type'    => 'checkbox',
			'label'   => __( 'Announcement bar', 'te-core' ),
			'default' => 0,
		),
		'announcement_text'    => array(
			'tab'     => 'header',
			'type'    => 'text',
			'label'   => __( 'Announcement', 'te-core' ),
			'max'     => 180,
			'default' => '',
		),
		'announcement_url'     => array(
			'tab'     => 'header',
			'type'    => 'url',
			'label'   => __( 'Announcement link', 'te-core' ),
			'default' => '',
		),
		'announcement_dismiss' => array(
			'tab'         => 'header',
			'type'        => 'checkbox',
			'label'       => __( 'Announcement can be dismissed', 'te-core' ),
			'description' => __( 'Dismissed for the browser session, so a later visit can still see it.', 'te-core' ),
			'default'     => 1,
		),
		'show_search'          => array(
			'tab'     => 'header',
			'type'    => 'checkbox',
			'label'   => __( 'Search', 'te-core' ),
			'default' => 1,
		),
		'predictive_search'    => array(
			'tab'         => 'header',
			'type'        => 'checkbox',
			'label'       => __( 'Predictive search', 'te-core' ),
			'description' => __( 'A short, debounced list. The form still submits without JavaScript.', 'te-core' ),
			'default'     => 1,
		),
		'search_placeholder'   => array(
			'tab'         => 'header',
			'type'        => 'text',
			'label'       => __( 'Search placeholder', 'te-core' ),
			'description' => __( 'Leave blank for the translated default.', 'te-core' ),
			'max'         => 80,
			'default'     => '',
		),
		'show_account'         => array(
			'tab'     => 'header',
			'type'    => 'checkbox',
			'label'   => __( 'Account link', 'te-core' ),
			'default' => 1,
		),
		'show_wishlist'        => array(
			'tab'         => 'header',
			'type'        => 'checkbox',
			'label'       => __( 'Wishlist', 'te-core' ),
			'description' => __( 'Stored in the browser. No extra database table.', 'te-core' ),
			'default'     => 1,
		),
		'show_departments'     => array(
			'tab'     => 'header',
			'type'    => 'checkbox',
			'label'   => __( 'Department row', 'te-core' ),
			'default' => 1,
		),
		'department_source'    => array(
			'tab'     => 'header',
			'type'    => 'select',
			'label'   => __( 'Department source', 'te-core' ),
			'options' => array(
				'categories' => __( 'Product categories', 'te-core' ),
				'menu'       => __( 'Header menu', 'te-core' ),
			),
			'default' => 'categories',
		),
		'show_category_panel'  => array(
			'tab'         => 'header',
			'type'        => 'checkbox',
			'label'       => __( 'Category panel', 'te-core' ),
			'description' => __( 'A precise stand-in for a mega menu: top-level departments and their children, opened on demand.', 'te-core' ),
			'default'     => 1,
		),
		'shop_columns'         => array(
			'tab'     => 'shop',
			'type'    => 'number',
			'label'   => __( 'Columns on desktop', 'te-core' ),
			'min'     => 2,
			'max'     => 5,
			'default' => 4,
		),
		'products_per_page'    => array(
			'tab'     => 'shop',
			'type'    => 'number',
			'label'   => __( 'Products per page', 'te-core' ),
			'min'     => 4,
			'max'     => 48,
			'default' => 12,
		),
		'image_ratio'          => array(
			'tab'     => 'shop',
			'type'    => 'select',
			'label'   => __( 'Image ratio', 'te-core' ),
			'options' => array(
				'1/1' => __( 'Square (1:1)', 'te-core' ),
				'4/5' => __( 'Portrait (4:5)', 'te-core' ),
				'3/4' => __( 'Tall (3:4)', 'te-core' ),
			),
			'default' => '1/1',
		),
		'image_fit'            => array(
			'tab'         => 'shop',
			'type'        => 'select',
			'label'       => __( 'Image fit', 'te-core' ),
			'description' => __( 'Contain keeps the whole product visible. Cover fills the frame.', 'te-core' ),
			'options'     => array(
				'contain' => __( 'Contain', 'te-core' ),
				'cover'   => __( 'Cover', 'te-core' ),
			),
			'default'     => 'contain',
		),
		'show_rating'          => array(
			'tab'     => 'shop',
			'type'    => 'checkbox',
			'label'   => __( 'Ratings on cards', 'te-core' ),
			'default' => 1,
		),
		'show_card_category'   => array(
			'tab'     => 'shop',
			'type'    => 'checkbox',
			'label'   => __( 'Category on cards', 'te-core' ),
			'default' => 1,
		),
		'sale_badge'           => array(
			'tab'     => 'shop',
			'type'    => 'select',
			'label'   => __( 'Sale badge', 'te-core' ),
			'options' => array(
				'percent' => __( 'Discount percent', 'te-core' ),
				'text'    => __( 'Text only', 'te-core' ),
			),
			'default' => 'percent',
		),
		'quick_view'           => array(
			'tab'     => 'shop',
			'type'    => 'checkbox',
			'label'   => __( 'Quick view', 'te-core' ),
			'default' => 1,
		),
		'ajax_cart'            => array(
			'tab'         => 'shop',
			'type'        => 'checkbox',
			'label'       => __( 'Add to cart without reload', 'te-core' ),
			'description' => __( 'Simple products only. Variable products open the product page so options stay accurate.', 'te-core' ),
			'default'     => 1,
		),
		'show_filters'         => array(
			'tab'     => 'shop',
			'type'    => 'checkbox',
			'label'   => __( 'Catalog filters', 'te-core' ),
			'default' => 1,
		),
		'catalog_width'        => array(
			'tab'         => 'shop',
			'type'        => 'select',
			'label'       => __( 'Catalog width', 'te-core' ),
			'description' => __( 'Full width runs the shop edge to edge. Wide keeps a little air on large screens.', 'te-core' ),
			'options'     => array(
				'contained' => __( 'Contained', 'te-core' ),
				'wide'      => __( 'Wide', 'te-core' ),
				'full'      => __( 'Full width', 'te-core' ),
			),
			'default'     => 'wide',
		),
		'low_stock'            => array(
			'tab'         => 'shop',
			'type'        => 'checkbox',
			'label'       => __( 'Low stock signal', 'te-core' ),
			'description' => __( 'Shown only when WooCommerce is tracking stock and the quantity is at or below the limit. No invented scarcity.', 'te-core' ),
			'default'     => 1,
		),
		'low_stock_qty'        => array(
			'tab'     => 'shop',
			'type'    => 'number',
			'label'   => __( 'Low stock at', 'te-core' ),
			'min'     => 1,
			'max'     => 20,
			'default' => 5,
		),
		'hover_image'          => array(
			'tab'         => 'shop',
			'type'        => 'checkbox',
			'label'       => __( 'Second image on hover', 'te-core' ),
			'description' => __( 'The next gallery image, lazy-loaded, and only for a fine pointer. Touch screens stay on the first image.', 'te-core' ),
			'default'     => 1,
		),
		'show_compare'         => array(
			'tab'         => 'shop',
			'type'        => 'checkbox',
			'label'       => __( 'Compare', 'te-core' ),
			'description' => __( 'Up to four products, stored in this browser. The table is rendered by the server so prices stay exact.', 'te-core' ),
			'default'     => 1,
		),
		'sticky_buybar'        => array(
			'tab'         => 'shop',
			'type'        => 'checkbox',
			'label'       => __( 'Sticky buy bar', 'te-core' ),
			'description' => __( 'A compact bar on small screens after the buy box scrolls away. Variable products scroll back to the options.', 'te-core' ),
			'default'     => 1,
		),
		'show_specs'           => array(
			'tab'         => 'shop',
			'type'        => 'checkbox',
			'label'       => __( 'Key specs', 'te-core' ),
			'description' => __( 'The first four visible attributes, in the buy box. Hidden when a product has none.', 'te-core' ),
			'default'     => 1,
		),
		'free_shipping_min'    => array(
			'tab'         => 'shop',
			'type'        => 'number',
			'label'       => __( 'Free-shipping threshold', 'te-core' ),
			'description' => __( 'Cart subtotal after discounts and before shipping, in store currency. 0 hides the meter. Set this only if that promise is true.', 'te-core' ),
			'min'         => 0,
			'max'         => 100000000,
			'default'     => 0,
		),
		'show_coupon'          => array(
			'tab'         => 'shop',
			'type'        => 'checkbox',
			'label'       => __( 'Coupon in the cart drawer', 'te-core' ),
			'description' => __( 'Applies through WooCommerce, so invalid codes fail honestly.', 'te-core' ),
			'default'     => 1,
		),
		'recent_search'        => array(
			'tab'         => 'header',
			'type'        => 'checkbox',
			'label'       => __( 'Recent searches', 'te-core' ),
			'description' => __( 'The last few queries on this device. No network request until a search actually runs.', 'te-core' ),
			'default'     => 1,
		),
		'sticky_buybox'        => array(
			'tab'     => 'shop',
			'type'    => 'checkbox',
			'label'   => __( 'Sticky buy box', 'te-core' ),
			'default' => 1,
		),
		'gallery_lightbox'     => array(
			'tab'     => 'shop',
			'type'    => 'checkbox',
			'label'   => __( 'Product image lightbox', 'te-core' ),
			'default' => 1,
		),
		'animations'           => array(
			'tab'         => 'performance',
			'type'        => 'checkbox',
			'label'       => __( 'Motion', 'te-core' ),
			'description' => __( 'Transform and opacity only. The visitor’s reduced-motion setting always wins.', 'te-core' ),
			'default'     => 1,
		),
		'preload_font'         => array(
			'tab'     => 'performance',
			'type'    => 'checkbox',
			'label'   => __( 'Preload the typeface', 'te-core' ),
			'default' => 1,
		),
		'content_visibility'   => array(
			'tab'         => 'performance',
			'type'        => 'checkbox',
			'label'       => __( 'Skip rendering of far sections', 'te-core' ),
			'description' => __( 'Uses content-visibility so below-the-fold sections do not block the first paint.', 'te-core' ),
			'default'     => 1,
		),
		'cart_fragments'       => array(
			'tab'         => 'performance',
			'type'        => 'checkbox',
			'label'       => __( 'WooCommerce cart fragments', 'te-core' ),
			'description' => __( 'Off by default. Fragments add a request to every page and hurt interaction speed. Turn on only if another plugin requires them.', 'te-core' ),
			'default'     => 0,
		),
		'disable_emoji'        => array(
			'tab'     => 'performance',
			'type'    => 'checkbox',
			'label'   => __( 'Remove emoji scripts', 'te-core' ),
			'default' => 1,
		),
		'footer_note'          => array(
			'tab'         => 'footer',
			'type'        => 'text',
			'label'       => __( 'Footer note', 'te-core' ),
			'description' => __( 'Shown next to the copyright. Leave blank to hide.', 'te-core' ),
			'max'         => 180,
			'default'     => '',
		),
		'payment_note'         => array(
			'tab'         => 'footer',
			'type'        => 'text',
			'label'       => __( 'Payment note', 'te-core' ),
			'description' => __( 'A plain sentence, not card logos. Example: Secure payment via your store gateway.', 'te-core' ),
			'max'         => 180,
			'default'     => '',
		),
	);

	/**
	 * Filter the settings schema.
	 *
	 * @param array $schema Setting definitions.
	 */
	return apply_filters( 'te_core_settings_schema', $schema );
}

/**
 * Homepage section types.
 *
 * @return array<string,array<string,mixed>>
 */
function te_core_section_types() {
	$trust_icons = array(
		'truck'   => __( 'Delivery', 'te-core' ),
		'returns' => __( 'Returns', 'te-core' ),
		'shield'  => __( 'Security', 'te-core' ),
		'headset' => __( 'Support', 'te-core' ),
		'star'    => __( 'Quality', 'te-core' ),
		'check'   => __( 'Check', 'te-core' ),
	);

	$types = array(
		'hero'        => array(
			'label'       => __( 'Hero', 'te-core' ),
			'description' => __( 'One message, one action. No rotating slider.', 'te-core' ),
			'template'    => 'hero',
			'fields'      => array(
				'kicker'          => te_core_text_field( __( 'Eyebrow', 'te-core' ), __( 'The mixed market', 'te-core' ) ),
				'title'           => te_core_text_field( __( 'Title', 'te-core' ), __( 'A quieter way to find everything.', 'te-core' ), 140 ),
				'text'            => te_core_textarea_field( __( 'Text', 'te-core' ), __( 'One precise catalog for home, style, electronics, and the things you did not know you needed.', 'te-core' ) ),
				'cta_label'       => te_core_text_field( __( 'Primary button', 'te-core' ), __( 'Browse the catalog', 'te-core' ) ),
				'cta_url'         => te_core_url_field( __( 'Primary link', 'te-core' ) ),
				'secondary_label' => te_core_text_field( __( 'Secondary button', 'te-core' ), __( 'Today’s deals', 'te-core' ) ),
				'secondary_url'   => te_core_url_field( __( 'Secondary link', 'te-core' ) ),
				'image'           => te_core_image_field( __( 'Image', 'te-core' ) ),
				'align'           => array(
					'type'    => 'select',
					'label'   => __( 'Alignment', 'te-core' ),
					'options' => array(
						'start'  => __( 'Start', 'te-core' ),
						'center' => __( 'Center', 'te-core' ),
					),
					'default' => 'start',
				),
			),
		),
		'categories'  => array(
			'label'       => __( 'Departments', 'te-core' ),
			'description' => __( 'Top-level product categories, with a monogram if no image is set.', 'te-core' ),
			'template'    => 'categories',
			'fields'      => array(
				'kicker' => te_core_text_field( __( 'Eyebrow', 'te-core' ), __( 'Departments', 'te-core' ) ),
				'title'  => te_core_text_field( __( 'Title', 'te-core' ), __( 'Shop by department', 'te-core' ) ),
				'count'  => te_core_number_field( __( 'How many', 'te-core' ), 8, 2, 12 ),
			),
		),
		'product_rail' => array(
			'label'       => __( 'Product rail', 'te-core' ),
			'description' => __( 'A grid from one accurate source: featured, sale, newest, best selling, top rated, or a category.', 'te-core' ),
			'template'    => 'product-rail',
			'fields'      => array(
				'kicker'     => te_core_text_field( __( 'Eyebrow', 'te-core' ), '' ),
				'title'      => te_core_text_field( __( 'Title', 'te-core' ), '' ),
				'source'     => array(
					'type'    => 'select',
					'label'   => __( 'Source', 'te-core' ),
					'options' => array(
						'featured'    => __( 'Featured', 'te-core' ),
						'sale'        => __( 'On sale', 'te-core' ),
						'newest'      => __( 'Newest', 'te-core' ),
						'bestselling' => __( 'Best selling', 'te-core' ),
						'top_rated'   => __( 'Top rated', 'te-core' ),
						'category'    => __( 'One category', 'te-core' ),
					),
					'default' => 'featured',
				),
				'category'   => array(
					'type'    => 'category',
					'label'   => __( 'Category', 'te-core' ),
					'default' => 0,
				),
				'count'      => te_core_number_field( __( 'How many', 'te-core' ), 8, 2, 16 ),
				'link_label' => te_core_text_field( __( 'Link label', 'te-core' ), __( 'View all', 'te-core' ) ),
			),
		),
		'trust'       => array(
			'label'       => __( 'Reassurance', 'te-core' ),
			'description' => __( 'Four short promises. Write only what the store actually does.', 'te-core' ),
			'template'    => 'trust',
			'fields'      => te_core_trust_fields( $trust_icons ),
		),
		'split'       => array(
			'label'       => __( 'Split promo', 'te-core' ),
			'description' => __( 'Image and a short argument, side by side.', 'te-core' ),
			'template'    => 'split',
			'fields'      => array(
				'kicker'    => te_core_text_field( __( 'Eyebrow', 'te-core' ), __( 'A note', 'te-core' ) ),
				'title'     => te_core_text_field( __( 'Title', 'te-core' ), __( 'Chosen, not dumped.', 'te-core' ) ),
				'text'      => te_core_textarea_field( __( 'Text', 'te-core' ), __( 'A mixed store only works when finding is faster than scrolling.', 'te-core' ) ),
				'cta_label' => te_core_text_field( __( 'Button', 'te-core' ), __( 'Read the story', 'te-core' ) ),
				'cta_url'   => te_core_url_field( __( 'Link', 'te-core' ) ),
				'image'     => te_core_image_field( __( 'Image', 'te-core' ) ),
				'image_end' => array(
					'type'    => 'checkbox',
					'label'   => __( 'Place the image at the end', 'te-core' ),
					'default' => 0,
				),
			),
		),
		'banners'     => array(
			'label'       => __( 'Promo tiles', 'te-core' ),
			'description' => __( 'Up to three quiet tiles. Empty tiles are not rendered.', 'te-core' ),
			'template'    => 'banners',
			'fields'      => te_core_banner_fields(),
		),
		'editorial'   => array(
			'label'       => __( 'Editorial', 'te-core' ),
			'description' => __( 'A narrow text block. For long stories, use a page.', 'te-core' ),
			'template'    => 'editorial',
			'fields'      => array(
				'kicker'    => te_core_text_field( __( 'Eyebrow', 'te-core' ), __( 'From the shop', 'te-core' ) ),
				'title'     => te_core_text_field( __( 'Title', 'te-core' ), __( 'Why this catalog is mixed', 'te-core' ) ),
				'text'      => te_core_textarea_field( __( 'Text', 'te-core' ), __( 'Departments stay clear, prices stay exact, and every product page answers the same questions in the same place.', 'te-core' ) ),
				'cta_label' => te_core_text_field( __( 'Button', 'te-core' ), '' ),
				'cta_url'   => te_core_url_field( __( 'Link', 'te-core' ) ),
			),
		),
		'brands'      => array(
			'label'       => __( 'Names', 'te-core' ),
			'description' => __( 'A quiet row of names. One per line. No logo carousel.', 'te-core' ),
			'template'    => 'brands',
			'fields'      => array(
				'title' => te_core_text_field( __( 'Title', 'te-core' ), __( 'In the catalog', 'te-core' ) ),
				'names' => te_core_textarea_field( __( 'Names', 'te-core' ), '' ),
			),
		),
		'posts'       => array(
			'label'       => __( 'Journal', 'te-core' ),
			'description' => __( 'Latest posts. Hidden when the blog is empty.', 'te-core' ),
			'template'    => 'posts',
			'fields'      => array(
				'kicker' => te_core_text_field( __( 'Eyebrow', 'te-core' ), __( 'Journal', 'te-core' ) ),
				'title'  => te_core_text_field( __( 'Title', 'te-core' ), __( 'Notes from the shop', 'te-core' ) ),
				'count'  => te_core_number_field( __( 'How many', 'te-core' ), 3, 2, 6 ),
			),
		),
		'recent'      => array(
			'label'       => __( 'Recently viewed', 'te-core' ),
			'description' => __( 'Filled from this browser after a product is opened. Hidden until then.', 'te-core' ),
			'template'    => 'recent',
			'fields'      => array(
				'kicker' => te_core_text_field( __( 'Eyebrow', 'te-core' ), __( 'Continue', 'te-core' ) ),
				'title'  => te_core_text_field( __( 'Title', 'te-core' ), __( 'Recently viewed', 'te-core' ) ),
				'count'  => te_core_number_field( __( 'How many', 'te-core' ), 4, 2, 8 ),
			),
		),
	);

	$widths = array(
		'hero'       => 'full',
		'categories' => 'wide',
		'trust'      => 'full',
		'banners'    => 'full',
		'brands'     => 'full',
	);
	foreach ( $types as $id => $type ) {
		$types[ $id ]['fields']['width'] = array(
			'type'        => 'select',
			'label'       => __( 'Width', 'te-core' ),
			'description' => __( 'Full width paints the band edge to edge. Copy stays measured.', 'te-core' ),
			'options'     => array(
				'contained' => __( 'Contained', 'te-core' ),
				'wide'      => __( 'Wide', 'te-core' ),
				'full'      => __( 'Full width', 'te-core' ),
			),
			'default'     => $widths[ $id ] ?? 'contained',
		);
	}

	/**
	 * Filter section types. A custom type needs a template or a render callback.
	 *
	 * @param array $types Section types.
	 */
	return apply_filters( 'te_core_section_types', $types );
}

/**
 * Text field definition.
 *
 * @param string $label    Label.
 * @param string $fallback Translated fallback used when the stored value is empty.
 * @param int    $max      Maximum characters.
 * @return array<string,mixed>
 */
function te_core_text_field( $label, $fallback = '', $max = 120 ) {
	return array(
		'type'     => 'text',
		'label'    => $label,
		'default'  => '',
		'fallback' => $fallback,
		'max'      => $max,
	);
}

/**
 * Textarea field definition.
 *
 * @param string $label    Label.
 * @param string $fallback Fallback copy.
 * @return array<string,mixed>
 */
function te_core_textarea_field( $label, $fallback = '' ) {
	return array(
		'type'     => 'textarea',
		'label'    => $label,
		'default'  => '',
		'fallback' => $fallback,
		'max'      => 800,
	);
}

/**
 * URL field definition.
 *
 * @param string $label Label.
 * @return array<string,mixed>
 */
function te_core_url_field( $label ) {
	return array(
		'type'    => 'url',
		'label'   => $label,
		'default' => '',
	);
}

/**
 * Image field definition.
 *
 * @param string $label Label.
 * @return array<string,mixed>
 */
function te_core_image_field( $label ) {
	return array(
		'type'    => 'image',
		'label'   => $label,
		'default' => 0,
	);
}

/**
 * Number field definition.
 *
 * @param string $label   Label.
 * @param int    $default Default.
 * @param int    $min     Minimum.
 * @param int    $max     Maximum.
 * @return array<string,mixed>
 */
function te_core_number_field( $label, $default, $min, $max ) {
	return array(
		'type'    => 'number',
		'label'   => $label,
		'default' => $default,
		'min'     => $min,
		'max'     => $max,
	);
}

/**
 * Trust item fields.
 *
 * @param array<string,string> $icons Icon options.
 * @return array<string,array<string,mixed>>
 */
function te_core_trust_fields( $icons ) {
	$items = array(
		1 => array( 'truck', __( 'Clear delivery', 'te-core' ), __( 'Shipping choices are shown before you pay.', 'te-core' ) ),
		2 => array( 'returns', __( 'Straightforward returns', 'te-core' ), __( 'A defined window. No maze of small print.', 'te-core' ) ),
		3 => array( 'shield', __( 'Secure checkout', 'te-core' ), __( 'Payment stays with WooCommerce and your gateway.', 'te-core' ) ),
		4 => array( 'headset', __( 'A person answers', 'te-core' ), __( 'Questions go to support, not a dead end.', 'te-core' ) ),
	);
	$fields = array(
		'title' => te_core_text_field( __( 'Section title', 'te-core' ), '' ),
	);
	foreach ( $items as $i => $item ) {
		$fields[ "item_{$i}_enabled" ] = array(
			'type'    => 'checkbox',
			'label'   => sprintf(
				/* translators: %d: item number */
				__( 'Show item %d', 'te-core' ),
				$i
			),
			'default' => 1,
		);
		$fields[ "item_{$i}_icon" ]    = array(
			'type'    => 'select',
			'label'   => __( 'Icon', 'te-core' ),
			'options' => $icons,
			'default' => $item[0],
		);
		$fields[ "item_{$i}_title" ]   = te_core_text_field( __( 'Title', 'te-core' ), $item[1] );
		$fields[ "item_{$i}_text" ]    = te_core_text_field( __( 'Text', 'te-core' ), $item[2], 180 );
	}
	return $fields;
}

/**
 * Banner tile fields.
 *
 * @return array<string,array<string,mixed>>
 */
function te_core_banner_fields() {
	$tiles = array(
		1 => array( __( 'New in', 'te-core' ), __( 'Just added to the catalog.', 'te-core' ) ),
		2 => array( __( 'Home', 'te-core' ), __( 'Things meant to stay.', 'te-core' ) ),
		3 => array( __( 'Deals', 'te-core' ), __( 'Reduced, still exact.', 'te-core' ) ),
	);
	$fields = array(
		'title' => te_core_text_field( __( 'Section title', 'te-core' ), '' ),
	);
	foreach ( $tiles as $i => $tile ) {
		$fields[ "b{$i}_enabled" ] = array(
			'type'    => 'checkbox',
			'label'   => sprintf(
				/* translators: %d: tile number */
				__( 'Show tile %d', 'te-core' ),
				$i
			),
			'default' => 1,
		);
		$fields[ "b{$i}_title" ]   = te_core_text_field( __( 'Title', 'te-core' ), $tile[0] );
		$fields[ "b{$i}_text" ]    = te_core_text_field( __( 'Text', 'te-core' ), $tile[1], 180 );
		$fields[ "b{$i}_url" ]     = te_core_url_field( __( 'Link', 'te-core' ) );
		$fields[ "b{$i}_image" ]   = te_core_image_field( __( 'Image', 'te-core' ) );
	}
	return $fields;
}

/**
 * One section type, or null.
 *
 * @param string $id Type id.
 * @return array<string,mixed>|null
 */
function te_core_get_section_type( $id ) {
	$types = te_core_section_types();
	return $types[ $id ] ?? null;
}

/**
 * Sections shipped on a fresh install. Text is left empty so it translates.
 *
 * @return array<int,array<string,mixed>>
 */
function te_core_default_sections() {
	return array(
		array(
			'uid'      => 'hero',
			'type'     => 'hero',
			'enabled'  => 1,
			'settings' => array(),
		),
		array(
			'uid'      => 'departments',
			'type'     => 'categories',
			'enabled'  => 1,
			'settings' => array( 'count' => 8 ),
		),
		array(
			'uid'      => 'selected',
			'type'     => 'product_rail',
			'enabled'  => 1,
			'settings' => array(
				'source' => 'featured',
				'count'  => 8,
			),
		),
		array(
			'uid'      => 'trust',
			'type'     => 'trust',
			'enabled'  => 1,
			'settings' => array(),
		),
		array(
			'uid'      => 'deals',
			'type'     => 'product_rail',
			'enabled'  => 1,
			'settings' => array(
				'source' => 'sale',
				'count'  => 8,
			),
		),
		array(
			'uid'      => 'tiles',
			'type'     => 'banners',
			'enabled'  => 1,
			'settings' => array(),
		),
		array(
			'uid'      => 'newest',
			'type'     => 'product_rail',
			'enabled'  => 1,
			'settings' => array(
				'source' => 'newest',
				'count'  => 8,
			),
		),
		array(
			'uid'      => 'note',
			'type'     => 'editorial',
			'enabled'  => 1,
			'settings' => array(),
		),
	);
}
