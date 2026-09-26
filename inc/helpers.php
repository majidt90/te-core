<?php
/**
 * Settings access, sanitization, and small shared helpers.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values derived from the schema so the two cannot drift.
 *
 * @return array<string,mixed>
 */
function te_core_defaults() {
	$defaults = array();
	foreach ( te_core_settings_schema() as $key => $field ) {
		$defaults[ $key ] = $field['default'];
	}
	return $defaults;
}

/**
 * Read one setting. Stored values win, including intentional zeros.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Optional fallback when the key was never stored.
 * @return mixed
 */
function te_core_get( $key, $default = null ) {
	$all = get_option( 'te_core_settings', array() );
	if ( ! is_array( $all ) ) {
		$all = array();
	}
	if ( array_key_exists( $key, $all ) ) {
		return $all[ $key ];
	}
	if ( null !== $default ) {
		return $default;
	}
	$defaults = te_core_defaults();
	return $defaults[ $key ] ?? null;
}

/**
 * Boolean setting.
 *
 * @param string $key Setting key.
 * @return bool
 */
function te_core_on( $key ) {
	return (bool) te_core_get( $key );
}

/**
 * Direction actually used on the storefront.
 *
 * @return bool
 */
function te_core_is_rtl() {
	$mode = te_core_get( 'direction' );
	if ( 'rtl' === $mode ) {
		return true;
	}
	if ( 'ltr' === $mode ) {
		return false;
	}
	return is_rtl();
}

/**
 * Persian digits, respecting the explicit setting and the locale.
 *
 * @return bool
 */
function te_core_persian_digits_enabled() {
	$mode = te_core_get( 'persian_digits' );
	if ( 'on' === $mode ) {
		return true;
	}
	if ( 'off' === $mode ) {
		return false;
	}
	return 0 === strpos( (string) determine_locale(), 'fa' );
}

/**
 * Replace ASCII digits. HTML tags and character references are left intact,
 * so a currency entity such as &#x062A; is not rewritten into visible text.
 *
 * @param string $text Text that may contain digits.
 * @return string
 */
function te_core_digits( $text ) {
	if ( ! te_core_persian_digits_enabled() || ! is_string( $text ) || '' === $text || ! preg_match( '/\d/', $text ) ) {
		return $text;
	}
	$held = array();
	$safe = preg_replace_callback(
		'/&#x[0-9a-fA-F]+;|&#\d+;|&[a-zA-Z][a-zA-Z0-9]+;|<[^>]*>/',
		static function ( $match ) use ( &$held ) {
			$token          = 'TEHOLD' . str_repeat( 'Z', count( $held ) + 1 ) . 'END';
			$held[ $token ] = $match[0];
			return $token;
		},
		$text
	);
	if ( ! is_string( $safe ) ) {
		return $text;
	}
	$safe = str_replace(
		array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
		$safe
	);
	return $held ? strtr( $safe, $held ) : $safe;
}

/**
 * Turn numeric character references above ASCII into real characters.
 * Used on price HTML after digits are converted, so a Persian currency
 * symbol stored as &#x062A; renders as text instead of an entity.
 *
 * @param string $html HTML.
 * @return string
 */
function te_core_decode_marks( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, '&#' ) ) {
		return $html;
	}
	$decoded = preg_replace_callback(
		'/&#x([0-9a-fA-F]+);|&#(\d+);/',
		static function ( $match ) {
			$point = '' !== $match[1] ? hexdec( $match[1] ) : (int) $match[2];
			if ( $point < 128 || $point > 0x10FFFF || ( $point >= 0xD800 && $point <= 0xDFFF ) ) {
				return $match[0];
			}
			$char = function_exists( 'mb_chr' ) ? mb_chr( $point, 'UTF-8' ) : html_entity_decode( $match[0], ENT_HTML5, 'UTF-8' );
			return is_string( $char ) && '' !== $char ? $char : $match[0];
		},
		$html
	);
	return is_string( $decoded ) ? $decoded : $html;
}

/**
 * Plain price text for JSON and copy. Entities become characters.
 *
 * @param string $html Price HTML.
 * @return string
 */
function te_core_price_text( $html ) {
	$text = wp_strip_all_tags( te_core_decode_marks( (string) $html ) );
	$text = preg_replace( '/\s+/u', ' ', $text );
	return trim( is_string( $text ) ? $text : '' );
}

/**
 * Product categories that should not be offered as departments.
 * The WooCommerce default category is usually the leftover "Uncategorized".
 *
 * @return array<int,int>
 */
function te_core_hidden_term_ids() {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$ids     = array();
	$default = absint( get_option( 'default_product_cat' ) );
	if ( $default ) {
		$ids[] = $default;
	}
	if ( taxonomy_exists( 'product_cat' ) ) {
		$slug = get_term_by( 'slug', 'uncategorized', 'product_cat' );
		if ( $slug && ! is_wp_error( $slug ) ) {
			$ids[] = (int) $slug->term_id;
		}
	}
	$ids = array_values( array_unique( array_filter( $ids ) ) );
	return $ids;
}

