<?php 
global $post, $current_user, $homey_prefix, $homey_local;
wp_get_current_user();

$listing_id = $post->ID;
$price_per_night = get_post_meta($listing_id, $homey_prefix.'night_price', true);
$instant_booking = get_post_meta($listing_id, $homey_prefix.'instant_booking', true);
$start_hour = get_post_meta($listing_id, $homey_prefix.'start_hour', true);
$end_hour = get_post_meta($listing_id, $homey_prefix.'end_hour', true);

$listing_price = homey_get_price();

$rating = homey_option('rating');
$total_rating = get_post_meta( $listing_id, 'listing_total_rating', true );

$key = '';
$userID      =   $current_user->ID;
$fav_option = 'homey_favorites-'.$userID;
$fav_option = get_option( $fav_option );
if( !empty($fav_option) ) {
    $key = array_search($post->ID, $fav_option);
}

$price_separator = homey_option('currency_separator');

if( $key != false || $key != '' ) {
    $favorite = $homey_local['remove_favorite'];
    $heart = 'fa-heart';
} else {
    $favorite = $homey_local['add_favorite'];
    $heart = 'fa-heart-o';
}

if($instant_booking) { 
	$btn_name = esc_html__('Instant Booking', 'homey');
} else {
	$btn_name = esc_html__('Request to Book', 'homey');
}
$booking_or_contact_theme_options = homey_option('what_to_show');
$booking_or_contact = homey_get_listing_data('booking_or_contact');
if(empty($booking_or_contact)) {
    $what_to_show = $booking_or_contact_theme_options;
} else {
    $what_to_show = $booking_or_contact;
}

if(empty($start_hour)) {
	$start_hour = '00:00';
}

if(empty($end_hour)) {
	$end_hour = '23:30';
}

$prefilled = homey_get_dates_for_booking();
$pre_start_hour = $prefilled['start'];
$pre_end_hour = $prefilled['end'];

$start_hours_list = '';
$end_hours_list = '';
$start_hour = strtotime($start_hour);
$end_hour = strtotime($end_hour);
for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
    $start_hours_list .= '<option '.($pre_start_hour == date('H:i',$halfhour) ? "selected":"").' value="'.date('H:i',$halfhour).'">'.date('H:i',$halfhour).'</option>';
    $end_hours_list .= '<option '.($pre_end_hour == date('H:i',$halfhour) ? "selected":"").' value="'.date('H:i',$halfhour).'">'.date('H:i',$halfhour).'</option>';
}
?>
<style>
	.not-panel.panel-default {
		border:none !important;
	}
	.not-panel .panel-heading{
		display:none;
	}
	.not-panel .panel-body {
		padding: 0px !important;
		border-top: 0px !important;
	}
	#overlay-booking-module {
		z-index: 1000 !important;
	}
	.overlay-booking-btn {
		z-index: 999 !important;
	}
	.panel {
		margin-top: 0px !important;
	}
	#accordion-mobile .filter-option::before{
		top:unset !important;
	}
	#boutons-wrapper {
		position: fixed;
		background: white;
		bottom: 50%;
		max-height: 210px;
		overflow-y: auto;
		margin-bottom: 5px;
		right: 5%;
		left: 5%;
	}
	.botton-show {
		width: 100%;
		height: 20px;
		line-height: 18px;
	}

	.btn-collaped {
		width: 100%;
		height: 20px;
		line-height: 18px;
	}
	.rotate{
		-moz-transition: all .5s linear;
		-webkit-transition: all .5s linear;
		transition: all .5s linear;
	}
	.rotate.down {
		-moz-transform: rotate(180deg);
		-webkit-transform: rotate(180deg);
		transform: rotate(180deg);
		line-height: 22px !important;
	}
	.overlay-booking-module-close {
		width: 50px !important;
		height: 50px !important;
	}
	.overlay-booking-module {
		padding: 50px 20px 0 !important;
	}
	@media (max-width: 576px){

		.dropdown-menu.open{
			max-height: 200px !important;
			overflow-y: auto !important;
			min-height: 144px !important;
		}

		.panel-body {
			padding: 5px !important;
		}

		.sidebar-booking-module {
			max-height: 66vh;
			overflow-y: auto;
			height: 66vh;
		}
	}

