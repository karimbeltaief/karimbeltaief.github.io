<?php
/*-----------------------------------------------------------------------------------*/
/*	Module 1
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_grids') ) {
	function homey_grids($atts, $content = null)
	{
		extract(shortcode_atts(array(
			'homey_grid_type' => '',
			'homey_grid_from' => '',
			'homey_show_child' => '',
			'orderby' 			=> '',
			'order' 			=> '',
			'homey_hide_empty' => '',
			'no_of_terms' 		=> '',
			'listing_type' => '',
			'room_type' => '',
			'listing_area' => '',
			'listing_state' => '',
			'listing_city' => '',
			'listing_country' => ''
		), $atts));

		ob_start();
		$module_type = '';
		$homey_local = homey_get_localization();

		$slugs = '';

		if( $homey_grid_from == 'listing_city' ) {
			$slugs = $listing_city;

		} else if ( $homey_grid_from == 'listing_area' ) {
			$slugs = $listing_area;

		} else if ( $homey_grid_from == 'room_type' ) {
			$slugs = $room_type;

		} else if ( $homey_grid_from == 'listing_state' ) {
			$slugs = $listing_state;

		} else if ( $homey_grid_from == 'listing_country' ) {
			$slugs = $listing_country;

		} else {
			$slugs = $listing_type;
		}

		if ($homey_show_child == 1) {
			$homey_show_child = '';
		}

		if ($homey_grid_type == 'grid_v1') {
			$taxonomy_grid_class = 'taxonomy-grid-1';
		} elseif($homey_grid_type == 'grid_v2') {
			$taxonomy_grid_class = 'taxonomy-grid-2';
		} elseif($homey_grid_type == 'grid_v3') {
			$taxonomy_grid_class = 'taxonomy-grid-3';
		} elseif($homey_grid_type == 'grid_v4') {
			$taxonomy_grid_class = 'taxonomy-grid-4';
		} else {
			$taxonomy_grid_class = 'taxonomy-grid-1';
		}

		$custom_link_for = '';

		$tax_name = $homey_grid_from;
		$taxonomy = get_terms(array(
			'hide_empty' => $homey_hide_empty,
			'parent' => $homey_show_child,
			'slug' => homey_traverse_comma_string($slugs),
			'number' => $no_of_terms,
			'orderby' => $orderby,
			'order' => $order,
			'taxonomy' => $tax_name,
		));
		?>

		<div class="module-wrap taxonomy-grid <?php esc_attr_e($taxonomy_grid_class);?>">
			<div class="row">
				<?php
				if ( !is_wp_error( $taxonomy ) ) {
					$i = 0;
					$j = 0;

					foreach ($taxonomy as $term) {

					$i++;
					$j++;

					$attach_id = get_term_meta($term->term_id, 'homey_taxonomy_img', true);


					if ($homey_grid_type == 'grid_v1') {
						$col = 'col-lg-6 col-md-6 col-sm-6 col-xs-12';
						$attachment = wp_get_attachment_image_src( $attach_id, 'homey_thumb_555_360' );
						
						$img_url = $attachment['0'];
						$img_width = $attachment['1'];
						$img_height = $attachment['2'];
						if(empty($attachment)) {
							$img_url = 'https://placehold.it/555x360';
							$img_width = '555';
							$img_height = '360';
						}
						
					} elseif ($homey_grid_type == 'grid_v3') {
						$col = 'col-sm-4 col-xs-6';
						$attachment = wp_get_attachment_image_src( $attach_id, 'homey_thumb_360_360' );

						$img_url = $attachment['0'];
						$img_width = $attachment['1'];
						$img_height = $attachment['2'];
						if(empty($attachment)) {
							$img_url = 'https://placehold.it/360x360';
							$img_width = '360';
							$img_height = '360';
						}
						
					} elseif ($homey_grid_type == 'grid_v4') {
						$col = 'col-sm-3 col-xs-6';
						$attachment = wp_get_attachment_image_src( $attach_id, 'homey_thumb_360_360' );

						$img_url = $attachment['0'];
						$img_width = $attachment['1'];
						$img_height = $attachment['2'];
						if(empty($attachment)) {
							$img_url = 'https://placehold.it/360x360';
							$img_width = '360';
							$img_height = '360';
						}
						
					} elseif ($homey_grid_type == 'grid_v2') {
						if ($i == 1 || $i == 6) {
							$col = 'col-lg-6 col-md-6 col-sm-6 col-xs-12';
							$attachment = wp_get_attachment_image_src( $attach_id, 'homey_thumb_555_262' );

							$img_url = $attachment['0'];
							$img_width = $attachment['1'];
							$img_height = $attachment['2'];
							if(empty($attachment)) {
								$img_url = 'https://placehold.it/555x262';
								$img_width = '555';
								$img_height = '262';
							}

						} else {
							$col = 'col-lg-3 col-md-3 col-sm-3 col-xs-6';
							$attachment = wp_get_attachment_image_src( $attach_id, 'homey_thumb_360_360' );

							$img_url = $attachment['0'];
							$img_width = $attachment['1'];
							$img_height = $attachment['2'];
							if(empty($attachment)) {
								$img_url = 'https://placehold.it/360x360';
								$img_width = '360';
								$img_height = '360';
							}
						}
						if ($i == 6) {
							$i = 0;
						}
					}
					
					$taxonomy_custom_link = '';//get_tax_meta($term->term_id, $custom_link_for);

					if( !empty($taxonomy_custom_link) ) {
						$term_link = $taxonomy_custom_link;
					} else {
						$term_link = get_term_link($term, $tax_name);
					}
					?>

					<div class="<?php esc_attr_e($col); ?>">
						<div class="taxonomy-item taxonomy-card">
							<a class="taxonomy-link hover-effect" href="<?php echo esc_url($term_link);?>">
								<div class="taxonomy-title"><?php echo esc_attr($term->name); ?></div>
								<img class="img-responsive" src="<?php echo esc_url($img_url); ?>" width="<?php echo $img_width; ?>" height="<?php echo $img_height; ?>" alt="<?php esc_attr_e($tax_name);?>">
							</a>
						</div>
					</div>

					<?php
					}
				}
				?>
				
			</div>
		</div>
		
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;

	}

	add_shortcode('homey-grids', 'homey_grids');
}
?>