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
		$img_link = upandup_woo_img_url( 'full' );
		$image = '<img src="' . $img_url . '">';
		
		$attachment_count = count( $product->get_gallery_attachment_ids() );	
		if ( $attachment_count > 0 ) {
			$gallery = '[product-gallery]';
		} else {
			$gallery = '';
		}
		
		echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" class="zoom th" title="%s" data-rel="prettyPhoto' . $gallery . '">%s</a>', $image_link, $image_title, $image ), $post->ID );
		
		do_action( 'woocommerce_product_thumbnails' ); ?>
	
	</div>	
<?php } ?>