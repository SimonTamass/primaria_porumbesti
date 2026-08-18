<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="elementor porumbesti-global-elementor" data-elementor-type="porumbesti-global">
	<?php \PrimariaPorumbesti\Frontend_Templates::instance()->render_safely(); ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
