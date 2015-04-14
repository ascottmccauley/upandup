<?php
/**
 * Custom Post Type - Slide
 *
 * Taxonomy:
 * slideshow - slideshows that the current slide is present in
**/

// Register Post Type
function upandup_slide_register_post_types() {
	$rewrite = apply_filters('upandup_slide_post_type_rewrite', array(
		'slug'       => 'slide',
		'with_front' => false,
		'pages'      => true,
		'feeds'      => true,
		'ep_mask'    => EP_PERMALINK,
	));
	
	$supports = apply_filters('upandup_slide_post_type_supports', array(
		'title',
		'thumbnail',
		'excerpt'
	));
	
	$labels = apply_filters('upandup_slide_post_type_labels', array(
		'name'               => __( 'Slides',                   'upandup' ),
		'singular_name'      => __( 'Slide',                    'upandup' ),
		'menu_name'          => __( 'Slides',                  	'upandup' ),
		'name_admin_bar'     => __( 'Slide',          					'upandup' ),
		'add_new'            => __( 'Add New',                  'upandup' ),
		'add_new_item'       => __( 'Add New Slide',            'upandup' ),
		'edit_item'          => __( 'Edit Slide',               'upandup' ),
		'new_item'           => __( 'New Slide',                'upandup' ),
		'view_item'          => __( 'View Slide',               'upandup' ),
		'search_items'       => __( 'Search Slides',            'upandup' ),
		'not_found'          => __( 'No slides found',          'upandup' ),
		'not_found_in_trash' => __( 'No slides found in trash', 'upandup' ),
		'all_items'          => __( 'Slides',                   'upandup' ),
	));
	
	$args = apply_filters('upandup_slide_post_type_args', array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-images-alt2',
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => false,
		'has_archive'         => false,
		'query_var'           => 'slide',
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'rewrite'							=> $rewrite,
		'supports'						=> $supports,
		'labels'							=> $labels,
	));
	
	register_post_type('slide', $args);
}
add_action( 'init', 'upandup_slide_register_post_types' );

// Register Taxonomy
function upandup_slide_register_taxonomies() {
	$rewrite = apply_filters('upandup_slideshow_taxonomy_rewrite', array(
		'slug'         => 'slideshow',
		'with_front'   => false,
		'hierarchical' => false,
		'ep_mask'      => EP_NONE
	));
	
	$labels = apply_filters('upandup_slideshow_taxonomy_labels', array(
		'name'                       => __( 'Slideshows',                  					'upandup' ),
		'singular_name'              => __( 'Slideshow',                    				'upandup' ),
		'menu_name'                  => __( 'Slideshows',                           'upandup' ),
		'name_admin_bar'             => __( 'Slideshow',                            'upandup' ),
		'search_items'               => __( 'Search Slideshows',                    'upandup' ),
		'popular_items'              => __( 'Popular Slideshows',                   'upandup' ),
		'all_items'                  => __( 'All Slideshows',                       'upandup' ),
		'edit_item'                  => __( 'Edit Slideshow',                       'upandup' ),
		'view_item'                  => __( 'View Slideshow',                       'upandup' ),
		'update_item'                => __( 'Update Slideshow',                     'upandup' ),
		'add_new_item'               => __( 'Add New Slideshow',                    'upandup' ),
		'new_item_name'              => __( 'New Slideshow Name',                   'upandup' ),
		'separate_items_with_commas' => __( 'Separate slideshows with commas',      'upandup' ),
		'add_or_remove_items'        => __( 'Add or remove slideshows',             'upandup' ),
		'choose_from_most_used'      => __( 'Choose from the most used slideshows', 'upandup' ),
		'not_found'                  => __( 'No slideshows found',                  'upandup' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
	));
	
	$args = apply_filters('upandup_slideshow_taxonomy_args', array(
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_admin_column' => true,
		'hierarchical'      => false,
		'query_var'         => 'slideshow',
		'rewrite'						=> $rewrite,
		'labels'						=> $labels,
	));
	
	register_taxonomy('slideshow', array('slide'), $args);
}
add_action( 'init', 'upandup_slide_register_taxonomies' );

// Register Meta
function upandup_slide_register_meta() {
	register_meta( 'post', '_slide_url', 'esc_url', '__return_true' );
}
add_action( 'init', 'upandup_slide_register_meta' );

// Add Meta Boxes
function upandup_slide_add_meta_boxes() {
	add_meta_box('upandup_slide_url_meta_box', __('URL Link', 'upandup'), 'upandup_slide_url_callback', 'slide', 'side', 'low');
}
add_action( 'add_meta_boxes', 'upandup_slide_add_meta_boxes' );

// Meta Box HTML
function upandup_slide_url_callback($post) {
	wp_nonce_field('upandup_nonce_slide_action','upandup_nonce');
	$slide_url = get_post_meta($post->ID, '_slide_url', true); ?>
	<div class="metabox">
		<p>
			<label>URL:</label>
			<input type="url" class="slide-url" name="slide-url" value="<?php echo $slide_url; ?>" onblur="var validURL = this.value; if(!~validURL.indexOf('http') && validURL != '' && validURL != 'http://') { validURL ='http://' + validURL; } this.value = validURL;" /> 
		</p>
	</div>
<?php }

// Save Meta Data
function upandup_slide_update_post_meta( $post_id ) {
// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'upandup_nonce' ] ) && wp_verify_nonce( $_POST[ 'upandup_nonce' ], 'upandup_nonce_slide_action' ) ) ? 'true' : 'false';
 
	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}

	if(!empty($_POST['slide-url'])) {
		update_post_meta($post_id, '_slide_url', $_POST['slide-url']);
	}
}
add_action('save_post', 'upandup_slide_update_post_meta');
