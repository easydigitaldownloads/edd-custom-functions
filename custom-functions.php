<?php

/*
Plugin Name: Custom Functions Plugin
Plugin URI: http://pippinsplugins.com/
Description: Put custom functions in this plugin
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Version: 10.0
*/


define( 'EDD_MENU_POSITION', 35 );
//define( 'EDD_SL_REDIRECT_UPDATES', true );

/* SearchWP Mods */

add_filter( 'searchwp_background_deltas', '__return_false' );
add_filter( 'searchwp_missing_integration_notices', '__return_false' );

function pw_edd_searchwp_indexed_types( $types ) {

	return array( 'post', 'page', 'extension', 'docs', 'videos', 'theme', 'topic', 'forum', 'reply' );

}
add_filter( 'searchwp_indexed_post_types', 'pw_edd_searchwp_indexed_types' );

/*********************************************
* Connection types
*********************************************/


function eddwp_connection_types() {
	p2p_register_connection_type( array(
		'name' => 'extensions_to_docs',
		'from' => 'extension',
		'to' => 'docs',
		'reciprocal' => true
	) );
	p2p_register_connection_type( array(
		'name' => 'docs_to_docs',
		'from' => 'docs',
		'to' => 'docs',
		'reciprocal' => true
	) );
	p2p_register_connection_type( array(
		'name' => 'videos_to_docs',
		'from' => 'videos',
		'to' => 'docs',
		'reciprocal' => true
	) );
	p2p_register_connection_type( array(
		'name' => 'extensions_to_forums',
		'from' => 'extension',
		'to' => 'forum',
		'reciprocal' => true
	) );
}
add_action( 'p2p_init', 'eddwp_connection_types' );


function eddwp_extenstion_cats_shortcode() {
	$cats = get_terms( 'extension_category' );

	if ( $cats ) {
		$return = '<div class="filter clearfix">';
			$return .= '<ul class="extension-categories clearfix">';
				$return .= '<li><a href="' . home_url('/extensions') . '">All</a></li>';
				$return .= '<li><a href="' . home_url('/extensions/?display=newest') . '">Newest</a></li>';

				foreach( $cats as $cat ) {
					$return .= '<li><a href="' . get_term_link( $cat->slug, 'extension_category' ) . '">' . $cat->name . '</a></li>';
				}
			$return .= '</ul>';
		$return .= '</div>';

		return $return;
	}
}
add_shortcode( 'extension_cats', 'eddwp_extenstion_cats_shortcode' );

function eddwp_query_filters( $query ) {
	if( ! isset( $_GET['display'] ) )
		return;

	switch( $_GET['display'] ) {

		case 'newest' :

			$query->set( 'order', 'DESC' );
			$query->set( 'orderby', 'date' );

			break;

	}
}
add_action( 'pre_get_posts', 'eddwp_query_filters', 999 );


function eddwp_filter_media_comment_status( $open, $post_id ) {
	$post = get_post( $post_id );
	if( $post->post_type == 'attachment' ) {
		return false;
	}
	return $open;
}
add_filter( 'comments_open', 'eddwp_filter_media_comment_status', 10 , 2 );

function eddwp_allowed_mime_types( $existing_mimes ) {
  $existing_mimes['mp4']  = 'video/mp4';
  $existing_mimes['ogg']  = 'video/ogg';
  $existing_mimes['ogv']  = 'video/ogv';
  $existing_mimes['txt']  = 'text/plain';

  return $existing_mimes;
}
add_filter( 'upload_mimes', 'eddwp_allowed_mime_types' );

// Disable heartbeat in dashboard
remove_action( 'plugins_loaded', array( 'EDD_Heartbeat', 'init' ) );

function pw_flush() {
  if( isset( $_GET['flush'] ) && isset( $_GET['pw11'] ) )
    flush_rewrite_rules(false);
}
add_action( 'init', 'pw_flush' );

function pw_edd_empty_cart_message( $text ) {
	return '<p class="edd_empty_cart">' . __( 'Your cart is empty. If this appears to be in error, please clear your browser cookies and try again.', 'edd' ) . '</p>';
}
add_filter( 'edd_empty_cart_message', 'pw_edd_empty_cart_message' );

add_filter( 'gform_enable_shortcode_notification_message', '__return_false' );

remove_action( 'the_excerpt', 'rcp_filter_feed_posts' );
remove_action( 'the_content', 'rcp_filter_feed_posts' );

function edd_rcp_force_auto_renew( $data ) {

	if( '45 Days' == $data['subscription_name'] ) {
		$data['auto_renew'] = false;
	}

	return $data;
}
add_filter( 'rcp_subscription_data', 'edd_rcp_force_auto_renew' );

function edd_ga_tracking_code() {
?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-30170438-1', 'auto');
  ga('send', 'pageview');

</script>
<?php
}
add_action( 'wp_footer', 'edd_ga_tracking_code' );

function eddwp_optimizely_code() {
?>
	<script src="//cdn.optimizely.com/js/3142510426.js"></script>
<?php
}
add_action( 'wp_head', 'eddwp_optimizely_code' );

function eddwp_optimizely_revenue_tracking() {

	if( ! function_exists( 'edd_get_purchase_session' ) ) {
		return;
	}

	if( function_exists( 'edd_is_success_page' ) && ! edd_is_success_page() ) {
		return;
	}

	$session = edd_get_purchase_session();
	if( ! $session ) {
		return;
	}
	$payment_id = edd_get_purchase_id_by_key( $session['purchase_key'] );
?>
<script>
	var price = <?php echo edd_get_payment_amount( $payment_id ); ?> 
	window.optimizely = window.optimizely || [];
	window.optimizely.push(['trackEvent', 'purchase_complete', {'revenue': price * 100}]);
</script>
<?php
}
add_action( 'wp_head', 'eddwp_optimizely_revenue_tracking', 11 );

function edd_snap_engage_code() {

	if( function_exists( 'is_bbpress' ) && is_bbpress() ) {
		return;
	}

	if( is_page( 'support' ) ) {
		return;
	}

?>
<!-- begin SnapEngage code -->
<script type="text/javascript">
  (function() {
    var se = document.createElement('script'); se.type = 'text/javascript'; se.async = true;
    se.src = '//storage.googleapis.com/code.snapengage.com/js/d6358744-b7cc-4b25-a5e0-ed98d5e10990.js';
    var done = false;
    se.onload = se.onreadystatechange = function() {
      if (!done&&(!this.readyState||this.readyState==='loaded'||this.readyState==='complete')) {
        done = true;
        /* Place your SnapEngage JS API code below */
        /* SnapEngage.allowChatSound(true); Example JS API: Enable sounds for Visitors. */
      }
    };
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(se, s);
  })();
</script>
<!-- end SnapEngage code -->
<?php
}
add_action( 'wp_footer', 'edd_snap_engage_code' );


/**
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
					'terms' => '3rd-party',
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
add_filter('gform_pre_render_14', 'edd_gf_extensions_dropdown', 9999, 3 );


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

function edd_redirect_docs() {

	if( is_post_type_archive( 'docs' ) ) {
		wp_redirect( 'http://docs.easydigitaldownloads.com' ); exit;
	}

}
add_action( 'template_redirect', 'edd_redirect_docs' );