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
define( 'EDD_CUSTOM_FUNCTIONS', dirname(__FILE__) . '/includes/' );

// Enable CC option in GF Help Scout add-on
add_filter( 'gform_helpscout_enable_cc', '__return_true' );

// Disable API request logging
add_filter( 'edd_api_log_requests', '__return_false' );

function eddwp_get_all_access_pass_id() {
	$aap = get_page_by_path( 'all-access-pass', OBJECT, 'download' );
	return $aap->ID;
}


/*
 * Registers the upgrade path for All Access pass
 */
function pw_edd_all_access_upgrade_path( $paths, $download_id ) {

	$bundle_id = eddwp_get_all_access_pass_id();

	if( ! is_user_logged_in() || is_admin() ) {
		return $paths;
	}

	$discount  = 0.00;
	$customer  = new EDD_Customer( get_current_user_id(), true );

	if( ! $customer->purchase_value > 0 ) {
		return $paths;
	}

	$now = current_time( 'timestamp' );

	foreach( $customer->get_payments( array( 'publish', 'edd_subscription' ) ) as $payment ) {

		if( ! $payment->total > 0 ) {
			continue; // Skip free payments
		}

		// Skip manual payments.
		if ( false !== strpos( $payment->gateway, 'manual' ) ) {
			continue; // skip manual purchases
		}

		if( 'publish' !== $payment->status && 'edd_subscription' !== $payment->status ) {
			continue; // Skip anything that is a renewal or not complete
		}

		$datediff   = $now - strtotime( $payment->date, $now );
		$days_since = floor( $datediff / ( 60 * 60 * 24 ) );

		if( $days_since > 365 ) {
			continue; // We will only count payments made within the last 365 days
		}

		foreach( $payment->cart_details as $item ) {

			if( $bundle_id === (int) $item['id'] ) {
				return $paths; // Customer has already purchased core bundle
			}

			if( ! $item['price'] > 0 ) {
				continue; // Skip free items and 100% discounted items
			}

			$discount += ( $item['price'] - $item['tax'] ); // Add the purchase price to the discount

		}

	}

	if( $discount >= 899 ) {
		$discount = 898.00; // Min purchase price of $1.00
	}

	if ( ! is_array( $paths ) ) {
		$paths = array();
	}

	$paths[$bundle_id] = array(
		'download_id' => $bundle_id,
		'price_id'    => false,
		'discount'    => $discount,
		'pro_rated'   => false
	);

	return $paths;
}
add_filter( 'edd_sl_get_upgrade_paths', 'pw_edd_all_access_upgrade_path', 10, 2 );

function eddwp_handle_all_access_pass_upgrade_billing( $args, $downloads, $gateway, $download_id, $price_id ) {

	$downloads = ! is_array( $downloads ) ? array() : $downloads;

	foreach ( $downloads as $download ) {

		// Account for the fact that PayPal Express deals with post-payment creation, which means we have item_number in play.
		$options = isset( $download['item_number'] ) ? : $download['options'];

		if ( ! isset( $options['is_upgrade'] ) ) {
			continue;
		}

		if ( (int) $download['id'] !== (int) $download_id ) {
			continue;
		}

		if ( isset( $options['price_id'] ) && $price_id != $options['price_id'] ) {
			continue;
		}

		$license_id = isset( $options['license_id'] ) ? $options['license_id'] : false;
		if ( empty( $license_id ) ) {
			continue;
		}

		$license_expiration = edd_software_licensing()->get_license_expiration( $license_id );
		if ( 'lifetime' === $license_expiration ) {
			continue;
		}

		$bundle_id = eddwp_get_all_access_pass_id();

		if ( (int) $download_id !== $bundle_id ) {
			continue;
		}

		switch( $gateway ) {

			case 'stripe':
				$args['trial_end'] = strtotime( '+1 Year', current_time( 'timestamp' ) );
				break;

			case 'paypalpro':
			case 'paypalexpress':
				$args['PROFILESTARTDATE'] = date( 'Y-m-d\Tg:i:s', strtotime( '+1 Year', current_time( 'timestamp' ) ) );
				break;

		}

	}

	return $args;

}
add_filter( 'edd_recurring_create_subscription_args', 'eddwp_handle_all_access_pass_upgrade_billing', 99, 5 );

