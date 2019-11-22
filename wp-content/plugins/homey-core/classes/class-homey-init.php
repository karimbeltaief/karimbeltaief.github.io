<?php


class Homey {

    /**
     * Plugin instance.
     *
     * @var homey
     */
    protected static $instance;


    /**
     * Plugin version.
     *
     * @var string
     */
    protected static $version = '1.0.0';


    /**
     * Constructor.
     */
    protected function __construct()
    {   
        $this->actions();
        $this->init();
        $this->homey_inc_files();
        $this->filters();
    }

    /**
     * Return plugin version.
     *
     * @return string
     */
    public static function getVersion() {
        return static::$version;
    }

    /**
     * Return plugin instance.
     *
     * @return homey
     */
    protected static function getInstance() {
        return is_null( static::$instance ) ? new Homey() : static::$instance;
    }

    /**
     * Initialize plugin.
     *
     * @return void
     */
    public static function run() {
        self::homey_class_loader();
        self::homey_function_loader();
        static::$instance = static::getInstance();
    }


    /**
     * include files
     *
     * @since 1.0
     *
    */
    function homey_inc_files() {

        $homey_theme_name = (wp_get_theme()->Name);

        $activation_status = get_option( 'homey_activation' );

        if( $homey_theme_name == 'Homey' || $homey_theme_name == 'Homey Child' ) {

            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/section-title.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/space.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/listings.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/listing-carousel.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/listing-by-id.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/listing-by-ids.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/grids.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/blog-posts.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/blog-posts-carousel.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/partners.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/testimonials.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/register.php');
            require_once(HOMEY_PLUGIN_PATH . '/vc_shortcodes/promo-box.php');

            //Emails
            require_once(HOMEY_PLUGIN_PATH . '/functions/emails.php');

            //Elementor Page Builder
            require_once(HOMEY_PLUGIN_PATH . '/elementor/elementor.php');

            // Widgets
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-listing.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-listing-list.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-reviews.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-taxonomies-card.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-taxonomies.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-about.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-contact.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-facebook.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-code-banner.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-flickr-photos.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-image-banner-300-250.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-latest-posts.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-latest-comments.php' );
            require_once ( HOMEY_PLUGIN_PATH . '/includes/widgets/homey-currency-switcher.php' );
            
            //Yelp API
            require_once ( HOMEY_PLUGIN_PATH . '/includes/yelpauth/yelpoauth.php' );

            //Meta boxes
            require_once(HOMEY_PLUGIN_PATH . '/includes/metaboxes.php');
            
            require_once(HOMEY_PLUGIN_PATH . '/includes/honor-ssl-for-attachments.php');

            //paypal
            require_once(HOMEY_PLUGIN_PATH . '/third-party/3rdparty_functions.php');

            if (!class_exists('RW_Meta_Box')) {
                require_once(HOMEY_PLUGIN_PATH . '/extensions/meta-box/meta-box.php');
            }
            if (!class_exists('MB_Tabs')) {
                require_once(HOMEY_PLUGIN_PATH . '/extensions/meta-box/addons/meta-box-tabs/meta-box-tabs.php');
            }
            if (!class_exists('RWMB_Columns')) {
                require_once(HOMEY_PLUGIN_PATH . '/extensions/meta-box/addons/meta-box-columns/meta-box-columns.php');
            }
            if (!class_exists('RWMB_Group')) {
                require_once(HOMEY_PLUGIN_PATH . '/extensions/meta-box/addons/meta-box-group/meta-box-group.php');
            }
            if (!class_exists('MB_Term_Meta_Box')) {
                require_once(HOMEY_PLUGIN_PATH . '/extensions/meta-box/addons/mb-term-meta/mb-term-meta.php');
            }
            if (!class_exists('MB_Conditional_Logic')) {
                require_once(HOMEY_PLUGIN_PATH . '/extensions/meta-box/addons/meta-box-conditional-logic/meta-box-conditional-logic.php');
            }

            // Include the Redux theme options Framework
            if (!class_exists('OCDI_Plugin')) {
                require_once(HOMEY_PLUGIN_PATH . '/extensions/one-click-demo-import/one-click-demo-import.php');
            }

        } // End theme check
    }


