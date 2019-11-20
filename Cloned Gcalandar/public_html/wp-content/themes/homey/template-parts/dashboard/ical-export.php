<?php
$listing_id = isset($_GET['edit_listing']) ? $_GET['edit_listing'] : '';
$iCal_export_link = homey_generate_ical_export_link($listing_id);
?>
<div class="modal fade custom-modal" id="modal-calendar-export" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p><strong><?php echo esc_html__('Export iCal', 'homey'); ?></strong></p>
                <div class="modal-calendar-availability clearfix">
                    <div class="form-group">
                        <label><?php echo esc_html__('Export Link', 'homey'); ?></label>
                        <p><?php echo $iCal_export_link; ?></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-center">
                <a href="<?php echo $iCal_export_link; ?>" target="_blank" class="btn btn-primary btn-half-width"><?php echo esc_html__('Download', 'homey'); ?></a>
                <button type="button" class="btn btn-grey-outlined btn-half-width" data-dismiss="modal"><?php echo esc_html__('Cancel', 'homey'); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->