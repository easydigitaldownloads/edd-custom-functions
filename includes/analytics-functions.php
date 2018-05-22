<?php
/*
 * analytics-functions.php
 */


// Monster Insights - Google Optimize delay tweak
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