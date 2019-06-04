<?php

/*
Plugin Name: Custom Functions Plugin
Plugin URI: http://pippinsplugins.com/
Description: Put custom functions in this plugin
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Version: 11.0

Please think about where your functions belong and place them there. :)
Create new files and directories if necessary.
*/


/**
 * Definitions
 */
define( 'EDD_MENU_POSITION', 35 );
//define( 'EDD_SL_REDIRECT_UPDATES', true );
define( 'EDD_CUSTOM_FUNCTIONS', dirname(__FILE__) . '/includes/' );

class EDD_Custom_Functions {
	private static $instance;

	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Custom_Functions ) ) {
			self::$instance = new EDD_Custom_Functions;

			self::$instance->includes();
		}

		return self::$instance;
	}

	private function includes() {

		include( EDD_CUSTOM_FUNCTIONS . '3rd-party-plugin-functions.php' );
		include( EDD_CUSTOM_FUNCTIONS . 'analytics-functions.php' );
		include( EDD_CUSTOM_FUNCTIONS . 'edd-filters-actions.php' );
		include( EDD_CUSTOM_FUNCTIONS . 'misc-functions.php' );
		include( EDD_CUSTOM_FUNCTIONS . 'shortcodes.php' );
		include( EDD_CUSTOM_FUNCTIONS . 'taxonomies.php' );


		/**
		 * Include custom post type functions
		 */
		include( EDD_CUSTOM_FUNCTIONS . 'post-types/post-types.php' );
		include( EDD_CUSTOM_FUNCTIONS . 'post-types/taxonomies.php' );
		include( EDD_CUSTOM_FUNCTIONS . 'post-types/metaboxes.php' );


		/**
		 * Include custom EDD extension functions
		 */
		include( EDD_CUSTOM_FUNCTIONS . 'extensions/software-licensing-functions.php' );
		include( EDD_CUSTOM_FUNCTIONS . 'extensions/recurring-payments-functions.php' );
		include( EDD_CUSTOM_FUNCTIONS . 'extensions/all-access-functions.php' );

	}
}

function EDD_Custom_Functions() {
	return EDD_Custom_Functions::instance();
}

add_action( 'plugins_loaded', 'EDD_Custom_Functions', 11 );