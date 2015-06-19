<?php
/**
 * @package groundup
 * @subpackage upandup
 */
?>
<?php get_header(); ?>
<main id="main" role="main">
	<?php if ( have_posts() ) {
		while( have_posts() ) : the_post();
	 		$post_type = get_post_type();
			if ( $post_type == 'post' ) {
				$post_type = get_post_format();
			}
			if ( is_single() || is_page() ) {
				get_template_part( 'templates/excerpt', $post_type );
			} else {
				get_template_part( 'templates/excerpt', $post_type );
			}
	 	endwhile;
 	} ?>
</main>
<?php get_footer(); ?>
			