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
 * Email notification to admin about Gravity Forms Help Scout API authentication problem and create Help Scout conversation
 *
 */
function edd_helpscout_authentication_mail( $feed, $entry, $form, $addon ) {

	// Only run this code if there is a problem with Help Scout API authentication.
	if ( ! $addon->is_authenticated() ) {

		// Create message and headers.
		$message = sprintf(
			'Unable to create conversation %s because API was not initialized. %s',
			'<a href="' . admin_url( 'admin.php?page=gf_entries&view=entry&id=' . absint( $form['id'] ) . '&lid=' . esc_attr( $entry['id'] ) . '">' . $entry['id'] ) . '</a>',
			'<a href="' . $addon->get_redirect_url() . '">Click here to authenticate with Help Scout.</a>'
		);
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Send the notification.
		wp_mail(
			array(
				'chris@sandhillsdev.com',
				'keri@sandhillsdev.com',
			),
			sprintf( '%s - Help Scout API problem', get_bloginfo( 'name' ) ),
			$message,
			$headers
		);

		// Send Help Scout notification to create conversation.
		GFAPI::send_notifications( $form, $entry, 'help_scout_conversation_error' );
	}
}
add_action( 'gform_gravityformshelpscout_post_process_feed', 'edd_helpscout_authentication_mail', 10, 4 );


/**
 * Add notification events
 *
 */
function edd_gf_notification_events( $notification_events ) {
	$notification_events['help_scout_conversation_error'] = 'Help Scout Converation Error';

	return $notification_events;
}
add_filter( 'gform_notification_events', 'edd_gf_notification_events' );


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
