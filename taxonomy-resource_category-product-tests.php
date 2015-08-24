<?php
/**
 * Template for the resource_category taxonomy "Product Tests"
 *
 * Includes a searchbar at the top that only searches lead-tests
 * Searches through the UPLOADS/lead-tests/ dir for all .pdfs and lists them
 * Note: This code assumes that all pdfs are lowercase and have no spaces
 *
 * @package groundup
 * @subpackage upandup
 *
**/
 ?>
<?php // only available for logged in users
if ( ! is_user_logged_in() ) {
  $redirect = home_url() . $_SERVER["REQUEST_URI"];
  wp_redirect( wp_login_url( $redirect ) );
 exit;
} ?>
<?php // check $_GET[] for a specific lead-test to search for
if ( isset( $_GET['test'] ) ) {
    $test = str_replace( ' ', '', strtolower( strip_tags( $_GET['test'] ) ) );
} else {
    $test = '';
}
// Check $_GET[] for page number
if ( isset( $_GET['page'] ) ) {
    $page = filter_var( $_GET['page'], FILTER_SANITIZE_NUMBER_INT );
} else {
    $page = 1;
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
							<input id="search" class="search-query round" type="text" value="<?php echo $test; ?>" name="test" placeholder="<?php _e(' Search Lead Tests', 'groundup'); ?>">
						</div>
						<div class="small-3 columns">
							<button class="button postfix round secondary" class="searchsubmit"><i class="icon-search"></i><span class="hide"><?php _(' search'); ?></span></button>
						</div>
					</div>
				</form>
			</div>
		</div>
        <div class="resource-list column-3">

            <?php // Loop through UPLOADS/lead-tests/ dir for all .pdfs
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['path'] . '/lead-tests/';
            $upload_url = $upload_dir['url'] . '/lead-tests/';

            $post_per_page = 40;
            $minId = ($page - 1) * $post_per_page;
            $maxId = ($page * $post_per_page) - 1;
            $total = 0;

            foreach( glob( $upload_path . '*' . $test . '*.pdf' ) as $id => $resource ) {
                $total = $id + 1;
                if ( $id > $maxId || $id < $minId ) {
                   continue;
                }
                $resource = str_replace( $upload_path, '', $resource ); ?>
                <a href="<?php echo $upload_url . $resource ?>" rel="alternate" title="<?php the_title_attribute(); ?>" class="bookmark" target="_blank" type="application/pdf'"><h5 class="entry-title"><i class="icon-file-text"></i> <?php echo str_replace( '.pdf', '', $resource ); ?></h5></a>
            <?php } ?>
        </div>
        <?php // Pagination
        $pagination = paginate_links( array(
            'base'               => add_query_arg( 'page', '%#%' ),
            'format'             => '?page=%#%',
            'total'              => ceil( $total / $post_per_page ),
            'current'            => $page,
            'show_all'           => true,
            'end_size'           => 1,
            'mid_size'           => 2,
            'prev_next'          => true,
            'prev_text'          => '←',
            'next_text'          => '→',
            'type'               => 'list',
            'add_args'           => false,
            'add_fragment'       => '',
            'before_page_number' => '',
            'after_page_number'  => '',
        ) );
        if ( $pagination != null ) { ?>
            <div class="pagination-centered">
                <?php echo $pagination; ?>
            </div>
        <?php }
	} ?>
</main>
<?php get_sidebar();
get_footer(); ?>