</style>
<div id="overlay-booking-module" class="overlay-booking-module overlay-hourly-booking-module overlay-contentscale">
	<div class="overlay-search-title"><?php echo esc_html__('Request to book', 'homey'); ?></div>
	<button type="button" class="overlay-booking-module-close btn-blank"><i class="fa fa-times" aria-hidden="true"></i></button> 
	<div class="sidebar-booking-module">
		<div class="block">
			<div class="sidebar-booking-module-body" style="position: relative;">
				<div class="panel-group" id="accordion-mobile" role="tablist" aria-multiselectable="true">
					<div class="panel panel-default not-panel">
						<div class="panel-heading" role="tab" id="headingMobile1">
							<h4 class="panel-title">
								<a role="button" data-toggle="collapse" data-parent="#accordion-mobile" href="#collapseMobile1" aria-expanded="true" aria-controls="collapseMobile1">
								RESERVATION 1
								</a>
							</h4>
						</div>
						<div id="collapseMobile1" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingMobile1">
							<div class="panel-body">
								<div class="homey_notification search-wrap search-banner">
									<div id="single-listing-date-range" class="search-date-range">

										<div class="search-date-range-arrive search-date-hourly-arrive">
											<input name="arrive" value="<?php echo esc_attr($prefilled['arrive']); ?>" readonly type="text" class="form-control check_in_date" autocomplete="off" placeholder="<?php echo esc_attr(homey_option('srh_arrive_label')); ?>">
										</div>

										<div id="single-booking-search-calendar" class="search-calendar hourly-js-mobile single-listing-booking-calendar-js clearfix" style="display: none;">
											<?php homeyHourlyAvailabilityCalendar(); ?>	
											
											<div class="calendar-navigation custom-actions">
												<button class="listing-cal-prev btn btn-action pull-left disabled"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
												<button class="listing-cal-next btn btn-action pull-right"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
											</div><!-- calendar-navigation -->                
										</div>
									</div>

									<div class="search-hours-range clearfix">
										<div class="search-hours-range-left">
											<select name="start_hour" id="start_hour_overlay" class="selectpicker start_hour" data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>">
												<option value=""><?php echo homey_option('srh_starts_label'); ?></option>
												<?php echo $start_hours_list; ?>
											</select>
										</div>
										<div class="search-hours-range-right">
											<select name="end_hour" id="end_hour_overlay" class="selectpicker end_hour" data-live-search="true" title="<?php echo homey_option('srh_ends_label'); ?>">
												<option value=""><?php echo homey_option('srh_ends_label'); ?></option>
												<?php echo $end_hours_list; ?>
											</select>
										</div>
									</div>
									
									<?php get_template_part('single-listing/booking/guests-overlay-hourly'); ?>

									<?php if(!$instant_booking) { ?>
									<div class="search-message">
										<textarea name="guest_message" class="form-control" rows="3" placeholder="<?php echo esc_html__('Présentez-vous à l\'hôte', 'homey'); ?>"></textarea>
									</div>
									<?php } ?>

									<div class="homey_preloader">
										<?php get_template_part('template-parts/spinner'); ?>
									</div>	

								</div><!-- block-body-sidebar -->
							</div>
						</div>
					</div>
					
				<!-- <input type="hidden" name="listing_id" id="listing_id" value="<?php echo intval($listing_id); ?>">
				<input type="hidden" name="reservation-security" id="reservation-security" value="<?php echo wp_create_nonce('reservation-security-nonce'); ?>"/> -->
				
				</div>
				<!-- action Button -->
				<section id="boutons-wrapper">
					<div id="homey_booking_cost_mobile" class="payment-list"></div>
					<a class="btn btn-primary botton-show" style="padding: 0px !important;" href="#boutons" data-toggle="collapse" aria-expanded="false" aria-controls="boutons">
						<i class="fa fa-chevron-up rotate" aria-hidden="true"></i>
					</a>
					<div id="boutons" class="collapse">
						<button id="add_reservation_mobile" type="button" class="btn btn-full-width btn-secondary">Ajouter une reservation</button>
						<?php if($instant_booking) { ?>
							<button id="instance_hourly_reservation_mobile" type="button" class="btn btn-full-width btn-primary"><?php echo esc_html__('Instant Booking', 'homey'); ?></button>
						<?php } else { ?> 
							<button id="request_hourly_reservation_mobile" type="button" class="btn btn-full-width btn-primary"><?php echo esc_html__('Request to Book', 'homey'); ?></button>
							<div class="text-center text-small"><i class="fa fa-info-circle"></i> <?php echo esc_html__('You won’t be charged yet', 'homey'); ?></div>
						<?php } ?>
					</div>
				</section>
			</div><!-- sidebar-booking-module-body -->
		</div><!-- block -->
	</div><!-- sidebar-booking-module -->
