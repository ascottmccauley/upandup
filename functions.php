<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php
// Shortcodes
$shortcodes = scandir(get_stylesheet_directory()  . '/includes/shortcodes');
// Remove '.' and '..' from $shortcodes
$shortcodes = array_slice($shortcodes, 2);

foreach ( $shortcodes as $file ) {
	if( !$filepath = locate_template( 'includes/shortcodes/' . $file ) ) {
		trigger_error( sprintf( __( 'Error locating %s for inclusion', 'groundup' ), $file ), E_USER_ERROR );
	}
	require_once $filepath;
}

// Post Types
$post_types = scandir(get_stylesheet_directory()  . '/includes/post-types');
// Remove '.' and '..' from $shortcodes
$post_types = array_slice($post_types, 2);

foreach ( $post_types as $file ) {
	if( !$filepath = locate_template( 'includes/post-types/' . $file ) ) {
		trigger_error( sprintf( __( 'Error locating %s for inclusion', 'groundup' ), $file ), E_USER_ERROR );
	}
	require_once $filepath;
}

// WooCommerce
if(class_exists('Woocommerce')) {
	add_theme_support('woocommerce');
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
add_action( 'wp_footer', 'upandup_wrapper_end' );

// Top Bar walker
// Creates a new walker for the `primary` menu to follow the default styling for Zurb Foundation's top bar
class Groundup_Topbar_Walker extends Walker_Nav_Menu {
	function check_current($classes) {
		return preg_match('/(current[-_])|active|dropdown/', $classes);
	}
	
	function start_lvl(&$output, $depth = 0, $args = array()) {
		$output .= "\n<ul class='dropdown'>\n";
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
				$element->classes[] = 'has-dropdown';
			}elseif ($depth === 1) {
				$element->classes[] = 'has-dropdown';
			}
		}
		parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
	}
}

// Off Canvas walker
// Creates a new walker for the `mobile` menu to follow the default styling for Zurb Foundation's off-canvas menu
class Groundup_Offcanvas_Walker extends Walker_Nav_Menu {
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