function eddwp_handle_all_access_pass_upgrade_expiration( $args, $recurring_gateway_data ) {

	$download_id = $args['product_id'];

	foreach ( $recurring_gateway_data->purchase_data['downloads'] as $download ) {
		if ( (int) $download['id'] !== (int) $download_id ) {
			continue;
		}

		if ( ! isset( $download['options']['is_upgrade'] ) ) {
			continue;
		}

		$license_id = isset( $download['options']['license_id'] ) ? $download['options']['license_id'] : false;
		if ( empty( $license_id ) ) {
			continue;
		}

		$license_expiration = edd_software_licensing()->get_license_expiration( $license_id );
		if ( 'lifetime' === $license_expiration ) {
			continue;
		}

		$bundle_id = eddwp_get_all_access_pass_id();

		if ( (int) $download_id !== $bundle_id ) {
			continue;
		}

		$all_access_pass_expiration = strtotime( '+1 Year', current_time( 'timestamp' ) );
		$args['expiration'] = date( 'Y-m-d H:i:s', $all_access_pass_expiration );

		$license = new EDD_SL_License( $license_id );
		$license->expiration = $all_access_pass_expiration;
	}

	return $args;

}
add_filter( 'edd_recurring_pre_record_signup_args', 'eddwp_handle_all_access_pass_upgrade_expiration', 99, 2 );

/**
 * Show the if the customer has an active All Access Pass on the customer card
 */
function eddwp_all_access_customer_card( $customer ) {

	$bundle_id      = eddwp_get_all_access_pass_id();
	$has_all_access = edd_all_access_check( array( 'customer_id' => $customer->id, 'download_id' => $bundle_id ) );

	if ( $has_all_access['success'] ) {
		?><span class="edd-fm status approved">All Access</span><?php
	}
}
add_action( 'edd_after_customer_edit_link', 'eddwp_all_access_customer_card', 10, 1 );

function eddwp_all_access_payment_details( $payment_id ) {

	if ( ! function_exists( 'edd_all_access_check' ) ) {
		return;
	}

	$bundle_id      = eddwp_get_all_access_pass_id();
	$customer_id    = edd_get_payment_customer_id( $payment_id );
	$has_all_access = edd_all_access_check( array( 'customer_id' => $customer_id, 'download_id' => $bundle_id ) );

	if ( $has_all_access['success'] ) {
		?><span class="edd-fm status approved">All Access</span><?php
	}
}
add_action( 'edd_payment_view_details', 'eddwp_all_access_payment_details', 10, 1 );

/*
 * Display checkbox to cancel existing subscriptions if purchasing the All Access Pass
 */
