<?php
/*
 * all-access-functions.php
 */


// Get the download ID of the All Access Pass
function eddwp_get_all_access_pass_id() {
	$aap = get_page_by_path( 'all-access-pass', OBJECT, 'download' );
	return $aap->ID;
}


/*
 * Registers the upgrade path for All Access pass
 */
function pw_edd_all_access_upgrade_path( $paths, $download_id ) {

	$aap_id = eddwp_get_all_access_pass_id();

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

			if( $aap_id === (int) $item['id'] ) {
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

	$paths[$aap_id] = array(
		'download_id' => $aap_id,
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

		$aap_id = eddwp_get_all_access_pass_id();

		if ( (int) $download_id !== $aap_id ) {
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

		$aap_id = eddwp_get_all_access_pass_id();

		if ( (int) $download_id !== $aap_id ) {
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

	$aap_id      = eddwp_get_all_access_pass_id();
	$has_all_access = edd_all_access_check( array( 'customer_id' => $customer->id, 'download_id' => $aap_id ) );

	if ( $has_all_access['success'] ) {
		?><span class="edd-fm status approved">All Access</span><?php
	}
}
add_action( 'edd_after_customer_edit_link', 'eddwp_all_access_customer_card', 10, 1 );

function eddwp_all_access_payment_details( $payment_id ) {

	if ( ! function_exists( 'edd_all_access_check' ) ) {
		return;
	}

	$aap_id      = eddwp_get_all_access_pass_id();
	$customer_id    = edd_get_payment_customer_id( $payment_id );
	$has_all_access = edd_all_access_check( array( 'customer_id' => $customer_id, 'download_id' => $aap_id ) );

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
	$aap_id = eddwp_get_all_access_pass_id();
	if ( (int) $cart_item['id'] !== $aap_id ) {
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