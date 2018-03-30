<?php

class EDD_Custom_SL_Functionality {

	private static $instance;
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Custom_SL_Functionality ) ) {
			self::$instance = new EDD_Custom_SL_Functionality;
		}

		self::hooks();
		self::filters();

		return self::$instance;
	}

	private function hooks() {

	}

	private function filters() {
		add_filter( 'edd_sl_license_response', array( self::$instance, 'filter_get_version_response' ), 10, 3 );
		add_filter( 'edd_sl_download_upgrade_file_key', array( self::$instance, 'filter_file_key' ), 10, 2 );
	}

	public function filter_get_version_response( $response, $download, $download_beta ) {
		if ( $download_beta ) {
			return $response;
		}

		$found_item = $this->detect_rollout( $download->ID );

		if ( empty( $found_item['rollout_pct'] ) ) {
			return $response;
		}

		$test_users = get_option( 'edd_rollout_' . $download->ID, true );
		if ( ! is_array( $test_users ) ) {
			$test_users = array();
		}

		if ( ! empty( $found_item['max_users'] ) && count( $test_users ) >= $found_item['max_users'] ) {
			return $response;
		}

		$license_key = ! empty( $_REQUEST['license'] ) ? sanitize_text_field( $_REQUEST['license'] ) : false;
		$url         = ! empty( $_REQUEST['url'] )     ? edd_software_licensing()->clean_site_url( $_REQUEST['url'] ) : false;
		if ( false === $license_key || false === $url ) {
			return $response;
		}

		$identifier = md5( $url . $license_key );
		if ( ! in_array( $identifier, $test_users ) ) {
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

		$test_users[] = $identifier;
		update_option( 'edd_rollout_' . $download->ID, $test_users, false );

		$package_url = edd_software_licensing()->get_encoded_download_package_url( $download->ID, $license_key, $url, false  );
		$response['package']       = $package_url;
		$response['download_link'] = $package_url;

		return $response;
	}

	public function filter_file_key( $file_key, $download ) {
		$found_item = $this->detect_rollout( $download->ID );

		if ( ! $found_item ) {
			return $file_key;
		}

		$test_users = get_option( 'edd_rollout_' . $download->ID, true );
		if ( ! is_array( $test_users ) ) {
			$test_users = array();
		}
		if ( false === stristr( $_SERVER['REQUEST_URI'], 'edd-sl/package_download' ) ) {
			$license_key = ! empty( $_REQUEST['license'] ) ? sanitize_text_field( $_REQUEST['license'] ) : false;
			$url         = ! empty( $_REQUEST['url'] ) ? edd_software_licensing()->clean_site_url( $_REQUEST['url'] ) : false;
			if ( false === $license_key || false === $url ) {
				return $file_key;
			}
		} else {
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

		$identifier = md5( $url . $license_key );
		if ( ! in_array( $identifier, $test_users ) ) {
			return $file_key;
		}

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
add_filter( 'plugins_loaded', 'eddwp_custom_sl_functionality', 20 );