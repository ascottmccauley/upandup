<?php
/**
 * Woo-Functions
 * Any custom functions related specifically to a WooCommerce setup
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/************************
 * Cleanup
************************/
// Make shop private
function upandup_shop_private() {
	if ( ! is_user_logged_in() ) {
		if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
			$redirect = home_url() . $_SERVER["REQUEST_URI"];
			wp_redirect( wp_login_url( $redirect ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'upandup_shop_private' );

// Change template for search.php to archive-product
function upandup_search_template( $template ) {
	if ( is_search() ) {
		return wc_get_template_part( 'archive', 'product' );
	} else {
		return $template;
	}
}
add_filter( 'template_include', 'upandup_search_template' );

// change posts_per page for search results
function upandup_search_per_page( $limits ) {
	global $wp_query;
	if ( is_search() ) {
		$wp_query->query_vars['posts_per_page'] = 30;
		$wp_query->query_vars['post_type'] = 'product';
	}
	return $limits;
}
add_filter( 'post_limits', 'upandup_search_per_page' );

// Remove Default Woocommerce Styling
add_filter( 'woocommerce_enqueue_styles', '__return_false' );

// Remove Default Woocommerce Image Sizes
function upandup_woo_remove_image_size() {
	$default_sizes = array( 'shop_thumbnail', 'shop_catalog', 'shop_single' );
	foreach ( $default_sizes as $size ) {
		remove_image_size( $size );
	}
}
add_action( 'init', 'upandup_woo_remove_image_size' );

// Remove WooCommerce Styles
function upandup_woo_remove_styles() {
	wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
}
add_action( 'wp_print_styles', 'upandup_woo_remove_styles', 100 );

// Remove WooCommerce js
function upandup_woo_remove_js() {
	wp_dequeue_script( 'prettyPhoto' );
	wp_dequeue_script( 'prettyPhoto-init' );
	wp_dequeue_script( 'fancybox' );
	wp_dequeue_script( 'enable-lightbox' );
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
	if ( is_search() ) {
		$sidebars = array();
	} elseif ( is_product() ) {
		$sidebars = array( 'Product' );
	} elseif ( woocommerce_products_will_display() ) {
		$sidebars = array( 'Products', 'Shop' );
	} elseif ( is_woocommerce() ) {
		$sidebars = array( 'Shop' );
	} elseif ( is_cart() || is_checkout() ) {
		$sidebars = array( 'Cart' );
	}
	return $sidebars;
}
add_filter( 'groundup_sidebars', 'upandup_woo_sidebars' );

// Remove wp-admin and admin-bar for customers and non-admins
update_option( 'woocommerce_lock_down_admin', 'yes' );

/************************
 * text changes
************************/
function upandup_woo_gettext( $translated_text, $text, $domain ) {
	switch ( $translated_text ) {
		case 'Price' :
			$translated_text = __( 'MSRP', 'woocommerce' );
			break;
	}
	return $translated_text;
}
add_filter( 'gettext', 'upandup_woo_gettext', 20, 3 );

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

// Move Breadcrumbs
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
// add_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 5, 0 );

// Add login/account and mini-cart to secondary-navbar and product categories to primary-navbar
function upandup_woo_add_to_nav( $items, $args ) {

	$menu_object = wp_get_nav_menu_object( $args->menu );

	if ( $menu_object->slug == 'primary' ) {
		if ( ! is_user_logged_in() ) {
			$items .= '<li class="login"><a href="' . wp_login_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">Log In</a></li>';
		} else {
			$args = array(
				'taxonomy' => 'product_cat',
				'order' => 'ASC',
				'orderby' => 'name',
				'show_count' => false,
				'hierarchical' => true,
				'title_li' => '',
				'hide_empty' => true,
				'show_option_none' => '',
				'echo' => 0,
			);
			$categories = wp_list_categories( $args );
			$shop_link = '<a href="' . get_permalink( woocommerce_get_page_id( 'shop' ) ) . '">Products</a>';
			$active = is_woocommerce() ? ' active': '';
			$items = '<li class="has-dropdown' . $active . '">' . $shop_link . '<ul class="dropdown">' . $categories . '</ul></li>' . $items;
		}
	}

	if ( $menu_object->slug == 'secondary' ) {
		$menu_object->count = $menu_object->count + 2;
		if ( is_user_logged_in() ) {
			// Add Product Search
			$items .= '<li class="search has-form">' . get_product_search_form( $echo = false ) . '</li>';

			// Add Login/Account Link
			$items .= '<li class="account"><a href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . '">Account</a></li>';
			$items .= '<li><a href="' . wp_logout_url( home_url() ) . '">Log Out</a></li>';
		}

		// Add Minicart Link
		$cart_quantity = sizeof( WC()->cart->get_cart() );
		if ( $cart_quantity > 0 ) {
			$cart_subtotal = WC()->cart->get_cart_subtotal();
			$checkout_link = WC()->cart->get_cart_url();
			$items .= '<li class="cart"><a class="cart-contents" href="' . $checkout_link . '" title="' .  __( 'View your shopping cart' ) . '"><i class="icon-shopping-cart"></i> ' . $cart_quantity . ' items</a></li>';
		} else {
			$items .= '<li class="cart"><a class="cart-contents empty"></a></li>';
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_items', 'upandup_woo_add_to_nav', 8, 2 );

// Ajaxify mini-cart
function woocommerce_header_add_to_cart_fragment( $fragments ) {
	ob_start();
	$cart_quantity = sizeof( WC()->cart->get_cart() );
	if ( $cart_quantity > 0 ) {
		$cart_subtotal = WC()->cart->get_cart_subtotal();
		$checkout_link = WC()->cart->get_cart_url();
		echo '<a class="cart-contents" href="' . $checkout_link . '" title="' .  __( 'View your shopping cart' ) . '"><i class="icon-shopping-cart"></i> ' . $cart_quantity . ' items</a>';
	} else {
		echo '<a class="cart-contents empty"></a>';
	}
	$fragments['a.cart-contents'] = ob_get_clean ();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment' );

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

// Replace placeholder image with a search for the correct `sku.jpg` image.
// If no image is found
function upandup_woo_img_url( $size = 'thumbnail', $_product = '' ) {
	global $post;
	global $product;

	if ( $_product == '' ) {
		$_product = $product;
	} elseif ( is_int( $_product ) ) {
		// try getting product by id
		$_product = wc_get_product( $_product );
	}
	if ( !$_product ) {
		return '';
	}

	if ( $size == 'full' || $size == 'large' ) {
		$img_url = wp_get_attachment_url( get_post_thumbnail_id() );
	} else {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size );
		if ( ! empty( $img ) ) {
			$img_url = $img[0];
		}
	}

	// check /media/products/size/sku.jpg
	if ( empty( $img_url ) ) {
		// scan media folder for sku.jpg
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['path'];
		$upload_url = $upload_dir['url'];
		$sku = $_product->get_sku();
		// special case: if sku ends in "n" but not "-n", grab parent image (without "n");
		if ( substr( $sku,  -1 ) == 'n' && substr( $sku,  -2 ) != '-n' ) {
			// remove trailing 'n'
			$sku = rtrim( $sku, 'n' );
		}

		// Change naming conventions to 'large' and 'thumb'
		if ( $size == 'full' ) {
      $size = 'large';
		} elseif ( $size == 'thumbnail' ) {
			$size = 'thumb';
		}

		if ( file_exists( $upload_path . '/products/' . $size . '/' . $sku . '.jpg' ) ) {
				$img_url = $upload_url . '/products/' . $size . '/' . $sku . '.jpg';
		} else {
			// load placeholder image
			$img_url = $upload_url . '/products/placeholder.jpg';
		}
	}

	// Check to see if this is a grouped product and load the first child image instead!
	$children = $_product->get_children();
	if ( $children ) {
		$firstChild = wc_get_product( $children[0] );
		if ( $firstChild ) {
			$img_url = upandup_woo_img_url( $size, $children[0] );
		}
	}

	return $img_url;
}

/************************
 * shop page
************************/
// Products per page
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 35;' ), 20 );
// Remove Title from Store Front
add_filter( 'woocommerce_show_page_title', function() { return false; } );
// Remove product count after categories
add_filter( 'woocommerce_subcategory_count_html', function() { return false; } );

// Remove Result Count
remove_action( 'woocommerce_before_shop_loop','woocommerce_result_count', 20);
// Remove Catalog Ordering
remove_action( 'woocommerce_before_shop_loop','woocommerce_catalog_ordering', 30);

// Replace Category Thumbnail
remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
add_action( 'woocommerce_before_subcategory_title', 'upandup_woo_subcategory_thumbnail', 10 );

/**
 * woocommerce_subcategory_thumbnail
 *
 * rewrite the category thumbnail search to look for a category thumbnail or else return a random product thumbnail.
 * TODO: add back in the random product thumbnail feature
**/
if ( ! function_exists( 'upandup_woo_subcategory_thumbnail' ) ) {
	function upandup_woo_subcategory_thumbnail( $category ) {
		$thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true );
		// Get uploaded thumbnail
		if ( $thumbnail_id ) {
			if ( wp_is_mobile() ) {
				$image = wp_get_attachment_image_src( $thumbnail_id, 'small' );
			} else {
				$image = wp_get_attachment_image_src( $thumbnail_id, 'medium' );
			}
			$image = $image[0];
			// Prevent esc_url from breaking spaces in urls for image embeds
			// Ref: http://core.trac.wordpress.org/ticket/23605
			$image = str_replace( ' ', '%20', $image );
			echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $category->name ) . '">';
		} else {
			$args = array(
				'orderby' => 'rand',
				'post_status' => 'publish',
				'post_type' => 'product',
				'numberposts' => 1,
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field' => 'slug',
						'terms' => $category->slug,
					)
				),
			);
			$posts = get_posts( $args );
			if ( $posts ) {
				foreach( $posts as $post ) {
					$image_url = upandup_woo_img_url( 'thumb', $post->ID );
					if ( ! empty ( $image_url ) ) {
						echo '<img src="' . $image_url . '" alt="' . esc_attr( $category->name ) . '">';
					}
				}
			}
		}
	}
}

/************************
 * products
************************/
// Remove *Sale*
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
// Replace thumbnail function
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
// Remove Rating
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
// Remove Price
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

// Add thumbnail first
function upandup_woo_template_loop_product_thumbnail() {
	global $post;
	global $product;

	$img_url = upandup_woo_img_url( 'thumbnail' );
	if ( ! empty( $img_url ) ) {
		echo '<a href="' . get_the_permalink() . '" class="square-thumb" style="background-image: url(\'' . $img_url . '\');"></a>';
	} else {
		echo '<a href="' . get_the_permalink() . '" class="square-thumb placeholder" style="background: white;"></a>';
	}
}
add_action( 'woocommerce_before_shop_loop_item', 'upandup_woo_template_loop_product_thumbnail', 10 );

// Add product info wrapper
function upandup_before_shop_loop_item() {
	echo '<div class="product-info">';
}
add_action( 'woocommerce_before_shop_loop_item', 'upandup_before_shop_loop_item', 20 );

function upandup_after_shop_loop_item() {
	echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item', 'upandup_after_shop_loop_item', 5 );

// remove .button class from "BUY" link and wrapp it in an <h3>
function upandup_woo_loop_add_to_cart_link( $add_to_cart_text, $product ) {
  $add_to_cart_text = str_replace( '">', '"><h3>', $add_to_cart_text );
	$add_to_cart_text = str_replace( '</a>', '</h3></a>', $add_to_cart_text );
	$add_to_cart_text = str_replace( '"button ', '"', $add_to_cart_text );
	return $add_to_cart_text;
};

// add the filter
add_filter( 'woocommerce_loop_add_to_cart_link', 'upandup_woo_loop_add_to_cart_link', 10, 2 );

// Add parent category to body class
// Add 'parent' class if only 1 category exists
function upandup_woo_body_class( $classes ) {
	global $wp_query;
	global $post;

	if ( is_product() ) {
		$categories = get_the_terms( $post->ID, 'product_cat' );
		if ( is_array( $categories ) && ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				if ( $category->parent == 0 ) {
					$classes[] = $category->slug;
				}
			}
		}
	}

	if ( is_product_category() ) {
		$current_category = $wp_query->get_queried_object();
		// add 'parent' class for top-level categories
		if ( $current_category->parent == 0 ) {
			if ( ! woocommerce_products_will_display() ) {
				$classes[] = 'parent';
			}
			$classes[] = $current_category->slug;
		} else {
			$parent = get_term_by( 'term_taxonomy_id', $current_category->parent, $current_category->taxonomy );
			$classes[] = $parent->slug;
		}
	}

	if ( is_shop() ) {
		$classes[] = 'shop';
	}

	return $classes;
}
add_filter( 'body_class', 'upandup_woo_body_class' );

// Add topmost category to html class
// Add 'parent' class if only 1 category exists
function upandup_woo_html_class( $output ) {
	global $wp_query;
	global $post;

	$classes = array();

	if ( is_product() ) {
		$categories = get_the_terms( $post->ID, 'product_cat' );
		if ( is_array( $categories ) && ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				if ( $category->parent == 0 ) {
					$output .= 'class="' . $category->slug .'"';
				}
			}
		}
	}

	if ( is_product_category() ) {
		$current_category = $wp_query->get_queried_object();
		if ( $current_category->parent == 0 && ! woocommerce_products_will_display() ) {
			$output .= 'class="parent ' . $current_category->slug .'"';
		} else {
			$parent = get_term_by( 'term_taxonomy_id', $current_category->parent, $current_category->taxonomy );
			$output .= 'class="' . $parent->slug . '"';
		}
	}

	return $output;
}
add_filter( 'language_attributes', 'upandup_woo_html_class' );


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

// Change grouped products to order alphanumerically
function upandup_woo_grouped_children_args( $args ) {
	$args['orderby'] = 'title ';
	return $args;
}
add_filter( 'woocommerce_grouped_children_args', 'upandup_woo_grouped_children_args' );

// If there is only 1 image (and it is 'featured') do not include gallery
function upandup_woo_check_images() {
	global $product;
	$attachment_ids = $product->get_gallery_attachment_ids();
	if ( count( $attachment_ids ) < 1 ) {
		remove_action( 'woocommerce_before_single_product_summary','woocommerce_show_product_images', 20 );
	} elseif ( count( $attachment_ids ) == 1 && has_post_thumbnail() ) {
		// Check to see if only image in gallery is the featured thumbnail
		if ( $attachment_ids[0] == get_post_thumbnail_id() ) {
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
		}
	}
}
add_action( 'woocommerce_before_single_product_summary', 'upandup_woo_check_images', 5 );

// Add Product Attributes, Size, Weight and Tags
function upandup_woo_product_attributes() {
	global $product;
	$attributeList = '';

	if ( $product->enable_dimensions_display() ) {
		if ( $product->has_weight() ) {
			$attributeList .= '<li>' . $product->get_weight() . ' ' . esc_attr( get_option( 'woocommerce_weight_unit' ) ) . '</li>';
		}
		if ( $product->has_dimensions() ) {
			$unit = get_option( 'woocommerce_dimension_unit' );
			$attributeList .= $product->length ? '<li>Length: <strong>' . $product->length . $unit . '</strong></li>' : '';
			$attributeList .= $product->width ? '<li>Width: <strong>' . $product->width . $unit . '</strong></li>' : '';
			$attributeList .= $product->height ? '<li>Height: <strong>' . $product->height . $unit . '</strong></li>' : '';
		}
	}

	// Get Attributes
	$attributes = $product->get_attributes();
	if ( ! empty( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ($attribute['is_visible']) {
				if ( $attribute['is_taxonomy'] ) {
					$values = wc_get_product_terms( $product->id, $attribute['name'], array( 'fields' => 'names' ) );
				}else {
					// Convert pipes to commas and display values
					$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
				}
				foreach ( $values as $value ) {
					$attributeName = str_replace( 'pa_' , '', $attribute['name'] );
					$attributeName = str_replace( '_' , ' ', $attributeName );
					$attributeName = ucwords( $attributeName );
					$attributeList .= '<li>' . $attributeName . ': <strong>' . $value . '</strong></li>';
				}
			}
		}
	}
	// Add Tags
	$attributeList .= $product->get_tags( '</strong>, <strong>', '<li>Theme: <strong>', '</strong></li>' );

	if ( $attributeList != '' ) {
		echo '<h4>Attributes</h4>';
		echo '<ul class="product-attributes column-2">';
		echo $attributeList;
		echo '</ul>';
	}
}
add_action( 'woocommerce_single_product_summary', 'upandup_woo_product_attributes', 40 );

// Add to Cart Text
function upandup_woo_add_to_cart_text() {
	global $product;

	$product_type = $product->product_type;
	switch ( $product_type ) {
		case 'external':
			return __( 'Buy', 'woocommerce' );
			break;
		case 'grouped':
			return __( 'Buy', 'woocommerce' );
			break;
		case 'simple':
			return __( 'Buy', 'woocommerce' );
			break;
		case 'variable':
			return __( 'Select Options', 'woocommerce' );
			break;
		default:
			return __( 'More Info', 'woocommerce' );
	}
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'upandup_woo_add_to_cart_text' );
add_filter( 'woocommerce_product_add_to_cart_text', 'upandup_woo_add_to_cart_text' );

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
add_action( 'woocommerce_single_product_summary', 'upandup_woo_product_description', 50 );

// Change Upsell Text - filter added to theme template /single-product/up-sells.php
function upandup_woo_upsell_text($text) {
	return '<h4>Similar Products</h4>';
}
add_filter( 'upandup_woo_upsell_text', 'upandup_woo_upsell_text' );

/************************
 * cart page
************************/
// Remove Cart Totals
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals' );

// Add the checkout link
add_action( 'woocommerce_cart_actions', 'woocommerce_button_proceed_to_checkout' );

// Add 'Empty Cart' check
function upandup_woo_empty_cart() {
	global $woocommerce;

	if ( isset( $_GET['empty-cart'] ) ) {
		$woocommerce->cart->empty_cart();
	}
}
add_action( 'init', 'upandup_woo_empty_cart' );

/************************
 * checkout page
************************/
// Move Shipping Choice to after Order Notes:
add_action( 'woocommerce_after_order_notes', 'wc_cart_totals_shipping_html', 20 );

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

/************************
 * account page
************************/
function upandup_woo_recent_products() {
	// get recent orders
	$customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
		'numberposts' => 10,
		'meta_key'    => '_customer_user',
		'meta_value'  => get_current_user_id(),
		'post_type'   => wc_get_order_types( 'view-orders' ),
		'post_status' => array_keys( wc_get_order_statuses() ),
	) ) );
	if ( $customer_orders ) {
		$ordered_products = array();
		foreach ( $customer_orders as $customer_order ) {
			$order = wc_get_order( $customer_order );
			// get products
			foreach( $order->get_items() as $item ) {
				array_push( $ordered_products, $item['product_id'] );
			}
		}
		if( is_array( $ordered_products ) ) {
			$ordered_products = array_unique( $ordered_products ); ?>
			<div class="medium-6 columns">
				<h2>Recent Products</h2>
				<table>
					<?php foreach ( $ordered_products as $product_id ) {
						$product_data = get_post( $product_id );
						if ( 'product' !== $product_data->post_type ) {
							continue;
						}
						$_product = wc_get_product( $product_data ); ?>
						<tr>
							<td>
								<a href="<?php echo get_permalink( $product_id ); ?>"><?php echo $_product->get_title(); ?></a>
							</td>
							<td>
								<a href="<?php echo esc_url( $_product->add_to_cart_url() ); ?>" class="button tiny">Buy</a>
							</td>
						</tr>
					<?php } ?>
				</table>
			</div>
		<?php }
	}
}
add_action( 'woocommerce_before_my_account', 'upandup_woo_recent_products' );