</div><!-- overlay-booking-module -->
<script>
(function($){
	/*$("div[id^=accordion]").resize(function(){
		var hInit = $(this).height();
		$("#boutons").css('top', hInit);
	});*/
})(jQuery);

</script>
<div class="overlay-booking-btn visible-sm visible-xs">
	<div class="pull-left">
		<div class="overlay-booking-price">
			<?php echo homey_formatted_price($listing_price, true, false); ?><span><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label();?></span>
		</div>
		<?php 
        if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
		<div class="list-inline rating">
			<?php echo homey_get_review_stars($total_rating, false, true); ?>
		</div>
		<?php } ?>
	</div>
	<?php
	if($what_to_show == 'booking_form') { ?>
        <button id="trigger-overlay-booking-form" class="trigger-overlay btn btn-primary" type="button"><?php echo esc_attr($btn_name); ?></button>
    <?php     
    } elseif($what_to_show == 'contact_form') { ?>
        <button type="button" data-toggle="modal" data-target="#modal-contact-host" class="trigger-overlay btn btn-primary"><?php echo esc_html__('Request Information', 'homey'); ?></button>
    <?php    
    }
	?>
</div>

<script type="text/template" id="template_mobile">
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingMobile{{ indice }}">
			<h4 class="panel-title" style="display: flex;justify-content: space-between;">
				<a role="button" data-toggle="collapse" data-parent="#accordion-mobile" href="#collapseMobile{{ indice }}" aria-expanded="true" aria-controls="collapseMobile{{ indice }}">
				RESERVATION {{ numReservation }}
				</a>
				<a href="javascript:void(0)" onClick="remove(this)"><i class="fa fa-remove"></i></a>
			</h4>
		</div>
		<div id="collapseMobile{{ indice }}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingMobile{{ indice }}">
			<div class="panel-body">
				<div class="homey_notification block-body-sidebar" style="padding: 0px !important;">
					<div id="single-listing-date-range{{ indice }}" class="search-date-range">
						<div class="search-date-range-arrive search-date-hourly-arrive">
							<input name="arrive" value="<?php echo esc_attr($prefilled['arrive']); ?>" readonly type="text" class="form-control check_in_date" autocomplete="off" placeholder="<?php echo esc_attr(homey_option('srh_arrive_label')); ?>">
						</div>
						
						<div id="single-booking-search-calendar{{ indice }}" class="search-calendar search-calendar-single clearfix single-listing-booking-calendar-js hourly-js-desktop clearfix" style="display: none;">
							<?php homeyHourlyAvailabilityCalendar(); ?>

							<div class="calendar-navigation custom-actions">
								<button class="listing-cal-prev btn btn-action pull-left disabled"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
								<button class="listing-cal-next btn btn-action pull-right"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
							</div><!-- calendar-navigation -->	                
						</div>
					</div>

					<div class="search-hours-range clearfix">
						<div class="search-hours-range-left">
							<select name="start_hour" id="start_hour_overlay" class="selectpicker start_hour" data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>">
								<option value=""><?php echo homey_option('srh_starts_label'); ?></option>
								<?php echo $start_hours_list; ?>
							</select>
						</div>
						<div class="search-hours-range-right">
							<select name="end_hour" id="end_hour_overlay" class="selectpicker end_hour" data-live-search="true" title="<?php echo homey_option('srh_ends_label'); ?>">
								<option value=""><?php echo homey_option('srh_ends_label'); ?></option>
								<?php echo $end_hours_list; ?>
							</select>
						</div>
					</div>
					
					<?php get_template_part('single-listing/booking/guests'); ?>

					
					<div class="homey_preloader">
						<?php get_template_part('template-parts/spinner'); ?>
					</div>				
					
				</div><!-- block-body-sidebar -->
			</div>
		</div>
	<div>