/**
 * Sanitize one schema field. The whitelist is the sanitizer for selects.
 *
 * @param mixed $value Raw value.
 * @param array $field Field schema.
 * @return mixed
 */
function te_core_sanitize_field( $value, $field ) {
	$type = $field['type'] ?? 'text';

	switch ( $type ) {
		case 'checkbox':
			return empty( $value ) ? 0 : 1;

		case 'number':
			$num = absint( $value );
			$min = isset( $field['min'] ) ? (int) $field['min'] : 0;
			$max = isset( $field['max'] ) ? (int) $field['max'] : 9999;
			return max( $min, min( $max, $num ) );

		case 'select':
			$allowed = array_keys( $field['options'] ?? array() );
			$value   = is_scalar( $value ) ? (string) $value : '';
			return in_array( $value, $allowed, true ) ? $value : ( $field['default'] ?? '' );

		case 'color':
			$color = sanitize_hex_color( is_string( $value ) ? $value : '' );
			return $color ? $color : ( $field['default'] ?? '#c2410c' );

		case 'url':
			$value = is_string( $value ) ? trim( $value ) : '';
			if ( '' === $value ) {
				return '';
			}
			$protocols   = wp_allowed_protocols();
			$protocols[] = 'tel';
			$protocols[] = 'tg';
			return esc_url_raw( $value, $protocols );

		case 'textarea':
			$text = sanitize_textarea_field( is_string( $value ) ? $value : '' );
			return te_core_limit_text( $text, (int) ( $field['max'] ?? 800 ) );

		case 'image':
		case 'category':
			return absint( $value );

		case 'text':
		default:
			$text = sanitize_text_field( is_string( $value ) ? $value : ( is_scalar( $value ) ? (string) $value : '' ) );
			return te_core_limit_text( $text, (int) ( $field['max'] ?? 180 ) );
	}
}

/**
 * Multibyte-aware length cap.
 *
 * @param string $text  Text.
 * @param int    $limit Maximum characters.
 * @return string
 */
function te_core_limit_text( $text, $limit ) {
	if ( $limit < 1 || '' === $text ) {
		return $text;
	}
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, 0, $limit );
	}
	return substr( $text, 0, $limit );
}

/**
 * Sanitize the whole settings option, merging only submitted keys.
 *
 * @param mixed $input Posted settings.
 * @return array<string,mixed>
 */
function te_core_sanitize_settings( $input ) {
	$current = get_option( 'te_core_settings', array() );
	if ( ! is_array( $current ) ) {
		$current = array();
	}
	$clean  = array_merge( te_core_defaults(), $current );
	$schema = te_core_settings_schema();
	if ( ! is_array( $input ) ) {
		return $clean;
	}
	foreach ( $input as $key => $value ) {
		$key = sanitize_key( $key );
		if ( ! isset( $schema[ $key ] ) ) {
			continue;
		}
		$clean[ $key ] = te_core_sanitize_field( $value, $schema[ $key ] );
	}
	return $clean;
}

/**
 * Asset version from filemtime so edits are not stuck behind a cache.
 *
 * @param string $relative Path relative to the theme root.
 * @return string
 */
function te_core_asset_ver( $relative ) {
	$path = TE_CORE_DIR . $relative;
	return file_exists( $path ) ? (string) filemtime( $path ) : TE_CORE_VERSION;
}

/**
 * Shop or home URL, never a broken link.
 *
 * @return string
 */
function te_core_shop_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'shop' );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/' );
}

/**
 * Account URL.
 *
 * @return string
 */
function te_core_account_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'myaccount' );
		if ( $url ) {
			return $url;
		}
	}
	return wp_login_url();
}

/**
 * Catalog URL for a product source. Used by rails and the hero.
 *
 * @param string $source   Source key.
 * @param int    $category Category term ID.
 * @return string
 */
function te_core_source_url( $source, $category = 0 ) {
	$shop = te_core_shop_url();
	switch ( $source ) {
		case 'sale':
			return add_query_arg( 'te_source', 'sale', $shop );
		case 'newest':
			return add_query_arg( 'orderby', 'date', $shop );
		case 'bestselling':
			return add_query_arg( 'orderby', 'popularity', $shop );
		case 'top_rated':
			return add_query_arg( 'orderby', 'rating', $shop );
		case 'category':
			$link = get_term_link( (int) $category, 'product_cat' );
			return is_wp_error( $link ) ? $shop : $link;
		case 'featured':
		default:
			return add_query_arg( 'te_source', 'featured', $shop );
	}
}

/**
 * Smart URL: stored value, otherwise a known storefront target.
 *
 * @param string $url      Stored URL.
 * @param string $fallback shop|sale|home.
 * @return string
 */
function te_core_smart_url( $url, $fallback = 'shop' ) {
	if ( is_string( $url ) && '' !== $url ) {
		return $url;
	}
	if ( 'sale' === $fallback ) {
		return te_core_source_url( 'sale' );
	}
	if ( 'home' === $fallback ) {
		return home_url( '/' );
	}
	return te_core_shop_url();
}
