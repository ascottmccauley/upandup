<?php
/**
 * Custom Post Type - Resource
 *
 * Taxonomy:
 * resource_category - hierarchical
 *
 * 
 * Meta:
 * thumbnail
 * _resource_media - int - attachment ID of downloadable resource
 * 
**/

// Register Post Type
function upandup_resource_register_post_types() {
	$rewrite = apply_filters('upandup_resource_post_type_rewrite', array(
		'slug'       => 'resource',
		'with_front' => false,
		'pages'      => true,
		'feeds'      => true,
		'ep_mask'    => EP_PERMALINK,
	));
	
	$supports = apply_filters('upandup_resource_post_type_supports', array(
		'title',
		'thumbnail',
		'excerpt'
	));
	
	$labels = apply_filters('upandup_resource_post_type_labels', array(
		'name'               => __( 'Resources',                   'upandup' ),
		'singular_name'      => __( 'Resource',                    'upandup' ),
		'menu_name'          => __( 'Resources',                   'upandup' ),
		'name_admin_bar'     => __( 'Resource',          					 'upandup' ),
		'add_new'            => __( 'Add New',                  	 'upandup' ),
		'add_new_item'       => __( 'Add New Resource',            'upandup' ),
		'edit_item'          => __( 'Edit Resource',               'upandup' ),
		'new_item'           => __( 'New Resource',                'upandup' ),
		'view_item'          => __( 'View Resource',               'upandup' ),
		'search_items'       => __( 'Search Resources',            'upandup' ),
		'not_found'          => __( 'No resources found',          'upandup' ),
		'not_found_in_trash' => __( 'No resources found in trash', 'upandup' ),
		'all_items'          => __( 'Resources',                   'upandup' ),
	));
	
	$args = apply_filters('upandup_resource_post_type_args', array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'exclude_from_search' => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-welcome-add-page',
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => false,
		'has_archive'         => true,
		'query_var'           => 'resource',
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'rewrite'							=> $rewrite,
		'supports'						=> $supports,
		'labels'							=> $labels,
	));
	
	register_post_type('resource', $args);
}
add_action( 'init', 'upandup_resource_register_post_types' );

