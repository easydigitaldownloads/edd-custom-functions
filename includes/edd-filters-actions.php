<?php
/*
 * edd-filters-actions.php
 */


/**
 * Disable API request logging
 */
add_filter( 'edd_api_log_requests', '__return_false' );


/**
 * Disable heartbeat in dashboard
 */
remove_action( 'plugins_loaded', array( 'EDD_Heartbeat', 'init' ) );


/**
 * Allow all usernames
 */
add_filter( 'edd_validate_username', '__return_true' );


/**
 * If the page loaded is the homepage, we don't need to start a session if one doesn't exist
 */
function eddwp_maybe_start_session( $start_session ) {

	if ( '/' == $_SERVER['REQUEST_URI'] ) {
		$start_session = false;
	}

	$to_skip = array(
		'activate_license',
		'deactivate_license',
		'check_license',
		'checkin',
		'get_version'
	);

	if( ! empty( $_REQUEST['edd_action'] ) && in_array( $_REQUEST['edd_action'], $to_skip ) ) {
		$start_session = false;
	}

	if ( strpos( $_SERVER['REQUEST_URI'], '/blog' ) !== false ) {
		$start_session = false;
	}

	// Finally, if there is a discount in the GET parameters, we should always start a session, so it applies correctly.
	if ( ! empty( $_GET['discount'] ) ) {
		$start_session = true;
	}

	return $start_session;
}
add_filter( 'edd_start_session', 'eddwp_maybe_start_session', 10, 1 );

/**
 * Conditionally load the Stripe JS
 *
 * Should only load the Stripe JS on non-blog post realted content.
 */
function eddwp_enqueue_stripe_scripts() {
	if ( strpos( $_SERVER['REQUEST_URI'], '/blog' ) === false ) {
		edd_stripe_js();
	}
}
add_action( 'wp_enqueue_scripts', 'eddwp_enqueue_stripe_scripts', 100 );

/**
 * Anytime we need to remove actions from core, we can use this function.
 *
 * By doing it on `init` we can wait until the last minute to remove any actions before we move forward.
 */
function eddwp_remove_actions() {
	remove_action( 'wp_enqueue_scripts', 'edd_stripe_js', 100 );
	if ( strpos( $_SERVER['REQUEST_URI'], '/blog' ) !== false ) {
		remove_action( 'wp_enqueue_scripts', 'rcp_load_gateway_scripts', 100 );
		if ( class_exists( 'EDD_Jilt_Loader' ) ) {
			remove_action( 'plugins_loaded', array( EDD_Jilt_Loader::instance(), 'init_plugin' ) );
		}
	}
}
add_action( 'plugins_loaded', 'eddwp_remove_actions', -1 );

