<?php
/*-----------------------------------------------------------------------------------*/
/*	Properties
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_listings') ) {
	function homey_listings($atts, $content = null)
	{
		extract(shortcode_atts(array(
			'listing_style' => '',
			'loadmore' => '',
			'listing_type' => '',
			'room_type' => '',
			'listing_country' => '',
			'listing_state' => '',
			'listing_city' => '',
			'listing_area' => '',
			'featured_listing' => '',
			'posts_limit' => '',
			'sort_by' => '',
			'offset' => ''
		), $atts));

		ob_start();
		global $paged;

		$local = homey_get_localization();

		if (is_front_page()) {
			$paged = (get_query_var('page')) ? get_query_var('page') : 1;
		}

		//do the query
		$the_query = Homey_Query::get_wp_query($atts, $paged); //by ref  do the query
		?>

		<div id="listings_module_section" class="module-wrap property-module-<?php echo esc_attr($listing_style);?>">
			<div class="listing-wrap item-<?php echo esc_attr($listing_style);?>-view">
				<div id="module_listings" class="row">
					<?php
					if ($the_query->have_posts()) :
						while ($the_query->have_posts()) : $the_query->the_post();

							if($listing_style == 'card') {
								get_template_part('template-parts/listing/listing-card');
							} else {
								get_template_part('template-parts/listing/listing-item');
							}

						endwhile;
						Homey_Query::loop_reset_postdata();
					else:
						get_template_part('template-parts/listing/listing-none');
					endif;
					?>
				</div>
				
				<?php if($loadmore == 'enable') { ?>
				<div class="homey-loadmore loadmore text-center">
					<a
					data-paged="2" 
                    data-limit="<?php esc_attr_e($posts_limit); ?>" 
                    data-style="<?php esc_attr_e($listing_style); ?>" 
                    data-type="<?php esc_attr_e($listing_type); ?>" 
                    data-roomtype="<?php esc_attr_e($room_type); ?>"
                    data-country="<?php esc_attr_e($listing_country); ?>"  
                    data-state="<?php esc_attr_e($listing_state); ?>" 
                    data-city="<?php esc_attr_e($listing_city); ?>" 
                    data-area="<?php esc_attr_e($listing_area); ?>" 
                    data-featured="<?php esc_attr_e($featured_listing); ?>" 
                    data-offset="<?php esc_attr_e($offset); ?>"
                    data-sortby="<?php esc_attr_e($sort_by); ?>"
                    href="#" 
					class="btn btn-primary btn-long">
						<i id="spinner-icon" class="fa fa-spinner fa-pulse fa-spin fa-fw" style="display: none;"></i>
						<?php esc_attr_e($local['loadmore_btn']); ?>
					</a>
				</div>
				<?php } ?>


			</div><!-- grid-listing-page -->
		</div>
		
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;

	}

	add_shortcode('homey-listings', 'homey_listings');
}
?>