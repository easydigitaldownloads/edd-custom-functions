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
function eddwp_shortcode_button_link( $atts, $content = NULL ) {

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
add_shortcode( 'button', 'eddwp_shortcode_button_link' );


/**
 * [toggle title="Button text!"]Hidden content.[/toggle]
 *
 * @param $atts
 * @param null $content
 *
 * @return string
 */
function eddwp_shortcode_toggle_content( $atts, $content = NULL ) {

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
add_shortcode( 'toggle', 'eddwp_shortcode_toggle_content' );


/**
 * [box style="warning"]You’re being warned about something![/box]
 *
 * @param $atts
 * @param null $content
 *
 * @return string
 */
function eddwp_shortcode_box_content( $atts, $content = NULL ) {

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
add_shortcode( 'box', 'eddwp_shortcode_box_content' );