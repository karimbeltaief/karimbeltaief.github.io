<?php
/*-----------------------------------------------------------------------------------*/
/*	Register
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_register_module') ) {
	function homey_register_module($atts, $content = null)
	{
		extract(shortcode_atts(array(
			'register_title' => ''
		), $atts));

		$terms_conditions = homey_option('login_terms_condition');
		$enable_password = homey_option('enable_password');
		$enable_forms_gdpr = homey_option('enable_forms_gdpr');
		$forms_gdpr_text = homey_option('forms_gdpr_text');

		ob_start();
		?>
		
		<div class="register_module_wrap">

	        <div class="homey_register_messages message"></div>

	        <h2><?php echo $register_title; ?></h2>
	        <div class="modal-login-form">
	            
	            <form>
	                <div class="form-group">
	                    <input name="username" type="text" class="form-control email-input-1" placeholder="<?php esc_html_e('Username','homey'); ?>" />
	                </div>
	                <div class="form-group">
	                    <input type="useremail" name="useremail" class="form-control email-input-1" placeholder="<?php echo esc_html__('Email', 'homey'); ?>">
	                </div>
	                <div class="form-group">
	                    <input type="hidden" name="role" value="homey_host">
	                </div>

	                <?php if( $enable_password == 'yes' ) { ?>
	                <div class="form-group">
	                    <input type="password" name="register_pass" class="form-control password-input-1" placeholder="<?php echo esc_html__('Password', 'homey'); ?>">
	                </div>
	                <div class="form-group">
	                    <input type="password" name="register_pass_retype" class="form-control password-input-2" placeholder="<?php echo esc_html__('Repeat Password', 'homey'); ?>">
	                </div>
	                <?php } ?>

	                <?php get_template_part('template-parts/google', 'reCaptcha'); ?>

	                <div class="checkbox pull-left">
	                    <label>
	                        <input name="term_condition" type="checkbox"> <?php echo sprintf( wp_kses(__( 'I agree with your <a href="%s">Terms & Conditions</a>', 'homey' ), homey_allowed_html()), get_permalink($terms_conditions) ); ?>
	                    </label>
	                </div>
	                <?php if($enable_forms_gdpr != 0) { ?>
                    <div class="checkbox pull-left">
                        <label>
                            <input name="privacy_policy" type="checkbox">
                            <?php echo wp_kses($forms_gdpr_text, homey_allowed_html()); ?>
                        </label>
                    </div>
                    <?php } ?>
	                <?php wp_nonce_field( 'homey_register_nonce', 'homey_register_security' ); ?>
	                <input type="hidden" name="action" value="homey_register">
	                <input type="hidden" name="role" value="homey_host">
	                <button type="submit" class="homey-register-button btn btn-primary btn-full-width"><?php echo esc_html__('Register', 'homey'); ?></button>
	            </form>
	        </div>
	    </div><!-- /.modal-content -->


		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;

	}

	add_shortcode('homey-register', 'homey_register_module');
}
?>