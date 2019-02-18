<?php
/**
 * Metaboxes and fields for various post types
 */

/**
 * Extensions (Downloads) meta
 */
$extensionmeta_1_metabox = array(
	'id'       => 'extensionmeta',
	'title'    => 'Download Meta',
	'page'     => array( 'download' ),
	'context'  => 'normal',
	'priority' => 'default',
	'fields'   => array(
		array(
			'name'        => 'Short Description',
			'desc'        => 'Displayed as short descriptions of the download.',
			'id'          => 'ecpt_shortdescription',
			'class'       => 'ecpt_shortdescription',
			'type'        => 'textarea',
			'rich_editor' => 0,
			'max'         => 0
		),
		array(
			'name'        => 'Developer',
			'desc'        => 'Download developer (for core downloads, use "Easy Digital Downloads Team")',
			'id'          => 'ecpt_developer',
			'class'       => 'ecpt_developer',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0
		),
		array(
			'name'        => 'Is External',
			'desc'        => 'Is this download sold or hosted off site?',
			'id'          => 'ecpt_is_external',
			'class'       => 'ecpt_is_external',
			'type'        => 'checkbox',
			'rich_editor' => 0,
			'max'         => 0
		),
		array(
			'name'        => 'External URL',
			'desc'        => 'The URL of the off site page.',
			'id'          => 'ecpt_externalurl',
			'class'       => 'ecpt_externalurl',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0
		),
		array(
			'name'        => 'Documentation Link',
			'desc'        => 'Paste the URL to the download\\\'s documentation',
			'id'          => 'ecpt_documentationlink',
			'class'       => 'ecpt_documentationlink',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0
		),
		array(
			'name'        => 'Demo Link',
			'desc'        => 'Paste the URL to the product demo.',
			'id'          => 'ecpt_demolink',
			'class'       => 'ecpt_demolink',
			'type'        => 'text',
			'rich_editor' => 1,
			'max'         => 0
		),
		array(
			'name'        => 'Minimum WP',
			'desc'        => 'Enter minimum WordPress version required (if applicable)',
			'id'          => 'ecpt_minimumwp',
			'class'       => 'ecpt_minimumwp',
			'type'        => 'text',
			'rich_editor' => 1,
			'max'         => 0
		),
		array(
			'name'        => 'Minimum EDD',
			'desc'        => 'Enter minimum EDD version required (if applicable)',
			'id'          => 'ecpt_minimumedd',
			'class'       => 'ecpt_minimumedd',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0
		),
		array(
			'name'        => 'Minimum PHP',
			'desc'        => 'Enter minimum PHP version required (if applicable)',
			'id'          => 'ecpt_minimumphp',
			'class'       => 'ecpt_minimumphp',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0
		),
	)
);

// Add extensions (downloads) metabox
function ecpt_add_extensionmeta_1_meta_box() {
	global $extensionmeta_1_metabox;

	foreach ( $extensionmeta_1_metabox['page'] as $page ) {
		add_meta_box(
			$extensionmeta_1_metabox['id'],
			$extensionmeta_1_metabox['title'],
			'ecpt_show_extensionmeta_1_box',
			$page,
			'normal',
			'default',
			$extensionmeta_1_metabox
		);
	}
}
add_action( 'admin_menu', 'ecpt_add_extensionmeta_1_meta_box' );

