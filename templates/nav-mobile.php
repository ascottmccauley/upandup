<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php // Mobile Menu
if ( has_nav_menu( 'mobile' ) ) {
	wp_nav_menu( array( 
		'menu' => 'mobile',
		'container' => 'nav',
		'container_id' => 'nav-mobile',
	) );
} elseif ( has_nav_menu( 'primary' ) ) {
	wp_nav_menu( array( 
		'menu' => 'primary',
		'container' => 'nav',
		'container_id' => 'nav-mobile',
	) );
}