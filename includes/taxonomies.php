<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the custom taxonomies relating to gateway features for the downloads custom post type
 *
 * @return void
 */
function eddwp_setup_gateway_taxonomies() {

	/** Gateway features */
	$features_slug = 'gateway_features';

	$feature_labels = array(
		'name'                  => 'Gateway Features',
		'singular_name'         => 'Gateway Feature',
		'search_items'          => 'Search Gateway Features',
		'all_items'             => 'All Gateway Features',
		'parent_item'           => 'Parent Gateway Feature',
		'parent_item_colon'     => 'Parent Gateway Feature:',
		'edit_item'             => 'Edit Gateway Feature',
		'update_item'           => 'Update Gateway Feature',
		'add_new_item'          => 'Add New Gateway Feature',
		'new_item_name'         => 'New Gateway Feature Name',
		'menu_name'             => 'Gateway Features',
		'choose_from_most_used' => 'Choose from most used gateway features',
	);

	$feature_args = array(
			'hierarchical' => false,
			'labels'       => $feature_labels,
			'show_ui'      => true,
			'query_var'    => $features_slug,
			'rewrite'      => array( 'slug' => $features_slug, 'with_front' => false, 'hierarchical' => true  ),
			'capabilities' => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )
	);
	register_taxonomy( 'gateway_features', array( 'download' ), $feature_args );
	register_taxonomy_for_object_type( $features_slug, 'download' );

	/** Gateway currencies */
	$currencies_slug = 'gateway_currencies';

	$currency_labels = array(
		'name'                  => 'Gateway Currencies',
		'singular_name'         => 'Gateway Currency',
		'search_items'          => 'Search Gateway Currencies',
		'all_items'             => 'All Gateway Currencies',
		'parent_item'           => 'Parent Gateway Currency',
		'parent_item_colon'     => 'Parent Gateway Currency:',
		'edit_item'             => 'Edit Gateway Currency',
		'update_item'           => 'Update Gateway Currency',
		'add_new_item'          => 'Add New Gateway Currency',
		'new_item_name'         => 'New Gateway Currency Name',
		'menu_name'             => 'Gateway Currencies',
		'choose_from_most_used' => 'Choose from most used gateway currencies',
	);

	$currency_args = array(
		'hierarchical' => false,
		'labels'       => $currency_labels,
		'show_ui'      => true,
		'query_var'    => $currencies_slug,
		'rewrite'      => array( 'slug' => $currencies_slug, 'with_front' => false, 'hierarchical' => true  ),
		'capabilities' => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )
	);
	register_taxonomy( 'gateway_currencies', array( 'download' ), $currency_args );
	register_taxonomy_for_object_type( $currencies_slug, 'download' );

	/** Gateway countries */
	$countries_slug = 'gateway_countries';

	$country_labels = array(
		'name'                  => 'Gateway Countries',
		'singular_name'         => 'Gateway Country',
		'search_items'          => 'Search Gateway Countries',
		'all_items'             => 'All Gateway Countries',
		'parent_item'           => 'Parent Gateway Country',
		'parent_item_colon'     => 'Parent Gateway Country:',
		'edit_item'             => 'Edit Gateway Country',
		'update_item'           => 'Update Gateway Country',
		'add_new_item'          => 'Add New Gateway Country',
		'new_item_name'         => 'New Gateway Country Name',
		'menu_name'             => 'Gateway Countries',
		'choose_from_most_used' => 'Choose from most used gateway countries',
	);

	$country_args = array(
		'hierarchical' => false,
		'labels'       => $country_labels,
		'show_ui'      => true,
		'query_var'    => $countries_slug,
		'rewrite'      => array( 'slug' => $countries_slug, 'with_front' => false, 'hierarchical' => true  ),
		'capabilities' => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )
	);
	register_taxonomy( 'gateway_countries', array( 'download' ), $country_args );
	register_taxonomy_for_object_type( $countries_slug, 'download' );
}
add_action( 'init', 'eddwp_setup_gateway_taxonomies' );