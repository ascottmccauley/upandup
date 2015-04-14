<?php 
/**
 * Template for the resource_category taxonomy "Lead Tests"
 *
 * @package groundup
 * @subpackage upandup
**/
 ?>
<?php get_header(); ?>
<main id="main" role="main">
	<?php if ( have_posts() ) {
		// Display Header
		$page_title = get_the_archive_title();
		// Remove Everything Before first :
		if ( ( $pos = strpos( $page_title, ':' ) ) !== false ) {
		   $page_title = substr($page_title, $pos + 1);
		}
		if ( $page_title != '' ) { ?>
			<header><h3 class="text-center"><?php echo $page_title; ?></h3></header>
		<?php } ?>
		<div class="row">
			<div class="small-6 columns">
				<form role="search" method="get" id="searchbar" action="<?php echo home_url('/'); ?>">
					<div class="row collapse postfix-round">
						<div class="small-9 columns">
							<input id="search" class="search-query round" type="text" value="<?php if ( is_search() ) { echo get_search_query(); } ?>" name="s" placeholder="<?php _e(' Search Lead Tests', 'fin'); ?>" required>
						</div>
						<div class="small-3 columns">
							<button class="button postfix round secondary" class="searchsubmit"><i class="icon-search"></i><span class="hide"><?php _(' search'); ?></span></button>
						</div>
					</div>
					<input type="hidden" name="tax_query[resource_category]" value="lead-tests" />
				</form>
			</div>
		</div>
		<?php while( have_posts() ) : the_post();
			$post_type = get_post_type();
			$post_format = get_post_format();
			if ( $post_type != 'post' ) {
				$type = $post_type;
			} else {
				$type = $post_format;
			}
			get_template_part( 'templates/excerpt', $type );
		endwhile;
		get_template_part( 'templates/pagination' );
	} ?>
</main>
<?php get_sidebar();
get_footer(); ?>