<?php
/*
 * shortcodes.php
 */


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