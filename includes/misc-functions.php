<?php
/*
 * misc-functions.php
 */


/**
 * Modify query for display of EDD's "newest" products
 */
function eddwp_query_filters( $query ) {

	if( ! isset( $_GET['display'] ) ) {
		return;
	}

	switch( $_GET['display'] ) {

		case 'newest' :

			$query->set( 'order', 'DESC' );
			$query->set( 'orderby', 'date' );

			break;

	}
}
add_action( 'pre_get_posts', 'eddwp_query_filters', 999 );


/**
 * Adjust allowed mime types
 */
function eddwp_allowed_mime_types( $existing_mimes ) {
	$existing_mimes['mp4']  = 'video/mp4';
	$existing_mimes['ogg']  = 'video/ogg';
	$existing_mimes['ogv']  = 'video/ogv';
	$existing_mimes['txt']  = 'text/plain';

	return $existing_mimes;
}
add_filter( 'upload_mimes', 'eddwp_allowed_mime_types' );


/**
 * Redirect old docs archive to Help Scout docs
 */
function edd_redirect_docs() {

	if( is_post_type_archive( 'docs' ) ) {
		wp_redirect( 'http://docs.easydigitaldownloads.com' ); exit;
	}

}
add_action( 'template_redirect', 'edd_redirect_docs' );


/**
 * Auto apply BFCM discount
 */
function pw_edd_auto_apply_discount() {

	if( function_exists( 'edd_is_checkout' ) && edd_is_checkout() ) {

		if( ! edd_cart_has_discounts() && edd_is_discount_valid( 'BFCM2016', '', false ) ) {
			edd_set_cart_discount( 'BFCM2016' );
		}
	}
}
//add_action( 'template_redirect', 'pw_edd_auto_apply_discount' );