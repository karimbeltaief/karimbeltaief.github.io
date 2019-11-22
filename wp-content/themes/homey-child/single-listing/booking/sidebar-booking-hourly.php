<?php 
global $post, $current_user, $homey_prefix, $homey_local;
wp_get_current_user();

$listing_id = $post->ID;
$price_per_night = get_post_meta($listing_id, $homey_prefix.'night_price', true);
$instant_booking = get_post_meta($listing_id, $homey_prefix.'instant_booking', true);
$start_hour = get_post_meta($listing_id, $homey_prefix.'start_hour', true);
$end_hour = get_post_meta($listing_id, $homey_prefix.'end_hour', true);

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
$listing_price = homey_get_price();

if(empty($start_hour)) {
	$start_hour = '00:00';
}

if(empty($end_hour)) {
	$end_hour = '23:00';
}

$prefilled = homey_get_dates_for_booking();
$pre_start_hour = $prefilled['start'];
$pre_end_hour = $prefilled['end'];

$start_hours_list = '';
$end_hours_list = '';
$start_hour = strtotime($start_hour);
$end_hour = strtotime($end_hour);
for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour+60*60) {
    $start_hours_list .= '<option '.($pre_start_hour == date('H:i',$halfhour) ? "selected":"").' value="'.date('H:i',$halfhour).'">'.date('H:i',$halfhour).'</option>';
    $end_hours_list .= '<option '.($pre_end_hour == date('H:i',$halfhour) ? "selected":"").' value="'.date('H:i',$halfhour).'">'.date('H:i',$halfhour).'</option>';
}
$index = '';
?>

<style>
	.not-panel.panel-default {
		border:none !important;
	}
	.not-panel .panel-heading{
		display:none;
	}
	.not-panel .panel-body {
		padding: 0px important;
		border-top: 0px !important;
	}
	.panel {
		margin-top: 0px !important;
	}

</style>
<div id="homey_remove_on_mobile" class="sidebar-booking-module hidden-sm hidden-xs">
	<div class="block">
		<div class="sidebar-booking-module-header">
			<div class="block-body-sidebar">
			
					<?php 
					if(!empty($listing_price)) { ?>

					<span class="item-price">
					<?php 	
					echo homey_formatted_price($listing_price, true, true); ?><sub><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label();?></sub>
					</span>

					<?php } else { 
						echo '<span class="item-price free">'.esc_html__('Free', 'homey').'</span>';
					}?>
				
			</div><!-- block-body-sidebar -->
		</div><!-- sidebar-booking-module-header -->
		<div class="sidebar-booking-module-body">
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
				<div class="panel panel-default not-panel">
					<div class="panel-heading" role="tab" id="heading1">
						<h4 class="panel-title">
							<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse1" aria-expanded="true" aria-controls="collapse1">
							RESERVATION 1
							</a>
						</h4>
					</div>
					<div id="collapse1" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading1">
						<div class="panel-body">
							<div class="homey_notification block-body-sidebar">
								<div id="single-listing-date-range1" class="search-date-range">
									<div class="search-date-range-arrive search-date-hourly-arrive">
										<input name="arrive" value="<?php echo esc_attr($prefilled['arrive']); ?>" readonly type="text" class="form-control check_in_date" autocomplete="off" placeholder="<?php echo esc_attr(homey_option('srh_arrive_label')); ?>">
									</div>
									
									<div id="single-booking-search-calendar1" class="search-calendar search-calendar-single clearfix single-listing-booking-calendar-js hourly-js-desktop clearfix" style="display: none;">
										<?php homeyHourlyAvailabilityCalendar(); ?>

										<div class="calendar-navigation custom-actions">
											<button class="listing-cal-prev btn btn-action pull-left disabled"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
											<button class="listing-cal-next btn btn-action pull-right"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
										</div><!-- calendar-navigation -->	                
									</div>
								</div>

								<div class="search-hours-range clearfix">
									<div class="search-hours-range-left">
										<select name="start_hour" id="start_hour" class="selectpicker start_hour" data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>">
											<option value=""><?php echo homey_option('srh_starts_label'); ?></option>
											<?php echo $start_hours_list; ?>
										</select>
									</div>
									<div class="search-hours-range-right">
										<select name="end_hour" id="end_hour" class="selectpicker end_hour" data-live-search="true" title="<?php echo homey_option('srh_ends_label'); ?>">
											<option value=""><?php echo homey_option('srh_ends_label'); ?></option>
											<?php echo $end_hours_list; ?>
										</select>
									</div>
								</div>
						
								<?php get_template_part('single-listing/booking/guests'); ?>

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
				<input type="hidden" name="listing_id" id="listing_id" value="<?php echo intval($listing_id); ?>">
				<input type="hidden" name="reservation-security" id="reservation-security" value="<?php echo wp_create_nonce('reservation-security-nonce'); ?>"/>			
			</div>

			<!-- Action Bouton -->
			<div id="homey_booking_cost_desk" class="payment-list"></div>
			<button id="add_reservation" type="button" class="btn btn-full-width btn-secondary">Ajouter une reservation</button>
			
			<?php if($instant_booking) { ?>
				<button id="instance_hourly_reservation" type="button" class="btn btn-full-width btn-primary"><?php echo esc_html__('Instant Booking', 'homey'); ?></button>
			<?php } else { ?> 
				<button id="request_hourly_reservation" type="button" class="btn btn-full-width btn-primary"><?php echo esc_html__('Request to Book', 'homey'); ?></button>
				<div class="text-center text-small"><i class="fa fa-info-circle"></i> <?php echo esc_html__('You won’t be charged yet', 'homey'); ?></div>
			<?php } ?>
		</div><!-- sidebar-booking-module-body -->
		
	</div><!-- block -->
