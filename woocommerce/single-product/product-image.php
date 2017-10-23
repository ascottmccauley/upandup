<?php
/**
 * Single Product Image
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.1.0
**/

/** Changes
* use custom function upandup_woo_img_url to just get image directly instead of attachment
*
*
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $woocommerce, $product;

$thumbnail = upandup_woo_img_url( 'medium' );
if ( ! empty( $thumbnail ) ) {
	$full_size_image   = upandup_woo_img_url( 'original' );
	$full_size_image_dimensions = getimagesize( $full_size_image );
	$image_title = basename( $thumbnail, '.jpg' );

	$attributes = array(
		'title'                   => $image_title,
		'data-src'                => $full_size_image,
		'data-large_image_width'  => $full_size_image_dimensions[0],
		'data-large_image_height' => $full_size_image_dimensions[1],
	);

	$html  = '<div class="th woocommerce-product-gallery__image "><a href="#">';
	$html .= '<img src="' . $thumbnail . '" ' . urldecode( http_build_query( $attributes, '', ' ' ) ) . ' />';
	$html .= '</a></div>';
	?>

	<div class="small-12 medium-6 medium-pull-6 columns gallery images">

		<?php

		echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, get_post_thumbnail_id( $post->ID ) );

		do_action( 'woocommerce_product_thumbnails' ); ?>

	</div>
<?php } ?>