/************************
 * Store Notices
************************/
function upandup_woo_add_to_cart_message( $message, $product_id ) {
	// get correct category back link
	$terms = wc_get_product_terms( $product_id, 'product_cat', array( 'orderby' => 'parent', 'order' => 'DESC' ) );
	$main_term = apply_filters( 'woocommerce_breadcrumb_main_term', $terms[0], $terms );
	$main_term_link = get_term_link( $main_term );
	$message = '<a href="' . esc_url( $main_term_link ) . '" class="continue third"><i class="icon-arrow-left"></i> Continue Shopping</a>';
	$message .= '<a href="' . wc_get_page_permalink( 'cart' ) . '" class="wc-forward third">View Your Cart <i class="icon-arrow-right"></i></a>';

	return $message;
}
//add_filter( 'wc_add_to_cart_message', 'upandup_woo_add_to_cart_message', 0, 2 ); // TODO

// Plugin Hack
function relevanssi_remove_punct_not_numbers( $a ) {
	$a = strip_tags($a);
	$a = stripslashes($a);

	$a = str_replace("·", '', $a);
	$a = str_replace("…", '', $a);
	$a = str_replace("€", '', $a);
	$a = str_replace("&shy;", '', $a);
	$a = str_replace(chr(194) . chr(160), ' ', $a);
	$a = str_replace("&nbsp;", ' ', $a);
	$a = str_replace('’', ' ', $a);
	$a = str_replace("'", ' ', $a);
	$a = str_replace("’", ' ', $a);
	$a = str_replace("‘", ' ', $a);
	$a = str_replace("”", ' ', $a);
	$a = str_replace("“", ' ', $a);
	$a = str_replace("„", ' ', $a);
	$a = str_replace("´", ' ', $a);
	$a = str_replace("—", ' ', $a);
	$a = str_replace("–", ' ', $a);
	$a = str_replace("×", ' ', $a);

  $a = preg_replace('/((?!(\.\d)):punct:)+/u', ' ', $a);
	//$a = preg_replace('/:punct:+/u', ' ', $a);
  $a = preg_replace('/:space:+/', ' ', $a);
	$a = trim($a);

        return $a;
}

/************************
 * Emails
************************/

// Add account number to customer details in emails
function upandup_woo_email_customer_details_fields( $fields, $sent_to_admin, $order ) {
	$user = get_user_by( 'id', $order->customer_user );
	$account = array(
		'label' => __( 'Account', 'upandup' ),
		'value' => wptexturize( $user->user_login ),
	);
	// Add account to beginning of associative array
	$fields = array( 'account' => $account ) + $fields;
	return $fields;
}
add_filter( 'woocommerce_email_customer_details_fields', 'upandup_woo_email_customer_details_fields', 5, 3 );

/************************
 * Hacks
************************/
// Have relevanssi work with products starting with a decimal
if ( function_exists( 'relevanssi_remove_punct' ) ) {
	remove_filter('relevanssi_remove_punctuation', 'relevanssi_remove_punct');
	add_filter('relevanssi_remove_punctuation', 'relevanssi_remove_punct_not_numbers');
}
// Bring back shipping rates
add_filter( 'woocommerce_enable_deprecated_additional_flat_rates', function() { return true; } );
