<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php // Desktop Menu
if ( has_nav_menu( 'primary' ) && ! wp_is_mobile() ) {
	wp_nav_menu( array( 
		'menu' => 'primary',
		'container' => 'nav',
		'container_id' => 'nav',
	) );
}