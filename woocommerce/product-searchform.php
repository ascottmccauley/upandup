<?php
/**
 * The template for displaying product search form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/product-searchform.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see     http://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<form role="search" method="get" class="searchbar" action="<?php echo home_url('/'); ?>">
	<div class="row">
		<div class="row collapse postfix-round">
			<div class="small-9 columns">
				<input class="search" class="search-query round" type="text" value="<?php if (is_search()) { echo get_search_query(); } ?>" name="s" placeholder="<?php _e(' Search', 'fin'); ?>" required>
			</div>
			<div class="small-3 columns">
				<button class="button postfix round searchsubmit"><i class="icon-search"></i><span class="hide"><?php _(' search'); ?></span></button>
			</div>
		</div>
	</div>
</form>