</script>

<script type="text/template" id="total-template-mobile">
<div class="payment-list-price-detail clearfix">
	<div class="pull-left">
		<div class="payment-list-price-detail-total-price">{{ tobj.resume.cs_total }}</div>
		<div class="payment-list-price-detail-note">{{ tobj.resume.cs_tax_fees }}</div>
	</div>
	<div class="pull-right text-right">
		<div class="payment-list-price-detail-total-price">{{ tobj.resume.total_price }}<sup>{{ currencySymbol }}</sup></div>
		<a class="payment-list-detail-btn" data-toggle="collapse" data-target="#collapseMob" href="javascript:void(0)" aria-expanded="false" aria-controls="collapseMob">{{ tobj.resume.cs_view_details }}</a>
	</div>
	<div class="collapse mob" id="collapseMob">
		<ul>
			{{{details}}}
		<!-- boucle costs 
				<li>Label <span> Value</span></li>
			 / boucle costs -->
			<li class="payment-due">{{ tobj.payment_due.label }} <span>{{ tobj.payment_due.value }}<sup>{{ currencySymbol }}</sup></span></li>
		</ul>
	</div>
</div>
</script>
<script>

// Global price
var listing_price = <?= $listing_price ?>;
var formatedPrice = '<?= homey_formatted_price($listing_price, true, true) ?>';
var currency = formatedPrice.split(/<sup>(\S)<\/sup>/g)[1];
var costs = [
	{
		index: 0
	}
];


var changeClass = function () {
    var $window = jQuery(window);
    var windowsize = $window.width();
    
    if(windowsize < 576) {
        jQuery('#homey_booking_cost_mobile').find('.payment-list-detail-btn').attr('data-target', '#collapseMobile');
        jQuery('#homey_booking_cost_mobile').find('.payment-list-detail-btn').attr('aria-controls', '#collapseMobile');
        jQuery('#homey_booking_cost_mobile').find('.mob').attr('id', 'collapseMobile');
    }
};


(function($){
	$(document).ready(function(){
		$(document).on('click', '.botton-show', function (){
			$('.rotate').toggleClass("down");
		});


		var panelCounter = 1;
		$("#add_reservation_mobile").bind("click", function(){
			var $reservations = jQuery(".panel", "#accordion-mobile");
			if($reservations.length == 1){
				var $reservation1 = $(".panel:first-child", "#accordion-mobile");
				$reservation1.toggleClass("not-panel");
				panelCounter = 1;
			}

			var cost = {
				index: $reservations.length
			};
			costs.push(cost);

			panelCounter ++;
			var $link0 = $("a[aria-expanded='true']");
			if($link0 !== undefined){
				$link0.trigger("click");
			}
			var template = $("#template_mobile").html();
			Mustache.parse(template);
			var rendu = Mustache.render(template, {
				indice: panelCounter,
				numReservation: $reservations.length + 1,
				accordion: true,
				first: false
			});
			$("#accordion-mobile").append(rendu);
			var select_picker = $('.selectpicker');
			if (select_picker.length > 0) {
				select_picker.selectpicker({
					dropupAuto: false
				});
			}
			//$("#accordion-mobile").trigger('resize');
		});
	});
})(jQuery);

var recreateIndexMobile = function($accordion){
	var $panels = jQuery('.panel', $accordion);
	$panels.each(function(indice){
		jQuery(".panel-heading>.panel-title>a[data-toggle]", jQuery(this)).text("RESERVATION " + (indice+1));
	});
}

var remove = function(a) {
	var $parent = jQuery(a).parents(".panel");
	var $accordion = jQuery(a).parents("#accordion-mobile");
	var parentIndex = jQuery(".panel", $accordion).index($parent);
	var res = costs.filter(function(c){
		return c.index != parentIndex;
	});
	costs = res;
	$parent.remove();
	var $reservations = jQuery(".panel", $accordion);
	if($reservations.length == 1){
		var $reservation1 = jQuery(".panel:first-child", $accordion);
		var $link0 = jQuery("a[data-toggle='collapse']",$reservation1);
		if($link0 !== undefined && $link0.attr('aria-expanded') == 'false'){
			$link0.trigger("click");
		}
		$reservation1.toggleClass("not-panel");
		
	}else {
		//jQuery("#accordion-mobile").trigger('resize');
	}
	updateTotal();
	recreateIndexMobile($accordion);
}

