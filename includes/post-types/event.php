<?php
/**
 * Custom Post Type - Event
 *
 * Taxonomy:
 * event_category - hierarchical
 *
 * Meta:
 * _event_start_date - YY-MM-DD format
 * _event_end_date - YY-MM-DD format (defaults to _event_start_date if empty)
 * _event_location - string
 * _event_venue - string
**/

// Register Post Type
function event_register_post_types() {
	$rewrite = apply_filters('event_post_type_rewrite', array(
		'slug'       => 'event',
		'with_front' => true,
		'pages'      => true,
		'feeds'      => true,
		'ep_mask'    => EP_PERMALINK,
	));

	$supports = apply_filters('event_post_type_supports', array(
		'title',
		'thumbnail',
		'excerpt'
	));

	$labels = apply_filters('event_post_type_labels', array(
		'name'               => __( 'Events',                   'event' ),
		'singular_name'      => __( 'Event',                    'event' ),
		'menu_name'          => __( 'Events',                   'event' ),
		'name_admin_bar'     => __( 'Event',          					'event' ),
		'add_new'            => __( 'Add New',                  'event' ),
		'add_new_item'       => __( 'Add New Event',            'event' ),
		'edit_item'          => __( 'Edit Event',               'event' ),
		'new_item'           => __( 'New Event',                'event' ),
		'view_item'          => __( 'View Event',               'event' ),
		'search_items'       => __( 'Search Events',            'event' ),
		'not_found'          => __( 'No events found',          'event' ),
		'not_found_in_trash' => __( 'No events found in trash', 'event' ),
		'all_items'          => __( 'Events',                   'event' ),
	));

	$args = apply_filters('event_post_type_args', array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'exclude_from_search' => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-calendar-alt',
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => false,
		'has_archive'         => true,
		'query_var'           => 'event',
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'rewrite'							=> $rewrite,
		'supports'						=> $supports,
		'labels'							=> $labels,
	));

	register_post_type('event', $args);
}
add_action( 'init', 'event_register_post_types' );

