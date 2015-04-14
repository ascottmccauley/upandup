<?php
/**
 * Single Product Price, including microdata for SEO
 * @version     1.6.4
**/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
global $product; ?>
<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
	<h5 class="price"><?php echo $product->get_price_html(); ?></h5>
	<meta itemprop="price" content="<?php echo $product->get_price(); ?>" />
	<meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
	<link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />
</div>