    /**
     * Plugin actions.
     *
     * @return void
     */
    public function actions() {

        add_action( 'admin_menu', array( $this, 'homey_register_admin_pages' ) );
        //add_action( 'activated_plugin', array( $this, 'redirect' ) );
    }

    /**
     * Add filters to the WordPress functionality.
     *
     * @return void
     */
    public function filters() {
        add_filter( 'homey_theme_meta', array( $this, 'homey_field_builder_meta' ), 9, 1 );
    }

    public function homey_field_builder_meta($meta_boxes) {
        //print_r($meta_boxes); 

        if(class_exists('Homey_Fields_Builder')) {
            $fields = array();
            $homey_prefix = 'homey_';
            $fields_array = Homey_Fields_Builder::get_form_fields();
            $i = 500; $j = 0;

            $columns = 6;
            
            if(!empty($fields_array)) {
                $numItems = count($fields_array);
                foreach ($fields_array as $value) {
                    $i++;

                    if($value->type == 'select') {
                        $options = unserialize($value->fvalues);

                        $options_array = array();
                        if(!empty($options)) {
                            foreach ($options as $key => $val) {
                                $options_array[$key] = $val;
                            }
                        }

                        $fields = array(
                            'id' => "{$homey_prefix}".$value->field_id,
                            'name' => $value->label,
                            'type' => $value->type,
                            'placeholder' => $value->placeholder,
                            'std' => "",
                            'desc' => '',
                            'options' => $options_array,
                            'columns' => $columns,
                            'tab' => 'listing_details',
                        );
                    } elseif($value->type == 'text') {
                        $fields = array(
                            'id' => "{$homey_prefix}".$value->field_id,
                            'name' => $value->label,
                            'type' => $value->type,
                            'placeholder' => $value->placeholder,
                            'std' => "",
                            'desc' => '',
                            'columns' => $columns,
                            'tab' => 'listing_details',
                        );
                    }

                    elseif($value->type == 'textarea') {
                        $fields = array(
                            'id' => "{$homey_prefix}".$value->field_id,
                            'name' => $value->label,
                            'type' => 'wysiwyg',
                            'placeholder' => $value->placeholder,
                            'std' => "",
                            'desc' => '',
                            'columns' => 12,
                            'tab' => 'listing_details',
                            'options' => array(
                                'textarea_rows' => 4,
                                'teeny'         => false,
                                'media_buttons' => false,
                                'wpautop' => true,
                            ),
                        );
                    }

                    $meta_boxes[0]['fields'][$i] = $fields;
                }
            }
        }

        return $meta_boxes;
    }

    