// Register Taxonomy
function event_register_taxonomies() {
	$rewrite = apply_filters('event_category_taxonomy_rewrite', array(
		'slug'         => 'event-category',
		'with_front'   => true,
		'hierarchical' => true,
		'ep_mask'      => EP_NONE
	));

	$labels = apply_filters('event_category_taxonomy_labels', array(
		'name'                       => __( 'Event Categories',                  				 'event' ),
		'singular_name'              => __( 'Event Category',                    				 'event' ),
		'menu_name'                  => __( 'Event Categories',                           'event' ),
		'name_admin_bar'             => __( 'Event Category',                          	 'event' ),
		'search_items'               => __( 'Search Event Categories',                    'event' ),
		'popular_items'              => __( 'Popular Event Categories',                   'event' ),
		'all_items'                  => __( 'All Event Categories',                       'event' ),
		'edit_item'                  => __( 'Edit Event Category',                      	 'event' ),
		'view_item'                  => __( 'View Event Category',                      'event' ),
		'update_item'                => __( 'Update Event Category',                   		'event' ),
		'add_new_item'               => __( 'Add New Event Category',                   	'event' ),
		'new_item_name'              => __( 'New Event Category Name',                  	 'event' ),
		'separate_items_with_commas' => __( 'Separate event categories with commas',      'event' ),
		'add_or_remove_items'        => __( 'Add or remove event categories',             'event' ),
		'choose_from_most_used'      => __( 'Choose from the most used event categories', 'event' ),
		'not_found'                  => __( 'No event categories found',                  'event' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
	));

	$args = apply_filters('event_category_taxonomy_args', array(
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'query_var'         => true,
		'rewrite'						=> $rewrite,
		'labels'						=> $labels,
	));

	register_taxonomy('event_category', array('event'), $args);
}
add_action( 'init', 'event_register_taxonomies' );

// Register Meta
function event_register_meta() {
	register_meta( 'post', '_event_start_date', 'sanitize_text_field',  '__return_true' );
	register_meta( 'post', '_event_end_date', 'sanitize_text_field',  '__return_true' );
	register_meta( 'post', '_event_location', 'sanitize_text_field',  '__return_true' );
	register_meta( 'post', '_event_venue', 'sanitize_text_field',  '__return_true' );
	register_meta( 'post', '_event_url', 'esc_url', '__return_true' );
}
add_action( 'init', 'event_register_meta' );

// Add Meta Boxes
function event_add_meta_boxes() {
	add_meta_box('event_date_meta_box', __('Event Date', 'event'), 'event_date_callback', 'event', 'side', 'low');
	add_meta_box('event_location_meta_box', __('Event Location', 'event'), 'event_location_callback', 'event', 'side', 'low');
	add_meta_box('event_url_meta_box', __('URL Link', 'event'), 'event_url_callback', 'event', 'side', 'low');
}
add_action( 'add_meta_boxes', 'event_add_meta_boxes' );

// Meta Box HTML
function event_date_callback($post) {
	wp_nonce_field('nonce_event_action','nonce');
	$event_start_date = get_post_meta($post->ID, '_event_start_date', true);
	if($event_start_date) {
		$event_start_date_display = date('F j, Y', strtotime($event_start_date));
	}

	$event_end_date = get_post_meta($post->ID, '_event_end_date', true);
	if($event_end_date) {
		$event_end_date_display = date('F j, Y', strtotime($event_end_date));
	} ?>
	<div class="metabox">
		<p>
			<label>Start Date</label>
			<input type="text" class="event-start-date-select" name="event-start-date-select" value="<?php if($event_start_date) { echo $event_start_date_display; } ?>" />
			<input type="hidden" name="event-start-date" class="event-start-date" value="<?php if($event_start_date) { echo $event_start_date; } ?>" />
		</p>
		<p>
			<label>End Date (optional)</label>
			<input type="text" class="event-end-date-select" name="event-end-date-select" value="<?php if($event_end_date) { echo $event_end_date_display; } ?>" />
			<input type="hidden" name="event-end-date" class="event-end-date" value="<?php if($event_end_date) { echo $event_end_date; } ?>" />
		</p>
	</div>
<?php }

// Meta Box HTML
function event_location_callback($post) {
	wp_nonce_field('nonce_event_action','nonce');
	$event_location = get_post_meta($post->ID, '_event_location', true);
	$event_venue = get_post_meta($post->ID, '_event_venue', true); ?>
	<div class="metabox">
		<p>
			<label>Location</label>
			<input type="text" class="event-location" id="event-location" name="event-location" value="<?php echo $event_location; ?>" />
		</p>
		<p>
			<label>Venue (optional)</label>
			<input type="text" class="event-venue" id="event-venue" name="event-venue" value="<?php echo $event_venue; ?>" />
		</p>
	</div>
<?php }

// Meta Box HTML
function event_url_callback($post) {
	wp_nonce_field('nonce_event_action','nonce');
	$event_url = get_post_meta($post->ID, '_event_url', true); ?>
	<div class="metabox">
		<p>
			<label>URL:</label>
			<input type="url" class="event-url" name="event-url" value="<?php echo $event_url; ?>" onblur="var validURL = this.value.replace(/ /g,''); if(!~validURL.indexOf('http') && validURL != '') { validURL ='http://' + validURL; } this.value = validURL;" />
		</p>
	</div>
<?php }

// Save Meta Data
function event_update_post_meta($post_id) {
	// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'nonce' ] ) && wp_verify_nonce( $_POST[ 'nonce' ], 'nonce_event_action' ) ) ? 'true' : 'false';

	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}

	update_post_meta($post_id, '_event_start_date', $_POST['event-start-date']);
	update_post_meta($post_id, '_event_end_date', $_POST['event-start-date']);
	if(!empty($_POST['event-end-date'])) {
		update_post_meta($post_id, '_event_end_date', $_POST['event-end-date']);
	}
	update_post_meta($post_id, '_event_location', $_POST['event-location']);
	update_post_meta($post_id, '_event_venue', $_POST['event-venue']);
	update_post_meta($post_id, '_event_url', $_POST['event-url']);
}
add_action('save_post', 'event_update_post_meta');

// Add Datepicker UI
function event_meta_scripts() {
	global $post_type;
	if( $post_type == 'event' ) {
		// enqueue datepicker
		wp_enqueue_script('jquery-ui-datepicker', array('jquery', 'jquery-ui-core'));
		wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
		wp_enqueue_style('jquery-ui'); ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('.event-start-date-select').datepicker({
					dateFormat : 'MM d, yy',
					altField: '.event-start-date',
					altFormat: 'yy-mm-dd',
					changeMonth: true,
					changeYear: true,
					onClose: function( selectedDate ) {
						jQuery( '.event-end-date-select' ).datepicker( 'option', 'minDate', selectedDate );
					}
				});
				jQuery('.event-end-date-select').datepicker({
					dateFormat : 'MM d, yy',
					altField: '.event-end-date',
					altFormat: 'yy-mm-dd',
					minDate: new Date(jQuery('.event-end-date').val()),
					changeMonth: true,
					changeYear: true,
				});
			});
		</script>
	<?php }
}
add_action( 'admin_head', 'event_meta_scripts' );

// Alter $wp_query to sort by date, and only show future events
function event_query( $query ) {
	if ( !is_admin() && $query->is_main_query() ){
		if( is_post_type_archive( 'event' ) || is_tax( 'event_category' ) ) {
			$today = date('Y-m-j');
			// add to test_query;
			$query->set('meta_key', '_event_end_date');
			$query->set('orderby', 'meta_value');
			$query->set('order', 'ASC');
			$query->set('meta_query', array(
				array(
					'key' => '_event_end_date',
					'value' => $today,
					'compare' => '>=',
					'type' => 'DATE'
				)
			));
		}
	}
	return $query;
}
add_action( 'pre_get_posts', 'event_query' );
