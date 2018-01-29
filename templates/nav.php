<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>

<nav class="tab-bar show-for-small">
	<section class="left-small">
		<a class="left-off-canvas-toggle menu-button"><span></span></a>
	</section>
	<section class="middle tab-bar-section">
		<h1 class="title text-center"><a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a></h1>
	</section>
	<section class="right-small">
		<a class="right-off-canvas-toggle menu-button"><span></span></a>
	</section>
</nav>

<div class="sticky hide-for-small">
	<?php // See if secondary_menu has any items
	$menu_object = groundup_get_menu_object( 'Secondary' );
	if ( $menu_object->count > 0 || ( is_user_logged_in() ) ) { ?>
		<nav id="secondary-navigation" class="second-bar top-bar" role="navigation" data-topbar>
			<section class="top-bar-section">
				<?php wp_nav_menu( array(
					'menu' => $menu_object->term_id,
					'link_before' => '',
					'before' => '',
					'after' => '',
					'menu_class' => 'right',
					'walker'=> new Upandup_Topbar_Walker,
					'fallback_cb' => '',
				) ); ?>
			</section>
		</nav>
	<?php } else { ?>
		<h6 class="header-fill text-center">Manufacturers of beautiful products made from precious metals.</h6>
	<?php } ?>
	<?php if ( class_exists( 'Woocommerce' ) ) {
		if ( is_shop() || is_product() || is_product_category() ) {
			woocommerce_breadcrumb();
		}
	} ?>
	<nav id="primary-navigation" class="top-bar" role="navigation" data-topbar>
		<section class="top-bar-section">
			<?php
			$menu_object = groundup_get_menu_object( 'Primary' );
			wp_nav_menu( array(
				'menu' => $menu_object->term_id,
				'link_before' => '',
				'before' => '',
				'after' => '',
				'container' => false,
				'menu_class' => 'left',
				'walker'=> new Upandup_Topbar_Walker,
				'fallback_cb' => '',
			) ); ?>
		</section>
	</nav>
</div>