var updateTotal = function(){
	var $ = jQuery;
	var totalTemplate = $("#total-template-mobile").html();
	Mustache.parse(totalTemplate);
	var totalObject = getTotalObject();
	var arrayKeys = Object.keys(totalObject.details);
	var details = "";
	arrayKeys.forEach(function(ark){
		details +="<li>";
		if(typeof totalObject.details[ark].label == "string"){
			details += totalObject.details[ark].label;
		}else{
			Object.keys(totalObject.details[ark].label.label).forEach(function(ar){
				details += totalObject.details[ark].label.label[ar]; 
				if(ar == "price_per_hour") details += " x ";
				else details += " ";
			});
		}
		details +="<span>" + totalObject.details[ark].value + " "+currency+"</span>";
		details +="</li>";
	});
	var renduTotal = Mustache.render(totalTemplate,{
		tobj: totalObject,
		currencySymbol: currency,
		details: details
	});
	
	$('#homey_booking_cost, .payment-list').empty().html(renduTotal);
}

function getTotalObject(){
	var c = costs.slice();
	var totalObj = {
		resume:{
			cs_view_details: "",
			cs_total: "",
			cs_tax_fees: "",
			total_price: 0
		},
		details:{
			
		},
		payment_due:{
			label: "",
			value: 0,
		}
	};
	if(c.length > 0){
		var c0 = c[0];
		var nonCumulableKeys = ["cs_sec_deposit"];
		if(typeof c0.json != "undefined"){
			totalObj.resume.cs_tax_fees = c0.json.resume.cs_tax_fees;
			totalObj.resume.cs_total = c0.json.resume.cs_total;
			totalObj.resume.cs_view_details = c0.json.resume.cs_view_details;
		}

		c.forEach(function(co){
			if(typeof co.json != "undefined"){
				var arrayKeys = Object.keys(co.json.details);
				arrayKeys.forEach(function(ark){
				if(Object.keys(totalObj.details).indexOf(ark) == -1){
					/**
					 * Si la clé n'existe pas alors créer la
					 */
					totalObj.details[ark] = {
					label: {},
					value: 0,
					};
					if(typeof co.json.details[ark].label !== "string"){
					totalObj.details[ark].label = {label: {}};
					Object.keys(co.json.details[ark].label.label).forEach(function(ar){
						if(ar == "no_of_hours" || ar == "additional_guests"){
						totalObj.details[ark].label.label[ar] = 0;
						}
						else{
						totalObj.details[ark].label.label[ar] = co.json.details[ark].label.label[ar];
						}
					});
					} else {
						totalObj.details[ark].label = co.json.details[ark].label;
					}
				}
				});
			}
		});
		c.forEach(function(co){
			if(typeof co.json != "undefined"){
				totalObj.resume.total_price += co.json.resume.total_price;
				totalObj.payment_due.label = co.json.cs_payment_due.label;
				totalObj.payment_due.value += co.json.cs_payment_due.value;
				var arrayKeys = Object.keys(co.json.details);
				arrayKeys.forEach(function(ark){
					// Label sum
					if(typeof co.json.details[ark].label !== "string"){
						Object.keys(co.json.details[ark].label.label).forEach(function(ar){
							if(ar == "no_of_hours" || ar == "additional_guests"){
								totalObj.details[ark].label.label[ar] += parseFloat(co.json.details[ark].label.label[ar]);
							}
							else{
								totalObj.details[ark].label.label[ar] = co.json.details[ark].label.label[ar];
							}
						});
					}
					// Value sum
					if(nonCumulableKeys.indexOf(ark) == -1){
						totalObj.details[ark].value += co.json.details[ark].value;
					}else{
						totalObj.details[ark].value = co.json.details[ark].value;
					}
				});
			}
		});
	}
	return totalObj;
}
</script>