function eddwp_edd_display_sub_cancellation_checkbox() {

	$is_checkout = ( isset( $_POST['action'] ) && 'edd_load_gateway' === $_POST['action'] ) || edd_is_checkout();
	if ( ! $is_checkout ) {
		return;
	}

	$cart_contents = array_values( edd_get_cart_contents() );

	// Don't show the checkbox if there is more than one product in the cart.
	if ( count( $cart_contents ) > 1 ) {
		return;
	}

	$cart_item = $cart_contents[0];
	$bundle_id = eddwp_get_all_access_pass_id();
	if ( (int) $cart_item['id'] !== $bundle_id ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	ob_start();
	$subscriber    = new EDD_Recurring_Subscriber( get_current_user_id(), true );
	$subscriptions = $subscriber->get_subscriptions();

	if ( ! empty( $subscriptions ) ) {
		$notice_subs = array();
		foreach ( $subscriptions as $sub ) {

			if ( 'cancelled' !== $sub->status ) {
				$notice_download = new EDD_Download( $sub->product_id );
				$notice_subs[]   = $notice_download->get_name();
			}

		}
		$sub_count   = count( $notice_subs );
		$show_notice = $sub_count > 0 ? true: false;

		if ( $show_notice ) {
			$notice_subs = implode( ', ', $notice_subs );
			?>
			<script>
				jQuery(function($) {
					$('#eddwp-confirm-cancel-subs').change(function () {
						var checked = $(this).is(':checked');
						var target          = $('input[name="edds_has_other_subs"]');
						var target_wrapper  = target.parent().parent();
						if (checked) {
							target.prop( 'disabled', 'disabled' );
							target_wrapper.hide();
						} else {
							target_wrapper.show();
							target.prop( 'disabled', '' );
						}
					});
				});
			</script>
			<div class="edd-alert edd-alert-warn">
				<p>
					<input type="checkbox" id="eddwp-confirm-cancel-subs" name="eddwp_confirm_cancel_subs" value="1" />
					<span>
						<label for="eddwp-confirm-cancel-subs">
							<?php
							printf( _n( 'Check this box to cancel your existing subscription for %s after your purchase of All Access pass is complete.',
								'Check this box to have the following subscriptions cancelled after your purchase of All Access Pass is complete: %s',
								$sub_count,
								'edd-custom-functions' ),
								$notice_subs );
							?>
						</label>
					</span>
					<span>
						<em><small>Or you can also do this manually from your Account, once your purchase is complete.</small></em>
					</span>
				</p>
			</div>
			<?php
		}
	}
	echo ob_get_clean();
}
add_action( 'edd_purchase_form_before_submit', 'eddwp_edd_display_sub_cancellation_checkbox' );

function eddwp_store_sub_cancellation_selection( $payment_id, $payment_data ) {
	$cancel_subs_on_complete = isset( $_POST['eddwp_confirm_cancel_subs'] ) ? intval( $_POST['eddwp_confirm_cancel_subs'] ): 0;

	// If the user has selected to cancel the subscriptions, store a meta value so we can do so on payment completion.
	if ( ! empty( $cancel_subs_on_complete ) ) {
		$payment = edd_get_payment( $payment_id );
		$payment->update_meta( '_edd_cancel_existing_subs', $cancel_subs_on_complete );
		$payment->add_note( 'Customer selected to cancel existing subscriptions at checkout.' );
	}
}
add_action( 'edd_insert_payment', 'eddwp_store_sub_cancellation_selection', 10, 2 );

function eddwp_process_subscription_cancellations( $payment_id ) {
	$payment = edd_get_payment( $payment_id );
	if ( empty( $payment ) ) {
		return;
	}

	$should_cancel_subs = $payment->get_meta( '_edd_cancel_existing_subs' );
	if ( empty( $should_cancel_subs ) ) {
		return;
	}

	$subscriber    = new EDD_Recurring_Subscriber( $payment->user_id, true );
	$subscriptions = $subscriber->get_subscriptions();

	$all_access_id = eddwp_get_all_access_pass_id();
	foreach ( $subscriptions as $subscription ) {

		// Don't cancel the All Access Pass subscription.
		if ( (int) $subscription->product_id === (int) $all_access_id ) {
			continue;
		}

		if ( $subscription->can_cancel() ) {
			// This do action is required in order for the subscription to get cancelled at the gateway
			do_action( 'edd_recurring_cancel_' . $subscription->gateway . '_subscription', $subscription, true );
			$subscription->cancel( $subscription, true );
			$subscription->add_note( sprintf( 'Customer selected to cancel subscription while purchasing All Access Pass on Payment #%d', $payment_id ) );
		}

	}
}
add_action( 'edd_after_payment_actions', 'eddwp_process_subscription_cancellations', 10, 1 );

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

/*
 * If the page loaded is the homepage, we don't need to start a session if one doesn't exist
 *
 * @param  bool $start_session
 * @return bool
 */
function eddwp_maybe_start_session( $start_session ) {

	if ( '/' == $_SERVER['REQUEST_URI'] ) {
		$start_session = false;
	}

	if( false !== strpos( $_SERVER['REQUEST_URI'], '/downloads' ) && '/downloads/' === trailingslashit( $_SERVER['REQUEST_URI'] ) ) {
		$start_session = false;
	}

	if( empty( $_REQUEST['edd_action'] ) && false === strpos( $_SERVER['REQUEST_URI'], '/downloads' ) ) {
		//	$start_session = false;
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

	// Finally, if there is a discount in the GET parameters, we should always start a session, so it applies correctly.
	if ( ! empty( $_GET['discount'] ) ) {
		$start_session = true;
	}

	return $start_session;
}
add_filter( 'edd_start_session', 'eddwp_maybe_start_session', 10, 1 );

/* SearchWP Mods */
add_filter( 'searchwp_background_deltas', '__return_false' );
add_filter( 'searchwp_missing_integration_notices', '__return_false' );

function pw_edd_searchwp_indexed_types( $types ) {

	return array( 'post', 'page', 'extension', 'docs', 'videos', 'theme', 'topic', 'forum', 'reply' );

}
add_filter( 'searchwp_indexed_post_types', 'pw_edd_searchwp_indexed_types' );



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

/*
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
	var price = <?php echo edd_get_payment_amount( $payment_id ); ?>;
	window.optimizely = window.optimizely || [];
	window.optimizely.push(['trackEvent', 'purchase_complete', {'revenue': price * 100}]);
</script>
<?php
}
add_action( 'wp_head', 'eddwp_optimizely_revenue_tracking', 11 );
*/

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

// Allow all usernames
add_filter( 'edd_validate_username', '__return_true' );

/**
 * Post Grid
 */
function eddwp_post_grid( $atts ) {
	$default = array(
		'categories'    => '',
		'cat'           => '',
		'category_name' => '',
		'tag'           => '',
		'columns' 		=> 3,
		'rows' 			=> 3,
		'orderby' 		=> 'date',
		'order' 		=> 'DESC',
		'offset' 		=> 0,
		'query' 		=> '',
		'crop'			=> '',
		'link' 			=> 0,
		'link_text' 	=> 'View All Posts',
		'link_url' 		=> 'http://google.com',
		'link_target' 	=> '_self'
	);
	shortcode_atts( $default, $atts );
	$post__in = explode( ',', $atts['include'] );
	$args = array(
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
		'post__in'       => $post__in,
		'post_type'      => 'any',
		'posts_per_page' => -1,
	);
	$query = new WP_Query( $args );
	ob_start();

	if ( $query->have_posts() ) :
		?>
		<div class="download-grid two-col narrow-grid download-grid-shortcode">
			<?php
			while ( $query->have_posts() ) : $query->the_post();
				?>
				<div class="download-grid-item">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="download-grid-thumb-wrap">
							<a href="<?php the_permalink(); ?>">
								<?php echo get_the_post_thumbnail( get_the_ID(), 'download-grid-thumb', array( 'class' => 'download-grid-thumb' ) ); ?>
							</a>
						</div>
					<?php endif; ?>
					<div class="download-grid-item-info">
						<h4 class="download-grid-title">
							<?php the_title( sprintf( '<h4 class="download-grid-title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h4>' ); ?>
						</h4>
						<?php echo get_post_meta( get_the_ID(), 'ecpt_shortdescription', true ); ?>
					</div>
				</div>
				<?php
			endwhile;
			?>
		</div>
		<?php
		wp_reset_postdata();
	endif;

	return ob_get_clean();
}
add_shortcode( 'post_grid', 'eddwp_post_grid' );


/**
 * Divider
 */
function eddwp_shortcode_divider( $atts, $content = null ) {
	return '<hr class="divider-shortcode">';
}
add_shortcode( 'divider', 'eddwp_shortcode_divider' );


/**
 * Clear Row
 */
function eddwp_shortcode_clear() {
	return '<div class="clear"></div>';
}
add_shortcode( 'clear', 'eddwp_shortcode_clear' );


/**
 * DELETION CANDIDATE - 30 March 2016 - start watching for usage
 */
function eddwp_extensions_cb() {
	echo '<div class="extensions clearfix">';
	$extensions = new WP_Query(
		array(
			'post_type' => 'download',
			'nopaging'  => true,
			'orderby'   => 'rand'
		)
	);
	while ( $extensions ->have_posts() ) : $extensions->the_post(); ?>

		<div class="extension">
			<?php
			if ( has_category( '3rd Party' ) )
				echo '<i class="icon-third-party"></i>';
			elseif ( has_category( 'Free' ) )
				echo '<i class="icon-free"></i>';
			?>

			<a href="<?php the_permalink(); ?>" title="<?php get_the_title(); ?>">
				<?php the_post_thumbnail( 'showcase' ); ?>
				<h2><?php the_title(); ?></h2>
				<?php the_excerpt(); ?>
			</a>
		</div>

	<?php endwhile; ?>

	<?php echo '</div>';
}
add_shortcode( 'extensions', 'eddwp_extensions_cb' );


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

/**
 * Include additional site functions
 */
include( EDD_CUSTOM_FUNCTIONS . 'taxonomies.php' );
include( EDD_CUSTOM_FUNCTIONS . 'software-licensing.php' );

/**
 * Monster Insights - Google Optimize delay tweak
 */
add_action( 'plugins_loaded', function() {
	remove_action( 'monsterinsights_tracking_before', 'monsterinsights_performance_frontend_tracking_options_before_analytics' );
	add_action( 'monsterinsights_tracking_before', 'eddcf_monsterinsights_performance_frontend_tracking_options_before_analytics' );
}, 1000);

function eddcf_monsterinsights_performance_frontend_tracking_options_before_analytics() {
	ob_start();
	$pagehide = monsterinsights_get_option( 'goptimize_pagehide', false );
	if ( ! $pagehide ) {
		return;
	}

	$container = monsterinsights_get_option( 'goptimize_container', '' );
	if ( empty( $container ) ) {
		return;
	}
	?>
	<style>.monsterinsights-async-hide { opacity: 0 !important} </style>
	<script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.start=1*new Date;
			h.end=i=function(){s.className=s.className.replace(RegExp(' ?'+y),'')};
			(a[n]=a[n]||[]).hide=h;setTimeout(function(){i();h.end=null},c);h.timeout=c;
		})(window,document.documentElement,'monsterinsights-async-hide','dataLayer',500,
			{<?php echo "'" . esc_js( $container ) . "'"; ?>:true});</script>
	<?php
	echo ob_get_clean();
}
add_action( 'monsterinsights_tracking_before', 'eddcf_monsterinsights_performance_frontend_tracking_options_before_analytics' );

function eddcf_throw_cancel_warning() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$purchase_history_page = edd_get_option( 'purchase_history', '' );
	if ( ! is_page( $purchase_history_page ) ) {
		return;
	}

	?>
	<script>
		jQuery(function($) {
			$('.edd_subscription_cancel').click(function (e) {
				var accepted = confirm('By canceling your subscription, you may be opting out of any previous pricing agreements. Any purchases for this product going forward will be at the current pricing, which may differ from your existing subscription terms.');
				if (accepted == true) {
					// They clicked OK...so just move on.
				} else {
					e.preventDefault();
					return false;
				}
			});
		});
	</script>
	<?php
}
add_action( 'wp_footer', 'eddcf_throw_cancel_warning', 9999999999 );

