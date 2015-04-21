<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php // Desktop Menu
if ( ! wp_is_mobile() ) { ?>
	<div class="sticky hide-for-small">
		<nav id="primary_navigation" class="top-bar" role="navigation" data-topbar>
			<?php // Add Company Name to Menubar if logo is not being shown
			if ( ! is_front_page() && ! groundup_is_new_user() ) { ?>
				<ul class="title-area">
					<li class="name"><h1><a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a></h1></li>
				</ul>
			<?php } ?>
			<section class="top-bar-section">
				<?php wp_nav_menu( array(
					'theme_location' => 'primary',
					'container' => false,
					'menu_class' => 'right',
					'walker'=> new Upandup_Topbar_Walker,
				) ); ?>
			</section>
		</nav>
		<?php // See if secondary_menu has any items
		$locations = get_nav_menu_locations();
		$menu_object = get_term( $locations['secondary'], 'nav_menu' );
		if ( $menu_object->count != 0 ) { ?>
			<nav class="second-bar" role="navigation" data-topbar>
				<section class="top-bar-section">
					<?php wp_nav_menu( array(
						'theme_location' => 'secondary_menu',
						'menu_class' => 'right',
						'walker'=> new Upandup_Topbar_Walker,
					) ); ?>
				</section>
			</nav>
		<?php } ?>
	</div>
<?php } ?>

<nav class="tab-bar show-for-small">
	<section class="left-small">
		<a class="left-off-canvas-toggle menu-button"><span></span></a>
	</section>
	<section class="middle tab-bar-section">
		<h1 class="title"><a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a></h1>
	</section>
	<section class="right-small">
		<a class="right-off-canvas-toggle menu-button"><span></span></a>
	</section>
</nav>