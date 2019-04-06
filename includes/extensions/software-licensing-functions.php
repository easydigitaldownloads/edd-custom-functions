<?php
/*
 * software-licensing-functions.php
 */


class EDD_Custom_SL_Functionality {

	private static $instance;
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Custom_SL_Functionality ) ) {
			self::$instance = new EDD_Custom_SL_Functionality;
		}

		self::$instance->filters();

		return self::$instance;
	}

	private function filters() {
		add_filter( 'edd_sl_license_response', array( self::$instance, 'filter_get_version_response' ), 10, 3 );
		add_filter( 'edd_sl_download_upgrade_file_key', array( self::$instance, 'filter_file_key' ), 10, 2 );
	}

	public function filter_get_version_response( $response, $download, $download_beta ) {
		$found_item = $this->detect_rollout( $download->ID );

		if ( empty( $found_item['rollout_pct'] ) ) {
			return $response;
		}

		$test_users = get_option( 'edd_rollout_' . $download->ID, array() );

		// Beta users bypass the max counts (as they are likely already running the base of the version in test).
		if ( empty( $download_beta ) && ! empty( $found_item['max_users'] ) && count( $test_users ) >= $found_item['max_users'] ) {
			return $response;
		}

		$license_key = ! empty( $_REQUEST['license'] ) ? sanitize_text_field( $_REQUEST['license'] ) : false;
		$url         = ! empty( $_REQUEST['url'] )     ? edd_software_licensing()->clean_site_url( $_REQUEST['url'] ) : false;
		if ( false === $license_key || false === $url ) {
			return $response;
		}

		$identifier = md5( $url . $license_key );
		// Beta users bypass the random checks and automatically get the test.
		if ( empty( $download_beta ) && empty( $test_users[ $identifier ] ) ) {

			$random_value = rand( 1, 100 ); // Get this user's randomized string.
			$test_group   = 100 - $found_item['rollout_pct']; // Determine the threshold in the 1-100 range that is the test group.

			if ( $random_value <= $test_group ) {

				// This user wasn't selected to be in the test group. Deliver the control package.
				return $response;

			}

		}

		// Yay! We've got a test user.
		$response['stable_version'] = $found_item['version'];
		$response['new_version']    = $found_item['version'];

		// Add this URL/License combination to the testing group, so that the package URL can properly define them as
		// a site that should get the rollout package.
		$test_users[ $identifier ] = array( 'url' => $url, 'license_key' => $license_key );
		update_option( 'edd_rollout_' . $download->ID, $test_users, false );

		// Define the package URL.
		$package_url = edd_software_licensing()->get_encoded_download_package_url( $download->ID, $license_key, $url, false  );
		$response['package']       = $package_url;
		$response['download_link'] = $package_url;

		// Setup the changelog.
		$sections = maybe_unserialize( $response['sections'] );
		if ( empty( $sections['changelog'] ) ) {
			$sections['changelog'] = wpautop( $found_item['changelog'] );
		} else {
			$sections['changelog'] = wpautop( $found_item['changelog'] ) . $sections['changelog'];
		}

		$response['sections']  = serialize( $sections );

		return $response;
	}

	public function filter_file_key( $file_key, $download ) {

		// Determine if there is a rollout defined for this download ID.
		$found_item = $this->detect_rollout( $download->ID );

		// If no rollout is found in the constant for this download ID, just move along and return the defined
		// file key in EDD SL settings for the product.
		if ( ! $found_item ) {
			return $file_key;
		}

		// Get the set of users that are already defined to be part of this rollout.
		$test_users = get_option( 'edd_rollout_' . $download->ID, array() );

		// Since we have to modify this at the request of `get_version` and when downloading the package,
		// we need to determine what context we're in.
		if ( false === stristr( $_SERVER['REQUEST_URI'], 'edd-sl/package_download' ) ) {

			// This is a `get_version` request, so the license and url are passed into the API. At this point we're generating
			// the package URL, which will be used later.
			$license_key = ! empty( $_REQUEST['license'] ) ? sanitize_text_field( $_REQUEST['license'] ) : false;
			$url         = ! empty( $_REQUEST['url'] ) ? edd_software_licensing()->clean_site_url( $_REQUEST['url'] ) : false;
			if ( false === $license_key || false === $url ) {
				return $file_key;
			}

		} else {

			// This was a request to download the package, so we have to deconstruct our token for the needed information.
			$url_parts = parse_url( $_SERVER['REQUEST_URI'] );
			$paths     = array_values( explode( '/', $url_parts['path'] ) );

			$token  = end( $paths );
			$values = explode( ':', base64_decode( $token ) );

			if ( count( $values ) !== 6 ) {
				wp_die( __( 'Invalid token supplied', 'edd_sl' ), __( 'Error', 'edd_sl' ), array( 'response' => 401 ) );
			}

			$license_key = $values[1];
			$url         = str_replace( '@', ':', $values[4] );
		}

		// Determine the Identifier of the site requesting the update.
		$identifier = md5( $url . $license_key );

		// This site was not part of the test group, so just return the file key defined in the download settings.
		if ( empty( $test_users[ $identifier ] ) ) {
			return $file_key;
		}

		// This user was part of the test group, return the file key defined in our rollout settings.
		return $found_item['file_id'];
	}

	private function detect_rollout( $download_id ) {
		// If we don't have any rollouts defined, the array is empty, or the download being requested doesn't have a rollout, just move along.
		if ( ! defined( 'EDD_ROLLOUT_PRODUCTS' ) || empty( EDD_ROLLOUT_PRODUCTS ) || ! array_key_exists( $download_id, EDD_ROLLOUT_PRODUCTS ) ) {
			return false;
		}

		return EDD_ROLLOUT_PRODUCTS[ $download_id ];
	}

}

