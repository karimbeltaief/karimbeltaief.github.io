<?php
global $post, $current_user, $homey_prefix, $homey_local, $hide_labels;
$min_book_hours  = homey_get_listing_data('min_book_hours');

if($min_book_hours > 1) {
    $min_book_hours_label = esc_html__('Hours', 'homey');
} else {
    $min_book_hours_label = esc_html__('Hour', 'homey');
}
?>
<?php
$homey_prefix ="homey_";
$start_hour = get_post_meta($post->ID, $homey_prefix.'start_hour', true);
$end_hour = get_post_meta($post->ID, $homey_prefix.'end_hour', true);


if(empty($start_hour)) {
	$start_hour = '00:00';
}

if(empty($end_hour)) {
	$end_hour = '23:30';
}

$start_hour = strtotime($start_hour);
$end_hour = strtotime($end_hour);
$role = "";
if(is_user_logged_in()){
    wp_get_current_user();
    $role = $current_user->roles[0];
}

?>
<div id="availability-section" class="availability-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_availability_label')); ?></h3>
                </div><!-- block-left -->
                
                <?php if($hide_labels['sn_min_stay_is'] != 1 || $hide_labels['sn_max_stay_is'] != 1) { ?>
                <div class="block-right">
                    <ul class="detail-list detail-list-2-cols">
                        <?php if($hide_labels['sn_min_stay_is'] != 1 && !empty($min_book_hours)) { ?>
                        <li>
                            <i class="fa fa-calendar-o" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_min_stay_is'));?> <strong><?php echo esc_attr($min_book_hours); ?> <?php echo esc_attr($min_book_hours_label);?></strong>
                        </li>
                        <?php } ?>

                        <?php if($hide_labels['sn_max_stay_is'] != 1 && !empty($max_book_days)) { ?>
                        <li>
                            <i class="fa fa-calendar-o" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_max_stay_is'));?> <strong><?php echo esc_attr($max_book_days); ?> <?php echo esc_attr(homey_option('sn_nights_label'));?></strong>
                        </li>
                        <?php } ?>
                    </ul>
                </div><!-- block-right -->
                <?php } ?>

            </div><!-- block-body -->
            <style> 
                .input-icons-calendar { 
                    width: 100%; 
                } 
                
                .icon { 
                    min-width: 40px; 
                } 
        
                .datepicker { 
                    width: 100%; 
                    text-align: center; 
                    margin-left: 5px;
                    border: none;
                    padding: 0 20px 0 10px;
                    outline: 0;
                } 
                .btn#disconnectAgenda{
                    margin-left: 10px;
                }
                .busy{
                    background-color: #ccc !important;
                    color: black !important;
                    border-color: black !important;
                }
                .cacheEcran{
                    position: absolute;
                    top:0;
                    left:0;
                    bottom: 0;
                    right: 0;
                    padding: 0 10px;
                    background-color: white;
                    border: solid 1px #ccc;
                    border-radius: 4px;
                }
                .sked-tape__locations li,
                .sked-tape__timeline li{
                    height: 54px !important;
                    line-height: 54px !important;
                }
                .sked-tape__grid li{
                    height: unset !important;
                    line-height: unset !important;
                }
                .sked-tape__time-frame {
                    overflow-y: hidden;
                    overflow-x: hidden;

                }
                @media (max-width: 576px){
                    .sked-tape__time-frame {
                    overflow-y: hidden;
                    overflow-x: auto;
                    }

                    .block-body {
                        padding: 5px !important;
                    }
                    .cacheEcran{
                        font-size: 12px !important;
                    }
                }
                .homey_preloader {
                    position: absolute;
                    top: 50%;
                    width: 100%;
                }
                .sked-tape__hours li time{
                    opacity: 0;
                }
                .sked-tape__hours li:first-child time{
                    opacity: 1;
                }
                .sked-tape__hours li:last-child time{
                    opacity: 1;
                }

            </style>
            <div class="block-availability-calendars">
                <div class="single-listing-calendar clearfix" style="position: relative !important;">
                    
                    <!-- <div id="oolnaCalendar"></div> -->
                    
                    <div class="input-group" style="width: 100%;">
                        <div class="btn-direction" style="display: flex; flex-direction: column; justify-content: space-between; margin-right: 15px; ">
                            
                            <div style="width: 100%; display: flex; flex-direction: row; justify-content: start; align-items: center;">
                                
                                <button title="Semaine précédente" class="btn btn-default btn-calendar-previous" type="button" style="width:100%; max-width: 42px; margin-left: 5px; padding: 0 15px !important; line-height: 38px; border-bottom-right-radius: 0; border-top-right-radius: 0;"><i class="fa fa-chevron-left"></i></button>
                                <button title="Semaine suivante" class="btn btn-default btn-calendar-next" type="button" style="width:100%; max-width: 42px; margin-right: 5px; padding: 0 15px !important; line-height: 38px; border-bottom-left-radius: 0; border-top-left-radius: 0;"><i class="fa fa-chevron-right"></i></button>
                                <div class="input-icon-calendar" style="width: 80%;position: relative; line-height: 40px;">
                                    <input id="debutSemaine" type="text" class="datepicker"/>
                                    <div title="Cette semaine" class="cacheEcran">
                                        
                                    </div>
                                    <i class="fa fa-calendar icon" style="position: absolute; right: 0; text-align: right; top: 0;padding: 10px; font-size: initial"></i>
                                </div>
                                <div class="input-icon-calendar hidden" style="position: relative; margin-right: 10px;">
                                    <i class="fa fa-calendar icon" style="position: absolute; right: 0; text-align: right;"></i>
                                    <input id="fromDate" type="text" placeholder="Depuis cette date" class="datepicker">
                                </div>
                                <div class="input-icon-calendar hidden" style="position: relative">
                                    <i class="fa fa-calendar icon" style="position: absolute; right: 0; text-align: right;"></i>
                                    <input id="toDate" type="text" placeholder="à cette date" class="datepicker">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="homey_hourly_calendar">

                    </div>
                    <div class="homey_preloader">
						<?php get_template_part('template-parts/spinner'); ?>
					</div>	
                </div>
            </div><!-- block-availability-calendars -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>


<script type="application/javascript">
var googleAgendaList    = [];

var openTime            = <?=$start_hour ?>;
var closeTime           = <?=$end_hour ?>;
var role_user           = '<?= $role ?>';
var postId              = <?= $post->ID ?>;
</script>