<?php
/**
 * Single Product Price, including microdata for SEO
 * @version 2.4.9
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;
if( $product->product_type != 'grouped' ){ ?>
	<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<h5 class="price"><small>MSRP: </small><?php echo $product->get_price_html(); ?></h5>
		<meta itemprop="price" content="<?php echo esc_attr( $product->get_price() ); ?>" />
		<meta itemprop="priceCurrency" content="<?php echo esc_attr( get_woocommerce_currency() ); ?>" />
		<link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />
	</div>
<?php } ?>