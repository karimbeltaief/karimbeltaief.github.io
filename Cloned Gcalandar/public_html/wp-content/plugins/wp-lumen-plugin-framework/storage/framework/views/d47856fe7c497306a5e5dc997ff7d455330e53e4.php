<?php $__env->startSection('heading'); ?>
<h1>Settings</h1>
<p>Simple flat-file JSON storage for key / value pairs.</p>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

<h1>Google synchronization agenda API</h1>

<form method="POST">
    <input type="hidden" name="action" value="save"/>
    <input type="hidden" name="_token" value="<?php echo e(wpLumen()->csrf()); ?>"/>
    <div class="form-group row">
        <div class="col-xs-12">
          <p>CLIENT ID<span>*</span></p>
          <span class="icon-case"><i class="fa fa-user"></i></span>
          <input type="text" name="oo_gsa_client_id" class="form-control  input-lg large-text" data-rule="required" id="oo_gsa_client_id" value="<?php echo e($settings['oo_gsa_client_id_value']); ?>">
        </div>
    </div>
    <div class="form-group row">
        <div class="col-xs-12">
            <p>API KEY<span>*</span></p>
            <span class="icon-case"><i class="fa fa-key"></i></span>
            <input type="text" name="oo_gsa_api_key"  class="form-control  input-lg large-text" data-rule="required" id="oo_gsa_api_key" value="<?php echo e($settings['oo_gsa_api_key_value']); ?>">
        </div>
    </div>
    <div class="form-group row">
        <div class="col-xs-12">
            <p>E-mail qui contourne le paiement<span>*</span></p>
            <span class="icon-case"><i class="fa fa-envelope"></i></span>
            <input type="email" name="god_email"  class="form-control input-lg large-text" data-rule="required" id="oo_gsa_api_key" value="<?php echo e($settings['god_email']); ?>">
        </div>
    </div>
    <div class="form-group row">
        <div class="col-xs-12">
          <p>PATTERN INDISPONIBLE<span>*</span></p>
          <span class="icon-case"><i class="fa fa-ban"></i></span>
          <input type="text" name="oo_gsa_pattern_indisponible" class="form-control  input-lg large-text" data-rule="required" id="oo_gsa_pattern_indisponible" value="<?php echo e($settings['oo_gsa_pattern_indisponible_value']); ?>">
        </div>
    </div>
    <div class="form-group row">
        <div class="col-xs-12">
            <p>PATTERN RESERVATION<span>*</span></p>
            <span class="icon-case"><i class="fa fa-flag-checkered"></i></span>
            <input type="text" name="oo_gsa_pattern_reservation"  class="form-control  input-lg large-text" data-rule="required" id="oo_gsa_pattern_reservation" value="<?php echo e($settings['oo_gsa_pattern_reservation_value']); ?>">
        </div>
    </div>
    <input type="submit" value="Save" class="button button-primary button-large">
</form> 

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.default', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>