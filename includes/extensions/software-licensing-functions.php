<?php
/*
 * software-licensing-functions.php
 */


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