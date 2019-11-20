<?php
/**
 * Invoice Post Type
 * Created by PhpStorm.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Homey_Post_Type_Invoice {
    /**
     * Initialize custom post type
     *
     * @access public
     * @return void
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'definition' ) );
    
        add_filter('manage_edit-homey_invoice_columns', array( __CLASS__, 'homey_invoices_edit_columns' ));
        add_action('manage_posts_custom_column', array( __CLASS__, 'homey_invoice_populate_columns' ) );
        add_filter( 'manage_edit-homey_invoice_sortable_columns', array( __CLASS__, 'homey_invoice_sort' ) );
    }

    /**
     * Custom post type definition
     *
     * @access public
     * @return void
     */
    public static function definition() {
        $labels = array(
            'name' => __( 'Invoices','homey-core'),
            'singular_name' => __( 'Invoice','homey-core' ),
            'add_new' => __('Add New','homey-core'),
            'add_new_item' => __('Add New Invoice','homey-core'),
            'edit_item' => __('Edit Invoice','homey-core'),
            'new_item' => __('New Invoice','homey-core'),
            'view_item' => __('View Invoice','homey-core'),
            'search_items' => __('Search Invoice','homey-core'),
            'not_found' =>  __('No Invoice found','homey-core'),
            'not_found_in_trash' => __('No Invoice found in Trash','homey-core'),
            'parent_item_colon' => ''
        );

        $labels = apply_filters( 'homey_post_type_invoices_labels', $labels );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'page',
            'hierarchical' => false,
            'menu_icon' => 'dashicons-book',
            'menu_position' => 24,
            'supports' => array('title'),
            'exclude_from_search'   => true,
            'can_export' => true,
            'rewrite' => array( 'slug' => 'invoice' )
        );

        register_post_type('homey_invoice',$args);
    }


    /**
     * Custom admin columns for post type
     *
     * @access public
     * @return array
     */
    
    public static function homey_invoices_edit_columns($columns)
    {

        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "title" => __( 'Invoice Title','homey-core' ),
            "invoice_price" => __( 'Price','homey-core' ),
            "billing_for" => __('Billion For','homey-core'),
            "invoice_type" => __('Invoice Type','homey-core'),
            "invoice_payment_method" => __( 'Payment Method','homey-core' ),
            "invoice_buyer" => __('Buyer','homey-core'),
            "invoice_status" => __( 'Status','homey-core' ),
            "date" => __( 'Date','homey-core' )
        );

        return $columns;
    }




    /**
     * Custom admin columns implementation
     *
     * @access public
     * @param string $column
     * @return array
     */
    public static function homey_invoice_populate_columns($column){
        global $post;
        $local = homey_get_localization();

        $invoice_meta = get_post_meta( $post->ID, '_homey_invoice_meta', true );
        switch ($column)
        {
            case 'invoice_price':
                echo homey_formatted_price( $invoice_meta['invoice_item_price'] );
                break;

            case 'invoice_payment_method':
                if( $invoice_meta['invoice_payment_method'] == 'Direct Bank Transfer' ) {
                    esc_html_e( 'Direct Bank Transfer', 'homey-core' );
                } else {
                    echo $invoice_meta['invoice_payment_method'];
                }
                break;

            case 'invoice_type':
                echo esc_attr( $invoice_meta['invoice_billing_type'] );
                break;

            case 'billing_for':
                if($invoice_meta['invoice_billion_for'] == 'reservation') {
                    echo $local['resv_fee_text'];
                } elseif($invoice_meta['invoice_billion_for'] == 'upgrade_featured') {
                    echo $local['upgrade_text'];
                } else {
                    echo esc_attr( $invoice_meta['invoice_billion_for'] );
                }
                break;

            case 'invoice_buyer':
                $user_info = get_userdata($invoice_meta['invoice_buyer_id']);
                if(!empty($user_info)) {
                    echo esc_attr( $user_info->display_name ).'<br>';
                    echo esc_attr( $user_info->user_email );
                }
                break;

            case 'invoice_status':
                $invoice_status = get_post_meta(  $post->ID, 'invoice_payment_status', true );
                if( $invoice_status == 0 ) {
                    echo '<span class="fave_admin_label float-none label-red">'.__('Not Paid','homey-core').'</span>';
                } else {
                    echo '<span class="fave_admin_label float-none label-green">'.__('Paid','homey-core').'</span>';
                }
                break;
        }
    }

    public static function homey_invoice_sort( $columns ) {
        $columns['invoice_price']  = 'invoice_price';
        $columns['invoice_payment_method']  = 'invoice_payment_method';
        $columns['invoice_type']   = 'invoice_type';
        $columns['billing_for']    = 'billing_for';
        $columns['invoice_buyer']  = 'invoice_buyer';
        $columns['invoice_buyer_email']  = 'invoice_buyer_email';
        $columns['invoice_status'] = 'invoice_status';
        return $columns;
    }

        
}