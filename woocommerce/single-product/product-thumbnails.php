<?php
/**
 * Single Product Thumbnails
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product, $woocommerce;

$attachment_ids = $product->get_gallery_attachment_ids();

if ( $attachment_ids ) {
	$loop 		= 0;
	$columns 	= apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
	?>
	<ul class="small-block-grid-<?php echo $columns; ?> thumbnails">

		<?php foreach ( $attachment_ids as $attachment_id ) {

			$classes = array( 'zoom' );

			if ( $loop == 0 || $loop % $columns == 0 )
				$classes[] = 'first';

			if ( ( $loop + 1 ) % $columns == 0 )
				$classes[] = 'last';

			$image_link = wp_get_attachment_url( $attachment_id );

			if ( ! $image_link )
				continue;

			$image       = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'small' ) );
			$image_class = esc_attr( implode( ' ', $classes ) );
			$image_title = esc_attr( get_the_title( $attachment_id ) );

			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<li><a href="%s" class="%s" title="%s">%s</a></li>', $image_link, $image_class, $image_title, $image ), $attachment_id, $post->ID, $image_class );

			$loop++;
		}

	?></ul>
	<?php
} else {
	// check /media/sku-$size.jpg and /media/sku.jpg
	$upload_dir = wp_upload_dir();
	$upload_path = $upload_dir['path'];
	$upload_url = $upload_dir['url'];
	$sku = $product->get_sku();
	
	echo '<ul class="thumbnails small-block-grid-4">';
		// glob uses -[a-z][a-z]* specifically to keep products like ES207 from showing thumbnails for ES207-P
		foreach( glob( $upload_path . '/products/thumb/' . $sku . '-[a-z][a-z]*.jpg' ) as $img_path ) {
				$img_url = str_replace( $upload_path, $upload_url, $img_path );
				$img_link = str_replace( 'thumb', 'large', $img_url );
				echo '<li><a href="' . $img_link . '" class="th"><img src="' . $img_url . '"></a></li>';
		}
	echo '</ul>';
}