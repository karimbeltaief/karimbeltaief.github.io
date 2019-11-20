<?php
global $homey_local, $edit_listing_id, $current_user;
$visisblty = 'hidden';
$class = '';
if (isset($_GET['tab']) && $_GET['tab'] == 'calendar') {
  $class = 'in active';
  $visisblty = 'visible';
}
$min_book_hours  = homey_get_listing_data_by_id('min_book_hours', $edit_listing_id);

if ($min_book_hours > 1) {
  $min_book_hours_label = esc_html__('Hours', 'homey');
} else {
  $min_book_hours_label = esc_html__('Hour', 'homey');
}
$meta_key = "homey_calendar_meta";
$calendar_meta = get_post_meta($edit_listing_id, $meta_key, true);

$calendarId = "''";
if($calendar_meta != null && $calendar_meta != "" && !empty($calendar_meta) ) {
  $foundCalendars = \App\Models\Calendar::query()->where("id", $calendar_meta["id"])->get();
  if ($foundCalendars->count() != 0) {
    $calendarId = $foundCalendars->first()->id;
  } else {
    $foundCalendars = \App\Models\Calendar::query()->where("google_id", $calendar_meta["google_id"])->get();
    if ($foundCalendars->count() != 0) {
      $calendar_meta["id"] = $foundCalendars->first()->id;
      // Mise à jour du post_meta
      update_post_meta($edit_listing_id, $meta_key, $calendar_meta);
      $calendarId = $foundCalendars->first()->id;
    }
  }
}
$homey_prefix ="homey_";
$start_hour = get_post_meta($edit_listing_id, $homey_prefix.'start_hour', true);
$end_hour = get_post_meta($edit_listing_id, $homey_prefix.'end_hour', true);

if (empty($start_hour)) {
  $start_hour = '00:00';
}

if (empty($end_hour)) {
  $end_hour = '23:30';
}

$start_hours_list = '';
$end_hours_list = '';
$start_hour = strtotime($start_hour);
$end_hour = strtotime($end_hour);

for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
  $start_hours_list .= '<option value="'.date('H:i', $halfhour).'">'.date('H:i', $halfhour).'</option>';
  $end_hours_list .= '<option value="'.date('H:i', $halfhour).'">'.date('H:i', $halfhour).'</option>';
}

if(is_user_logged_in()){
  $userId = $current_user->ID;
}
else{
  $userId = 'null';
}
$postId =$edit_listing_id;

