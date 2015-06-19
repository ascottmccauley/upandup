<?php
/**
 * Custom Post Type - Portfolio
 *
 * Post Type:
 * portfolio_project
 *
 * Taxonomy:
 * portfolio_category - hierarchical
 * portfolio_tags - not hierarchical
 *
 * Meta:
 * thumbnail
 * gallery
 * 
**/

// Register Post Type
function upandup_portfolio_register_post_types() {
	
	$rewrite = apply_filters( 'upandup_portfolio_post_type_rewrite', array(
		'slug'       => 'project',
		'with_front' => false,
		'pages'      => true,
		'feeds'      => true,
		'ep_mask'    => EP_PERMALINK,
	) );
	
	$supports = apply_filters( 'upandup_portfolio_post_type_supports', array(
		'title',
		'editor',
		'thumbnail',
		'excerpt',
		'author',
	) );
	
	$labels = apply_filters( 'upandup_portfolio_post_type_labels', array(
		'name'               => __( 'Projects',                   'upandup' ),
		'singular_name'      => __( 'Project',                    'upandup' ),
		'menu_name'          => __( 'Portfolio',                  'upandup' ),
		'name_admin_bar'     => __( 'Portfolio Project',          'upandup' ),
		'add_new'            => __( 'Add New',                    'upandup' ),
		'add_new_item'       => __( 'Add New Project',            'upandup' ),
		'edit_item'          => __( 'Edit Project',               'upandup' ),
		'new_item'           => __( 'New Project',                'upandup' ),
		'view_item'          => __( 'View Project',               'upandup' ),
		'search_items'       => __( 'Search Projects',            'upandup' ),
		'not_found'          => __( 'No projects found',          'upandup' ),
		'not_found_in_trash' => __( 'No projects found in trash', 'upandup' ),
		'all_items'          => __( 'Projects',                   'upandup' ),
	) );
	
	$args = apply_filters( 'upandup_portfolio_post_type_args', array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'exclude_from_search' => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 12,
		'menu_icon'           => 'dashicons-portfolio',
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => false,
		'has_archive'         => true,
		'query_var'           => 'portfolio_project',
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'rewrite'							=> $rewrite,
		'supports'						=> $supports,
		'labels'							=> $labels,
	) );
	
	register_post_type( 'portfolio', $args );

}
add_action( 'init', 'upandup_portfolio_register_post_types' );

// Register Taxonomy
function upandup_portfolio_register_taxonomies() {
	
	/* Register Portfolio Categories */
	
	$rewrite = apply_filters( 'upandup_portfolio_category_taxonomy_rewrite', array(
		'slug'         => 'portfolio',
		'with_front'   => true,
		'hierarchical' => true,
		'ep_mask'      => EP_NONE
	) );
	
	$labels = apply_filters( 'upandup_portfolio_category_taxonomy_labels', array(
		'name'                       => __( 'Categories',                  				  'upandup' ),
		'singular_name'              => __( 'Category',                    				  'upandup' ),
		'menu_name'                  => __( 'Categories',                           'upandup' ),
		'name_admin_bar'             => __( 'Category',                          	  'upandup' ),
		'search_items'               => __( 'Search Categories',                    'upandup' ),
		'popular_items'              => __( 'Popular Categories',                   'upandup' ),
		'all_items'                  => __( 'All Portfolio Categories',             'upandup' ),
		'edit_item'                  => __( 'Edit Category',                      	'upandup' ),
		'view_item'                  => __( 'View Category',                        'upandup' ),
		'update_item'                => __( 'Update Category',                   	  'upandup' ),
		'add_new_item'               => __( 'Add New Portfolio Category',           'upandup' ),
		'new_item_name'              => __( 'New Portfolio Category Name',          'upandup' ),
		'separate_items_with_commas' => null,
		'add_or_remove_items'        => __( 'Add or remove portfolio categories',   'upandup' ),
		'choose_from_most_used'      => null,
		'not_found'                  => __( 'No portfolio categories found',        'upandup' ),
		'parent_item'                => __( 'Parent Category',                      'upandup' ),
		'parent_item_colon'          => __( 'Parent Category:',                     'upandup' ),
	) );
	
	$args = apply_filters( 'upandup_portfolio_category_taxonomy_args', array(
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'query_var'         => 'portfolio_category',
		'rewrite'						=> $rewrite,
		'labels'						=> $labels,
	) );
	
	register_taxonomy( 'portfolio_category', array( 'portfolio' ), $args );
	
	/* Register Portfolio Tags */
	
	$rewrite = apply_filters( 'upandup_portfolio_tag_taxonomy_rewrite', array(
		'slug'         => 'tag',
		'with_front'   => false,
		'hierarchical' => false,
		'ep_mask'      => EP_NONE,
	) );
	
	$labels = apply_filters( 'upandup_portfolio_tag_taxonomy_labels', array(
		'name'                       => __( 'Project Tags',                   'upandup' ),
		'singular_name'              => __( 'Project Tag',                    'upandup' ),
		'menu_name'                  => __( 'Tags',                           'upandup' ),
		'name_admin_bar'             => __( 'Tag',                            'upandup' ),
		'search_items'               => __( 'Tags',                           'upandup' ),
		'popular_items'              => __( 'Popular Tags',                   'upandup' ),
		'all_items'                  => __( 'All Tags',                       'upandup' ),
		'edit_item'                  => __( 'Edit Tag',                       'upandup' ),
		'view_item'                  => __( 'View Tag',                       'upandup' ),
		'update_item'                => __( 'Update Tag',                     'upandup' ),
		'add_new_item'               => __( 'Add New Tag',                    'upandup' ),
		'new_item_name'              => __( 'New Tag Name',                   'upandup' ),
		'separate_items_with_commas' => __( 'Separate tags with commas',      'upandup' ),
		'add_or_remove_items'        => __( 'Add or remove tags',             'upandup' ),
		'choose_from_most_used'      => __( 'Choose from the most used tags', 'upandup' ),
		'not_found'                  => __( 'No tags found',                  'upandup' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
	) );
	
	$args = apply_filters( 'upandup_portfolio_tag_taxonomy_args', array(
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
		'show_admin_column' => true,
		'hierarchical'      => false,
		'query_var'         => 'portfolio_tag',
		'rewrite'						=> $rewrite,
		'labels'						=> $labels,
	) );
	
	register_taxonomy( 'portfolio_tag', array( 'portfolio' ), $args );
	
}
add_action( 'init', 'upandup_portfolio_register_taxonomies' );
