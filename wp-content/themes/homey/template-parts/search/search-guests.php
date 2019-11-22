<?php
global $homey_local;
$pets = '';
if(isset($_GET['pets'])) {
	$pets = $_GET['pets'];
}
$search_fields = homey_option('search_hide_fields');
?>
<div class="search-guests-wrap search-guests-wrap-js clearfix">
	<input type="hidden" class="search_adult_guest" value="0">
	<input type="hidden" class="search_child_guest" value="0">

	<?php if($search_fields['adults'] != 1) { ?>
	<div class="adults-calculator">
		<span class="quantity-calculator search_homey_adult">0</span>
		<span class="calculator-label"><?php echo esc_attr(homey_option('srh_adults_label')); ?></span>
		<button class="search_adult_plus btn btn-secondary-outlined" type="button"><i class="fa fa-plus" aria-hidden="true"></i></button>
		<button class="search_adult_minus btn btn-secondary-outlined" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>
	</div>
	<?php } ?>

	<?php if($search_fields['children'] != 1) { ?>
	<div class="children-calculator">
		<span class="quantity-calculator search_homey_child">0</span>
		<span class="calculator-label"><?php echo esc_attr(homey_option('srh_child_label')); ?></span>
		<button class="search_child_plus btn btn-secondary-outlined" type="button"><i class="fa fa-plus" aria-hidden="true"></i></button>
		<button class="search_child_minus btn btn-secondary-outlined" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>
	</div>
	<?php } ?>

	<?php if($search_fields['pets'] != 1) { ?>
	<div class="pets-calculator">
		<span class="calculator-label"><?php echo esc_html__('Pets', 'homey'); ?></span>
		<div class="pets-calculator-control-wrap">
			<label class="control control--radio radio-tab">
				<input type="radio" <?php checked( $pets, 1 ); ?> name="pets" value="1">
				<span class="control-text"><?php echo esc_html__('Yes', 'homey'); ?></span>
				<span class="control__indicator"></span>
				<span class="radio-tab-inner"></span>
			</label>
			<label class="control control--radio radio-tab">
				<input type="radio" <?php checked( $pets, 0 ); ?> name="pets" value="0">
				<span class="control-text"><?php echo esc_html__('No', 'homey'); ?></span>
				<span class="control__indicator"></span>
				<span class="radio-tab-inner"></span>
			</label>		
		</div>
	</div><!-- pets-calculator -->
	<?php } ?>
	<div class="guest-apply-btn">
		<button class="btn btn-primary" type="button"><?php echo esc_attr($homey_local['sr_apply_label']); ?></button>
	</div><!-- guest-apply-btn -->
</div><!-- search-guests -->