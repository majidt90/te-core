<?php
/**
 * Theme supports, menus, and activation.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'te_core_setup', 0 );
add_action( 'after_switch_theme', 'te_core_activate' );
add_action( 'widgets_init', 'te_core_widgets' );
add_filter( 'language_attributes', 'te_core_language_attributes' );
add_filter( 'body_class', 'te_core_body_class' );
add_action( 'init', 'te_core_patterns' );

/**
 * Core theme supports. Textdomain loads first so later strings translate.
 *
 * @return void
 */
function te_core_setup() {
	load_theme_textdomain( 'te-core', TE_CORE_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 180,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 720,
			'single_image_width'    => 960,
		)
	);
	if ( te_core_on( 'gallery_lightbox' ) ) {
		add_theme_support( 'wc-product-gallery-lightbox' );
	}

	register_nav_menus(
		array(
			'header' => __( 'Header', 'te-core' ),
			'footer' => __( 'Footer', 'te-core' ),
			'legal'  => __( 'Legal', 'te-core' ),
		)
	);

	add_image_size( 'te-card', 720, 900, false );
}

/**
 * Seed options once. Never overwrite a merchant’s later edits.
 *
 * @return void
 */
function te_core_activate() {
	if ( false === get_option( 'te_core_settings' ) ) {
		add_option( 'te_core_settings', te_core_defaults() );
	}
	if ( false === get_option( 'te_core_sections' ) ) {
		add_option( 'te_core_sections', te_core_default_sections() );
	}
	set_transient( 'te_core_activated', 1, WEEK_IN_SECONDS );
}

/**
 * Widget areas. The shop area is optional; the theme has its own filters.
 *
 * @return void
 */
function te_core_widgets() {
	$areas = array(
		'shop'     => __( 'Shop filters', 'te-core' ),
		'footer-1' => __( 'Footer 1', 'te-core' ),
		'footer-2' => __( 'Footer 2', 'te-core' ),
		'footer-3' => __( 'Footer 3', 'te-core' ),
	);
	foreach ( $areas as $id => $label ) {
		register_sidebar(
			array(
				'name'          => $label,
				'id'            => $id,
				'before_widget' => '<section id="%1$s" class="te-widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="te-widget__title">',
				'after_title'   => '</h2>',
			)
		);
	}
}

/**
 * Force dir when the setting says so. Automatic leaves WordPress alone.
 *
 * @param string $output Language attributes.
 * @return string
 */
function te_core_language_attributes( $output ) {
	$mode = te_core_get( 'direction' );
	if ( 'auto' === $mode ) {
		return $output;
	}
	$dir = ( 'rtl' === $mode ) ? 'rtl' : 'ltr';
	if ( preg_match( '/\bdir="[^"]*"/', $output ) ) {
		return preg_replace( '/\bdir="[^"]*"/', 'dir="' . $dir . '"', $output, 1 );
	}
	return trim( $output . ' dir="' . $dir . '"' );
}

/**
 * Body classes that CSS and JS both trust.
 *
 * @param array $classes Classes.
 * @return array
 */
function te_core_body_class( $classes ) {
	$classes[] = 'te-core';
	if ( te_core_is_rtl() ) {
		$classes[] = 'te-rtl';
	}
	if ( ! te_core_on( 'animations' ) ) {
		$classes[] = 'te-reduce';
	}
	if ( te_core_on( 'content_visibility' ) ) {
		$classes[] = 'te-cv';
	}
	if ( te_core_on( 'sticky_header' ) ) {
		$classes[] = 'te-sticky';
	}
	if ( te_core_on( 'sticky_buybox' ) ) {
		$classes[] = 'te-sticky-buy';
	}
	if ( te_core_on( 'mobile_dock' ) ) {
		$classes[] = 'te-has-dock';
	}
	$fit = te_core_get( 'image_fit' );
	$classes[] = ( 'cover' === $fit ) ? 'te-fit-cover' : 'te-fit-contain';
	return $classes;
}

/**
 * Pattern category. Files in /patterns are registered by core.
 *
 * @return void
 */
function te_core_patterns() {
	register_block_pattern_category(
		'te-core',
		array( 'label' => __( 'TE Core', 'te-core' ) )
	);
}