function eddcf_renewal_license_warning( $item ) {
	if ( empty( $item['options']['is_renewal'] ) ) {
		return;
	}

	$payment_ids = get_post_meta( $item['options']['license_id'], '_edd_sl_payment_id' );

	if( ! is_array( $payment_ids ) ) {
		return;
	}

	$sub         = false;
	$payment_id  = end( $payment_ids );
	$download_id = edd_software_licensing()->get_download_id( $item['options']['license_id'] );

	if( $payment_id && $download_id )  {

		$subs_db = new EDD_Subscriptions_DB();
		$subs = $subs_db->get_subscriptions( array(
			'product_id'        => $download_id,
			'parent_payment_id' => $payment_id,
			'status'            => array( 'active', 'trialling' ),
			'number'            => 1,
			'order'             => 'DESC'
		) );

		if( $subs ) {
			$sub = array_pop( $subs );
		}

	}

	$period = EDD_Recurring()->get_pretty_subscription_frequency( $item['options']['recurring']['period'] );
	$price  = edd_get_download_price( $item['id'] );

	if ( ! empty( $sub ) ) {
		?>
		<tr class="renew-existing-sub-warning edd-alert edd-alert-warn">
			<td colspan="3">
				<p>
					The above license for <strong><?php echo edd_get_cart_item_name( $item ); ?></strong> is associated with an existing subscription, which renews <?php echo lcfirst( $period ); ?> at <strong><?php echo edd_currency_filter( edd_sanitize_amount( $sub->recurring_amount ) ); ?></strong>. By manually renewing, your existing subscription will be canceled and the new one will renew <?php echo lcfirst( $period ); ?> at <strong><?php echo edd_currency_filter( edd_sanitize_amount( $price ) ); ?></strong>. Doing this may also opt you out of any existing subscription terms.
				</p>
			</td>
		</tr>

		<?php
	}

}
add_action( 'edd_checkout_table_body_last', 'eddcf_renewal_license_warning', 999, 1 );

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
