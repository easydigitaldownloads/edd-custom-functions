<?php
/*
 * 3rd-party-plugin-functions.php
 */


// Gravity Forms - Enable CC option in GF Help Scout add-on
add_filter( 'gform_helpscout_enable_cc', '__return_true' );


// Gravity Forms - Allows the disabling of the notification message defined in the shortcode
add_filter( 'gform_enable_shortcode_notification_message', '__return_false' );


// Gravity Forms - Add 'priority' tag to Priority Support member tickets
function edd_gf_add_priority_to_tags( $tags, $feed, $entry, $form ) {

	$email_id = $feed['meta']['customer_email'];
	$email    = $entry[ $email_id ];

	$user  = get_user_by( 'email', $email );

	if( $user && rcp_is_active( $user->ID ) ) {
		$tags[] = 'priority';
	}

	return $tags;

}
add_filter( 'gform_helpscout_tags', 'edd_gf_add_priority_to_tags', 10, 4 );


/**
 * Gravity Forms
 *
 * Pre-populates radios based on previous name field
 * Searches post type 'person'
 *
 * @param array @form Current Form Object
 * @return array @form Modified Form Object
 */
function edd_gf_extensions_dropdown( $form, $ajax, $values ) {

	foreach( $form['fields'] as &$field ) {

		if ( false === strpos( $field->cssClass, 'extension-list' ) ) {
			continue;
		}

		// For Normal Name input
		$downloads = get_posts( array(
			'post_type' => 'download',
			'nopaging' => true,
			'orderby' => 'title',
			'order' => 'ASC',
			'tax_query' => array(
				array(
					'taxonomy' => 'download_category',
					'field' => 'slug',
					'terms' => array( '3rd-party', 'bundles' ),
					'operator' => 'NOT IN'
				)
			)
		) );

		if ( $downloads ) {
			$field->choices = array();
			foreach( $downloads as $d ) {
				$field->choices[] = array( 'text' => $d->post_title, 'value' => $d->post_title );
			}
		}

		// Add Other Choice
		$field->enableOtherChoice = 1;

	}

	return $form;

}
add_filter('gform_pre_render_11', 'edd_gf_extensions_dropdown', 9999, 3 );
add_filter('gform_pre_render_16', 'edd_gf_extensions_dropdown', 9999, 3 );

/**
 * Send a pushover notification when the Gravity Forms Help Scout addon is not authenticated.
 *
 */
function edd_helpscout_authentication_notification( $feed, $entry, $form, $addon ) {
	// Only run this code if there is a problem with Help Scout API authentication.
	if ( ! $addon->is_authenticated() && function_exists( 'ckpn_send_notification' ) ) {

		$users = ckpn_get_users_with_keys();

		$options = ckpn_get_options();

		// Add the default admin key from settings.
		$user_keys = array( $options['api_key'] );

		$alert_capability = apply_filters( 'ckpn_sales_alert_capability', 'manage_options' );

		// Find the users who can view_shop_reports and have a user key.
		foreach ( $users as $user_id => $user_key ) {
			if ( ! user_can( $user_id, $alert_capability ) ) {
				continue;
			}

			$user_keys[] = $user_key;
		}


		$args = array(
			'priority'  => 1,
			'title'     => 'Gravity Forms Error',
			'message'   => 'There was an error sending a Support request to Help Scout. Please reconnect the API.',
			'token'     => ckpn_get_application_key_by_id(),
			'url'       => $addon->get_redirect_url(),
			'url_title' => 'Reconnect Help Scout',
			'sound'     => 'gamelan',
		);

		foreach ( $user_keys as $user ) {
			$args['user'] = $user;
			ckpn_send_notification( $args );
		}

	}
}
add_action( 'gform_gravityformshelpscout_post_process_feed', 'edd_helpscout_authentication_notification', 10, 4 );

/**
 * Prevent "45 Days" memberships from auto renewing.
 *
 * @param bool $auto_renew
 *
 * @return bool
 */
function edd_rcp_maybe_disable_auto_renew( $auto_renew ) {

	// Bail if we don't have a level ID.
	if ( empty( $_POST['rcp_level'] ) ) {
		return $auto_renew;
	}

	$level = rcp_get_subscription_details( absint( $_POST['rcp_level'] ) );

	// Bail if this isn't the 45 Day membership level.
	if ( empty( $level ) || '45 Days' != $level->name ) {
		return $auto_renew;
	}

	return false;

}
add_filter( 'rcp_registration_is_recurring', 'edd_rcp_maybe_disable_auto_renew' );


// Restrict Content Pro -
remove_action( 'the_excerpt', 'rcp_filter_feed_posts' );
remove_action( 'the_content', 'rcp_filter_feed_posts' );


/**
 * Simple Notices Pro
 *
 * Determine if a sale notice is active (published)
 *
 * @return boolean $found true if found, false otherwise
 */
function eddwp_sale_notice_active() {

	$args           = array(
		'posts_per_page' => -1,
		'meta_key'       => 'eddwp_notice_is_sale',
		'post_type'      => 'notices',
		'post_status'    => 'publish',
	);

	$posts          = get_posts( $args );
	$found          = false;

	if ( $posts ) {
		foreach ( $posts as $post ) {
			$notice_enabled = get_post_meta( $post->ID, '_enabled', true );

			// Is this notice published and enabled?
			if ( 'publish' === $post->post_status && $notice_enabled ) {
				$found = true;
			}
		}
	}

	return $found;
}


/**
 * Simple Notices Pro
 *
 * Determine if a Partnership notice is published
 *
 * @return boolean $found true if found, false otherwise
 */
function eddwp_notice_is_partnership() {

	$args           = array(
		'posts_per_page' => -1,
		'meta_key'       => 'eddwp_notice_is_partnership',
		'post_type'      => 'notices',
		'post_status'    => 'publish',
	);

	$posts          = get_posts( $args );
	$found          = false;

	if ( $posts ) {
		foreach ( $posts as $post ) {
			$notice_enabled = get_post_meta( $post->ID, '_enabled', true );

			// Is this notice published and enabled?
			if ( 'publish' === $post->post_status && $notice_enabled ) {
				$found = true;
			}
		}
	}

	return $found;
}
