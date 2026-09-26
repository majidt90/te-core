<?php
/**
 * Appearance → TE Core. Native settings screens, not a custom skin.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'te_core_admin_menu' );
add_action( 'admin_init', 'te_core_register_settings' );
add_action( 'admin_enqueue_scripts', 'te_core_admin_assets' );
add_action( 'admin_notices', 'te_core_activation_notice' );
add_action( 'admin_bar_menu', 'te_core_admin_bar', 80 );
add_action( 'admin_post_te_core_reset_sections', 'te_core_reset_sections' );
add_filter( 'option_page_capability_te_core_settings_group', 'te_core_settings_cap' );
add_filter( 'option_page_capability_te_core_sections_group', 'te_core_settings_cap' );

/**
 * Editors who can manage the theme can save these screens.
 *
 * @return string
 */
function te_core_settings_cap() {
	return 'edit_theme_options';
}

/**
 * Submenu under Appearance, where theme settings belong.
 *
 * @return void
 */
function te_core_admin_menu() {
	add_theme_page(
		__( 'TE Core', 'te-core' ),
		__( 'TE Core', 'te-core' ),
		'edit_theme_options',
		'te-core',
		'te_core_render_settings_page'
	);
}

/**
 * Two options: shell settings, and the ordered homepage sections.
 *
 * @return void
 */
function te_core_register_settings() {
	register_setting(
		'te_core_settings_group',
		'te_core_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'te_core_sanitize_settings',
			'default'           => te_core_defaults(),
		)
	);
	register_setting(
		'te_core_sections_group',
		'te_core_sections',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'te_core_sanitize_sections',
			'default'           => te_core_default_sections(),
		)
	);
}

/**
 * Admin assets only on this screen. jQuery is already part of wp-admin.
 *
 * @param string $hook Hook suffix.
 * @return void
 */
function te_core_admin_assets( $hook ) {
	if ( 'appearance_page_te-core' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_media();
	wp_enqueue_style(
		'te-core-admin',
		TE_CORE_URI . '/assets/css/admin.css',
		array(),
		te_core_asset_ver( '/assets/css/admin.css' )
	);
	wp_enqueue_script(
		'te-core-admin',
		TE_CORE_URI . '/assets/js/admin.js',
		array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ),
		te_core_asset_ver( '/assets/js/admin.js' ),
		true
	);
	wp_localize_script(
		'te-core-admin',
		'teCoreAdmin',
		array(
			'removeConfirm' => __( 'Remove this section?', 'te-core' ),
			'resetConfirm'  => __( 'Restore the default homepage sections?', 'te-core' ),
			'mediaTitle'    => __( 'Choose an image', 'te-core' ),
			'mediaButton'   => __( 'Use this image', 'te-core' ),
		)
	);

	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	$screen->add_help_tab(
		array(
			'id'      => 'te-core-help',
			'title'   => __( 'Core', 'te-core' ),
			'content' => '<p>' . esc_html__( 'TE Core has one registry. These fields and the storefront read the same definitions. A control that is off is not printed, and its script is not loaded.', 'te-core' ) . '</p>',
		)
	);
	$screen->add_help_tab(
		array(
			'id'      => 'te-core-sections',
			'title'   => __( 'Homepage', 'te-core' ),
			'content' => '<p>' . esc_html__( 'Add, remove, and drag sections. The order on this screen is the order on the front page. Disabled sections stay saved but are not rendered. Empty product rails are skipped so the page never shows a false heading.', 'te-core' ) . '</p>',
		)
	);
	$screen->set_help_sidebar(
		'<p><strong>' . esc_html__( 'For more information:', 'te-core' ) . '</strong></p><p><a href="https://wordpress.org/documentation/article/appearance-themes-screen/">' . esc_html__( 'Themes', 'te-core' ) . '</a></p>'
	);
}

/**
 * One-time pointer after activation.
 *
 * @return void
 */
