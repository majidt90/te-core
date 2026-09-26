<?php
/**
 * Document head and storefront header.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="te-skip" href="#primary"><?php esc_html_e( 'Skip to content', 'te-core' ); ?></a>
<?php
if ( te_core_on( 'announcement_enabled' ) && te_core_on( 'announcement_dismiss' ) && te_core_get( 'announcement_text' ) ) {
	echo '<script>try{if(sessionStorage.getItem("te_core_announce")==="1"){document.documentElement.classList.add("te-announce-off");}}catch(e){}</script>';
}
get_template_part( 'template-parts/header/site-header' );
