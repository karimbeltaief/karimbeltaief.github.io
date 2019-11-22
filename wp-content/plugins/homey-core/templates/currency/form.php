<?php
$instance = null;

if ( Homey_Currencies::is_edit_field() ) {
    $page_title = esc_html__( 'Update Currency', 'homey-core' );
    $button_title = esc_html__( 'Update', 'homey-core' );
    $instance = Homey_Currencies::get_edit_field();
} else {
    $page_title = esc_html__( 'Create Currency', 'homey-core' );
    $button_title = esc_html__( 'Submit', 'homey-core' );
}
$add_currency = Homey_Currencies::currency_add_link();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Currencies', 'homey-core');?></h1>
    <a href="<?php echo esc_url($add_currency);?>" class="page-title-action"><?php esc_html_e('Add New', 'homey-core');?></a>
    <hr class="wp-header-end">

    <form action="" method="POST">
        <div class="form-wrap">
            <?php
            echo Homey::render_form_field( __( 'Currency Name', 'homey-core' ), 'hz_currency[name]', 'text', array(
                'required' => 'required',
                'value' => Homey_Currencies::get_field_value( $instance, 'currency_name' ),
            ));


            echo Homey::render_form_field( __( 'Currency Code', 'homey-core' ), 'hz_currency[code]', 'text', array(
                'required' => 'required',
                'value' => Homey_Currencies::get_field_value( $instance, 'currency_code' ),
            ));

            echo Homey::render_form_field( __( 'Currency Symbol', 'homey-core' ), 'hz_currency[symbol]', 'text', array(
                'required' => 'required',
                'value' => Homey_Currencies::get_field_value( $instance, 'currency_symbol' ),
            ));

            echo Homey::render_form_field(__( 'Currency Position', 'homey-core' ), 'hz_currency[position]', 'list', array(
                'values' => array('before' => esc_html__('Before', 'homey'), 'after' => esc_html__('After', 'homey')),
                'required' => 'required',
                'value' => Homey_Currencies::get_field_value( $instance, 'currency_position' )
            ) );

            echo Homey::render_form_field(__( 'Number of decimal points?', 'homey-core' ), 'hz_currency[decimals]', 'list', array(
                'values' => array( 
                    '0' => '0',
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '10' => '10',
                ),
                'required' => 'required',
                'value' => Homey_Currencies::get_field_value( $instance, 'currency_decimal' )
            ) );

            echo Homey::render_form_field( __( 'Decimal Point Separator(eg: .)', 'homey-core' ), 'hz_currency[decimal_separator]', 'text', array(
                'required' => 'required',
                'value' => Homey_Currencies::get_field_value( $instance, 'currency_decimal_separator' ),
            )); 

            echo Homey::render_form_field( __( 'Thousands Separator(eg: ,)', 'homey-core' ), 'hz_currency[thousand_separator]', 'text', array(
                'required' => 'required',
                'value' => Homey_Currencies::get_field_value( $instance, 'currency_thousand_separator' ),
            )); 
            ?>

            <input type="submit" class="button button-primary" value="<?php echo esc_attr($button_title);?>"/>
            <?php if ( ! empty( $instance['id'] ) ) : ?>
                <input type="hidden" name="hz_currency[id]" value="<?php echo $instance['id']; ?>"/>
            <?php endif; ?>

            <?php wp_nonce_field( 'homey_currency_save_field', 'homey_currency_save_field' ); ?>	
        </div>
    </form>
</div>