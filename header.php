<?php
/**
* @package groundup
* @subpackage upandup
 */
?>
<?php get_template_part( 'head' ); ?>
<header id="header" role="banner" class="hide-for-small">
	<figure class="logo" role="logo">
		<a href="<?php echo get_home_url(); ?>/">
			<img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/logo.png'; ?>">
		</a>
	</figure>
	<h1 class="brand"><a href="<?php echo home_url(); ?>/"><?php bloginfo( 'name' ); ?></a></h1>
	<?php if(get_bloginfo( 'description' ) != '') { ?>
		<h2 class="description"><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'about' ) ) ); ?>" target="_self"><?php echo get_bloginfo( 'description' ); ?></a></h2>
	<?php } ?>
</header>

<?php get_template_part( 'templates/nav', 'primary'); ?>