function te_core_activation_notice() {
	if ( ! current_user_can( 'edit_theme_options' ) || ! get_transient( 'te_core_activated' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_te-core' === $screen->id ) {
		delete_transient( 'te_core_activated' );
		return;
	}
	$url = admin_url( 'themes.php?page=te-core' );
	echo '<div class="notice notice-info"><p>';
	echo esc_html__( 'TE Core is active. Set the catalog, direction, and homepage sections from its settings screen.', 'te-core' );
	echo ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Open TE Core settings', 'te-core' ) . '</a>';
	echo '</p></div>';
}

/**
 * Admin bar shortcut.
 *
 * @param WP_Admin_Bar $bar Bar.
 * @return void
 */
function te_core_admin_bar( $bar ) {
	if ( ! current_user_can( 'edit_theme_options' ) || is_admin() ) {
		return;
	}
	$bar->add_node(
		array(
			'id'    => 'te-core',
			'title' => 'TE Core',
			'href'  => admin_url( 'themes.php?page=te-core' ),
		)
	);
}

/**
 * Restore homepage sections.
 *
 * @return void
 */
function te_core_reset_sections() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'te-core' ) );
	}
	check_admin_referer( 'te_core_reset_sections' );
	update_option( 'te_core_sections', te_core_default_sections() );
	wp_safe_redirect( admin_url( 'themes.php?page=te-core&tab=homepage&reset=1' ) );
	exit;
}

/**
 * Settings screen.
 *
 * @return void
 */
function te_core_render_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$tabs = te_core_settings_tabs();
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $tabs[ $tab ] ) ) {
		$tab = 'general';
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'What you set here is what the storefront renders. Shell, catalog, and homepage sections share one core.', 'te-core' ); ?>
		</p>
		<?php if ( ! empty( $_GET['reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Homepage sections restored.', 'te-core' ); ?></p></div>
		<?php endif; ?>
		<nav class="nav-tab-wrapper wp-clearfix" aria-label="<?php esc_attr_e( 'TE Core settings', 'te-core' ); ?>">
			<?php foreach ( $tabs as $id => $label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=te-core&tab=' . $id ) ); ?>" class="nav-tab <?php echo $tab === $id ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php
		if ( 'homepage' === $tab ) {
			te_core_render_sections_tab();
		} else {
			te_core_render_fields_tab( $tab );
		}
		?>
	</div>
	<?php
}

/**
 * A standard settings form for one tab.
 *
 * @param string $tab Tab id.
 * @return void
 */
function te_core_render_fields_tab( $tab ) {
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'te_core_settings_group' ); ?>
		<?php if ( 'general' === $tab ) : ?>
			<?php te_core_status_box(); ?>
		<?php endif; ?>
		<table class="form-table" role="presentation">
			<?php
			foreach ( te_core_settings_schema() as $key => $field ) {
				if ( ( $field['tab'] ?? '' ) !== $tab ) {
					continue;
				}
				te_core_render_setting_row( $key, $field );
			}
			?>
		</table>
		<?php submit_button(); ?>
	</form>
	<?php
}

/**
 * A short, factual status box. It reports; it does not decorate.
 *
 * @return void
 */
function te_core_status_box() {
	$wc     = class_exists( 'WooCommerce' );
	$dir    = te_core_is_rtl() ? __( 'Right to left', 'te-core' ) : __( 'Left to right', 'te-core' );
	$count  = count( te_core_get_sections() );
	$active = 0;
	foreach ( te_core_get_sections() as $section ) {
		if ( ! empty( $section['enabled'] ) ) {
			++$active;
		}
	}
	?>
	<div class="notice notice-info inline te-status">
		<p><strong><?php esc_html_e( 'Core status', 'te-core' ); ?></strong></p>
		<ul>
			<li>
				<?php
				echo $wc
					? esc_html__( 'WooCommerce is active.', 'te-core' )
					: esc_html__( 'WooCommerce is not active. The shell still loads; catalog sections stay hidden.', 'te-core' );
				?>
			</li>
			<li>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: locale, 2: direction */
						__( 'Locale %1$s, direction %2$s.', 'te-core' ),
						determine_locale(),
						$dir
					)
				);
				?>
			</li>
			<li>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: active sections, 2: saved sections */
				__( '%1$d homepage sections active, %2$d saved.', 'te-core' ),
					$active,
					$count
				)
			);
			?>
			</li>
			<li>
				<?php
				$catalog = te_core_locale_mofile( determine_locale() );
				echo esc_html(
					( '' !== $catalog && is_textdomain_loaded( 'te-core' ) )
						? __( 'Theme translations are loaded.', 'te-core' )
						: __( 'Theme translations are not loaded for this locale.', 'te-core' )
				);
				?>
			</li>
		</ul>
	</div>
	<?php
}