function eddwp_custom_sl_functionality() {
	return EDD_Custom_SL_Functionality::instance();
}
add_action( 'plugins_loaded', 'eddwp_custom_sl_functionality' );


/*
 * Disables renewal notifications for specific products
 */
function eddwp_maybe_disable_renewal_notice( $send, $license_id, $notice_id ) {

	$product_id = get_post_meta( $license_id, '_edd_sl_download_id', true );

	switch( $product_id ) {

		case 96640 :
			// Sales Recovery
			$send = false;
			break;
	}

	return $send;
}
add_filter( 'edd_sl_send_renewal_reminder', 'eddwp_maybe_disable_renewal_notice', 10, 3 );


/*
 * Sets renewal discount to 30% for any customer that purchased before September 1, 2017
 */
function eddwp_edd_grandfather_renewal_discount( $renewal_discount, $license_id ) {
	$license = edd_software_licensing()->get_license( $license_id );
	if( ! empty( $license->date_created ) && strtotime( $license->date_created ) < strtotime( 'September 9, 2017' ) ) {
		$renewal_discount = 30;
	}
	return $renewal_discount;
}
add_filter( 'edd_sl_renewal_discount_percentage', 'eddwp_edd_grandfather_renewal_discount', 10, 2 );


/**
 * Add to the domain whitelist for local/testing sites
 */
function eddwp_whitelist_sl_domains( $is_local, $url ) {

	$domains = array(
		'wpengine.com',
		'pressdns.com',
	);

	foreach( $domains as $domain ) {

		if( false !== strpos( $url, $domain ) ) {
			$is_local = true;
		}

	}

	return $is_local;
}
add_filter( 'edd_sl_is_local_url', 'eddwp_whitelist_sl_domains', 10, 2 );

/**
 * Fix Mail Chimp to be Mailchimp
 */
function eddwp_account_for_mailchimp_name_change( $args ) {
	if ( ! empty( $args['item_name'] ) && strtolower( $args['item_name'] ) === 'mail chimp' ) {
		$args['item_name'] = 'Mailchimp';
	}
	return $args;
}
add_filter( 'edd_sl_pre_activate_license_args', 'eddwp_account_for_mailchimp_name_change', 10, 1 );
add_filter( 'edd_sl_pre_deactivate_license_args', 'eddwp_account_for_mailchimp_name_change', 10, 1 );
add_filter( 'edd_sl_pre_check_license_args', 'eddwp_account_for_mailchimp_name_change', 10, 1 );
