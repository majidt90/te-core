<?php
/**
 * TE Core bootstrap.
 *
 * The theme is a hybrid: classic templates for WooCommerce accuracy,
 * theme.json for editor tokens, and one PHP registry for settings.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'TE_CORE_VERSION', '1.0.0' );
define( 'TE_CORE_DIR', get_template_directory() );
define( 'TE_CORE_URI', get_template_directory_uri() );
define( 'TE_CORE_FILE', TE_CORE_DIR . '/style.css' );

require TE_CORE_DIR . '/inc/helpers.php';
require TE_CORE_DIR . '/inc/icons.php';
require TE_CORE_DIR . '/inc/registry.php';
require TE_CORE_DIR . '/inc/sections.php';
require TE_CORE_DIR . '/inc/setup.php';
require TE_CORE_DIR . '/inc/assets.php';
require TE_CORE_DIR . '/inc/performance.php';
require TE_CORE_DIR . '/inc/template-tags.php';
require TE_CORE_DIR . '/inc/woocommerce.php';
require TE_CORE_DIR . '/inc/ajax.php';
require TE_CORE_DIR . '/inc/admin.php';