// Register Taxonomy
function upandup_resource_register_taxonomies() {
	$rewrite = apply_filters('upandup_resource_category_taxonomy_rewrite', array(
		'slug'         => 'resource-category',
		'with_front'   => false,
		'hierarchical' => true,
		'ep_mask'      => EP_NONE
	));
	
	$labels = apply_filters('upandup_resource_category_taxonomy_labels', array(
		'name'                       => __( 'Resource Categories',                  				 'upandup' ),
		'singular_name'              => __( 'Resource Category',                    				 'upandup' ),
		'menu_name'                  => __( 'Resource Categories',                           'upandup' ),
		'name_admin_bar'             => __( 'Resource Category',                          	 'upandup' ),
		'search_items'               => __( 'Search Resource Categories',                    'upandup' ),
		'popular_items'              => __( 'Popular Resource Categories',                   'upandup' ),
		'all_items'                  => __( 'All Resource Categories',                       'upandup' ),
		'edit_item'                  => __( 'Edit Resource Category',                      	 'upandup' ),
		'view_item'                  => __( 'View Resource Category',                      	 'upandup' ),
		'update_item'                => __( 'Update Resource Category',                   	 'upandup' ),
		'add_new_item'               => __( 'Add New Resource Category',                   	 'upandup' ),
		'new_item_name'              => __( 'New Resource Category Name',                  	 'upandup' ),
		'separate_items_with_commas' => __( 'Separate resource categories with commas',      'upandup' ),
		'add_or_remove_items'        => __( 'Add or remove resource categories',             'upandup' ),
		'choose_from_most_used'      => __( 'Choose from the most used resource categories', 'upandup' ),
		'not_found'                  => __( 'No resource categories found',                  'upandup' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
	));
	
	$args = apply_filters('upandup_resource_category_taxonomy_args', array(
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'query_var'         => 'resource_category',
		'rewrite'						=> $rewrite,
		'labels'						=> $labels,
	));
	
	register_taxonomy('resource_category', array('resource'), $args);
}
add_action( 'init', 'upandup_resource_register_taxonomies' );

// Register Meta
function upandup_resource_register_meta() {
	register_meta( 'post', '_resource_media', 'absint', '__return_true' ); //stores ID of attachment
}
add_action( 'init', 'upandup_resource_register_meta' );

// Add Meta Boxes
function upandup_resource_add_meta_boxes() {
	add_meta_box('upandup_resource_media_meta_box', __('Resource Media', 'upandup'), 'upandup_resource_media_callback', 'resource', 'side', 'low');
}
add_action( 'add_meta_boxes', 'upandup_resource_add_meta_boxes' );

// Meta Box HTML
function upandup_resource_media_callback($post) {
	wp_nonce_field('upandup_nonce_resource_action','upandup_nonce');
	$resource_media = get_post_meta($post->ID, '_resource_media', true); ?>
	<div class="metabox">
		<p>Upload/Select the Resource</p>
		<div class="resource-media">
			<?php if ($resource_media) {
				$upload_iframe_src = esc_url( get_upload_iframe_src('image', $post->ID ) );
				$attach_thumb = wp_get_attachment_image( $resource_media, array(233,233), true );
			  $attach_full =  wp_get_attachment_url( $resource_media );
			  echo '<p class="hide-if-no-js"><a title="' . __('Upload', 'upandup') . '" href="" class="media-upload-button">' . $attach_thumb . '</a></p>';
			  echo '<p>Title: ' . get_the_title( $resource_media ) . '</p>';
			  echo '<p><a target="_blank" href="' . $attach_full . '">URL:</a> <input type="text" readonly onclick="this.select()" value="' . $attach_full . '" /></p>';
			} ?>
		</div>
		<p>
			<input type="hidden" class="mediaID" id="mediaID" name="mediaID" value="<?php echo $resource_media; ?>" /> 
			<input class="media-upload-button"  type="button" value="Choose File" />
		</p>
	</div>
<?php }

// Save Meta Data
function upandup_resource_update_post_meta($post_id) {
	// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'upandup_nonce' ] ) && wp_verify_nonce( $_POST[ 'upandup_nonce' ], 'upandup_nonce_resource_action' ) ) ? 'true' : 'false';
 
	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}
		
	if(isset($_POST['mediaID'])) {
		update_post_meta($post_id, '_resource_media', $_POST['mediaID']);
	}
}
add_action('save_post', 'upandup_resource_update_post_meta');

// jQuery for upload button
function upandup_resource_media_js() {
	global $post_type;
	if( $post_type == 'resource' ) {
		wp_enqueue_media(); ?>
	  <script type="text/javascript">
	  	jQuery(document).ready(function($){
	  	  // Uploading files
	  	  var file_frame;
	  	  
  	    jQuery('.media-upload-button').live('click', function( event ){
  	  
	      event.preventDefault();
	  
	      // If the media frame already exists, reopen it.
	      if ( file_frame ) {
        file_frame.open();
        return;
	      }
	  
	      // Create the media frame.
	      file_frame = wp.media.frames.file_frame = wp.media({
        title: jQuery( this ).data( 'uploader_title' ),
        button: {
          text: jQuery( this ).data( 'uploader_button_text' ),
        },
        multiple: false,  // Set to true to allow multiple files to be selected
	      });
	  
	      // When an image is selected, run a callback.
	      file_frame.on( 'select', function() {
	        // We set multiple to false so only get one image from the uploader
	        attachment = file_frame.state().get('selection').first().toJSON();
	        jQuery('.mediaID').val(attachment.id);
	        if(attachment.type == 'image') {
	        	jQuery('.resource-media').html('<p><a class="media-upload-button" href=""><img width="266" height="266" src="'+ attachment.sizes.thumbnail.url + '" /></a></p><p><a target="_blank" href="' + attachment.url + '">URL:</a> <input type="text" readonly onclick="this.select()" value="' + attachment.url + '" /></p>');
	        }else {
	        	jQuery('.resource-media').html('<p><a class="media-upload-button" target="_blank" href=""><img src="'+ attachment.icon + '" /></a></p><p>Title: ' + attachment.title + '.' + attachment.subtype + '</p><p><a target="_blank" href="' + attachment.url + '">URL:</a> <input type="text" readonly onclick="this.select()" value="' + attachment.url + '" /></p>');
	        }
	  			
	      });
	  		
	      file_frame.open();
  	  });
	  });
	 </script> 
	<?php }
}
add_action('admin_head', 'upandup_resource_media_js');
