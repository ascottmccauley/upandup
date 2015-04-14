<?php 
/**
 * Woo-Functions
 * Any custom functions related specifically to a WooCommerce setup
**/ 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/************************
 * Cleanup
************************/
// Make shop private
function upandup_shop_private() {
	if ( ! is_user_logged_in() ) {
		if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
			$redirect = urlencode( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
			wp_redirect( home_url() . '/login?redirect_to=' . $redirect );
			exit;
		}
	}
}
add_action( 'template_redirect', 'upandup_shop_private' );

// Remove Default Woocommerce Styling
add_filter( 'woocommerce_enqueue_styles', '__return_false' );

// Remove Default Woocommerce Image Sizes
function upandup_woo_remove_image_size() {
	$default_sizes = array('shop_thumbnail','shop_catalog','shop_single');
	foreach ( $default_sizes as $size ) {
		remove_image_size( $size );
	}
}
add_action( 'upandup_init', 'upandup_woo_remove_image_size' );

// Remove WooCommerce Styles
function upandup_woo_remove_styles() {
	wp_deregister_style( 'woocommerce_prettyPhoto_css' );
}
add_action( 'wp_print_styles', 'upandup_woo_remove_styles', 100 );

// Remove WooCommerce js
function upandup_woo_remove_js() {
	wp_deregister_script( 'prettyPhoto' );
	wp_deregister_script( 'prettyPhoto-init' );
}
add_action( 'wp_print_scripts', 'upandup_woo_remove_js', 100 );

// Conditionally remove WooCommerce js
function upandup_woo_dequeue_script() {
	if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
		wp_dequeue_script( 'wc_price_slider' );
		wp_dequeue_script( 'wc-single-product' );
		wp_dequeue_script( 'wc-add-to-cart' );
		wp_dequeue_script( 'wc-cart-fragments' );
		wp_dequeue_script( 'wc-checkout' );
		wp_dequeue_script( 'wc-add-to-cart-variation' );
		wp_dequeue_script( 'wc-single-product' );
		wp_dequeue_script( 'wc-cart' );
		wp_dequeue_script( 'wc-chosen' );
		wp_dequeue_script( 'woocommerce' );
		wp_dequeue_script( 'prettyPhoto' );
		wp_dequeue_script( 'prettyPhoto-init' );
		wp_dequeue_script( 'jquery-blockui' );
		wp_dequeue_script( 'jquery-placeholder' );
		wp_dequeue_script( 'fancybox' );
		wp_dequeue_script( 'jqueryui' );
	}
}
add_action( 'wp_enqueue_scripts', 'upandup_woo_dequeue_script', 99);

// Create custom sidebar for WooCommerce pages
function upandup_woo_register_sidebars( $sidebars ) {
	$woo_sidebars = array( 'Shop', 'Products', 'Product', 'Cart' );
	return array_merge( $sidebars, $woo_sidebars );
}
add_filter( 'groundup_register_sidebars', 'upandup_woo_register_sidebars' );

// Add custom sidebars to the correct pages
function upandup_woo_sidebars( $sidebars ) {
	if ( is_product() ) {
		$sidebars = array( 'Product' );
	}elseif ( woocommerce_products_will_display() ) {
		$sidebars = array( 'Products', 'Shop' );
	}elseif ( is_woocommerce() ) {
		$sidebars = array( 'Shop' );
	}elseif ( is_cart() || is_checkout() ) {
		$sidebars = array( 'Cart' );
	}
	return $sidebars;
}
add_filter( 'groundup_sidebars', 'upandup_woo_sidebars' );

// Remove wp-admin and admin-bar for customers and non-admins
update_option( 'woocommerce_lock_down_admin', 'yes' );

/************************
 * global
************************/
// Change WooCommerce wrappers
function woocommerce_output_content_wrapper() {
	echo '<main id="main" role="main">';
}
function woocommerce_output_content_wrapper_end() {
	echo '</main>';
}

