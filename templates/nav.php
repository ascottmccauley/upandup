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
		<?php // See if secondary_menu has any items
		$locations = get_nav_menu_locations();
		$menu_object = get_term( $locations['secondary'], 'nav_menu' );
		if ( $menu_object->count != 0 || is_user_logged_in() ) { ?>
			<nav id="secondary-navigation" class="second-bar top-bar" role="navigation" data-topbar>
				<section class="top-bar-section">
					<?php wp_nav_menu( array(
						'theme_location' => 'secondary',
						'menu_class' => 'right',
						'walker'=> new Upandup_Topbar_Walker,
					) ); ?>
				</section>
				
			</nav>
		<?php } ?>
		<?php if ( is_shop() || is_product() || is_product_category() ) {
			woocommerce_breadcrumb();
		} ?>
		<nav id="primary-navigation" class="top-bar" role="navigation" data-topbar>
			<section class="top-bar-section">
				<?php wp_nav_menu( array(
					'theme_location' => 'primary',
					'container' => false,
					'menu_class' => 'left',
					'walker'=> new Upandup_Topbar_Walker,
				) ); ?>
			</section>
		</nav>
	</div>
<?php } ?>

<nav class="tab-bar show-for-small">
	<section class="left-small">
		<a class="left-off-canvas-toggle menu-button"><span></span></a>
	</section>
	<?php if ( ! is_front_page() && ! groundup_is_new_user() ) { ?>
		<section class="middle tab-bar-section">
			<h1 class="title"><a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a></h1>
		</section>
	<?php } ?>
	<section class="right-small">
		<a class="right-off-canvas-toggle menu-button"><span></span></a>
	</section>
</nav>