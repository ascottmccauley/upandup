<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php
// Shortcodes
$shortcodes = scandir( get_stylesheet_directory()  . '/includes/shortcodes' );
// Remove '.' and '..' from array
$shortcodes = array_slice( $shortcodes, 2 );

foreach ( $shortcodes as $file ) {
	if ( ! $filepath = locate_template( 'includes/shortcodes/' . $file ) ) {
		trigger_error( sprintf( __( 'Error locating %s for inclusion', 'groundup' ), $file ), E_USER_ERROR );
	}
	require_once $filepath;
}

// Post Types
$post_types = scandir( get_stylesheet_directory()  . '/includes/post-types' );
// Remove '.' and '..' from array
$post_types = array_slice( $post_types, 2 );

foreach ( $post_types as $file ) {
	if ( ! $filepath = locate_template( 'includes/post-types/' . $file ) ) {
		trigger_error( sprintf( __( 'Error locating %s for inclusion', 'groundup' ), $file ), E_USER_ERROR );
	}
	require_once $filepath;
}

// WooCommerce
if ( class_exists( 'Woocommerce' ) ) {
	add_theme_support( 'woocommerce' );
	require_once('woocommerce/woo-functions.php');
}

// Add offcanvas wrappers
function upandup_wrapper_start() {
	echo '<div class="off-canvas-wrap" data-offcanvas><div class="inner-wrap">';
}
add_action( 'groundup_inside_body', 'upandup_wrapper_start' );

function upandup_wrapper_end() {
	echo '</div></div>';
}
add_action( 'wp_footer', 'upandup_wrapper_end', 999 );

// Top Bar walker
// Creates a new walker for the `primary` menu to follow the default styling for Zurb Foundation's top bar
class Upandup_Topbar_Walker extends Walker_Nav_Menu {
	function check_current( $classes ) {
		return preg_match( '/(current[-_])|active|dropdown/', $classes );
	}
	
	function start_lvl(&$output, $depth = 0, $args = array()) {
		$output .= "\n<ul class='dropdown'>\n";
	}
	
	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
		$item_html = '';
		parent::start_el( $item_html, $item, $depth, $args );
	
		if (stristr($item_html, 'li class="divider')) {
			$item_html = preg_replace( '/<a[^>]*>.*?<\/a>/iU', '', $item_html );    
		}elseif ( stristr( $item_html, 'li class="nav-header' ) ) {
			$item_html = preg_replace( '/<a[^>]*>(.*)<\/a>/iU', '$1', $item_html );
		}   
		$output .= $item_html;
	}
	
	function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
		$element->is_dropdown = ! empty( $children_elements[$element->ID] );
	
		if ( $element->is_dropdown ) {
			if ( $depth === 0 ) {
				$element->classes[] = 'has-dropdown';
			}elseif ( $depth === 1 ) {
				$element->classes[] = 'has-dropdown';
			}
		}
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
}

// Off Canvas walker
// Creates a new walker for the `mobile` menu to follow the default styling for Zurb Foundation's off-canvas menu
class Upandup_Offcanvas_Walker extends Walker_Nav_Menu {
	function check_current($classes) {
		return preg_match('/(current[-_])|active|dropdown/', $classes);
	}
	
	function start_lvl(&$output, $depth = 0, $args = array()) {
		$output .= "\n<ul class='right-submenu'>\n<li class='back'><a href='#'>Back</a></li>\n";
	}
	
	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
		$item_html = '';
		parent::start_el($item_html, $item, $depth, $args);
	
		if (stristr($item_html, 'li class="divider')) {
			$item_html = preg_replace('/<a[^>]*>.*?<\/a>/iU', '', $item_html);    
		}elseif (stristr($item_html, 'li class="nav-header')) {
			$item_html = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $item_html);
		}   
		$output .= $item_html;
	}
	
	function display_element($element, &$children_elements, $max_depth, $depth = 0, $args, &$output) {
		$element->is_dropdown = !empty($children_elements[$element->ID]);
	
		if ($element->is_dropdown) {
			if ($depth === 0) {
				$element->classes[] = 'has-submenu';
			}elseif ($depth === 1) {
				$element->classes[] = 'has-submenu';
			}
		}
		parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
	}
}

// Add offcanvas menu to #footer
function upandup_offcanvas_footer_menu () {
	echo '<a class="exit-off-canvas"></a>';
	echo '<aside class="right-off-canvas-menu">';
	
	$locations = get_nav_menu_locations();
	$menu_object = get_term( $locations['mobile'], 'nav_menu' );
	if ( $menu_object->count != 0 ) {
		wp_nav_menu( array(
			'theme_location' => 'mobile',
			'menu_class' => 'off-canvas-list',
			'walker'=> new Upandup_Offcanvas_Walker,
		) );
	} else {
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'menu_class' => 'off-canvas-list',
			'walker'=> new Upandup_Offcanvas_Walker,
		) );
	}
	echo '</aside>';
}
add_action( 'wp_footer', 'upandup_offcanvas_footer_menu' );

// Add Extra Logos to #footer
function upandup_extra_logos() {
	echo '<a href="#" class="logo kiddiekraft"><img src="' . get_home_url() . '/assets/img/logo-kiddiekraft.png"></a>';
	echo '<a href="#" class="logo steppingstones"><img src="' . get_home_url() . '/assets/img/logo-steppingstones.png"></a>';
	echo '<a href="http://locketstudio.com" target="_blank" class="logo locketstudio"><img src="' . get_home_url() . '/assets/img/logo-locketstudio.png"></a>';
}
add_action( 'wp_footer', 'upandup_extra_logos' );

// Remove default page_title for pages
function upandup_page_title( $page_title ) {
	if ( is_page() ) {
		$page_title = null;
	}
	return $page_title;
}
add_filter( 'page_title', 'upandup_page_title' );