// Add login/account and mini-cart to secondary-navbar
function upandup_woo_add_to_nav( $items, $args ) {

	$locations = get_nav_menu_locations();
	$menu_object = get_term( $locations[$args->theme_location], 'nav_menu' );
	
	if ( $menu_object->slug == 'primary-menu') {
		if ( ! is_user_logged_in() ) {
			$items .= '<li class="login"><a href="' . get_home_url() . '/login?redirect_to=' . urlencode(get_permalink(wc_get_page_id( 'shop' ))) . '">Log In</a></li>';
		}
	}
	
	if ($menu_object->slug == 'secondary-menu') {
		$menu_object['count'] = $menu_object->count + 2;
		update_term($menu_object);
		if ( is_user_logged_in() ) {
			// Add Product Search
			$items .= '<li class="search has-form">' . get_product_search_form( $echo = false ) . '</li>';
			
			// Add Login/Account Link
			$items .= '<li class="account has-dropdown"><a href="' . get_permalink(wc_get_page_id( 'myaccount' )) . '">Account</a><ul class="dropdown"><li><a href="' . wp_logout_url( home_url() ) . '">Log Out</a></li></ul></li>';
		}
		
		// Add Minicart Link
		$cart_quantity = sizeof(WC()->cart->get_cart());
		if ( $cart_quantity > 0 ) {
			$cart_subtotal = WC()->cart->get_cart_subtotal();
			$checkout_link = WC()->cart->get_cart_url();
			$items .= '<li class="cart"><a href="' . $checkout_link . '"><i class="icon-shopping-cart"></i> ' . $cart_quantity . ' items - '. $cart_subtotal . '</a></li>';	
		}
	}
	
	return $items;
}
add_filter( 'wp_nav_menu_items', 'upandup_woo_add_to_nav', 8, 2 );

// Change "Home" in breadcrumbs to the "Shop" page.
function upandup_woo_breadcrumb_home_url() {
	$shop_page_id = wc_get_page_id( 'shop' );
	$shop_page    = get_post( $shop_page_id );
	return get_permalink( $shop_page );
}
add_filter( 'woocommerce_breadcrumb_home_url', 'upandup_woo_breadcrumb_home_url' );

// Style breadcrumbs to match zurb foundation
function upandup_woo_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter'] = '';
	$defaults['before'] = '<li>';
	$defaults['after'] = '</li>';
	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'upandup_woo_breadcrumb_defaults' );

/************************
 * shop page
************************/
// Remove Title from Store Front
add_filter( 'woocommerce_show_page_title', function() { return false; } );
// Remove product count after categories
add_filter( 'woocommerce_subcategory_count_html', function() { return false; } );

// Remove Result Count
remove_action( 'woocommerce_before_shop_loop','woocommerce_result_count', 20);
// Remove Catalog Ordering
remove_action( 'woocommerce_before_shop_loop','woocommerce_catalog_ordering', 30);
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);

// Change loop-start
function woocommerce_product_loop_start() {
	echo '<ul class="products unstyled small-block-grid-2 medium-block-grid-4 large-block-grid-6">';
}

// Replace Category Thumbnail
remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
add_action( 'woocommerce_after_subcategory_title', 'upandup_woo_subcategory_thumbnail', 10 );

// Remove Thumbnail Price
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

/**
 * woocommerce_subcategory_thumbnail
 *
 * rewrite the category thumbnail search to look for a category thumbnail or else return the first ## product thumbnails it can find.
**/
if ( ! function_exists( 'upandup_woo_subcategory_thumbnail' ) ) {
	function upandup_woo_subcategory_thumbnail( $category, $thumb_quantity = 5 ) {
		$thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true  );
		// Get uploaded thumbnail
		if ( $thumbnail_id ) {
			if ( wp_is_mobile() ) {
				$image = wp_get_attachment_image_src( $thumbnail_id, 'small' );
			}else {
				$image = wp_get_attachment_image_src( $thumbnail_id, 'medium' );
			}
			$image = $image[0];
			// Prevent esc_url from breaking spaces in urls for image embeds
			// Ref: http://core.trac.wordpress.org/ticket/23605
			$image = str_replace( ' ', '%20', $image );
			echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $category->name ) . '">';
		}else {
			$args = array('post_status'=>'publish',
				'post_type' => 'product',
				'numberposts' => $thumb_quantity,
				'tax_query' => array( array( 
					'taxonomy'=>'product_cat',
					'field' => 'id',
					'terms' => $category->term_id					
				) )
			);
			$posts = get_posts( $args );
			echo '<div class="subcategory image-list">';
			foreach ($posts as $post) {
				$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
				$image_url = wp_get_attachment_image_src( $post_thumbnail_id, 'thumbnail' );
				$image_url = $image_url[0];
				echo '<img src="' . $image_url . '" alt="' . esc_attr( $category->name ) . '">';
			}
			echo '</div>';
		}
	}
}

