<?php
/**
 * Storefront footer and overlays.
 *
 * @package TE_Core
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/footer/site-footer' );
get_template_part( 'template-parts/components/drawers' );
?>
<div id="te-live" class="screen-reader-text" role="status" aria-live="polite"></div>
<?php wp_footer(); ?>
</body>
</html>
