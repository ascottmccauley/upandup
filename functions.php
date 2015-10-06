<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php
// correct path to /media folder
update_option( 'upload_path', str_replace( '/wp/', '/', ABSPATH ) . 'media' );

// Rebuild search index regularly
if ( ! wp_next_scheduled( 'relevanssi_build_index' ) ) {
	wp_schedule_event( time(), 'daily', 'relevanssi_build_index' );
}

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
class Upandup_Topbar_Walker extends Walker_Nav_Menu {
	function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
		$element->has_children = ! empty( $children_elements[ $element->ID ] );
		$element->classes[] = ( $element->current || $element->current_item_ancestor ) ? 'active' : '';
		$element->classes[] = ( $element->has_children && 1 !== $max_depth ) ? 'has-dropdown' : '';
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
	function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$item_html = '';
		parent::start_el( $item_html, $object, $depth, $args );
		// $output .= ( 0 == $depth ) ? '<li class="divider"></li>' : '';
		$classes = empty( $object->classes ) ? array() : (array) $object->classes;
		if ( in_array( 'label', $classes ) ) {
			// $output .= '<li class="divider"></li>';
			$item_html = preg_replace( '/<a[^>]*>(.*)<\/a>/iU', '<label>$1</label>', $item_html );
		}
	if ( in_array( 'divider', $classes ) ) {
		$item_html = preg_replace( '/<a[^>]*>( .* )<\/a>/iU', '', $item_html );
	}
		$output .= $item_html;
	}
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= "\n<ul class=\"sub-menu dropdown\">\n";
	}
}

class Upandup_Offcanvas_Walker extends Walker_Nav_Menu {
	function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
		$element->has_children = ! empty( $children_elements[ $element->ID ] );
		$element->classes[] = ( $element->current || $element->current_item_ancestor ) ? 'active' : '';
		$element->classes[] = ( $element->has_children && 1 !== $max_depth ) ? 'has-submenu' : '';
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
	function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$item_html = '';
		parent::start_el( $item_html, $object, $depth, $args );
		$classes = empty( $object->classes ) ? array() : (array) $object->classes;
		if ( in_array( 'label', $classes ) ) {
			$item_html = preg_replace( '/<a[^>]*>(.*)<\/a>/iU', '<label>$1</label>', $item_html );
		}
		$output .= $item_html;
	}
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= "\n<ul class=\"left-submenu\">\n<li class=\"back\"><a href=\"#\">". __( 'Back', 'upandup' ) ."</a></li>\n";
	}
}

// change children to dropdown for wp_list_categories
function upandup_list_categories( $html ) {
	$html = preg_replace( '/\sclass=\'children\'/', ' class=\'dropdown\'', $html );
	return $html;
}
add_filter( 'wp_list_categories', 'upandup_list_categories' );

// change wp_list_categories css to include 'has-dropdown' when there are children
function upandup_category_css_class( $css_classes, $category, $depth, $args ) {
	// check if the category has any children and add the has-dropdown css_class
	$children = get_term_children( $category->term_id, $category->taxonomy );
	if ( $children ) {
		return array( $category->slug, 'has-dropdown' );
	} else {
		return array( $category->slug );
	}
}
add_filter( 'category_css_class', 'upandup_category_css_class', 1, 4 );

// Add offcanvas menu to #footer
function upandup_offcanvas_footer_menu () {
	echo '<a class="exit-off-canvas"></a>';
	echo '<aside class="right-off-canvas-menu">';

	$menu_object = groundup_get_menu_object( 'Mobile' ) ?: groundup_get_menu_object( 'Primary' );
	if ( $menu_object->count > 0 ) {
		wp_nav_menu( array(
			'menu' => $menu_object->term_id,
			'menu_class' => 'off-canvas-list',
			'walker'=> new Upandup_Offcanvas_Walker,
		) );
	}
	echo '</aside>';
}
add_action( 'wp_footer', 'upandup_offcanvas_footer_menu' );

// Add Extra Logos to #footer
function upandup_extra_logos() {
	echo '<a href="#" class="logo kiddiekraft"><img src="' . get_stylesheet_directory_uri() . '/assets/img/logo-kiddiekraft.png"></a>';
	echo '<a href="#" class="logo steppingstones"><img src="' . get_stylesheet_directory_uri() . '/assets/img/logo-steppingstones.png"></a>';
	echo '<a href="http://locketstudio.com" target="_blank" class="logo locketstudio"><img src="' . get_stylesheet_directory_uri() . '/assets/img/logo-locketstudio.png"></a>';
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

// Add contents of `Footer` page to the #footer
function upandup_footer_contents() {
    $page = get_page_by_title( 'footer' );
    if ( $page ) {
        echo '<div class="footer-content">';
        echo apply_filters( 'the_content', $page->post_content );
        echo '</div>';
    }
}
add_action( 'groundup_inside_footer', 'upandup_footer_contents' );

function upandup_ie_fix() {
	echo '<!--[if lt IE 9]>
  <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
  <script src="//s3.amazonaws.com/nwapi/nwmatcher/nwmatcher-1.2.5-min.js"></script>
  <script src="//html5base.googlecode.com/svn-history/r38/trunk/js/selectivizr-1.0.3b.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.1.0/respond.min.js"></script>
<![endif]-->';
}
add_action( 'wp_head', 'upandup_ie_fix' );

function upandup_modernizr() {
	wp_enqueue_script( 'modernizr', get_stylesheet_directory_uri() . '/assets/js/modernizr.js', null, null, false );
}
add_action( 'wp_enqueue_scripts', 'upandup_modernizr' );

function groundup_defer_script( $tag, $handle ) {
	if ( $handle != 'jquery' && $handle != 'modernizr' ) {
		$tag = str_replace(' src', ' defer="defer" src', $tag );
	}
	return $tag;
}