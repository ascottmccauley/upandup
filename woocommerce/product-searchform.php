<?php 
/**
 * Searchform
 *
 * Default searchform used for anywhere get_product_search_form() is used
 * This essentially mirrors /content/searchform.php with the added hidden input "post_type"="product"
**/ ?>
<form role="search" method="get" id="searchbar" action="<?php echo home_url('/'); ?>">
	<div class="row">
		<div class="row collapse postfix-round">
			<div class="small-9 columns">
				<input id="search" class="search-query round" type="text" value="<?php if (is_search()) { echo get_search_query(); } ?>" name="s" placeholder="<?php _e(' Search', 'fin'); ?>" required>
			</div>
			<div class="small-3 columns">
				<button class="button postfix round secondary" class="searchsubmit"><i class="icon-search"></i><span class="hide"><?php _(' search'); ?></span></button>
			</div>
		</div>
	</div>
</form>