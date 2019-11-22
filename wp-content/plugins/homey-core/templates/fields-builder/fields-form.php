<?php
$instance = null;

if ( Homey_Fields_Builder::is_edit_field() ) {
    $page_title = esc_html__( 'Update field', 'homey-core' );
    $button_title = esc_html__( 'Update', 'homey-core' );
    $instance = Homey_Fields_Builder::get_edit_field();
} else {
    $page_title = esc_html__( 'Create field', 'homey-core' );
    $button_title = esc_html__( 'Submit', 'homey-core' );
}
$add_new = Homey_Fields_Builder::field_add_link();
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Fields Builder', 'homey-core');?></h1>
    <a href="<?php echo esc_url($add_new);?>" class="page-title-action"><?php esc_html_e('Add New', 'homey-core');?></a>
    <hr class="wp-header-end">
    <form action="" method="POST">
        <div class="form-wrap">
            <?php

            echo Homey::render_form_field( esc_html__( 'Field Name', 'homey-core' ), 'hz_fbuilder[label]', 'text', array(
                'required' => 'required',
                'value' => Homey_Fields_Builder::get_field_value( $instance, 'label' ),
            ));

            echo Homey::render_form_field( esc_html__( 'Placeholder', 'homey-core' ), 'hz_fbuilder[placeholder]', 'text', array(
                'value' => Homey_Fields_Builder::get_field_value( $instance, 'placeholder' ),
            ));


            echo Homey::render_form_field(esc_html__( 'Type', 'homey-core' ), 'hz_fbuilder[type]', 'list', array(
                'values' => Homey_Fields_Builder::get_field_types(),
                'placeholder' => esc_html__( '-- Choose field type --', 'homey-core' ),
                'required' => 'required',
                'value' => Homey_Fields_Builder::get_field_value( $instance, 'type' ),
                'class' => 'homey-fbuilder-js-on-change',
            ) );

            echo '<div class="homey_select_options_loader_js">';
            if($instance['type'] == 'select') {
                include HOMEY_TEMPLATES . '/fields-builder/multiple.php';
            }
            echo '</div>';
            
            /*echo Homey::render_form_field(esc_html__( 'Add in Search?', 'homey-core' ), 'hz_fbuilder[is_search]', 'list', array(
                'values' => array('no' => esc_html__('No', 'homey'), 'yes' => esc_html__('Yes', 'homey')),
                'required' => 'required',
                'value' => Homey_Fields_Builder::get_field_value( $instance, 'is_search' )
            ) );

            echo Homey::render_form_field( esc_html__( 'Icon (only for luxury homes layout)', 'homey-core' ), 'hz_fbuilder[icon]', 'text', array(
                'id' => 'c_icon',
                'value' => Homey_Fields_Builder::get_field_value( $instance, 'options' ),
            )); */

            ?>
            <!-- <input id="upload_icon_button" type="button" class="button" value="<?php _e( 'Upload Icon', 'homey' ); ?>" />
            <input type='hidden' name='hz_fbuilder[icon_attachment_id]' id='icon_attachment_id' value=''> -->


            <input type="submit" class="button button-primary" value="<?php echo esc_attr($button_title);?>"/>
            <?php if ( ! empty( $instance['id'] ) ) : ?>
                <input type="hidden" name="hz_fbuilder[id]" value="<?php echo $instance['id']; ?>"/>
            <?php endif; ?>

            <?php wp_nonce_field( 'homey_fbuilder_save_field', 'homey_fbuilder_save_field' ); ?>	
        </div>
    </form>
</div>