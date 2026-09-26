<?php
/**
 * Inline icons. No icon font on the storefront.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Icon path data.
 *
 * @return array<string,string>
 */
function te_core_icon_paths() {
	return array(
		'search'   => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/>',
		'user'     => '<circle cx="12" cy="8" r="3.2"/><path d="M5.2 19.2c1.1-2.7 3.3-4 6.8-4s5.7 1.3 6.8 4"/>',
		'heart'    => '<path d="M12 19.2S5.5 15 5.5 10.2A3.7 3.7 0 0 1 12 8a3.7 3.7 0 0 1 6.5 2.2C18.5 15 12 19.2 12 19.2z"/>',
		'bag'      => '<path d="M6.2 8h11.6l-.8 11.2H7z"/><path d="M9 8V7.2a3 3 0 0 1 6 0V8"/>',
		'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'grid'     => '<path d="M4 4h6.2v6.2H4zM13.8 4H20v6.2h-6.2zM4 13.8h6.2V20H4zM13.8 13.8H20V20h-6.2z"/>',
		'chevron'  => '<path d="m9 6 6 6-6 6"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'home'     => '<path d="m4 11 8-7 8 7"/><path d="M6.5 10.5V20h11V10.5"/>',
		'truck'    => '<path d="M3 7h11v9H3z"/><path d="M14 11h4l3 3v2h-7z"/><circle cx="7" cy="17.5" r="1.4"/><circle cx="17" cy="17.5" r="1.4"/>',
		'returns'  => '<path d="M4 12a8 8 0 1 0 2.2-5.5"/><path d="M4 4.5V9h4.5"/>',
		'shield'   => '<path d="M12 3.5 19 6.2v5.4c0 4.2-2.8 7.2-7 8.9-4.2-1.7-7-4.7-7-8.9V6.2z"/>',
		'headset'  => '<path d="M5 13V12a7 7 0 0 1 14 0v1"/><path d="M5 13h2.5v6H6a2 2 0 0 1-2-2zM19 13h-2.5v6H18a2 2 0 0 0 2-2z"/><path d="M12 20h3"/>',
		'star'     => '<path d="m12 3.8 2.1 4.6 5 .6-3.7 3.4.9 5-4.3-2.4L7.7 17.4l.9-5L4.9 9l5-.6z"/>',
		'check'    => '<path d="m5 12 5 5L20 7"/>',
		'filter'   => '<path d="M4 6h16M7 12h10M10 18h4"/>',
		'scale'    => '<path d="M12 3v18"/><path d="M6 8h12"/><path d="M8.2 8 6 14.2a2.5 2.5 0 0 0 4.6 0z"/><path d="M15.8 8 13.6 14.2a2.5 2.5 0 0 0 4.6 0z"/>',
		'plus'     => '<path d="M12 5v14M5 12h14"/>',
		'minus'    => '<path d="M5 12h14"/>',
		'trash'    => '<path d="M5 7h14"/><path d="M9 7V5h6v2"/><path d="M7 7l1 13h8l1-13"/>',
		'up'       => '<path d="M12 19V5"/><path d="m6 11 6-6 6 6"/>',
		'share'    => '<circle cx="6" cy="12" r="2"/><circle cx="16" cy="7" r="2"/><circle cx="16" cy="17" r="2"/><path d="m8 11 6-3M8 13l6 3"/>',
		'copy'     => '<rect x="8" y="8" width="11" height="12" rx="2"/><path d="M6 16H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v1"/>',
		'list'     => '<path d="M8 6h12M8 12h12M8 18h12"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
	);
}

/**
 * Print an icon.
 *
 * @param string $name Icon key.
 * @return void
 */
function te_core_icon( $name ) {
	$paths = te_core_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) {
		return;
	}
	echo '<svg class="te-icon" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
	echo $paths[ $name ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static path data.
	echo '</svg>';
}