/**
 * One settings row, using the WordPress form-table pattern.
 *
 * @param string $key   Setting key.
 * @param array  $field Field schema.
 * @return void
 */
function te_core_render_setting_row( $key, $field ) {
	$id    = 'te-core-' . $key;
	$value = te_core_get( $key );
	?>
	<tr>
		<th scope="row">
			<?php if ( 'checkbox' === $field['type'] ) : ?>
				<?php echo esc_html( $field['label'] ); ?>
			<?php else : ?>
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			<?php endif; ?>
		</th>
		<td>
			<?php te_core_render_control( 'te_core_settings[' . $key . ']', $id, $field, $value ); ?>
			<?php if ( ! empty( $field['description'] ) ) : ?>
				<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * A control. Names are passed in so sections and settings share the renderer.
 *
 * @param string $name  Input name.
 * @param string $id    Input id.
 * @param array  $field Field schema.
 * @param mixed  $value Current value.
 * @return void
 */
function te_core_render_control( $name, $id, $field, $value ) {
	$type = $field['type'] ?? 'text';
	switch ( $type ) {
		case 'checkbox':
			echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="0">';
			echo '<label for="' . esc_attr( $id ) . '">';
			echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1" ' . checked( (int) $value, 1, false ) . '> ';
			echo esc_html__( 'Enabled', 'te-core' );
			echo '</label>';
			break;

		case 'select':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( $field['options'] as $option => $label ) {
				echo '<option value="' . esc_attr( $option ) . '" ' . selected( (string) $value, (string) $option, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
			break;

		case 'color':
			echo '<input type="text" class="te-color" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" data-default-color="' . esc_attr( $field['default'] ?? '#c2410c' ) . '">';
			break;

		case 'number':
			echo '<input type="number" class="small-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" min="' . esc_attr( (string) ( $field['min'] ?? 0 ) ) . '" max="' . esc_attr( (string) ( $field['max'] ?? 9999 ) ) . '">';
			break;

		case 'textarea':
			echo '<textarea class="large-text" rows="4" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" placeholder="' . esc_attr( $field['fallback'] ?? '' ) . '">' . esc_textarea( (string) $value ) . '</textarea>';
			break;

		case 'url':
			echo '<input type="url" class="regular-text code" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" placeholder="https://">';
			break;

		case 'image':
			te_core_render_media_control( $name, $id, (int) $value );
			break;

		case 'category':
			te_core_render_category_control( $name, $id, (int) $value );
			break;

		case 'text':
		default:
			echo '<input type="text" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" placeholder="' . esc_attr( $field['fallback'] ?? '' ) . '">';
			break;
	}
}

/**
 * Media field using the WordPress media modal.
 *
 * @param string $name  Name.
 * @param string $id    Id.
 * @param int    $value Attachment ID.
 * @return void
 */
function te_core_render_media_control( $name, $id, $value ) {
	$src = $value ? wp_get_attachment_image_url( $value, 'thumbnail' ) : '';
	echo '<div class="te-media">';
	echo '<input type="hidden" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '">';
	echo '<img alt="" src="' . esc_url( $src ? $src : '' ) . '" ' . ( $src ? '' : 'hidden' ) . '>';
	echo '<button type="button" class="button te-media-select">' . esc_html__( 'Select image', 'te-core' ) . '</button> ';
	echo '<button type="button" class="button-link te-media-clear">' . esc_html__( 'Remove', 'te-core' ) . '</button>';
	echo '</div>';
}

/**
 * Product category select.
 *
 * @param string $name  Name.
 * @param string $id    Id.
 * @param int    $value Term ID.
 * @return void
 */
function te_core_render_category_control( $name, $id, $value ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		echo '<p class="description">' . esc_html__( 'WooCommerce categories appear here after WooCommerce is active.', 'te-core' ) . '</p>';
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="0">';
		return;
	}
	wp_dropdown_categories(
		array(
			'taxonomy'          => 'product_cat',
			'name'              => $name,
			'id'                => $id,
			'selected'          => $value,
			'hide_empty'        => false,
			'show_option_none'  => __( 'Select a category', 'te-core' ),
			'option_none_value' => '0',
			'hierarchical'      => true,
		)
	);
}

/**
 * Homepage section manager. Looks like the menus / postbox screens.
 *
 * @return void
 */
function te_core_render_sections_tab() {
	$sections = te_core_get_sections();
	$types    = te_core_section_types();
	?>
	<form method="post" action="options.php" id="te-sections-form">
		<?php settings_fields( 'te_core_sections_group' ); ?>
		<div id="poststuff" class="te-sections-layout">
			<div class="te-sections-main">
				<div class="postbox">
					<div class="postbox-header">
						<h2 class="hndle"><?php esc_html_e( 'Add a section', 'te-core' ); ?></h2>
					</div>
					<div class="inside">
						<label for="te-section-type" class="screen-reader-text"><?php esc_html_e( 'Section type', 'te-core' ); ?></label>
						<select id="te-section-type">
							<?php foreach ( $types as $id => $type ) : ?>
								<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $type['label'] ); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="button" id="te-add-section"><?php esc_html_e( 'Add section', 'te-core' ); ?></button>
						<p class="description"><?php esc_html_e( 'Drag the handle to reorder. Uncheck Enabled to keep a section without showing it.', 'te-core' ); ?></p>
					</div>
				</div>

				<div id="te-sections">
					<?php
					foreach ( $sections as $index => $section ) {
						te_core_section_box( (string) $index, $section );
					}
					?>
				</div>
				<?php submit_button( __( 'Save sections', 'te-core' ) ); ?>
			</div>
		</div>
	</form>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="te-reset-form">
		<?php wp_nonce_field( 'te_core_reset_sections' ); ?>
		<input type="hidden" name="action" value="te_core_reset_sections">
		<?php submit_button( __( 'Restore default sections', 'te-core' ), 'secondary', 'submit', false ); ?>
	</form>
	<div hidden>
		<?php foreach ( $types as $id => $type ) : ?>
			<template id="te-tpl-<?php echo esc_attr( $id ); ?>">
				<?php
				te_core_section_box(
					'__i__',
					array(
						'uid'      => 'sec___i__',
						'type'     => $id,
						'enabled'  => 1,
						'settings' => array(),
					)
				);
				?>
			</template>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * One section postbox.
 *
 * @param string $index   Index token.
 * @param array  $section Section data.
 * @return void
 */
function te_core_section_box( $index, $section ) {
	$type = te_core_get_section_type( $section['type'] ?? '' );
	if ( ! $type ) {
		return;
	}
	$name    = 'te_core_sections[' . $index . ']';
	$uid     = $section['uid'] ?? ( 'sec_' . $index );
	$enabled = ! isset( $section['enabled'] ) || ! empty( $section['enabled'] );
	$stored  = isset( $section['settings'] ) && is_array( $section['settings'] ) ? $section['settings'] : array();
	?>
	<div class="postbox te-section">
		<div class="postbox-header">
			<h2 class="hndle">
				<span class="te-drag dashicons dashicons-menu" aria-hidden="true"></span>
				<?php echo esc_html( $type['label'] ); ?>
				<span class="te-section__uid"><?php echo esc_html( $uid ); ?></span>
			</h2>
			<div class="handle-actions">
				<button type="button" class="handlediv te-section-toggle" aria-expanded="true">
					<span class="screen-reader-text"><?php esc_html_e( 'Toggle section', 'te-core' ); ?></span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
			</div>
		</div>
		<div class="inside">
			<?php if ( ! empty( $type['description'] ) ) : ?>
				<p class="description"><?php echo esc_html( $type['description'] ); ?></p>
			<?php endif; ?>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[uid]" value="<?php echo esc_attr( $uid ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[type]" value="<?php echo esc_attr( $section['type'] ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[enabled]" value="0">
			<p>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[enabled]" value="1" <?php checked( $enabled ); ?>>
					<?php esc_html_e( 'Enabled', 'te-core' ); ?>
				</label>
			</p>
			<table class="form-table" role="presentation">
				<?php foreach ( $type['fields'] as $key => $field ) : ?>
					<?php
					$field_name = $name . '[settings][' . $key . ']';
					$field_id   = 'te-' . $index . '-' . $key;
					$value      = array_key_exists( $key, $stored ) ? $stored[ $key ] : ( $field['default'] ?? '' );
					?>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
						<td>
							<?php te_core_render_control( $field_name, $field_id, $field, $value ); ?>
							<?php if ( ! empty( $field['description'] ) ) : ?>
								<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<button type="button" class="button-link-delete te-section-remove"><?php esc_html_e( 'Remove section', 'te-core' ); ?></button>
		</div>
	</div>
	<?php
}
