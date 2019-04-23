<?php
/**
 * Set up taxonomies for custom post types used throughout the theme
 */

/**
 * Register taxonomies for custom post types
 */
function eddwp_theme_custom_taxonomies() {

	// Consultant tags
	$consultant_tag_labels = array(
		'name'                       => 'Tags',
		'singular_name'              => 'Tag',
		'search_items'               => 'Search Tags',
		'popular_items'              => 'Popular Tags',
		'all_items'                  => 'All Tags',
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => 'Edit Tag',
		'update_item'                => 'Update Tag',
		'add_new_item'               => 'Add New Tag',
		'new_item_name'              => 'New Tag Name',
		'separate_items_with_commas' => 'Separate tags with commas',
		'add_or_remove_items'        => 'Add or remove Tags',
		'choose_from_most_used'      => 'Choose from the most used tags',
		'not_found'                  => 'No tags found.',
		'menu_name'                  => 'Tags',
	);
	$consultant_tag_args = array(
		'hierarchical'          => false,
		'labels'                => $consultant_tag_labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'consultant_tag' ),
	);
	register_taxonomy( 'consultant_tag', 'consultant', $consultant_tag_args );

	// Showcase category
	$showcase_labels = array(
		'name'               => 'Categories',
		'singular_name'      => 'Category',
		'add_new'            => 'Add New Category',
		'add_new_item'       => 'Add New Category',
		'edit_item'          => 'Edit Category',
		'new_item'           => 'New Category',
		'view_item'          => 'View Category',
		'search_items'       => 'Search Categories',
		'not_found'          => 'No Category found',
		'not_found_in_trash' => 'No Category found in Trash',
	);
	$showcase_args  = array(
		'labels'            => $showcase_labels,
		'singular_label'    => 'Category',
		'public'            => true,
		'show_ui'           => true,
		'hierarchical'      => false,
		'show_tagcloud'     => false,
		'show_in_nav_menus' => false,
		'rewrite'           => array( 'slug' => 'showcase_category', 'with_front' => false ),
	);
	register_taxonomy( 'showcasecategory', 'showcase', $showcase_args );

	// Testimonial categories
	$testimonial_cat_labels = array(
		'name'               => 'Categories',
		'singular_name'      => 'Category',
		'add_new'            => 'Add New Category',
		'add_new_item'       => 'Add New Category',
		'edit_item'          => 'Edit Category',
		'new_item'           => 'New Category',
		'view_item'          => 'View Category',
		'search_items'       => 'Search Categories',
		'not_found'          => 'No Category found',
		'not_found_in_trash' => 'No Category found in Trash',
	);
	$testimonial_cat_args  = array(
		'labels'            => $testimonial_cat_labels,
		'hierarchical'      => true,
		'singular_label'    => 'Category',
		'public'            => true,
		'show_ui'           => true,
		'show_tagcloud'     => false,
		'show_in_nav_menus' => false,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'testimonial_category', 'with_front' => false ),
	);
	register_taxonomy( 'testimonial_category', 'testimonials', $testimonial_cat_args );

	// Testimonial tags
	$testimonial_tag_labels = array(
		'name'                       => 'Tags',
		'singular_name'              => 'Tag',
		'search_items'               => 'Search Tags',
		'popular_items'              => 'Popular Tags',
		'all_items'                  => 'All Tags',
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => 'Edit Tag',
		'update_item'                => 'Update Tag',
		'add_new_item'               => 'Add New Tag',
		'new_item_name'              => 'New Tag Name',
		'separate_items_with_commas' => 'Separate tags with commas',
		'add_or_remove_items'        => 'Add or remove Tags',
		'choose_from_most_used'      => 'Choose from the most used tags',
		'not_found'                  => 'No tags found.',
		'menu_name'                  => 'Tags',
	);
	$testimonial_tag_args = array(
		'hierarchical'          => false,
		'labels'                => $testimonial_tag_labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'testimonial_tag' ),
	);
	register_taxonomy( 'testimonial_tag', 'testimonials', $testimonial_tag_args );

	// Resource category
	$resource_labels = array(
		'name'               => 'Categories',
		'singular_name'      => 'Category',
		'add_new'            => 'Add New Category',
		'add_new_item'       => 'Add New Category',
		'edit_item'          => 'Edit Category',
		'new_item'           => 'New Category',
		'view_item'          => 'View Category',
		'search_items'       => 'Search Categories',
		'not_found'          => 'No Category found',
		'not_found_in_trash' => 'No Category found in Trash',
	);
	$resource_args  = array(
		'labels'            => $resource_labels,
		'singular_label'    => 'Category',
		'public'            => true,
		'show_ui'           => true,
		'hierarchical'      => false,
		'show_tagcloud'     => false,
		'show_in_nav_menus' => false,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'resource_category', 'with_front' => false ),
	);
	register_taxonomy( 'resource_category', 'resource', $resource_args );
}
add_action( 'init', 'eddwp_theme_custom_taxonomies' );
