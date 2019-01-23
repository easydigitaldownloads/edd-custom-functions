<?php
/*
 * shortcodes.php
 */


/**
 * [button link="#"]Button Text[/button]
 *
 * @param $atts
 * @param null $content
 *
 * @return string
 */
function eddwp_shortcode_button( $atts, $content = NULL ) {

	shortcode_atts( array(

		// link – (required) Provide a URL to which the button should link.
		// If left blank, the button will not display.
		'link'      => '',

		// color – (optional) Set the button color to blue, darkblue, gray, green, or white.
		// Omit attribute for default blue button.
		'color'     => '',

		// secondary – (optional) Set value to true for a bordered button with no background color (still has border).
		// Omit attribute for default solid background.
		'secondary' => '',

		// external – (optional) Set value to true to open the link in another tab.
		// Omit the attribute to open in the same tab.
		'external'  => '',

		// icon – (optional) Provide the name of a Font Awesome icon.
		// If your desired icon’s name is fa-cloud-download, the attribute value should be cloud-download.
		// Only solid style icons are used.
		'icon'      => '',

	), $atts, 'button' );

	// Bail if no link target is provided
	if ( ! isset( $atts['link'] ) || empty( $atts['link'] ) ) {
		return '';
	}

	$color = isset( $atts['color'] ) && ! empty( $atts['color'] ) ? $atts['color'] : 'blue';

	// Default classes for the button output
	$button_classes = array( 'eddwp-button', 'button', $color );

	// Set as a secondary button style if desired
	if ( 'true' === $atts['secondary'] ) {
		$button_classes[] = 'secondary-button';
	}

	// Open link in a new tab if necessary
	$target = '';
	if ( 'true' === $atts['external'] ) {
		$target = 'target="_blank"';
	}

	// Add an option Font Awesome icon to the button text
	if ( ! empty( $atts['icon'] ) ) :
		$fontawesome = '<i class="fas fa-' . $atts['icon'] . '" aria-hidden="true"></i>';
	else :
		$fontawesome = '';
	endif;

	// Prepare the button classes for output
	$button_classes = implode( ' ', $button_classes );

	return '<p><a href="' . esc_url( $atts['link'] ) . '" class="' . $button_classes . '" ' . $target . '>' . $fontawesome . $content . '</a></p>';
}
add_shortcode( 'button', 'eddwp_shortcode_button' );


/**
 * [toggle title="Button text!"]Hidden content.[/toggle]
 *
 * @param $atts
 * @param null $content
 *
 * @return string
 */
function eddwp_shortcode_toggle( $atts, $content = NULL ) {

	shortcode_atts( array(

		// The text shown on the toggle button
		'title' => ''
	), $atts, 'toggle' );

	// Bail if no button text is provided
	if ( ! isset( $atts['title'] ) || empty( $atts['title'] ) ) {
		return '';
	}

	$content = wpautop( do_shortcode( stripslashes( $content ) ) );

	ob_start();
	?>

	<div class="tb-toggle">
		<a href="#" title="<?php echo $atts['title']; ?>" class="toggle-trigger secondary-button darkblue"><?php echo $atts['title']; ?></a>
		<div class="toggle-content"><?php echo $content; ?></div>
	</div>

	<?php
	$output = ob_get_clean();
	return $output;
}
add_shortcode( 'toggle', 'eddwp_shortcode_toggle' );


/**
 * [box style="warning"]You’re being warned about something![/box]
 *
 * @param $atts
 * @param null $content
 *
 * @return string
 */
function eddwp_shortcode_box( $atts, $content = NULL ) {

	shortcode_atts( array(

		// Which box style is this?
		'style' => ''
	), $atts, 'box' );

	// Give the box a style if no style is provided
	if ( ! isset( $atts['style'] ) || empty( $atts['style'] ) ) {
		$style = 'info-box';
	} else {
		$style = $atts['style'];
	}

	return '<div class="' . $style . '">' . do_shortcode( $content ) . '</div>';
}
add_shortcode( 'box', 'eddwp_shortcode_box' );


/**
 * Display a grid of downloads (this is styled and current, May 2018)
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
add_shortcode( 'post_grid', 'eddwp_post_grid' ); // let's not use this anymore
add_shortcode( 'downloads_grid', 'eddwp_post_grid' );