</div><!-- sidebar-booking-module -->
<div class="sidebar-booking-module-footer">
	<div class="block-body-sidebar">

		<?php if(homey_option('detail_favorite') != 0) { ?>
		<button type="button" data-listid="<?php echo intval($post->ID); ?>" class="add_fav btn btn-full-width btn-grey-outlined"><i class="fa <?php echo esc_attr($heart); ?>" aria-hidden="true"></i> <?php echo esc_attr($favorite); ?></button>
		<?php } ?>
		
		<?php if(homey_option('detail_contact_form') != 0 && homey_option('hide-host-contact') !=1 ) { ?>
		<button type="button" data-toggle="modal" data-target="#modal-contact-host" class="btn btn-full-width btn-grey-outlined"><?php echo esc_attr($homey_local['pr_cont_host']); ?></button>
		<?php } ?>
		
		<?php if(homey_option('print_button') != 0) { ?>
		<button type="button" id="homey-print" class="btn btn-full-width btn-blank" data-listing-id="<?php echo intval($listing_id);?>">
			<i class="fa fa-print" aria-hidden="true"></i> <?php echo esc_attr($homey_local['print_label']); ?>
		</button>
		<?php } ?>
	</div><!-- block-body-sidebar -->
	
	<?php 
	if(homey_option('detail_share') != 0) {
		get_template_part('single-listing/share'); 
	}
	?>
</div><!-- sidebar-booking-module-footer -->
<script type="text/template" id="template">
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="heading{{ indice }}">
			<h4 class="panel-title" style="display: flex;justify-content: space-between;">
				<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse{{ indice }}" aria-expanded="true" aria-controls="collapse{{ indice }}">
				RESERVATION {{ numReservation }}
				</a>
				<a href="javascript:void(0)" onClick="rem(this)"><i class="fa fa-remove"></i></a>
			</h4>
		</div>
		<div id="collapse{{ indice }}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading{{ indice }}">
			<div class="panel-body">
				<div class="homey_notification block-body-sidebar">
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
							<select name="start_hour" id="start_hour" class="selectpicker start_hour" data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>">
								<option value=""><?php echo homey_option('srh_starts_label'); ?></option>
								<?php echo $start_hours_list; ?>
							</select>
						</div>
						<div class="search-hours-range-right">
							<select name="end_hour" id="end_hour" class="selectpicker end_hour" data-live-search="true" title="<?php echo homey_option('srh_ends_label'); ?>">
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

<script type="text/template" id="total-template">
<div class="payment-list-price-detail clearfix tonga-desk">
	<div class="pull-left">
		<div class="payment-list-price-detail-total-price">{{ tobj.resume.cs_total }}</div>
		<div class="payment-list-price-detail-note">{{ tobj.resume.cs_tax_fees }}</div>
	</div>
	<div class="pull-right text-right">
		<div class="payment-list-price-detail-total-price">{{ tobj.resume.total_price }}<sup>{{ currencySymbol }}</sup></div>
		<a class="payment-list-detail-btn" data-toggle="collapse" data-target="#collapseDesk" href="javascript:void(0)" aria-expanded="false" aria-controls="collapseDesk">{{ tobj.resume.cs_view_details }}</a>
	</div>
	<div class="collapse" id="collapseDesk">
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
(function($){
	$(document).ready(function(){
		var panelCounter = 1;

		$("#add_reservation").bind("click", function(){
			var $reservations = jQuery(".panel", "#accordion");
			if($reservations.length == 1){
				var $reservation1 = $(".panel:first-child", "#accordion");
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
			var template = $("#template").html();
			Mustache.parse(template);

			var rendu = Mustache.render(template, {
				indice: panelCounter,
				numReservation: $reservations.length + 1,
				accordion: true,
				first: false
			});

			$("#accordion").append(rendu);
			var select_picker = $('.selectpicker');
			if (select_picker.length > 0) {
				select_picker.selectpicker({
					dropupAuto: false
				});
			}
            var errorExists = getCostErrors(costs).length > 0;
            disableReservationButton(errorExists);
		});
	});
})(jQuery);

var recreateIndex = function($accordion){
	var $panels		= jQuery(".panel", $accordion);
	$panels.each(function(indice){
		jQuery(".panel-heading>.panel-title>a[data-toggle]", jQuery(this)).text("RESERVATION " + (indice+1));
	});
}

var rem = function(a) {
	var $parent = jQuery(a).parents(".panel");
	var $accordion = jQuery(a).parents("div[id^=accordion]");
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
		
	}
	updateTotalDesk();
	recreateIndex($accordion);
}

var updateTotalDesk = function(){
	var $ = jQuery;
	var totalTemplate = $("#total-template").html();
	Mustache.parse(totalTemplate);
	var totalObject = getTotalObjectDesk();
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
function getTotalObjectDesk(){
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