// Show extensions (downloads) metabox
function ecpt_show_extensionmeta_1_box() {
	global $post, $extensionmeta_1_metabox, $ecpt_prefix, $wp_version;

	// Use nonce for verification
	echo '<input type="hidden" name="ecpt_extensionmeta_1_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	foreach ( $extensionmeta_1_metabox['fields'] as $field ) {
		// get current post meta data

		$meta = get_post_meta( $post->ID, $field['id'], true );

		echo '<tr>',
		'<th style="width:20%"><label for="', $field['id'], '">', stripslashes( $field['name'] ), '</label></th>',
			'<td class="ecpt_field_type_' . str_replace( ' ', '_', $field['type'] ) . '">';
		switch ( $field['type'] ) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:97%" /><br/>', '', stripslashes( $field['desc'] );
				break;
			case 'date':
				if ( $meta ) {
					$value = ecpt_timestamp_to_date( $meta );
				} else {
					$value = '';
				}
				echo '<input type="text" class="ecpt_datepicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value . '" size="30" style="width:97%" />' . '' . stripslashes( $field['desc'] );
				break;
			case 'upload':
				echo '<input type="text" class="ecpt_upload_field" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:80%" /><input class="ecpt_upload_image_button" type="button" value="Upload Image" /><br/>', '', stripslashes( $field['desc'] );
				break;
			case 'textarea':

				if ( $field['rich_editor'] == 1 ) {
					if ( $wp_version >= 3.3 ) {
						echo wp_editor( $meta, $field['id'], array( 'textarea_name' => $field['id'] ) );
					} else {
						// older versions of WP
						$editor = '';
						if ( ! post_type_supports( $post->post_type, 'editor' ) ) {
							$editor = wp_tiny_mce( true, array( 'editor_selector'   => $field['class'],
							                                    'remove_linebreaks' => false
							) );
						}
						$field_html = '<div style="width: 97%; border: 1px solid #DFDFDF;"><textarea name="' . $field['id'] . '" class="' . $field['class'] . '" id="' . $field['id'] . '" cols="60" rows="8" style="width:100%">' . $meta . '</textarea></div><br/>' . __( stripslashes( $field['desc'] ) );
						echo $editor . $field_html;
					}
				} else {
					echo '<div style="width: 100%;"><textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="8" style="width:97%">', $meta ? $meta : '', '</textarea></div>', '', stripslashes( $field['desc'] );
				}

				break;
			case 'select':
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ( $field['options'] as $option ) {
					echo '<option value="' . $option . '"', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>', '', stripslashes( $field['desc'] );
				break;
			case 'radio':
				foreach ( $field['options'] as $option ) {
					echo '<input type="radio" name="', $field['id'], '" value="', $option, '"', $meta == $option ? ' checked="checked"' : '', ' /> ', $option;
				}
				echo '<br/>' . stripslashes( $field['desc'] );
				break;
			case 'multicheck':
				foreach ( $field['options'] as $option ) {
					echo '<input type="checkbox" name="' . $field['id'] . '[' . $option . ']" value="' . $option . '"' . checked( true, in_array( $option, $meta ), false ) . '/> ' . $option;
				}
				echo '<br/>' . stripslashes( $field['desc'] );
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' /> ';
				echo stripslashes( $field['desc'] );
				break;
			case 'slider':
				echo '<input type="text" rel="' . $field['max'] . '" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="1" style="float: left; margin-right: 5px" />';
				echo '<div class="ecpt-slider" rel="' . $field['id'] . '" style="float: left; width: 60%; margin: 5px 0 0 0;"></div>';
				echo '<div style="width: 100%; clear: both;">' . stripslashes( $field['desc'] ) . '</div>';
				break;
			case 'repeatable' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_field_name" value=""/>';
				if ( is_array( $meta ) ) {
					$count = 1;
					foreach ( $meta as $key => $value ) {
						$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[ $key ] . '" size="30" style="width:90%" />';
						if ( $count > 1 ) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count ++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:90%" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_field button-secondary">' . __( 'Add New', 'ecpt' ) . '</button>  ' . __( stripslashes( $field['desc'] ) );

				echo $field_html;

				break;

			case 'repeatable upload' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_upload_field_name" value=""/>';
				if ( is_array( $meta ) ) {
					$count = 1;
					foreach ( $meta as $key => $value ) {
						$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[ $key ] . '" size="30" style="width:80%" /><button class="button-secondary ecpt_upload_image_button">Upload File</button>';
						if ( $count > 1 ) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count ++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:80%" /><input class="button-secondary ecpt_upload_image_button" type="button" value="Upload File" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_upload_field button-secondary">' . __( 'Add New', 'ecpt' ) . '</button>  ' . __( stripslashes( $field['desc'] ) );

				echo $field_html;

				break;
		}
		echo '<td>',
		'</tr>';
	}

	echo '</table>';
}

// Save data from extensions (downloads) metabox
function ecpt_extensionmeta_1_save( $post_id ) {
	global $post, $extensionmeta_1_metabox;

	// verify nonce
	if ( ! isset( $_POST['ecpt_extensionmeta_1_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ecpt_extensionmeta_1_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	foreach ( $extensionmeta_1_metabox['fields'] as $field ) {

		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];

		if ( $new && $new != $old ) {
			if ( $field['type'] == 'date' ) {
				$new = ecpt_format_date( $new );
				update_post_meta( $post_id, $field['id'], $new );
			} else {
				if ( is_string( $new ) ) {
					$new = $new;
				}
				update_post_meta( $post_id, $field['id'], $new );


			}
		} elseif ( '' == $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}
add_action( 'save_post', 'ecpt_extensionmeta_1_save' );


/**
 * Testimonials meta
 */
$testimonialmeta_2_metabox = array(
	'id'       => 'testimonialmeta',
	'title'    => 'Testimonial Meta',
	'page'     => array( 'testimonials' ),
	'context'  => 'normal',
	'priority' => 'default',
	'fields'   => array(
		array(
			'name'        => 'Testimonial Author',
			'desc'        => 'The testimonial author\\\'s name',
			'id'          => 'ecpt_author',
			'class'       => 'ecpt_author',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0
		),
		array(
			'name'        => 'Author URL',
			'desc'        => 'The testimonial author\\\'s URL',
			'id'          => 'ecpt_url',
			'class'       => 'ecpt_url',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0
		),
	)
);

// Add testimonials metabox
function ecpt_add_testimonialmeta_2_meta_box() {
	global $testimonialmeta_2_metabox;

	foreach ( $testimonialmeta_2_metabox['page'] as $page ) {
		add_meta_box(
			$testimonialmeta_2_metabox['id'],
			$testimonialmeta_2_metabox['title'],
			'ecpt_show_testimonialmeta_2_box',
			$page,
			'normal',
			'default',
			$testimonialmeta_2_metabox
		);
	}
}
add_action( 'admin_menu', 'ecpt_add_testimonialmeta_2_meta_box' );

// Show testimonials metabox
function ecpt_show_testimonialmeta_2_box() {
	global $post, $testimonialmeta_2_metabox, $ecpt_prefix, $wp_version;

	// Use nonce for verification
	echo '<input type="hidden" name="ecpt_testimonialmeta_2_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	foreach ( $testimonialmeta_2_metabox['fields'] as $field ) {
		// get current post meta data

		$meta = get_post_meta( $post->ID, $field['id'], true );

		echo '<tr>',
		'<th style="width:20%"><label for="', $field['id'], '">', stripslashes( $field['name'] ), '</label></th>',
			'<td class="ecpt_field_type_' . str_replace( ' ', '_', $field['type'] ) . '">';
		switch ( $field['type'] ) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:97%" /><br/>', '', stripslashes( $field['desc'] );
				break;
			case 'date':
				if ( $meta ) {
					$value = ecpt_timestamp_to_date( $meta );
				} else {
					$value = '';
				}
				echo '<input type="text" class="ecpt_datepicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value . '" size="30" style="width:97%" />' . '' . stripslashes( $field['desc'] );
				break;
			case 'upload':
				echo '<input type="text" class="ecpt_upload_field" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:80%" /><input class="ecpt_upload_image_button" type="button" value="Upload Image" /><br/>', '', stripslashes( $field['desc'] );
				break;
			case 'textarea':

				if ( $field['rich_editor'] == 1 ) {
					if ( $wp_version >= 3.3 ) {
						echo wp_editor( $meta, $field['id'], array( 'textarea_name' => $field['id'] ) );
					} else {
						// older versions of WP
						$editor = '';
						if ( ! post_type_supports( $post->post_type, 'editor' ) ) {
							$editor = wp_tiny_mce( true, array( 'editor_selector'   => $field['class'],
							                                    'remove_linebreaks' => false
							) );
						}
						$field_html = '<div style="width: 97%; border: 1px solid #DFDFDF;"><textarea name="' . $field['id'] . '" class="' . $field['class'] . '" id="' . $field['id'] . '" cols="60" rows="8" style="width:100%">' . $meta . '</textarea></div><br/>' . __( stripslashes( $field['desc'] ) );
						echo $editor . $field_html;
					}
				} else {
					echo '<div style="width: 100%;"><textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="8" style="width:97%">', $meta ? $meta : '', '</textarea></div>', '', stripslashes( $field['desc'] );
				}

				break;
			case 'select':
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ( $field['options'] as $option ) {
					echo '<option value="' . $option . '"', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>', '', stripslashes( $field['desc'] );
				break;
			case 'radio':
				foreach ( $field['options'] as $option ) {
					echo '<input type="radio" name="', $field['id'], '" value="', $option, '"', $meta == $option ? ' checked="checked"' : '', ' /> ', $option;
				}
				echo '<br/>' . stripslashes( $field['desc'] );
				break;
			case 'multicheck':
				foreach ( $field['options'] as $option ) {
					echo '<input type="checkbox" name="' . $field['id'] . '[' . $option . ']" value="' . $option . '"' . checked( true, in_array( $option, $meta ), false ) . '/> ' . $option;
				}
				echo '<br/>' . stripslashes( $field['desc'] );
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' /> ';
				echo stripslashes( $field['desc'] );
				break;
			case 'slider':
				echo '<input type="text" rel="' . $field['max'] . '" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="1" style="float: left; margin-right: 5px" />';
				echo '<div class="ecpt-slider" rel="' . $field['id'] . '" style="float: left; width: 60%; margin: 5px 0 0 0;"></div>';
				echo '<div style="width: 100%; clear: both;">' . stripslashes( $field['desc'] ) . '</div>';
				break;
			case 'repeatable' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_field_name" value=""/>';
				if ( is_array( $meta ) ) {
					$count = 1;
					foreach ( $meta as $key => $value ) {
						$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[ $key ] . '" size="30" style="width:90%" />';
						if ( $count > 1 ) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count ++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:90%" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_field button-secondary">' . __( 'Add New', 'ecpt' ) . '</button>  ' . __( stripslashes( $field['desc'] ) );

				echo $field_html;

				break;

			case 'repeatable upload' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_upload_field_name" value=""/>';
				if ( is_array( $meta ) ) {
					$count = 1;
					foreach ( $meta as $key => $value ) {
						$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[ $key ] . '" size="30" style="width:80%" /><button class="button-secondary ecpt_upload_image_button">Upload File</button>';
						if ( $count > 1 ) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count ++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:80%" /><input class="button-secondary ecpt_upload_image_button" type="button" value="Upload File" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_upload_field button-secondary">' . __( 'Add New', 'ecpt' ) . '</button>  ' . __( stripslashes( $field['desc'] ) );

				echo $field_html;

				break;
		}
		echo '<td>',
		'</tr>';
	}

	echo '</table>';
}

// Save data from testimonials metabox
function ecpt_testimonialmeta_2_save( $post_id ) {
	global $post;
	global $testimonialmeta_2_metabox;

	// verify nonce
	if ( ! isset( $_POST['ecpt_testimonialmeta_2_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ecpt_testimonialmeta_2_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	foreach ( $testimonialmeta_2_metabox['fields'] as $field ) {

		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];

		if ( $new && $new != $old ) {
			if ( $field['type'] == 'date' ) {
				$new = ecpt_format_date( $new );
				update_post_meta( $post_id, $field['id'], $new );
			} else {
				if ( is_string( $new ) ) {
					$new = $new;
				}
				update_post_meta( $post_id, $field['id'], $new );


			}
		} elseif ( '' == $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}
add_action( 'save_post', 'ecpt_testimonialmeta_2_save' );


/**
 * Partners meta
 */
$partnerdetails_4_metabox = array(
	'id'       => 'partnerdetails',
	'title'    => 'Partner Details',
	'page'     => array( 'partner' ),
	'context'  => 'normal',
	'priority' => 'default',
	'fields'   => array(
		array(
			'name'        => 'Partner URL',
			'desc'        => 'The URL we will send people to from all partner links.',
			'id'          => 'ecpt_partnerurl',
			'class'       => 'ecpt_partnerurl',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0
		),
	)
);

// Add partners metabox
function ecpt_add_partnerdetails_4_meta_box() {
	global $partnerdetails_4_metabox;

	foreach ( $partnerdetails_4_metabox['page'] as $page ) {
		add_meta_box( $partnerdetails_4_metabox['id'],
			$partnerdetails_4_metabox['title'],
			'ecpt_show_partnerdetails_4_box',
			$page,
			'normal',
			'default',
			$partnerdetails_4_metabox
		);
	}
}
add_action( 'admin_menu', 'ecpt_add_partnerdetails_4_meta_box' );

// Show partners metabox
function ecpt_show_partnerdetails_4_box() {
	global $post, $partnerdetails_4_metabox, $ecpt_prefix, $wp_version;

	// Use nonce for verification
	echo '<input type="hidden" name="ecpt_partnerdetails_4_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	foreach ( $partnerdetails_4_metabox['fields'] as $field ) {
		// get current post meta data

		$meta = get_post_meta( $post->ID, $field['id'], true );

		echo '<tr>',
		'<th style="width:20%"><label for="', $field['id'], '">', stripslashes( $field['name'] ), '</label></th>',
			'<td class="ecpt_field_type_' . str_replace( ' ', '_', $field['type'] ) . '">';
		switch ( $field['type'] ) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:97%" /><br/>', '', stripslashes( $field['desc'] );
				break;
			case 'date':
				if ( $meta ) {
					$value = ecpt_timestamp_to_date( $meta );
				} else {
					$value = '';
				}
				echo '<input type="text" class="ecpt_datepicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value . '" size="30" style="width:97%" />' . '' . stripslashes( $field['desc'] );
				break;
			case 'upload':
				echo '<input type="text" class="ecpt_upload_field" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:80%" /><input class="ecpt_upload_image_button" type="button" value="Upload Image" /><br/>', '', stripslashes( $field['desc'] );
				break;
			case 'textarea':

				if ( $field['rich_editor'] == 1 ) {
					if ( $wp_version >= 3.3 ) {
						echo wp_editor( $meta, $field['id'], array( 'textarea_name' => $field['id'] ) );
					} else {
						// older versions of WP
						$editor = '';
						if ( ! post_type_supports( $post->post_type, 'editor' ) ) {
							$editor = wp_tiny_mce( true, array( 'editor_selector'   => $field['class'],
							                                    'remove_linebreaks' => false
							) );
						}
						$field_html = '<div style="width: 97%; border: 1px solid #DFDFDF;"><textarea name="' . $field['id'] . '" class="' . $field['class'] . '" id="' . $field['id'] . '" cols="60" rows="8" style="width:100%">' . $meta . '</textarea></div><br/>' . __( stripslashes( $field['desc'] ) );
						echo $editor . $field_html;
					}
				} else {
					echo '<div style="width: 100%;"><textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="8" style="width:97%">', $meta ? $meta : '', '</textarea></div>', '', stripslashes( $field['desc'] );
				}

				break;
			case 'select':
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ( $field['options'] as $option ) {
					echo '<option value="' . $option . '"', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>', '', stripslashes( $field['desc'] );
				break;
			case 'radio':
				foreach ( $field['options'] as $option ) {
					echo '<input type="radio" name="', $field['id'], '" value="', $option, '"', $meta == $option ? ' checked="checked"' : '', ' /> ', $option;
				}
				echo '<br/>' . stripslashes( $field['desc'] );
				break;
			case 'multicheck':
				foreach ( $field['options'] as $option ) {
					echo '<input type="checkbox" name="' . $field['id'] . '[' . $option . ']" value="' . $option . '"' . checked( true, in_array( $option, $meta ), false ) . '/> ' . $option;
				}
				echo '<br/>' . stripslashes( $field['desc'] );
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' /> ';
				echo stripslashes( $field['desc'] );
				break;
			case 'slider':
				echo '<input type="text" rel="' . $field['max'] . '" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="1" style="float: left; margin-right: 5px" />';
				echo '<div class="ecpt-slider" rel="' . $field['id'] . '" style="float: left; width: 60%; margin: 5px 0 0 0;"></div>';
				echo '<div style="width: 100%; clear: both;">' . stripslashes( $field['desc'] ) . '</div>';
				break;
			case 'repeatable' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_field_name" value=""/>';
				if ( is_array( $meta ) ) {
					$count = 1;
					foreach ( $meta as $key => $value ) {
						$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[ $key ] . '" size="30" style="width:90%" />';
						if ( $count > 1 ) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count ++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:90%" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_field button-secondary">' . __( 'Add New', 'ecpt' ) . '</button>  ' . __( stripslashes( $field['desc'] ) );

				echo $field_html;

				break;

			case 'repeatable upload' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_upload_field_name" value=""/>';
				if ( is_array( $meta ) ) {
					$count = 1;
					foreach ( $meta as $key => $value ) {
						$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[ $key ] . '" size="30" style="width:80%" /><button class="button-secondary ecpt_upload_image_button">Upload File</button>';
						if ( $count > 1 ) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count ++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:80%" /><input class="button-secondary ecpt_upload_image_button" type="button" value="Upload File" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_upload_field button-secondary">' . __( 'Add New', 'ecpt' ) . '</button>  ' . __( stripslashes( $field['desc'] ) );

				echo $field_html;

				break;
		}
		echo '<td>',
		'</tr>';
	}

	echo '</table>';
}

// Save data from partners metabox
function ecpt_partnerdetails_4_save( $post_id ) {
	global $post;
	global $partnerdetails_4_metabox;

	// verify nonce
	if ( ! isset( $_POST['ecpt_partnerdetails_4_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ecpt_partnerdetails_4_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	foreach ( $partnerdetails_4_metabox['fields'] as $field ) {

		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];

		if ( $new && $new != $old ) {
			if ( $field['type'] == 'date' ) {
				$new = ecpt_format_date( $new );
				update_post_meta( $post_id, $field['id'], $new );
			} else {
				if ( is_string( $new ) ) {
					$new = $new;
				}
				update_post_meta( $post_id, $field['id'], $new );


			}
		} elseif ( '' == $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}
add_action( 'save_post', 'ecpt_partnerdetails_4_save' );


/**
 * Simple Notices Pro meta
 */
$simplenoticesproicon_6_metabox = array(
	'id'       => 'simplenoticesproicon',
	'title'    => 'Simple Notices Pro Icon',
	'page'     => array( 'notices' ),
	'context'  => 'normal',
	'priority' => 'default',
	'fields'   => array(
		array(
			'name'        => 'Font Awesome Icon Name',
			'desc'        => 'The unique part of the class for the Font Awesome icon. Ex. If the icon class is \\\'fa-shopping-cart\\\', enter \\\'shopping-cart\\\' here.',
			'id'          => 'ecpt_fa_icon',
			'class'       => 'ecpt_fa_icon',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0
		),
	)
);

// Add SNP metabox
function ecpt_add_simplenoticesproicon_6_meta_box() {
	global $simplenoticesproicon_6_metabox;

	foreach ( $simplenoticesproicon_6_metabox['page'] as $page ) {
		add_meta_box( $simplenoticesproicon_6_metabox['id'],
			$simplenoticesproicon_6_metabox['title'],
			'ecpt_show_simplenoticesproicon_6_box',
			$page,
			'normal',
			'default',
			$simplenoticesproicon_6_metabox
		);
	}
}
add_action( 'admin_menu', 'ecpt_add_simplenoticesproicon_6_meta_box' );

// Show SNP metabox
function ecpt_show_simplenoticesproicon_6_box() {
	global $post, $simplenoticesproicon_6_metabox, $ecpt_prefix, $wp_version;

	// Use nonce for verification
	echo '<input type="hidden" name="ecpt_simplenoticesproicon_6_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	foreach ( $simplenoticesproicon_6_metabox['fields'] as $field ) {
		// get current post meta data

		$meta = get_post_meta( $post->ID, $field['id'], true );

		echo '<tr>',
		'<th style="width:20%"><label for="', $field['id'], '">', stripslashes( $field['name'] ), '</label></th>',
			'<td class="ecpt_field_type_' . str_replace( ' ', '_', $field['type'] ) . '">';
		switch ( $field['type'] ) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:97%" /><br/>', '', stripslashes( $field['desc'] );
				break;
			case 'date':
				if ( $meta ) {
					$value = ecpt_timestamp_to_date( $meta );
				} else {
					$value = '';
				}
				echo '<input type="text" class="ecpt_datepicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value . '" size="30" style="width:97%" />' . '' . stripslashes( $field['desc'] );
				break;
			case 'upload':
				echo '<input type="text" class="ecpt_upload_field" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:80%" /><input class="ecpt_upload_image_button" type="button" value="Upload Image" /><br/>', '', stripslashes( $field['desc'] );
				break;
			case 'textarea':

				if ( $field['rich_editor'] == 1 ) {
					if ( $wp_version >= 3.3 ) {
						echo wp_editor( $meta, $field['id'], array( 'textarea_name' => $field['id'] ) );
					} else {
						// older versions of WP
						$editor = '';
						if ( ! post_type_supports( $post->post_type, 'editor' ) ) {
							$editor = wp_tiny_mce( true, array( 'editor_selector'   => $field['class'],
							                                    'remove_linebreaks' => false
							) );
						}
						$field_html = '<div style="width: 97%; border: 1px solid #DFDFDF;"><textarea name="' . $field['id'] . '" class="' . $field['class'] . '" id="' . $field['id'] . '" cols="60" rows="8" style="width:100%">' . $meta . '</textarea></div><br/>' . __( stripslashes( $field['desc'] ) );
						echo $editor . $field_html;
					}
				} else {
					echo '<div style="width: 100%;"><textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="8" style="width:97%">', $meta ? $meta : '', '</textarea></div>', '', stripslashes( $field['desc'] );
				}

				break;
			case 'select':
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ( $field['options'] as $option ) {
					echo '<option value="' . $option . '"', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>', '', stripslashes( $field['desc'] );
				break;
			case 'radio':
				foreach ( $field['options'] as $option ) {
					echo '<input type="radio" name="', $field['id'], '" value="', $option, '"', $meta == $option ? ' checked="checked"' : '', ' /> ', $option;
				}
				echo '<br/>' . stripslashes( $field['desc'] );
				break;
			case 'multicheck':
				foreach ( $field['options'] as $option ) {
					echo '<input type="checkbox" name="' . $field['id'] . '[' . $option . ']" value="' . $option . '"' . checked( true, in_array( $option, $meta ), false ) . '/> ' . $option;
				}
				echo '<br/>' . stripslashes( $field['desc'] );
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' /> ';
				echo stripslashes( $field['desc'] );
				break;
			case 'slider':
				echo '<input type="text" rel="' . $field['max'] . '" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="1" style="float: left; margin-right: 5px" />';
				echo '<div class="ecpt-slider" rel="' . $field['id'] . '" style="float: left; width: 60%; margin: 5px 0 0 0;"></div>';
				echo '<div style="width: 100%; clear: both;">' . stripslashes( $field['desc'] ) . '</div>';
				break;
			case 'repeatable' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_field_name" value=""/>';
				if ( is_array( $meta ) ) {
					$count = 1;
					foreach ( $meta as $key => $value ) {
						$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[ $key ] . '" size="30" style="width:90%" />';
						if ( $count > 1 ) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count ++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:90%" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_field button-secondary">' . __( 'Add New', 'ecpt' ) . '</button>  ' . __( stripslashes( $field['desc'] ) );

				echo $field_html;

				break;

			case 'repeatable upload' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_upload_field_name" value=""/>';
				if ( is_array( $meta ) ) {
					$count = 1;
					foreach ( $meta as $key => $value ) {
						$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[ $key ] . '" size="30" style="width:80%" /><button class="button-secondary ecpt_upload_image_button">Upload File</button>';
						if ( $count > 1 ) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count ++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:80%" /><input class="button-secondary ecpt_upload_image_button" type="button" value="Upload File" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_upload_field button-secondary">' . __( 'Add New', 'ecpt' ) . '</button>  ' . __( stripslashes( $field['desc'] ) );

				echo $field_html;

				break;
		}
		echo '<td>',
		'</tr>';
	}

	echo '</table>';
}

// Save data from SNP metabox
function ecpt_simplenoticesproicon_6_save( $post_id ) {
	global $post;
	global $simplenoticesproicon_6_metabox;

	// verify nonce
	if ( ! isset( $_POST['ecpt_simplenoticesproicon_6_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ecpt_simplenoticesproicon_6_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	foreach ( $simplenoticesproicon_6_metabox['fields'] as $field ) {

		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];

		if ( $new && $new != $old ) {
			if ( $field['type'] == 'date' ) {
				$new = ecpt_format_date( $new );
				update_post_meta( $post_id, $field['id'], $new );
			} else {
				if ( is_string( $new ) ) {
					$new = $new;
				}
				update_post_meta( $post_id, $field['id'], $new );


			}
		} elseif ( '' == $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}
add_action( 'save_post', 'ecpt_simplenoticesproicon_6_save' );