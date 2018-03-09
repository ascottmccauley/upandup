<?php
/**
 * Single Product Thumbnails
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-thumbnails.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $product;

$upload_dir = wp_upload_dir();
$upload_path = $upload_dir['path'];
$upload_url = set_url_scheme( $upload_dir['url'] );

$downloads = array();

// get main thumbnail that will only be shown if more thumbnails are found
$thumbnail = upandup_woo_img_url( 'thumbnail' );
if ( ! empty( $thumbnail ) ) {
  $medium_size_image = str_replace( 'thumb', 'medium', $thumbnail );
	$full_size_image = str_replace( 'thumb', 'original', $thumbnail );
	// $source_image = str_replace( 'thumb', 'source', $thumbnail );
	$full_size_image_dimensions = getimagesize( $full_size_image );
	$image_title = basename( $thumbnail, '.jpg' );

  // array_push( $downloads, $source_image );

	$attributes = array(
		'title'                   => $image_title,
		'data-src'                => $full_size_image,
    'data-medium_image'       => $medium_size_image,
		'data-large_image'        => $full_size_image,
		'data-large_image_width'  => $full_size_image_dimensions[0],
		'data-large_image_height' => $full_size_image_dimensions[1],
	);

  $first_html = '<ul class="small-block-grid-4 thumbnails">';
	$first_html .= '<li class="woocommerce-product-gallery__thumbnail">';
	$first_html .= '<a class="th square-thumb active" style="background-image: url(\'' . $thumbnail . '\');"' . urldecode( http_build_query( $attributes, '', ' ' ) ) . ' /></a>';
	$first_html .= '</li>';
}

$sku = $product->get_sku();

// glob uses -[a-z][a-z]* specifically to keep products like ES207 from showing thumbnails for ES207-P
foreach( glob( $upload_path . '/products/thumb/' . $sku . '-[a-z][a-z]*.jpg' ) as $img_path ) {
  $thumbnail = str_replace( $upload_path, $upload_url, $img_path );
  $medium_size_image = str_replace( 'thumb', 'medium', $thumbnail );
	$full_size_image = str_replace( 'thumb', 'original', $thumbnail );
	// $source_image = str_replace( 'thumb', 'source', $thumbnail );
  $full_size_image_dimensions = getimagesize( $full_size_image );
	$image_title     = $image_title = basename( $thumbnail, '.jpg' );

  // array_push( $downloads, $source_image );

  $attributes = array(
		'title'                   => $image_title,
		'data-src'                => $full_size_image,
    'data-medium_image'       => $medium_size_image,
		'data-large_image'        => $full_size_image,
		'data-large_image_width'  => $full_size_image_dimensions[0],
		'data-large_image_height' => $full_size_image_dimensions[1],
	);

	$html  = '<li class="woocommerce-product-gallery__thumbnail">';
	$html .= '<a class="th square-thumb" style="background-image: url(\'' . $thumbnail . '\');"' . urldecode( http_build_query( $attributes, '', ' ' ) ) . ' /></a>';
	$html .= '</li>';

  // show main thumbnail now.
  if( '' != $first_html ) {
    echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $first_html, $thumbnail );
    $first_html = '';
  }

  echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $thumbnail );
}

// For grouped product, add grouped children image
$children = $product->get_children();
if ( $children ) {
	foreach( $children as $child ) {
		$child_product = wc_get_product( $child );
		$thumbnail = upandup_woo_img_url( 'thumbnail', $child_product );
		if( $thumbnail != '' ) {
      $medium_size_image = str_replace( 'thumb', 'medium', $thumbnail );
    	$full_size_image = str_replace( 'thumb', 'original', $thumbnail );
			// $source_image = str_replace( 'thumb', 'source', $thumbnail );
			$full_size_image_dimensions = getimagesize( $full_size_image );
			$image_title     = $image_title = basename( $thumbnail, '.jpg' );

      // array_push( $downloads, $source_image );

			$attributes = array(
				'title'                   => $image_title,
				'data-src'                => $full_size_image,
        'data-medium_image'       => $medium_size_image,
				'data-large_image'        => $full_size_image,
				'data-large_image_width'  => $full_size_image_dimensions[0],
				'data-large_image_height' => $full_size_image_dimensions[1],
			);

			$html  = '<li class="woocommerce-product-gallery__thumbnail">';
			$html .= '<a class="th square-thumb" style="background-image: url(\'' . $thumbnail . '\');"' . urldecode( http_build_query( $attributes, '', ' ' ) ) . ' /></a>';
			$html .= '</li>';

      // show main thumbnail now.
      if( '' != $first_html ) {
        echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $first_html, $thumbnail );
        $first_html = '';
      }

			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $thumbnail );
		}
	}
}

if( '' == $first_html ) {
  echo '</ul>';
}
if( current_user_can( 'download_images' ) && 1 == 2 ) {
	echo '<button class="small secondary downloadImage" data-files="' . $sku . '">Download All Images</button>';
}