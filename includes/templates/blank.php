<?php
/**
 * A blank template.
 */
if ( !defined( 'ABSPATH' ) ) { exit; }
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<style>
		html,body {
			margin: 0;
			padding: 0;
		}
	</style>
</head>
<body <?php body_class(); ?>>
	<main id="reblock-<?php the_ID(); ?>">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php the_content(); ?>
		</article>
	</main>
	<?php wp_footer(); ?>
</body>
</html>