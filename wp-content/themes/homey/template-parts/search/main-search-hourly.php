<?php
global $post, $homey_local, $homey_prefix;
$advanced_filter = homey_option('advanced_filter');
$search_width = homey_option('search_width');
$sticky_search = homey_option('sticky_search');

$location_search = isset($_GET['location_search']) ? $_GET['location_search'] : '';
$country = isset($_GET['search_country']) ? $_GET['search_country'] : '';
$city = isset($_GET['search_city']) ? $_GET['search_city'] : '';
$area = isset($_GET['search_area']) ? $_GET['search_area'] : '';

$arrive = isset($_GET['arrive']) ? $_GET['arrive'] : '';
$depart = isset($_GET['depart']) ? $_GET['depart'] : '';
$guest = isset($_GET['guest']) ? $_GET['guest'] : '';

$class = '';
if($advanced_filter != 1) {
	$class = 'without-filters';
}

$listing_type_pre = '';
if(isset($_GET['listing_type'])) {
	$listing_type_pre = $_GET['listing_type'];
}

$listing_country_pre = '';
if(isset($_GET['country'])) {
    $listing_country_pre = $_GET['country'];
}

$listing_state_pre = '';
if(isset($_GET['state'])) {
    $listing_state_pre = $_GET['state'];
}

$listing_city_pre = '';
if(isset($_GET['city'])) {
    $listing_city_pre = $_GET['city'];
}

$get_start_time = '';
if(isset($_GET['start'])) {
    $get_start_time = $_GET['start'];
}

$get_end_time = '';
if(isset($_GET['end'])) {
    $get_end_time = $_GET['end'];
}

$listing_area_pre = '';
if(isset($_GET['area'])) {
	$listing_area_pre = $_GET['area'];
}

$location_field = homey_option('location_field');
if($location_field == 'geo_location') {
    $location_classes = "search-destination";
} else {
    $location_classes = "search-destination with-select";
}

$layout_order = homey_option('hourly_search_visible_fields');
$layout_order = $layout_order['enabled'];

$total_fields = count($layout_order);
$total_fields = $total_fields - 1;

