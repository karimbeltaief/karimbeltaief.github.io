<?php
$instance = null;

if ( FCC_Currencies::is_edit_field() ) {
    $page_title = esc_html__( 'Update Currency', 'favethemes-currency-converter' );
    $button_title = esc_html__( 'Update', 'favethemes-currency-converter' );
    $instance = FCC_Currencies::get_edit_field();
} else {
    $page_title = esc_html__( 'Create Currency', 'favethemes-currency-converter' );
    $button_title = esc_html__( 'Submit', 'favethemes-currency-converter' );
}
$add_currency = FCC_Currencies::currency_add_link();
?>

<div class="wrap">
    <br>
    <a href="<?php echo esc_url($add_currency);?>" class="page-title-action"><?php esc_html_e('Add New Currency', 'favethemes-currency-converter');?></a>
    <hr class="wp-header-end">

    <form action="" method="POST">
        <div class="form-wrap">
            <?php
            echo FCC_Currencies::render_form_field( __( 'Currency Name', 'favethemes-currency-converter' ), 'hz_currency[name]', 'text', array(
                'required' => 'required',
                'value' => FCC_Currencies::get_field_value( $instance, 'currency_name' ),
            ));


            echo FCC_Currencies::render_form_field( __( 'Currency Code', 'favethemes-currency-converter' ), 'hz_currency[code]', 'text', array(
                'required' => 'required',
                'value' => FCC_Currencies::get_field_value( $instance, 'currency_code' ),
            ));

            echo FCC_Currencies::render_form_field( __( 'Currency Symbol', 'favethemes-currency-converter' ), 'hz_currency[symbol]', 'text', array(
                'required' => 'required',
                'value' => FCC_Currencies::get_field_value( $instance, 'currency_symbol' ),
            ));

            echo FCC_Currencies::render_form_field(__( 'Currency Position', 'favethemes-currency-converter' ), 'hz_currency[position]', 'list', array(
                'values' => array('before' => esc_html__('Before', 'homey'), 'after' => esc_html__('After', 'homey')),
                'required' => 'required',
                'value' => FCC_Currencies::get_field_value( $instance, 'currency_position' )
            ) );

            echo FCC_Currencies::render_form_field(__( 'Number of decimal points?', 'favethemes-currency-converter' ), 'hz_currency[decimals]', 'list', array(
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
                'value' => FCC_Currencies::get_field_value( $instance, 'currency_decimal' )
            ) );

            echo FCC_Currencies::render_form_field( __( 'Decimal Point Separator(eg: .)', 'favethemes-currency-converter' ), 'hz_currency[decimal_separator]', 'text', array(
                'required' => 'required',
                'value' => FCC_Currencies::get_field_value( $instance, 'currency_decimal_separator' ),
            )); 

            echo FCC_Currencies::render_form_field( __( 'Thousands Separator(eg: ,)', 'favethemes-currency-converter' ), 'hz_currency[thousand_separator]', 'text', array(
                'required' => 'required',
                'value' => FCC_Currencies::get_field_value( $instance, 'currency_thousand_separator' ),
            )); 
            ?>

            <input type="submit" class="button button-primary" value="<?php echo esc_attr($button_title);?>"/>
            <?php if ( ! empty( $instance['id'] ) ) : ?>
                <input type="hidden" name="hz_currency[id]" value="<?php echo $instance['id']; ?>"/>
            <?php endif; ?>

            <?php wp_nonce_field( 'fcc_currency_save_field', 'fcc_currency_save_field' ); ?>	
        </div>
    </form>
</div>