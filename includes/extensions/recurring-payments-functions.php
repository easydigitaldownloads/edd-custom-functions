<?php
/*
 * recurring-payments-functions.php
 */


/**
 * Warn customers of the consequences of manually renewing a subscription early.
 */
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

		<div class="renew-existing-sub-warning warning">
			<p>The above license for <strong><?php echo edd_get_cart_item_name( $item ); ?></strong> is associated with an existing subscription, which renews <?php echo lcfirst( $period ); ?> at <strong><?php echo edd_currency_filter( edd_sanitize_amount( $sub->recurring_amount ) ); ?></strong>. By manually renewing, your existing subscription will be canceled and the new one will renew <?php echo lcfirst( $period ); ?> at <strong><?php echo edd_currency_filter( edd_sanitize_amount( $price ) ); ?></strong>. Doing this may also opt you out of any existing subscription terms.</p>
		</div>

		<?php
	}

}
add_action( 'edd_checkout_cart_item_title_after', 'eddcf_renewal_license_warning', 999, 1 );


/**
 * Warn customers of the consequences when they click the subscription cancellation link.
 */
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
//add_action( 'wp_footer', 'eddcf_throw_cancel_warning', 9999999999 );