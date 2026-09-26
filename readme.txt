=== TE Core ===
Contributors: majidt90
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: e-commerce, custom-menu, custom-logo, featured-images, translation-ready, rtl-language-support, threaded-comments, wide-blocks

A precision-first WooCommerce theme for mixed marketplaces. One registry drives the WordPress settings screen and the storefront.

== Description ==

TE Core is a hybrid theme for a mixed catalog: search first, departments second, a fast buy box, and no page-builder weight.

* Settings live under Appearance → TE Core and use the WordPress settings screens: tabs, form tables, postboxes, the media modal, and the color picker.
* Homepage sections are added, removed, disabled, and reordered from that screen. The same registry renders them.
* Persian and RTL are first-class: Vazirmatn, logical CSS, a direction control, and a fa_IR translation.
* WooCommerce stays accurate. Simple products can be added without a reload. Variable products open the product page. Prices, stock, and totals come from WooCommerce.
* Performance defaults: no cart-fragments request, no jQuery on the storefront, one variable font, conditional assets, and motion that yields to reduced-motion.

== Installation ==

1. Copy the theme into wp-content/themes/te-core.
2. Activate TE Core.
3. Install and activate WooCommerce.
4. Open Appearance → TE Core.
5. Set the site language to فارسی if you want the Persian translation and automatic RTL.

== Frequently Asked Questions ==

= Where do I edit the homepage? =

Appearance → TE Core → Homepage. Drag sections, add a type, or restore the defaults. Empty product rails are skipped so a heading is never shown without products.

= Does it replace WooCommerce checkout? =

No. Classic and block checkout keep WooCommerce’s own flow. The theme only styles them and declares compatibility with high-performance order storage.

= Why are cart fragments off? =

They add a request to every page and slow interaction. The header count is rendered with the page, and the cart drawer updates after an add. Turn fragments on only if another plugin requires them.

== WooCommerce template overrides ==

* woocommerce/archive-product.php — 8.6.0
* woocommerce/content-product.php — 9.4.0

Hooks inside those templates are kept. Other WooCommerce markup is styled, not copied, so updates do not fork the checkout.

== Changelog ==

= 1.0.0 =
* First release. Registry-driven settings, mixed-catalog storefront, Persian/RTL, and a performance-first WooCommerce integration.
* Section width (contained, wide, full), catalog width, low-stock signal, compare, sticky buy bar, key specs, free-shipping meter, cart coupon, hover image, and removable filter chips.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
