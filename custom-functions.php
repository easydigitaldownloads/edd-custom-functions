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
//add_filter( 'gform_helpscout_enable_cc', '__return_false' );

// Disable API request logging
add_filter( 'edd_api_log_requests', '__return_false' );

/*
 * Registers the upgrade path for All Access pass
 */
function pw_edd_all_access_upgrade_path( $paths, $download_id ) {

	if( false !== strpos( home_url(), 'staging' ) ) {

		$bundle_id = 1046254; // ID of the all access pass on staging

	} else {

		// TODO: set to real ID
		$bundle_id = 1150319; // ID of the all access pass on live
	
	}

	if( ! is_user_logged_in() || is_admin() ) {
		return $paths;
	}

	$discount  = 0.00;
	$customer  = new EDD_Customer( get_current_user_id(), true );

	if( ! $customer->purchase_value > 0 ) {
		return $paths;
	}

	$now = current_time( 'timestamp' );

	foreach( $customer->get_payments( 'publish' ) as $payment ) {

		if( ! $payment->total > 0 ) {
			continue; // Skip free payments
		}

		if( 'publish' !== $payment->status ) {
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

			if( ! empty( $item['item_number']['options']['is_renewal'] ) ) {
				continue; // Only count new purchases
			}

			$discount += ( $item['price'] - $item['tax'] ); // Add the purchase price to the discount

		}

	}

	if( $discount >= 899 ) {
		$discount = 898.00; // Min purchase price of $1.00
	}

	$paths[] = array(
		'download_id' => $bundle_id,
		'price_id'    => false,
		'discount'    => $discount,
		'pro_rated'   => false
	);

	return $paths;
}
add_filter( 'edd_sl_get_upgrade_paths', 'pw_edd_custom_upgrade_paths', 10, 2 );

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
	$license = get_post( $license_id );
	if( is_a( $license, 'WP_Post' ) && strtotime( $license->post_date ) < strtotime( 'September 9, 2017' ) ) {
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
