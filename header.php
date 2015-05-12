<?php
/**
* @package groundup
* @subpackage upandup
 */
?>
<?php get_template_part( 'head' ); ?>
<?php // Homepage or First Visit
if ( is_front_page() || groundup_is_new_user() ) { 
	$class = ' large';
	$logo = get_home_url() . '/assets/img/logo.png';
} else {
	$class = '';
	$logo = get_home_url() . '/assets/img/logo-small.png';
} ?>
<header id="header" role="banner">
	<figure class="logo<?php echo $class; ?>" role="logo">
		<a href="<?php echo get_home_url(); ?>/">
			<img src="<?php echo $logo; ?>">
		</a>
	</figure>
	<h1 class="brand"><a href="<?php echo home_url(); ?>/"><?php bloginfo( 'name' ); ?></a></h1>
	<?php if(get_bloginfo( 'description' ) != '') { ?>
		<h2 class="description"><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'about' ) ) ); ?>"><?php echo get_bloginfo( 'description' ); ?></a></h2>
	<?php } ?>
</header>

<?php get_template_part( 'templates/nav', 'primary'); ?>