$listing_time_zone = get_post_meta($edit_listing_id, "listing_timezone", true);
?>
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

    .cacheEcranBo{
        position: absolute;
        top:0;
        left:0;
        bottom: 0;
        right: 0;
        padding: 0 10px;
        background-color: white;
        border: solid 1px #ccc;
        border-radius: 4px;
        white-space: nowrap;
        overflow: hidden;
    }
    .busy{
        background-color: #ccc !important;
        color: black !important;
        border-color: black !important;
    }
    #connectAgenda,
    #syncAgenda,
    #disconnectAgenda{
        height: 45px;
    }
    #ui-datepicker-div{
        z-index: 1051 !important;
    }
    .timeline-controls{
        display: flex;
        align-items: end;
    }

    .homey_preloader {
        position: absolute;
        top: 50%;
        width: 100%;
    }
    .sked-tape__time-frame {
        overflow-y: hidden;
        overflow-x: hidden;
    }
    @media (max-width: 700px){
        .sked-tape__time-frame {
            overflow-y: hidden;
            overflow-x: auto !important;
        }

        .block-body {
            padding: 5px !important;
        }

        .cacheEcran{
            font-size: 12px !important;
        }
    }
    .no-gutters {
        margin-right: 0;
        margin-left: 0;
    }
    .no-gutters  .col,
    .no-gutters  [class*=col-] {
        padding-right: 0;
        padding-left: 0;
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
<div id="calendar-tab" style="visibility: <?php echo $visisblty; ?>;" class="tab-pane11 fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_attr($homey_local['cal_label']); ?></h3>
    </div>
    <div class="block-body">
<!--        <div class="row">-->
<!--            <div class="col">-->
<!--                <div class="form-group">-->
<!--                    <label>Fuseau horaire du studio: </label>-->
<!--                  --><?php
//                  $timezoneIdentifiers = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
//                  ?>
<!--                    <select class="form-control select custom-select" name="listing_timezone">-->
<!--                        <option>Selectionner</option>-->
<!--                      --><?php
//                      foreach ($timezoneIdentifiers as $identifier):
//                      ?>
<!--                          <option value="--><?//= $identifier ?><!--" --><?//= ($identifier === $listing_time_zone) ? "selected" : "" ?><!-- >--><?//=$identifier?><!--</option>-->
<!--                        --><?php
//                      endforeach;
//                      ?>
<!--                    </select>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
        <div id="property-calendar" class="property-calendar spin">

            <div id="oolnaCalendar"></div>
            <div class="input-group" style="width: 100%; padding: 5px 0px; position: relative !important;">

                <div style="display: flex; flex-direction: row; justify-content: space-between;">
                    <div style="display: flex; width: 100%; flex-direction: inherit; justify-content: start; align-items: center;">
                        <button title="Semaine précédente" class="btn btn-default btn-calendarBo-previous" type="button" style="width:100%; max-width: 42px; padding: 0 15px !important; line-height: 38px; border-bottom-right-radius: 0; border-top-right-radius: 0;"><i class="fa fa-chevron-left"></i></button>
                        <button title="Semaine suivante" class="btn btn-default btn-calendarBo-next" type="button" style="width:100%; max-width: 42px; margin-right: 5px; padding: 0 15px !important; line-height: 38px; border-bottom-left-radius: 0; border-top-left-radius: 0;"><i class="fa fa-chevron-right"></i></button>
                        <div class="input-icon-calendar" style="width: calc(100% - 89px);position: relative; line-height: 40px;">
                            <input id="debutSemaineBo" type="text" class="datepicker"/>
                            <div class="cacheEcranBo" title="Cette semaine">

                            </div>
                            <i class="fa fa-calendar icon" style="position: absolute; right: 0; text-align: right; top: 0;padding: 10px; font-size: initial"></i>
                        </div>
                        <div class="input-icon-calendar hidden" style="position: relative; margin-right: 10px;">
                            <i class="fa fa-calendar icon" style="position: absolute; right: 0; text-align: right;"></i>
                            <input id="fromDateBo" type="text" placeholder="Depuis cette date" class="datepicker">
                        </div>
                        <div class="input-icon-calendar hidden" style="position: relative">
                            <i class="fa fa-calendar icon" style="position: absolute; right: 0; text-align: right;"></i>
                            <input id="toDateBo" type="text" placeholder="à cette date" class="datepicker">
                        </div>
                    </div>
                </div>
            </div>
            <div id="homey_hourly_calendar_edit_listing"></div>

            <div class="homey_preloader">
              <?php get_template_part('template-parts/spinner'); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/template" id="alertTemplate">
    <div class="alert alert-{{ class }} alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <p>{{ message }}</p>
    </div>
</script>
<!-- Modal -->
<div id="calendarModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <input type="hidden" name="postId" id="postId" value="<?php echo $post->ID?>"/>
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Horaire d'indisponibilité</h4>
                <small id="date">le ....</small>
            </div>
            <div class="modal-body">
                <form>
                    <div class="search-hours-range clearfix" style="margin-bottom: 10px; position:relative;">
                        <input class="form-control" id="datePicker" />
                    </div>
                    <div class="search-hours-range clearfix">
                        <div class="search-hours-range-left">
                            <select name="hstart" id="hstart" class="selectpicker start_hour" data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>">
                                <option value=""><?php echo homey_option('srh_starts_label'); ?></option>
                              <?php echo $start_hours_list; ?>
                            </select>
                        </div>
                        <div class="search-hours-range-right">
                            <select name="hend" id="hend" class="selectpicker end_hour" data-live-search="true" title="<?php echo homey_option('srh_ends_label'); ?>">
                                <option value=""><?php echo homey_option('srh_ends_label'); ?></option>
                              <?php echo $end_hours_list; ?>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" id="params" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="trash-event"><span class="fa fa-trash"></span></button>
                <button type="button" class="btn btn-success" id="okButton">Enregistrer</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
            </div>
        </div>

    </div>
</div>

<!--<script async defer src="https://apis.google.com/js/api.js"-->
<!--    onload="this.onload=function(){};handleClientLoad()"-->
<!--    onreadystatechange="if (this.readyState === 'complete') this.onload()">-->
<!--</script>-->

<script type="text/javascript">
    var userId      = <?= $userId ?>;
    var postId      = <?= $postId ?>;
    var calendarId  = <?= $calendarId ?>;

    var openTime    = <?=$start_hour ?>;
    var closeTime   = <?=$end_hour ?>;
    var useOldCalendar= true;


    (function($){
        $(document).ready(function(){
            $datePicker = $("#datePicker");
            $datePicker.datepicker({
                dateFormat: "dd/mm/yy"
            });
            $datePicker.on("change",function(e){
                var momentDateFormat = 'DD/MM/YYYY';
                var mDate = moment($(this).val(), momentDateFormat);
                $("#date").text("le " + mDate.format("DD MMMM YYYY"));
            });
        });
    })(jQuery);
</script>