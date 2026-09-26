<?php
/**
 * Homepage sections: sanitize, resolve, render.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Saved sections, or the defaults when the option has never been stored.
 *
 * @return array<int,array<string,mixed>>
 */
function te_core_get_sections() {
	$stored = get_option( 'te_core_sections', '__missing__' );
	if ( '__missing__' === $stored ) {
		return te_core_default_sections();
	}
	return is_array( $stored ) ? $stored : array();
}

/**
 * Sanitize the section option. Unknown types are dropped, not guessed.
 *
 * @param mixed $input Posted sections.
 * @return array<int,array<string,mixed>>
 */
function te_core_sanitize_sections( $input ) {
	if ( ! is_array( $input ) ) {
		return array();
	}
	$clean = array();
	$seen  = array();

	foreach ( $input as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$type = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : '';
		$def  = te_core_get_section_type( $type );
		if ( ! $def ) {
			continue;
		}
		$uid = isset( $row['uid'] ) ? sanitize_key( $row['uid'] ) : '';
		if ( '' === $uid || isset( $seen[ $uid ] ) ) {
			$uid = 'sec_' . strtolower( wp_generate_password( 6, false, false ) );
		}
		$seen[ $uid ] = true;

		$raw      = isset( $row['settings'] ) && is_array( $row['settings'] ) ? $row['settings'] : array();
		$settings = array();
		foreach ( $def['fields'] as $key => $field ) {
			$settings[ $key ] = te_core_sanitize_field( $raw[ $key ] ?? ( $field['default'] ?? '' ), $field );
		}

		$clean[] = array(
			'uid'      => $uid,
			'type'     => $type,
			'enabled'  => empty( $row['enabled'] ) ? 0 : 1,
			'settings' => $settings,
		);
		if ( count( $clean ) >= 24 ) {
			break;
		}
	}
	return $clean;
}

/**
 * Resolved args for one section. Empty text falls back to the registry copy.
 *
 * @param array $section Stored section.
 * @return array<string,mixed>
 */
function te_core_section_args( $section ) {
	$type = te_core_get_section_type( $section['type'] ?? '' );
	$args = array(
		'uid'  => $section['uid'] ?? '',
		'type' => $section['type'] ?? '',
	);
	if ( ! $type ) {
		return $args;
	}
	$stored = isset( $section['settings'] ) && is_array( $section['settings'] ) ? $section['settings'] : array();
	foreach ( $type['fields'] as $key => $field ) {
		if ( array_key_exists( $key, $stored ) && '' !== $stored[ $key ] && null !== $stored[ $key ] ) {
			$args[ $key ] = $stored[ $key ];
			continue;
		}
		if ( isset( $field['fallback'] ) && '' !== $field['fallback'] ) {
			$args[ $key ] = $field['fallback'];
			continue;
		}
		$args[ $key ] = $field['default'] ?? '';
	}
	return $args;
}

/**
 * Read a resolved section value.
 *
 * @param array  $args Section args.
 * @param string $key  Field key.
 * @return mixed
 */
function te_core_field( $args, $key ) {
	return $args[ $key ] ?? '';
}

/**
 * Render the homepage from the saved order.
 *
 * @return void
 */
function te_core_render_sections() {
	foreach ( te_core_get_sections() as $section ) {
		if ( empty( $section['enabled'] ) ) {
			continue;
		}
		$type = te_core_get_section_type( $section['type'] ?? '' );
		if ( ! $type ) {
			continue;
		}
		$args = te_core_section_args( $section );
		if ( ! empty( $type['render'] ) && is_callable( $type['render'] ) ) {
			call_user_func( $type['render'], $args );
			continue;
		}
		get_template_part( 'template-parts/sections/' . $type['template'], null, $args );
	}
}

/**
 * Whether the front page has an enabled hero. Used for the document heading.
 *
 * @return bool
 */
function te_core_front_has_hero() {
	foreach ( te_core_get_sections() as $section ) {
		if ( ! empty( $section['enabled'] ) && 'hero' === ( $section['type'] ?? '' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Attachment image or an empty string.
 *
 * @param int    $id   Attachment ID.
 * @param string $size Image size.
 * @param array  $attr Attributes.
 * @return string
 */
function te_core_media( $id, $size = 'large', $attr = array() ) {
	$id = absint( $id );
	if ( ! $id || ! wp_attachment_is_image( $id ) ) {
		return '';
	}
	$attr = array_merge(
		array(
			'class'    => 'te-media',
			'loading'  => 'lazy',
			'decoding' => 'async',
		),
		$attr
	);
	return wp_get_attachment_image( $id, $size, false, $attr );
}
