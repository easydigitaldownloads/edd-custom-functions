<?php
/*
 * 3rd-party-plugin-functions.php
 */


// SearchWP - Stop automatically performing delta updates
add_filter( 'searchwp_background_deltas', '__return_false' );


// SearchWP - Stop showing notices for missing integration extensions
add_filter( 'searchwp_missing_integration_notices', '__return_false' );


// SearchWP - Modify array of searchable post types
function pw_edd_searchwp_indexed_types( $types ) {
	return array( 'post', 'page', 'extension', 'docs', 'videos', 'theme', 'topic', 'forum', 'reply' );
}
add_filter( 'searchwp_indexed_post_types', 'pw_edd_searchwp_indexed_types' );


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


// Restrict Content Pro - prevent auto-renewal of 45 day support subscription
function edd_rcp_force_auto_renew( $data ) {

	if( '45 Days' == $data['subscription_name'] ) {
		$data['auto_renew'] = false;
	}

	return $data;
}
add_filter( 'rcp_subscription_data', 'edd_rcp_force_auto_renew' );


// Restrict Content Pro -
remove_action( 'the_excerpt', 'rcp_filter_feed_posts' );
remove_action( 'the_content', 'rcp_filter_feed_posts' );