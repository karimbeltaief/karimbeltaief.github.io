<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Homey_Listing_Post_Type {
    /**
     * Initialize custom post type
     *
     * @access public
     * @return void
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'definition' ) );
      
        add_action( 'init', array( __CLASS__, 'listing_type' ) );
        add_action( 'init', array( __CLASS__, 'room_type' ) );
        add_action( 'init', array( __CLASS__, 'listing_amenities' ) );
        add_action( 'init', array( __CLASS__, 'listing_facilities' ) );

        add_action( 'init', array( __CLASS__, 'listing_country' ) );
        add_action( 'init', array( __CLASS__, 'listing_state' ) );
        add_action( 'init', array( __CLASS__, 'listing_city' ) );
        add_action( 'init', array( __CLASS__, 'listing_area' ) );

        add_action( 'save_post', array( __CLASS__, 'save_listing_post_type' ), 10, 3 );

        add_filter( 'manage_edit-listing_columns', array( __CLASS__, 'custom_columns' ) );
        add_action( 'manage_pages_custom_column', array( __CLASS__, 'custom_columns_manage' ) );

        add_filter('manage_edit-listing_area_columns', array( __CLASS__, 'listingArea_columns_head' ));
        add_filter('manage_listing_area_custom_column',array( __CLASS__, 'listingArea_columns_content_taxonomy' ), 10, 3);

        add_filter('manage_edit-listing_city_columns', array( __CLASS__, 'listingCity_columns_head' ));
        add_filter('manage_listing_city_custom_column',array( __CLASS__, 'listingCity_columns_content_taxonomy' ), 10, 3);

        add_filter('manage_edit-listing_state_columns', array( __CLASS__, 'listingState_columns_head' ));
        add_filter('manage_listing_state_custom_column',array( __CLASS__, 'listingState_columns_content_taxonomy' ), 10, 3);

        add_action('admin_init', array( __CLASS__, 'homey_approve_listing' ));
        add_action('admin_init', array( __CLASS__, 'homey_expire_listing' ));
    
    }

    /**
     * Custom post type definition
     *
     * @access public
     * @return void
     */
    public static function definition() {
        $labels = array(
            'name' => esc_html__( 'Listings','homey-core'),
            'singular_name' => esc_html__( 'Listing','homey-core' ),
            'add_new' => esc_html__('Add New','homey-core'),
            'add_new_item' => esc_html__('Add New','homey-core'),
            'edit_item' => esc_html__('Edit Listing','homey-core'),
            'new_item' => esc_html__('New Listing','homey-core'),
            'view_item' => esc_html__('View Listing','homey-core'),
            'search_items' => esc_html__('Search Listing','homey-core'),
            'not_found' =>  esc_html__('No Listing found','homey-core'),
            'not_found_in_trash' => esc_html__('No Listing found in Trash','homey-core'),
            'parent_item_colon' => ''
          );

        $labels = apply_filters( 'homey_listing_post_type_labels', $labels );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'has_archive' => true,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
            'hierarchical' => true,
            'menu_icon' => 'dashicons-location',
            'menu_position' => 20,
            'can_export' => true,
            'show_in_rest'       => true,
            'rest_base'          => 'listings',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'supports' => array('title','editor','thumbnail','revisions','author','page-attributes','excerpt'),

             // The rewrite handles the URL structure.
            'rewrite' => array(
                  'slug'       => homey_get_listing_rewrite_slug(),
                  'with_front' => false,
                  'pages'      => true,
                  'feeds'      => true,
                  'ep_mask'    => EP_PERMALINK,
            ),
        );

        $args = apply_filters( 'homey_listing_post_type_args', $args );

        register_post_type('listing',$args);
    }


    public static function listing_type() {

        $type_labels = array(
                    'name'              => esc_html__('Listing Type','homey-core'),
                    'add_new_item'      => esc_html__('Add New','homey-core'),
                    'new_item_name'     => esc_html__('New Listing Type','homey-core')
        );
        $type_labels = apply_filters( 'listing_type_labels', $type_labels );

        register_taxonomy('listing_type', 'listing', array(
                'labels' => $type_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'listing_type',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'       => array( 'slug' => homey_get_listing_type_rewrite_slug() )
            )
        );
    }

    public static function room_type() {

        $room_type_labels = array(
                    'name'              => esc_html__('Room Type','homey-core'),
                    'add_new_item'      => esc_html__('Add New','homey-core'),
                    'new_item_name'     => esc_html__('New Room Type','homey-core')
        );
        $room_type_labels = apply_filters( 'room_type_labels', $room_type_labels );

        register_taxonomy('room_type', 'listing', array(
                'labels' => $room_type_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'room_type',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'       => array( 'slug' => homey_get_room_type_rewrite_slug() )
            )
        );
    }

    public static function listing_amenities() {

        $listing_amenity_labels = array(
                    'name'              => esc_html__('Amenities','homey-core'),
                    'add_new_item'      => esc_html__('Add New','homey-core'),
                    'new_item_name'     => esc_html__('New Amenity','homey-core')
        );
        $listing_amenity_labels = apply_filters( 'listing_amenity_labels', $listing_amenity_labels );

        register_taxonomy('listing_amenity', 'listing', array(
                'labels' => $listing_amenity_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'amenities',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'       => array( 'slug' => homey_get_amenity_rewrite_slug() )
            )
        );
    }

    public static function listing_facilities() {

        $listing_facility_labels = array(
                    'name'              => esc_html__('Facilities','homey-core'),
                    'add_new_item'      => esc_html__('Add New','homey-core'),
                    'new_item_name'     => esc_html__('New Facility','homey-core')
        );
        $listing_facility_labels = apply_filters( 'listing_facility_labels', $listing_facility_labels );

        register_taxonomy('listing_facility', 'listing', array(
                'labels' => $listing_facility_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'facilities',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'       => array( 'slug' => homey_get_facility_rewrite_slug() )
            )
        );
    }

    public static function listing_country() {

        $listing_country_labels = array(
                    'name'              => esc_html__('Country','homey-core'),
                    'add_new_item'      => esc_html__('Add New','homey-core'),
                    'new_item_name'     => esc_html__('New Country','homey-core')
        );
        $listing_country_labels = apply_filters( 'listing_country_labels', $listing_country_labels );

        register_taxonomy('listing_country', 'listing', array(
                'labels' => $listing_country_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'listing_countries',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'       => array( 'slug' => homey_get_country_rewrite_slug() )
            )
        );
    }

    public static function listing_state() {

        $listing_state_labels = array(
                    'name'              => esc_html__('State','homey-core'),
                    'add_new_item'      => esc_html__('Add New','homey-core'),
                    'new_item_name'     => esc_html__('New State','homey-core')
        );
        $listing_state_labels = apply_filters( 'listing_state_labels', $listing_state_labels );

        register_taxonomy('listing_state', 'listing', array(
                'labels' => $listing_state_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'listing_states',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'       => array( 'slug' => homey_get_state_rewrite_slug() )
            )
        );
    }

    public static function listing_city() {

        $listing_city_labels = array(
                    'name'              => esc_html__('City','homey-core'),
                    'add_new_item'      => esc_html__('Add New','homey-core'),
                    'new_item_name'     => esc_html__('New City','homey-core')
        );
        $listing_city_labels = apply_filters( 'listing_city_labels', $listing_city_labels );

        register_taxonomy('listing_city', 'listing', array(
                'labels' => $listing_city_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'listing_cities',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'       => array( 'slug' => homey_get_city_rewrite_slug() )
            )
        );
    }

    public static function listing_area() {

        $listing_area_labels = array(
                    'name'              => esc_html__('Area','homey-core'),
                    'add_new_item'      => esc_html__('Add New','homey-core'),
                    'new_item_name'     => esc_html__('New area','homey-core')
        );
        $listing_area_labels = apply_filters( 'listing_area_labels', $listing_area_labels );

        register_taxonomy('listing_area', 'listing', array(
                'labels' => $listing_area_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'listing_areas',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'       => array( 'slug' => homey_get_area_rewrite_slug() )
            )
        );
    }

    /**
     * Update post meta associated info when post updated
     *
     * @access public
     * @return
     */
    public static function save_listing_post_type($post_id, $post, $update) {

        if(isset($_POST) && $_POST && !defined( 'DOING_AJAX' )) {
            // If this is just a revision, don't send the email.
            if ( wp_is_post_revision( $post_id ) )
            return;

            if (!is_object($post) || !isset($post->post_type)) {
                return;
            }

            $checkPost = get_post($post_id);
            

            $slug = 'listing';
            // If this isn't a 'book' post, don't update it.
            if ($slug != $post->post_type) {
                return;
            }

            $listing_total_rating = get_post_meta( $post_id, 'listing_total_rating', true );
            if( $listing_total_rating === '') {
                update_post_meta($post_id, 'listing_total_rating', '0');
            }

            $lat_long = get_post_meta( $post_id, 'homey_listing_location', true );
            if( isset($lat_long) && !empty($lat_long)) {
                $lat_long = explode(',', $lat_long);
                $lat = $lat_long[0];
                $long = $lat_long[1];

                update_post_meta($post_id, 'homey_geolocation_lat', $lat);
                update_post_meta($post_id, 'homey_geolocation_long', $long);

                if( $checkPost->post_modified_gmt == $checkPost->post_date_gmt ){
                    self::insert_lat_long($lat, $long, $post_id);
                }else{
                    self::update_lat_long($lat, $long, $post_id);
                }

            }
        }
    }

    public static function insert_lat_long($lat, $long, $list_id) {
        global $wpdb;
        $table_name  = $wpdb->prefix . 'homey_map';

        $wpdb->insert( 
            $table_name, 
            array( 
                'latitude' => $lat,
                'longitude' => $long, 
                'listing_id' => $list_id 
            ), 
            array( 
                '%s',
                '%s', 
                '%d' 
            ) 
        );
        return true;
    }

    public static function update_lat_long($lat, $long, $list_id) {
        
        global $wpdb;
        $table_name  = $wpdb->prefix . 'homey_map';

        $myRow = $wpdb->get_row( "SELECT * FROM $table_name WHERE listing_id = $list_id" );

        if ( null !== $myRow ) {
          $wpdb->update( 
                $table_name, 
                array( 
                    'latitude' => $lat,  // string
                    'longitude' => $long   // integer (number) 
                ), 
                array( 'listing_id' => $list_id ), 
                array( 
                    '%s',   // value1
                    '%s'    // value2
                ), 
                array( '%d' ) 
            );
        } else {
          self::insert_lat_long($lat, $long, $list_id);
        }

        return true;
    }

    /**
     * Custom admin columns for post type
     *
     * @access public
     * @return array
     */
    public static function custom_columns() {

        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "title" => esc_html__( 'Title','homey-core' ),
            "thumbnail" => esc_html__( 'Thumbnail','homey-core' ),
            "type" => esc_html__('Type','homey-core'),
            "price" => esc_html__('Price','homey-core'), 
            "featured" => esc_html__( 'Featured','homey-core' ),
            //"status" => esc_html__('Status','homey-core'),
            //"listing_posted" => esc_html__( 'Posted','homey-core' ),
            //"listing_expiry" => esc_html__( 'Expires','homey-core' ),
            //"original_id" => esc_html__( 'ID','homey-core' ),
            "listing_id" => esc_html__( 'Listing ID','homey-core' ),
            "date" => esc_html__( 'Date','homey-core' ),
            "homey_actions" => esc_html__( 'Actions','homey-core' ),
        );

        $columns = apply_filters( 'homey_custom_post_listing_columns', $columns );

        return $columns;
        
    }

    /**
     * Custom admin columns for area taxonomy
     *
     * @access public
     * @return array
     */
    
    public static function listingArea_columns_head() {

        $new_columns = array(
            'cb'            => '<input type="checkbox" />',
            'name'          => esc_html__('Name','homey-core'),
            'city'          => esc_html__('City','homey-core'),
            'header_icon'   => '',
            'slug'          => esc_html__('Slug','homey-core'),
            'posts'         => esc_html__('Posts','homey-core')
        );


        return $new_columns;
    }


    public static function listingArea_columns_content_taxonomy($out, $column_name, $term_id) {
        if ($column_name == 'city') {
            $term_meta= get_option( "_homey_listing_area_$term_id");
            $term = get_term_by('slug', $term_meta['parent_city'], 'listing_city'); 
            if(!empty($term)) {
                print stripslashes( $term->name );
            }
            return;
        }
    }

    /**
     * Custom admin columns for city taxonomy
     *
     * @access public
     * @return array
     */
    public static function listingCity_columns_head() {

        $new_columns = array(
            'cb'            => '<input type="checkbox" />',
            'name'          => esc_html__('Name','homey-core'),
            'county_state'          => esc_html__('County/State','homey-core'),
            'header_icon'   => '',
            'slug'          => esc_html__('Slug','homey-core'),
            'posts'         => esc_html__('Posts','homey-core')
        );


        return $new_columns;
    }


    public static function listingCity_columns_content_taxonomy($out, $column_name, $term_id) {
        if ($column_name == 'county_state') {
            $term_meta= get_option( "_homey_listing_city_$term_id");
            $term = get_term_by('slug', $term_meta['parent_state'], 'listing_state'); 
            if(!empty($term)) {
                print stripslashes( $term->name );
            }
            return;
        }
    }



    /**
     * Custom admin columns for state taxonomy
     *
     * @access public
     * @return array
     */
    public static function listingState_columns_head() {

        $new_columns = array(
            'cb'            => '<input type="checkbox" />',
            'name'          => esc_html__('Name','homey-core'),
            'country'       => esc_html__('Country','homey-core'),
            'header_icon'   => '',
            'slug'          => esc_html__('Slug','homey-core'),
            'posts'         => esc_html__('Posts','homey-core')
        );


        return $new_columns;
    }


    public static function listingState_columns_content_taxonomy($out, $column_name, $term_id) {
        if ($column_name == 'country') {
            $term_meta = get_option( "_homey_listing_state_$term_id");
            $term = get_term_by('slug', $term_meta['parent_country'], 'listing_country'); 
            if(!empty($term)) {
                print stripslashes( $term->name );
            }
            return;
        }
    }

    /**
     * Custom admin columns implementation
     *
     * @access public
     * @param string $column
     * @return array
     */
    public static function custom_columns_manage( $column ) {
        global $post;
        $prefix = 'homey_';
        switch ($column)
        {
            case 'thumbnail':
                if ( has_post_thumbnail() ) {
                    the_post_thumbnail( 'thumbnail', array(
                        'class'     => 'attachment-thumbnail attachment-thumbnail-small',
                    ) );
                } else {
                    echo '-';
                }
                break;

            case 'listing_id':
                echo get_the_ID();
                break;
            case 'featured':
                $featured = get_post_meta($post->ID, $prefix.'featured',true);
                if($featured != 1 ) {
                    _e( 'No', 'homey-core' );
                } else {
                    _e( 'Yes', 'homey-core' );
                }
                break;
            case 'address':
                $address = get_post_meta($post->ID, $prefix.'listing_address',true);
                if(!empty($address)){
                    echo esc_attr( $address );
                }
                else{
                    _e('No Address Provided!','homey-core');
                }
                break;
            case 'type':
                echo Homey::admin_taxonomy_terms ( $post->ID, 'listing_type', 'listing' );
                break;
            case 'status':
                
                break;
            case 'price':
                $price = get_post_meta($post->ID, $prefix.'night_price',true);
                if(!empty($price)){
                    echo homey_formatted_price( $price, true );
                }
                else{
                    echo '-';
                }
                break;
            case 'bedrooms':
                $bed = get_post_meta($post->ID, $prefix.'listing_bedrooms',true);
                if(!empty($bed)){
                    echo esc_attr( $bed );
                }
                else{
                    _e('NA','homey-core');
                }
                break;
            case 'baths':
                $bath = get_post_meta($post->ID, $prefix.'baths',true);
                if(!empty($bath)){
                    echo esc_attr( $bath );
                }
                else{
                    _e('NA','homey-core');
                }
                break;
            case 'guests':
                $guests = get_post_meta($post->ID, $prefix.'guests',true);
                if(!empty($guests)){
                    echo esc_attr( $guests );
                }
                else{
                    _e('NA','homey-core');
                }
                break;
            case 'homey_actions':
                echo '<div class="actions">';
                $admin_actions = apply_filters( 'post_row_actions', array(), $post );

                $user = wp_get_current_user();

                if ( in_array( $post->post_status, array( 'pending' ) ) && in_array( 'administrator', (array) $user->roles ) ) {
                    $admin_actions['approve']   = array(
                        'action'  => 'approve',
                        'name'    => esc_html__( 'Approve', 'homey-core' ),
                        'url'     =>  wp_nonce_url( add_query_arg( 'approve_listing', $post->ID ), 'approve_listing' )
                    );
                }

                $admin_actions = apply_filters( 'homey_admin_actions', $admin_actions, $post );

                foreach ( $admin_actions as $action ) {
                    if ( is_array( $action ) ) {
                        printf( '<a class="button button-icon tips icon-%1$s" href="%2$s" data-tip="%3$s">%4$s</a>', $action['action'], esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_html( $action['name'] ) );
                    } else {
                        //echo str_replace( 'class="', 'class="button ', $action );
                    }
                }

                echo '</div>';

                break;
                case "listing_posted" :
                    echo '<p>' . date_i18n( get_option('date_format').' '.get_option('time_format'), strtotime( $post->post_date ) ) . '</p>';
                    echo '<p>'.( empty( $post->post_author ) ? esc_html__( 'by a guest', 'homey-core' ) : sprintf( esc_html__( 'by %s', 'homey-core' ), '<a href="' . esc_url( add_query_arg( 'author', $post->post_author ) ) . '">' . get_the_author() . '</a>' ) ) . '</p>';
                    break;
            case "listing_expiry" :
                if( homey_user_role_by_post_id($post->ID) != 'administrator' && get_post_status ( $post->ID ) == 'publish' ) {
                    homey_listing_expire();

                }
                break;
        }
    }

    public static function homey_approve_listing()
    {
        if (!empty($_GET['approve_listing']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'approve_listing') && current_user_can('publish_post', $_GET['approve_listing'])) {
            $post_id = absint($_GET['approve_listing']);
            $listing_data = array(
                'ID' => $post_id,
                'post_status' => 'publish'
            );
            wp_update_post($listing_data);

            $author_id = get_post_field ('post_author', $post_id);
            $user           =   get_user_by('id', $author_id );
            $user_email     =   $user->user_email;

            $args = array(
                'listing_title' => get_the_title($post_id),
                'listing_url' => get_permalink($post_id)
            );
            //homey_email_type( $user_email,'listing_approved', $args );

            wp_redirect(remove_query_arg('approve_listing', add_query_arg('approve_listing', $post_id, admin_url('edit.php?post_type=listing'))));
            exit;
        }
    }

    public static function homey_expire_listing() {

        if (!empty($_GET['expire_listing']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'expire_listing') && current_user_can('publish_post', $_GET['expire_listing'])) {
            $post_id = absint($_GET['expire_listing']);
            $listing_data = array(
                'ID' => $post_id,
                'post_status' => 'expired'
            );
            wp_update_post($listing_data);

            $author_id = get_post_field ('post_author', $post_id);
            $user           =   get_user_by('id', $author_id );
            $user_email     =   $user->user_email;

            $args = array(
                'listing_title' => get_the_title($post_id),
                'listing_url' => get_permalink($post_id)
            );
            //homey_email_type( $user_email,'listing_expired', $args );

            wp_redirect(remove_query_arg('expire_listing', add_query_arg('expire_listing', $post_id, admin_url('edit.php?post_type=listing'))));
            exit;
        }
    }


}