$start_hour = strtotime('00:00');
$end_hour = strtotime('23:30');
?>
<div id="homey-main-search" class="main-search main-hourly-search <?php echo esc_attr($class);?>" data-sticky="<?php echo esc_attr( $sticky_search ); ?>">
	<div class="<?php echo esc_attr($search_width); ?>">
		<form class="clearfix" action="<?php echo homey_get_search_result_page(); ?>" method="GET">
			<div id="search-desktop" class="search-wrap hidden-sm hidden-xs">
				
				<?php
                if ($layout_order) { 
                    foreach ($layout_order as $key=>$value) {

                        switch($key) { 
                            case 'location':
                                ?>
                                <div class="<?php echo esc_attr($location_classes); ?>">
									<?php if($location_field == 'geo_location') { ?>
		                            <label class="animated-label"><?php echo esc_attr(homey_option('srh_whr_to_go')); ?></label>    
		                            <input type="text" name="location_search" autocomplete="off" id="location_search_banner" value="<?php echo esc_attr($location_search); ?>" class="form-control input-search" placeholder="<?php echo esc_attr(homey_option('srh_whr_to_go')); ?>">
		                            <input type="hidden" name="search_city" data-value="<?php echo esc_attr($city); ?>" value="<?php echo esc_attr($city); ?>"> 
		                            <input type="hidden" name="search_area" data-value="<?php echo esc_attr($area); ?>" value="<?php echo esc_attr($area); ?>"> 
		                            <input type="hidden" name="search_country" data-value="<?php echo esc_attr($country); ?>" value="<?php echo esc_attr($country); ?>">
		                            <button type="reset" class="btn clear-input-btn"><i class="fa fa-times" aria-hidden="true"></i></button>

		                            <?php } elseif($location_field == 'country') { ?>

		                            <select name="country" class="selectpicker" data-live-search="false" data-live-search-style="begins">
		                            <?php
		                            // All Option
		                            echo '<option value="">'.esc_attr(homey_option('srh_whr_to_go')).'</option>';

		                            $listing_country = get_terms (
		                                array(
		                                    "listing_country"
		                                ),
		                                array(
		                                    'orderby' => 'name',
		                                    'order' => 'ASC',
		                                    'hide_empty' => false,
		                                    'parent' => 0
		                                )
		                            );
		                            homey_hirarchical_options('listing_country', $listing_country, $listing_country_pre );
		                            ?>
		                            </select>
		                            
		                            <?php } elseif($location_field == 'state') { ?>

		                            <select name="state" class="selectpicker" data-live-search="false" data-live-search-style="begins">
		                            <?php
		                            // All Option
		                            echo '<option value="">'.esc_attr(homey_option('srh_whr_to_go')).'</option>';

		                            $listing_state = get_terms (
		                                array(
		                                    "listing_state"
		                                ),
		                                array(
		                                    'orderby' => 'name',
		                                    'order' => 'ASC',
		                                    'hide_empty' => false,
		                                    'parent' => 0
		                                )
		                            );
		                            homey_hirarchical_options('listing_state', $listing_state, $listing_state_pre );
		                            ?>
		                            </select>
		                            
		                            <?php } elseif($location_field == 'city') { ?>

		                            <select name="city" class="selectpicker" data-live-search="false" data-live-search-style="begins">
		                            <?php
		                            // All Option
		                            echo '<option value="">'.esc_attr(homey_option('srh_whr_to_go')).'</option>';

		                            $listing_city = get_terms (
		                                array(
		                                    "listing_city"
		                                ),
		                                array(
		                                    'orderby' => 'name',
		                                    'order' => 'ASC',
		                                    'hide_empty' => false,
		                                    'parent' => 0
		                                )
		                            );
		                            homey_hirarchical_options('listing_city', $listing_city, $listing_city_pre );
		                            ?>
		                            </select>

		                            <?php } elseif($location_field == 'area') { ?>

		                            <select name="area" class="selectpicker" data-live-search="false" data-live-search-style="begins">
		                            <?php
		                            // All Option
		                            echo '<option value="">'.esc_attr(homey_option('srh_whr_to_go')).'</option>';

		                            $listing_area = get_terms (
		                                array(
		                                    "listing_area"
		                                ),
		                                array(
		                                    'orderby' => 'name',
		                                    'order' => 'ASC',
		                                    'hide_empty' => false,
		                                    'parent' => 0
		                                )
		                            );
		                            homey_hirarchical_options('listing_area', $listing_area, $listing_area_pre );
		                            ?>
		                            </select>

		                            <?php } ?>
								</div>
                                <?php
                            break;

                            case 'arrive':
                            	?>
                            	<div class="search-date-range main-search-date-range-js">
									<div class="search-date-range-arrive search-date-hourly-arrive">
										<label class="animated-label"><?php echo esc_attr(homey_option('srh_arrive_label')); ?></label>
										<input name="arrive" autocomplete="off" value="<?php echo esc_attr($arrive); ?>" type="text" class="form-control" placeholder="<?php echo esc_attr(homey_option('srh_arrive_label')); ?>">
									</div>
									<?php get_template_part ('template-parts/search/search-calendar'); ?>
								</div>
                            	<?php
                            break;

                            case 'start_end_hours':
                            ?>
                            <div class="search-hours-range clearfix">
								<div class="search-hours-range-left search-hours-range-js">
									<select name="start" class="selectpicker" data-live-search="true" data-live-search-style="begins" title="<?php echo esc_attr(homey_option('srh_starts_label'));?>">
										<option value=""><?php echo esc_attr(homey_option('srh_starts_label'));?></option>
										<?php
										for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
										    echo '<option '.selected(date('H:i',$halfhour), $get_start_time, true).' value="'.date('H:i',$halfhour).'">'.date('H:i',$halfhour).'</option>';
										}
										?>
									</select>
								</div>
								<div class="search-hours-range-right search-hours-range-js">
									<select name="end" class="selectpicker" data-live-search="true" data-live-search-style="begins" title="<?php echo esc_attr(homey_option('srh_ends_label'));?>">
										<option value=""><?php echo esc_attr(homey_option('srh_ends_label'));?></option>
										<?php
										for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
										    echo '<option '.selected(date('H:i',$halfhour), $get_end_time, true).' value="'.date('H:i',$halfhour).'">'.date('H:i',$halfhour).'</option>';
										}
										?>
									</select>
								</div>
							</div>
                            <?php
                            break;

                            case 'guests':
                            	?>
                            	<div class="search-guests search-guests-js">
                            		
									<input name="guest" autocomplete="off" value="<?php echo esc_attr($guest); ?>" type="text" class="form-control" placeholder="<?php echo esc_attr(homey_option('srh_guests_label')); ?>">
									<?php get_template_part ('template-parts/search/search-guests'); ?>
								</div>
                            	<?php
                            break;

                            case 'listing_type':
                            	?>
                            	<div class="search-type">
	                            	<select name="listing_type" class="selectpicker" data-live-search="false" data-live-search-style="begins">
	                                    <?php
	                                    // All Option
	                                    echo '<option value="">'.esc_attr(homey_option('srh_listing_type')).'</option>';

	                                    $listing_type = get_terms (
	                                        array(
	                                            "listing_type"
	                                        ),
	                                        array(
	                                            'orderby' => 'name',
	                                            'order' => 'ASC',
	                                            'hide_empty' => false,
	                                            'parent' => 0
	                                        )
	                                    );
	                                    homey_hirarchical_options('listing_type', $listing_type, $listing_type_pre );
	                                    ?>
	                                </select>
	                            </div>
                            	<?php
                            break;
                     
                        }
                    }
                }
                ?>

				<?php if( $advanced_filter != 0 ) { ?>
				<div class="search-filters">
					<button type="button" class="btn btn-grey-outlined search-filter-btn"><i class="fa fa-sliders fa-rotate-90" aria-hidden="true"></i></button>
				</div>
				<?php } ?>

				<div class="search-button">
					<button type="submit" class="btn btn-primary"><?php echo esc_attr($homey_local['search_btn']); ?></button>
				</div>
				
			</div><!-- search-wrap -->

			<div class="search-wrap search-banner-mobile">
				
				<div class="search-destination">
					<input value="<?php echo esc_attr($location_search); ?>" type="text" class="form-control" placeholder="<?php echo esc_attr(homey_option('srh_whr_to_go')); ?>" onfocus="blur();">
				</div>
				
			</div><!-- search-wrap -->		
			<?php 
			if( $advanced_filter != 0 ) {
				get_template_part ('template-parts/search/search-filter-full-width'); 
			}
			?>	
		</form>
	</div>
</div>
