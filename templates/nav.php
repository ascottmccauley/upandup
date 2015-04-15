<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php // Desktop Menu
if ( wp_is_mobile() && has_nav_menu( 'mobile' ) ) {
	wp_nav_menu( array( 'theme_location' => 'mobile', 'container' => 'nav', 'walker'=> new Groundup_Offcanvas_Walker ) );
} else if ( has_nav_menu( 'primary' ) ) { ?>
	<nav id="primary_navigation" class="top-bar" role="navigation" data-topbar>
		<?php if ( !is_front_page() && !groundup_is_new_user() ) { ?>
			<ul class="title-area">
				<li class="name"><h1><a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a></h1></li>
			</ul>
		<?php } ?>
		<section class="top-bar-section">
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'right', 'walker'=> new Groundup_Topbar_Walker ) ); ?>
		</section>
	</nav>
<?php }