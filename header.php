<?php
/**
 * @package groundup
 */
?>
<?php get_template_part( 'head' ); ?>
<?php // Homepage or First Visit
if ( is_front_page() || groundup_is_new_user() ) { ?>
	<header id="header" role="banner">
		<h1 class="brand"><a href="<?php echo home_url(); ?>/"><?php bloginfo( 'name' ); ?></a></h1>
		<?php if( get_bloginfo( 'description' ) != '' ) { ?>
			<h2 class="description"><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'about' ) ) ); ?>"><?php echo get_bloginfo( 'description' ); ?></a></h2>
		<?php } ?>
	</header>
<?php } ?>

<?php if ( ! wp_is_mobile() ) {
	get_template_part( 'templates/nav', 'primary' );
}