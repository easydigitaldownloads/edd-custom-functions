<?php
// Options pages built with Advanced Custom Fields Pro

if ( function_exists('acf_add_options_page' ) ) {

	acf_add_options_page( array(
		'page_title' 	  => 'Site Settings',
		'menu_title'	  => 'Site Settings',
		'menu_slug' 	  => 'site-settings',
		'capability'	  => 'edit_posts',
		'updated_message' => 'Settings Updated',
		'redirect'        => true
	) );

	acf_add_options_sub_page( array(
		'page_title' 	=> 'Support Tools',
		'menu_title'	=> 'Support',
		'parent_slug'	=> 'site-settings'
	) );
}