/************************
 * products
************************/
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

function upandup_woo_template_loop_product_thumbnail() {
	global $post;
	$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail' );
	if ( ! empty( $thumbnail ) ) {
		echo '<a href="'. get_the_permalink() . '" class="square-thumb" style="background-image: url(\'' . $thumbnail[0] . '\')"></a>';
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'upandup_woo_template_loop_product_thumbnail', 10);

/************************
 * single-product 
************************/
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_sale_flash', 7 );

add_action( 'woocommerce_after_single_product_summary', 'woocommerce_show_product_images', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
remove_action( 'woocommerce_product_tabs', 'woocommerce_product_reviews_tab', 30 );
remove_action( 'woocommerce_product_tab_panels', 'woocommerce_product_reviews_panel', 30 );

// If there is only 1 image (and it is 'featured') do not include gallery
function upandup_woo_check_images() {
	global $product;
	$attachment_ids = $product->get_gallery_attachment_ids();
	if ( count( $attachment_ids ) < 1) {
		remove_action( 'woocommerce_before_single_product_summary','woocommerce_show_product_images', 20 );
	} elseif ( count( $attachment_ids ) == 1 && has_post_thumbnail() ) {
		// Check to see if only image in gallery is the featured thumbnail
		if ( $attachment_ids[0] == get_post_thumbnail_id() ) {
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
		}
	}
}
add_action( 'woocommerce_before_single_product_summary','upandup_woo_check_images', 5 );

// Add Product Attributes
function upandup_woo_product_attributes() {
	global $product;
	// Get Attributes
	$attributes = $product->get_attributes();
	$attributeList = '';
	if ( ! empty( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ($attribute['is_visible']) {
				if ( $attribute['is_taxonomy'] ) {
					$values = wc_get_product_terms( $product->id, $attribute['name'], array( 'fields' => 'names' ) );
				}else {
					// Convert pipes to commas and display values
					$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
				}
				foreach ($values as $value) {
					$attributeList .= '<li>' . apply_filters( 'woocommerce_attribute', $value, $attribute ) . '</li>';
				}
			}
		}
	}
	// Add Tags
	$attributeList .= $product->get_tags( '</li><li>', '<li>', '</li>' );
	
	if ( $attributeList != '' ) {
		echo '<h4>Attributes</h4>';
		echo '<ul class="product-attributes column-2">';
		echo $attributeList;
		echo '</ul>';
	}
}
add_action( 'woocommerce_single_product_summary','upandup_woo_product_attributes', 40 );

// Add to Cart Text
function upandup_woo_add_to_cart_text() {
	global $product;

	$product_type = $product->product_type;
	switch ( $product_type ) {
		case 'external':
			return __( 'Buy', 'woocommerce' );
			break;
		case 'grouped':
			return __( 'View products', 'woocommerce' );
			break;
		case 'simple':
			return __( 'Buy', 'woocommerce' );
			break;
		case 'variable':
			return __( 'Select options', 'woocommerce' );
			break;
		default:
			return __( 'More Info', 'woocommerce' );
	}
}
add_filter( 'woocommerce_product_single_add_to_cart_text','upandup_woo_add_to_cart_text' );
add_filter( 'woocommerce_product_add_to_cart_text','upandup_woo_add_to_cart_text' );

// Product Description
function upandup_woo_product_description() {
	global $post;
	if ( ! empty( $post->post_content ) ) {
		echo '<h4>Description</h4>';
		echo '<section class="description">';
		the_content();
		echo '</section>';
	}
}
add_action( 'woocommerce_single_product_summary','upandup_woo_product_description', 50 );

// Change Upsell Text - filter added to theme template /single-product/up-sells.php
function upandup_woo_upsell_text($text) {
	return '<h4>Similar Products</h4>';
}
add_filter( 'upandup_woo_upsell_text', 'upandup_woo_upsell_text' );

/************************
 * cart page
************************/


/************************
 * checkout page
************************/
// Remove login form
update_option( 'woocommerce_enable_guest_checkout', 'no' );
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

// Remove Coupons
update_option( 'woocommerce_enable_coupons', 'no' );
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

// Start out with same shipping address
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

// Change # of rows for Order Notes
function custom_override_checkout_fields( $fields ) {
    $fields['order']['order_comments']['custom_attributes'] = array('rows' => 6);
    return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );