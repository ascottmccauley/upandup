<?php 
/**
 * Template for the resource_category taxonomy "Lead Tests"
 *
 * Includes a searchbar at the top that only searches lead-tests
 * Searches through the UPLOADS/lead-tests/ dir for all .pdfs and lists them
 * Note: This code assumes that all pdfs are lowercase and have no spaces
 *
 * @package groundup
 * @subpackage upandup
 *
 * TODO: Add pagination
**/
 ?>
<?php // check $_GET[] for a specific lead-test to search for
if ( isset( $_GET['test'] ) ) {
    $test = str_replace( ' ', '', strtolower( strip_tags( $_GET['test'] ) ) );   
} else {
    $test = '';
} ?>
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
				<form role="search" method="get" id="searchbar" action="#">
					<div class="row collapse postfix-round">
						<div class="small-9 columns">
							<input id="search" class="search-query round" type="text" value="<?php echo $test; ?>" name="test" placeholder="<?php _e(' Search Lead Tests', 'groundup'); ?>" required>
						</div>
						<div class="small-3 columns">
							<button class="button postfix round secondary" class="searchsubmit"><i class="icon-search"></i><span class="hide"><?php _(' search'); ?></span></button>
						</div>
					</div>
				</form>
			</div>
		</div>
        <div class="">
    
            <?php // Loop through UPLOADS/lead-tests/ dir for all .pdfs
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['path'] . '/lead-tests/';
            $upload_url = $upload_dir['url'] . '/lead-tests/';
            
            foreach( glob( $upload_path . '*' . $test . '*.pdf' ) as $resource ) {
                $resource = str_replace( $upload_path, '', $resource ); ?>
                <a href="<?php echo $upload_url . $resource ?>" rel="alternate" title="<?php the_title_attribute(); ?>" class="bookmark" target="_blank" type="application/pdf'"><h5 class="entry-title"><i class="icon-file-text"></i> <?php echo str_replace( '.pdf', '', $resource ); ?></h5></a>
            <?php } ?>
        </div>
	<?php } ?>
</main>
<?php get_sidebar();
get_footer(); ?>