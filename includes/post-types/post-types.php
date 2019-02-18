<?php
/**
 * Set up custom post types used throughout the theme
 */

/**
 * Register custom post types
 */
function eddwp_custom_post_types() {

	// Showcase post type
	$showcase_labels = array(
		'name'               => 'Showcases',
		'singular_name'      => 'Showcase',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Showcase',
		'edit_item'          => 'Edit Showcase',
		'new_item'           => 'New Showcase',
		'view_item'          => 'View Showcase',
		'all_items'          => 'All Showcases',
		'search_items'       => 'Search Showcases',
		'not_found'          => 'No showcases found.',
		'not_found_in_trash' => 'No showcases found in Trash.',
		'menu_name'          => 'Showcases',
		'name_admin_bar'     => 'Showcase',
	);
	$showcase_taxonomies = array();
	$showcase_args       = array(
		'labels'              => $showcase_labels,
		'singular_label'      => 'Showcase',
		'public'              => true,
		'show_ui'             => true,
		'publicly_queryable'  => true,
		'query_var'           => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => false,
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'showcase', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-laptop',
		'taxonomies'          => $showcase_taxonomies,
	);
	register_post_type( 'showcase', $showcase_args );

	// Testimonial post type
	$testimonial_labels = array(
		'name'               => 'Testimonials',
		'singular_name'      => 'Testimonial',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Testimonial',
		'edit_item'          => 'Edit Testimonial',
		'new_item'           => 'New Testimonial',
		'view_item'          => 'View Testimonial',
		'search_items'       => 'Search Testimonials',
		'not_found'          => 'No Testimonials found.',
		'not_found_in_trash' => 'No Testimonials found in Trash.',
		'parent_item_colon'  => '',
		'menu_name'          => 'Testimonials',
		'name_admin_bar'     => 'Testimonial',
	);
	$testimonial_taxonomies = array( 'testimonial_category', 'testimonial_tag' );
	$testimonial_args       = array(
		'labels'              => $testimonial_labels,
		'singular_label'      => 'Testimonial',
		'public'              => true,
		'show_ui'             => true,
		'publicly_queryable'  => true,
		'query_var'           => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => true,
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'testimonials', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-format-quote',
		'taxonomies'          => $testimonial_taxonomies
	);
	register_post_type( 'testimonials', $testimonial_args );

	// Partner post type
	$partner_labels = array(
		'name'               => 'Partners',
		'singular_name'      => 'Partner',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Partner',
		'edit_item'          => 'Edit Partner',
		'new_item'           => 'New Partner',
		'view_item'          => 'View Partner',
		'search_items'       => 'Search Partners',
		'not_found'          => 'No Partners found.',
		'not_found_in_trash' => 'No Partners found in Trash.',
		'parent_item_colon'  => '',
		'menu_name'          => 'Partners',
		'name_admin_bar'     => 'Partner',
	);
	$partner_taxonomies = array();
	$partner_args       = array(
		'labels'              => $partner_labels,
		'singular_label'      => 'Partner',
		'public'              => true,
		'show_ui'             => true,
		'publicly_queryable'  => true,
		'query_var'           => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => true,
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'partners', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-groups',
		'taxonomies'          => $partner_taxonomies
	);
	register_post_type( 'partner', $partner_args );

	// Consultant post type
	$consultant_labels = array(
		'name'               => 'Consultants',
		'singular_name'      => 'Consultant',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Consultant',
		'edit_item'          => 'Edit Consultant',
		'new_item'           => 'New Consultant',
		'view_item'          => 'View Consultant',
		'search_items'       => 'Search Consultants',
		'not_found'          => 'No Consultants found.',
		'not_found_in_trash' => 'No Consultants found in Trash.',
		'parent_item_colon'  => '',
		'menu_name'          => 'Consultants',
		'name_admin_bar'     => 'Consultant',
	);
	$consultant_taxonomies = array( 'consultant_tag' );
	$consultant_args       = array(
		'labels'              => $consultant_labels,
		'singular_label'      => 'Consultant',
		'public'              => true,
		'show_ui'             => true,
		'publicly_queryable'  => true,
		'query_var'           => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => true,
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'consultants', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-businessman',
		'taxonomies'          => $consultant_taxonomies
	);
	register_post_type( 'consultant', $consultant_args );

	// Host post type
	$host_labels = array(
		'name'               => 'Hosts',
		'singular_name'      => 'Host',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Host',
		'edit_item'          => 'Edit Host',
		'new_item'           => 'New Host',
		'view_item'          => 'View Host',
		'search_items'       => 'Search Hosts',
		'not_found'          => 'No Hosts found.',
		'not_found_in_trash' => 'No Hosts found in Trash.',
		'parent_item_colon'  => '',
		'menu_name'          => 'Hosts',
		'name_admin_bar'     => 'Host',
	);
	$host_taxonomies = array();
	$host_args                = array(
		'labels'              => $host_labels,
		'singular_label'      => 'Host',
		'public'              => true,
		'show_ui'             => true,
		'publicly_queryable'  => true,
		'query_var'           => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => true,
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'hosts', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-admin-generic',
		'taxonomies'          => $host_taxonomies
	);
	register_post_type( 'host', $host_args );

	// Resource post type
	$resource_labels = array(
		'name'               => 'Resources',
		'singular_name'      => 'Resource',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Resource',
		'edit_item'          => 'Edit Resource',
		'new_item'           => 'New Resource',
		'view_item'          => 'View Resource',
		'search_items'       => 'Search Resources',
		'not_found'          => 'No Resources found.',
		'not_found_in_trash' => 'No Resources found in Trash.',
		'parent_item_colon'  => '',
		'menu_name'          => 'Resources',
		'name_admin_bar'     => 'Resource',
	);
	$resource_taxonomies = array( 'resource_category' );
	$resource_args       = array(
		'labels'              => $resource_labels,
		'singular_label'      => 'Resource',
		'public'              => true,
		'show_ui'             => true,
		'publicly_queryable'  => true,
		'query_var'           => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => true,
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'resources', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-welcome-learn-more',
		'taxonomies'          => $resource_taxonomies
	);
	register_post_type( 'resource', $resource_args );
}
add_action( 'init', 'eddwp_custom_post_types' );