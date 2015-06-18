<?php
/**
 * Single Product Image
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.14
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $woocommerce, $product;

$img_url = upandup_woo_img_url( 'medium' );
if ( ! empty( $img_url ) ) { ?>
	
	<div class="small-12 medium-6 medium-pull-6 columns gallery images">
	
		<?php 
		$image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
		$image_link = upandup_woo_img_url( 'large' );
		$image = '<img src="' . $img_url . '" />';
		
		echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" style="background-image: url(%s);" itemprop="image" class="zoom th product-image" title="%s"></a>', $image_link, $img_url, $image_title, $image ), $post->ID );
		
		do_action( 'woocommerce_product_thumbnails' ); ?>
	
	</div>	
<?php } ?>