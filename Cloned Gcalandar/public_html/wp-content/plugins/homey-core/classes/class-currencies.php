<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Homey_Currencies {
    
    /**
     * Initialize custom post type
     *
     * @access public
     * @return void
     */
    public static function init() {
        add_action( 'init', array( __CLASS__ , 'submit_currencies_form' ) );
        add_action( 'init', array( __CLASS__ , 'delete_currency' ) );
    }


    /**
     * Render currency main page
     * @return void
     */
    public static function render()
    {
        $template = apply_filters( 'homey_dashboard_template_path', HOMEY_TEMPLATES . '/currency/main.php' );

        if ( file_exists( $template ) ) {
            include_once( $template );
        }
    }

    public static function delete_currency() {

        $nonce = 'delete-currency';

        if ( ! empty( $_GET[ 'nonce' ] ) && wp_verify_nonce( $_GET[ 'nonce' ], $nonce ) && ! empty( $_GET['id'] ) ) {

            global $wpdb;

            $wpdb->delete( $wpdb->prefix . 'homey_currencies', array( 'id' => $_GET['id'] ) );
            wp_redirect( 'admin.php?page=homey_currencies' ); die;

        }

    }

    public static function currency_add_link() {
        $url = site_url( 'wp-admin/admin.php?page=homey_currencies' );
        return add_query_arg( 'action', 'add-new', $url);
    }

    public static function get_listing_currency($listing_id) {
        global $wpdb;

        $currency_code = get_post_meta( get_the_ID(), 'fave_currency', true);
        
        $result = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "homey_currencies where currency_code='$currency_code'");
        
        if(!empty($result)) {
            return $result;
        }
        return false;
    }

    public static function get_listing_currency_by_id($listing_id) {
        global $wpdb;

        $currency_code = get_post_meta( $listing_id, 'fave_currency', true);
        
        $result = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "homey_currencies where currency_code='$currency_code'");
        
        if(!empty($result)) {
            return $result;
        }
        return false;
    }

    public static function get_listing_currency_2($listing_id, $currency_code) {
        global $wpdb;
    
        $result = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "homey_currencies where currency_code='$currency_code'", ARRAY_A);

        if(!empty($result)) {
            return $result;
        }
        return false;
    }

    public static function get_currency_by_code($currency_code) {
        global $wpdb;
    
        $result = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "homey_currencies where currency_code='$currency_code'", ARRAY_A);

        if(!empty($result)) {
            return $result;
        }
        return false;
    }

    public static function get_form_fields() {
        global $wpdb;

        $result = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "homey_currencies");

        if(!empty($result)) {
            return $result;
        }
        return false;
    }

    public static function get_currency_codes() {
        global $wpdb;

        $result = $wpdb->get_results(" SELECT currency_code FROM " . $wpdb->prefix . "homey_currencies");

        if(!empty($result)) {
            return $result;
        }
        return false;
    }

    public static function submit_currencies_form() {
        global $wpdb;
        $nonce = 'homey_currency_save_field';

        if ( ! empty( $_REQUEST[ $nonce ] ) && wp_verify_nonce( $_REQUEST[ $nonce ], $nonce ) ) {

            $data = $_POST['hz_currency'];

            $currency_name = $data['name'];
            $currency_code = $data['code'];
            $currency_symbol = $data['symbol'];
            $currency_position = $data['position'];
            $currency_decimal = self::get_field_value( $data, 'decimals' );
            $currency_decimal_separator = self::get_field_value( $data, 'decimal_separator' );
            $currency_thousand_separator = self::get_field_value( $data, 'thousand_separator' );

            $instance = apply_filters( 'homey_currencies_before_save', array(
                'currency_name' => $currency_name,
                'currency_code' => $currency_code,
                'currency_symbol' => $currency_symbol,
                'currency_position' => $currency_position,
                'currency_decimal' => !empty($currency_decimal) ? $currency_decimal : '0',
                'currency_decimal_separator' => $currency_decimal_separator,
                'currency_thousand_separator' => $currency_thousand_separator,
            ) );

            if ( ! empty( $data['id'] ) ) {
                $wpdb->update( $wpdb->prefix . 'homey_currencies', $instance, array( 'id' => $data['id'] ) );
                add_action( 'admin_notices', array( __CLASS__ , 'update_currency_notice' ) );
            } else {
                $inserted = $wpdb->insert( $wpdb->prefix . 'homey_currencies', $instance);
                if($inserted) {
                    add_action( 'admin_notices', array( __CLASS__ , 'add_currency_notice' ) );
                } else {
                    add_action( 'admin_notices', array( __CLASS__ , 'error_currency_notice' ) );
                }
            }
        }
    }


    public static function add_currency_notice() { ?>
        <div class="updated notice notice-success is-dismissible">
            <p><?php esc_html_e( 'The currency has been added, excellent!', 'homey-core' ); ?></p>
        </div>
    <?php    
    }

    public static function update_currency_notice() { ?>
        <div class="updated notice notice-success is-dismissible">
            <p><?php esc_html_e( 'The currency has been updated, excellent!', 'homey-core' ); ?></p>
        </div>
    <?php    
    }

    public static function error_currency_notice() { ?>
        <div class="error notice notice-error is-dismissible">
            <p><?php esc_html_e( 'There has been an error. Bummer!', 'homey-core' ); ?></p>
        </div>
    <?php    
    }

    public static function currency_edit_link( $id ) {
        return add_query_arg( array(
            'action' => 'edit-currency',
            'id' => $id,
        ) );
    }

    public static function currency_delete_link( $id ) {
        
        return add_query_arg( array(
            'action' => 'delete-currency',
            'id' => $id,
            'nonce' => wp_create_nonce( 'delete-currency' )
        ) );
    }

    public static function get_edit_field()
    {
        if ( ! empty( $_GET['id'] ) && ! empty( $_GET['action'] ) ) {
            $field =  self::get_field( $_GET['id'] );

            return $field;
        }

        return null;
    }

    public static function get_field( $id ) {
        global $wpdb;
        $instance = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "homey_currencies WHERE id = '{$id}'", ARRAY_A );

        return $instance;
    }

    public static function get_field_value( $instance, $key, $default = null ) {
        return apply_filters( 'homey_currencies_get_field_value', ! empty( $instance[ $key ] ) ? $instance[ $key ] : $default, $key, $instance );
    }

    public static function is_edit_field() {
        return self::get_edit_field() ? true : false;
    }

        
}
?>