    /**
     * Initialize classes
     *
     * @return void
     */
    public function init() {

        Homey_Listing_Post_Type::init();
        Homey_Reservation_Post_Type::init();
        Homey_Post_Type_Testimonials::init();
        

        if( is_admin() ) {
            
            Homey_Dashboard::init();
            Homey_Fields_Builder::init();
            Homey_Permalinks::init();
            Homey_Post_Type_Invoice::init();
            Homey_Review_Post_Type::init();
            Homey_Post_Type_Partner::init();
        }

        add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'enqueue_scripts' ) );
        add_filter('cron_schedules', array( __CLASS__, 'homey_core_cron_schedules' ), 10, 1);

    }


    public static function enqueue_scripts() {
        $js_path = 'assets/admin/js/';
        $css_path = 'assets/admin/css/';

        //wp_enqueue_style('homey-admin-style', HOMEY_PLUGIN_URL . $css_path . 'style.css', array(), '1.0.0', 'all');
    }

    /**
     * Add new schedules to wp_cron.
     *
     */
    public static function homey_core_cron_schedules( $schedules ) {
        $schedules['hourlyfour'] = array(
            'interval' => 14400, // evey 4 hours
            'display'  => esc_html__(  'Every 4 hours','homey'),
        );
        return $schedules;
    }


    /**
     * Load plugin files.
     *
     * @return void
     */
    public static function homey_class_loader()
    {
        $files = apply_filters( 'homey_class_loader', array(
            HOMEY_PLUGIN_PATH . '/classes/class-listing-post-type.php',
            HOMEY_PLUGIN_PATH . '/classes/class-reservation-post-type.php',
            HOMEY_PLUGIN_PATH . '/classes/class-review-post-type.php',
            HOMEY_PLUGIN_PATH . '/classes/class-partners-post-type.php',
            HOMEY_PLUGIN_PATH . '/classes/class-testimonials-post-type.php',
            HOMEY_PLUGIN_PATH . '/classes/class-invoice-post-type.php',
            HOMEY_PLUGIN_PATH . '/classes/Homey_Query.php',
            HOMEY_PLUGIN_PATH . '/classes/class-dashboard.php',
            HOMEY_PLUGIN_PATH . '/classes/class-fields-builder.php',
            HOMEY_PLUGIN_PATH . '/classes/class-permalinks.php',
        ) );

        foreach ( $files as $file ) {
            if ( file_exists( $file ) ) {
                include $file;
            }
        }
    }

    public static function homey_function_loader() {
        $files = apply_filters( 'homey_function_loader', array(
            HOMEY_PLUGIN_PATH . '/functions/functions.php',
            HOMEY_PLUGIN_PATH . '/functions/functions-rewrite.php',
            HOMEY_PLUGIN_PATH . '/functions/functions-options.php',
            
        ) );

        foreach ( $files as $file ) {
            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }
    }

    /*
    * Render Form fields
    */
    public static function render_form_field( $label, $field_name, $type, $options = array() )
    {
        $template = '<div class="">
                        <div class=""><label>%s</label></div>
                        <div class="">%s</div>
                    </div>';

        $template = apply_filters( 'homey_form_fields_template', $template, $label, $options );

        $options_string = null;
        $options['name'] = $field_name;
        $options['value'] = ! empty( $options['value'] ) ? $options['value'] : false;

        foreach ( $options as $key => $value ) {
            if ( is_array( $value ) || ! $value ) continue;
            $options_string .= $key . '="' . $value . '" ';
        }

        switch ( $type ) {
            case 'checkbox':
                $field = "<input type='hidden' name='{$field_name}' value='0'/>
                          <input type='checkbox' {$options_string}>";
                break;

            case 'list':
            case 'select':
            case 'selectbox':
                $field = "<select {$options_string}>";

                if ( ! empty( $options['placeholder'] ) ) {
                    $field .= '<option value="">' . $options['placeholder'] . '</option>';
                }

                if ( ! empty( $options['values'] ) ) {
                    foreach ( $options['values'] as $pvalue => $plabel ) {
                        $field .= '<option value="' . $pvalue . '" '. selected( $pvalue, $options['value'], false ) .'>' .
                            ( is_string( $plabel ) ? $plabel : $plabel['label'] )
                            . '</option>';
                    }
                }

                $field .= '</select>';

                break;

            default:
                $field = "<input type='" . $type . "' {$options_string}>";
        }

        $template = sprintf( $template, $label, $field );

        return $template;
    }

    /**
    *
    * Register admin dashboard pages
    */

    public function homey_register_admin_pages() {

        add_menu_page(
            esc_html__( 'Homey', 'homey-core' ),
            esc_html__( 'Homey', 'homey-core' ),
            'manage_options',
            'homey_dashboard',
            array( 'homey_Dashboard', 'render' ),
            '',
            '4'
        );

        add_submenu_page(
            'homey_dashboard',
            esc_html__( 'Dashboard', 'homey-core' ),
            esc_html__( 'Dashboard', 'homey-core' ),
            'manage_options',
            'homey_dashboard',
            array( 'homey_Dashboard', 'render' )
        );

        add_submenu_page(
            'homey_dashboard',
            esc_html__( 'Fields builder', 'homey-core' ),
            esc_html__( 'Fields builder', 'homey-core' ),
            'manage_options',
            'homey_fbuilder',
            array( 'homey_Fields_Builder', 'render' )
        );

        add_submenu_page(
            'homey_dashboard',
            esc_html__( 'Currencies', 'homey-core' ),
            esc_html__( 'Currencies', 'homey-core' ),
            'manage_options',
            'fcc_currencies',
            array( 'FCC_Currencies', 'render' )
        );

        add_submenu_page(
            'homey_dashboard',
            esc_html__( 'API Settings', 'homey-core' ),
            '',
            'manage_options',
            'fcc_api_settings',
            array( 'FCC_API_Settings', 'render' )
        );

        add_submenu_page(
            'homey_dashboard',
            esc_html__( 'Permalinks', 'homey-core' ),
            esc_html__( 'Permalinks', 'homey-core' ),
            'manage_options',
            'homey_permalinks',
            array( 'homey_Permalinks', 'render' )
        );

    }
    

    public static function homey_plugin_activation() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $table_name         = $wpdb->prefix . 'homey_fields_builder';
        $charset_collate    = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
          id int(10) NOT NULL AUTO_INCREMENT,
          label varchar(255) NOT NULL,
          field_id varchar(255) NOT NULL,
          type varchar(25) NOT NULL,
          options text NULL,
          fvalues text NULL,
          is_search varchar(25) NULL,
          search_compare varchar(25) NULL,
          placeholder varchar(255) NULL,
          order_id int(255) NOT NULL DEFAULT 0,
          PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta( $sql );

        $table_name         = $wpdb->prefix . 'homey_map';
        $charset_collate    = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
          id int(10) NOT NULL AUTO_INCREMENT,
          latitude varchar(255) NOT NULL,
          longitude varchar(255) NOT NULL,
          listing_id varchar(25) NOT NULL,
          PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta( $sql );

        $table_name         = $wpdb->prefix . 'homey_threads';
        $charset_collate    = $wpdb->get_charset_collate();
        $sql                = "CREATE TABLE $table_name (
           id mediumint(9) NOT NULL AUTO_INCREMENT,
           sender_id mediumint(9) NOT NULL,
           receiver_id mediumint(9) NOT NULL,
           listing_id mediumint(9) NOT NULL,
           seen mediumint(9) NOT NULL,
           receiver_delete mediumint(9) NOT NULL DEFAULT '0',
           sender_delete mediumint(9) NOT NULL DEFAULT '0',
           time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
           UNIQUE KEY id (id)
       ) $charset_collate;";

        dbDelta( $sql );

        $table_name         = $wpdb->prefix . 'homey_thread_messages';
        $charset_collate    = $wpdb->get_charset_collate();
        $sql                = "CREATE TABLE $table_name (
           id mediumint(9) NOT NULL AUTO_INCREMENT,
           created_by mediumint(9) NOT NULL,
           thread_id mediumint(9) NOT NULL,
           message longtext DEFAULT '' NOT NULL,
           attachments longtext DEFAULT '' NOT NULL,
           receiver_delete mediumint(9) NOT NULL DEFAULT '0',
           sender_delete mediumint(9) NOT NULL DEFAULT '0',
           time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
           UNIQUE KEY id (id)
       ) $charset_collate;";

        dbDelta( $sql );

        $table_name         = $wpdb->prefix . 'homey_earnings';
        $charset_collate    = $wpdb->get_charset_collate();
        $sql                = "CREATE TABLE $table_name (
           id bigint(20) NOT NULL AUTO_INCREMENT,
           user_id bigint(20) NOT NULL,
           guest_id bigint(20) NOT NULL,
           listing_id bigint(20) NOT NULL,
           reservation_id bigint(20) NOT NULL,
           services_fee varchar(255) NOT NULL DEFAULT '0',
           host_fee varchar(255) NOT NULL DEFAULT '0',
           upfront_payment varchar(255) NOT NULL DEFAULT '0',
           payment_due varchar(255) NOT NULL DEFAULT '0',
           net_earnings varchar(255) NOT NULL DEFAULT '0',
           total_amount varchar(255) NOT NULL DEFAULT '0',
           security_deposit varchar(255) NOT NULL DEFAULT '0',
           chargeable_amount varchar(255) NOT NULL DEFAULT '0',
           host_fee_percent bigint(20) NOT NULL DEFAULT '0',
           time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
           UNIQUE KEY id (id)
       ) $charset_collate;";

        dbDelta( $sql );

        $table_name         = $wpdb->prefix . 'homey_payouts';
        $charset_collate    = $wpdb->get_charset_collate();
        $sql                = "CREATE TABLE $table_name (
           payout_id bigint(20) NOT NULL AUTO_INCREMENT,
           user_id bigint(20) NOT NULL,
           total_amount varchar(255) NOT NULL DEFAULT '0',
           transfer_fee varchar(255) NOT NULL DEFAULT '0',
           payout_method varchar(255) NULL DEFAULT '',
           payout_method_data varchar(255) NULL DEFAULT '',
           payout_beneficiary varchar(255) NULL DEFAULT '',
           payout_status bigint(20) NOT NULL DEFAULT '1',
           action varchar(55) NULL DEFAULT 'host_payout',
           note text NULL DEFAULT '',
           date_requested TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
           date_processed datetime DEFAULT '0000-00-00 00:00' NOT NULL,
           UNIQUE KEY id (payout_id)
       ) $charset_collate;";

        dbDelta( $sql );

        if ( ! wp_next_scheduled( 'homey_featured_listing_expire_check' ) ) {
          wp_schedule_event( time(), 'hourly', 'homey_featured_listing_expire_check' );
        }

        if ( ! wp_next_scheduled( 'homey_reservation_declined' ) ) {
          wp_schedule_event( time(), 'hourly', 'homey_reservation_declined' );
        }

        if ( ! wp_next_scheduled( 'homey_ical_sync' ) ) {
          wp_schedule_event( time(), 'hourlyfour', 'homey_ical_sync' );
        }

    }

    public static function homey_plugin_deactivate() {

        global $wpdb;
        wp_clear_scheduled_hook('homey_featured_listing_expire_check');
        wp_clear_scheduled_hook('homey_reservation_declined');
        wp_clear_scheduled_hook('homey_ical_sync');


    }

    /**
     * Comma separated taxonomy terms with admin side links
     *
     * @return boolean | term
     */
    public static function admin_taxonomy_terms( $post_id, $taxonomy, $post_type ) {

        $terms = get_the_terms( $post_id, $taxonomy );

        if ( ! empty ( $terms ) ) {
            $out = array();
            /* Loop through each term, linking to the 'edit posts' page for the specific term. */
            foreach ( $terms as $term ) {
                $out[] = sprintf( '<a href="%s">%s</a>',
                    esc_url( add_query_arg( array( 'post_type' => $post_type, $taxonomy => $term->slug ), 'edit.php' ) ),
                    esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $taxonomy, 'display' ) )
                );
            }
            /* Join the terms, separating them with a comma. */
            return join( ', ', $out );
        }

        return false;
    }

    public function redirect($plugin) {
        /*if ( $plugin == HOMEY_PLUGIN_BASENAME ) {
            wp_redirect( 'admin.php?page=homey_dashboard' );
            wp_die();
        }*/
    }

}
?>