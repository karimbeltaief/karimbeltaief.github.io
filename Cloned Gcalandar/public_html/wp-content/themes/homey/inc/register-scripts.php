<?php
/**
 * Enqueue scripts and styles.
 */
if( !function_exists('homey_scripts') ) {
  function homey_scripts()
  {
    global $paged, $post, $current_user;
    wp_get_current_user();
    $userID = $current_user->ID;
    $homey_local = homey_get_localization();
    $header_map_selected_city = $homey_allow_additional_guests = $login_redirect = $allowed_guests = $is_singular_listing = $booking_start_hour = $booking_end_hour = $homey_min_book_days = '';
    $is_listing_detail = 'no';
    $booked_hours_array = $pending_hours_array = array();
    
    $after_login_redirect = homey_option('login_redirect');
    $login_redirect = homey_after_login_redirect_page();
    
    $map_api_key = homey_option('map_api_key');
    $menu_sticky = homey_option('menu-sticky');
    $search_position = homey_option('search_position');
    $geo_country_limit = homey_option('geo_country_limit');
    $geocomplete_country = homey_option('geocomplete_country');
    
    $homey_booking_type = homey_booking_type();
    
    $replytocom = isset($_GET['replytocom']) ? $_GET['replytocom'] : '';
    
    if (!is_404() && !is_search() && !is_tax() && !is_author()) {
      
      $header_map_selected_city = get_post_meta($post->ID, 'homey_map_city', false);
    }
    
    $homey_current_lang = get_locale();
    $homey_current_lang = explode('_', $homey_current_lang);
    
    
    $edit_listing_id = isset($_GET['edit_listing']) ? $_GET['edit_listing'] : '';
    $edit_listing_page = homey_get_template_link_2('template/dashboard-submission.php');
    $edit_listing_calendar = add_query_arg( array(
      'edit_listing' => $edit_listing_id,
      'tab' => 'calendar'
    ), $edit_listing_page );
    
    $edit_listing_pricing = add_query_arg( array(
      'edit_listing' => $edit_listing_id,
      'tab' => 'pricing'
    ), $edit_listing_page );
    
    if(is_singular('listing')) {
      
      $homey_allow_additional_guests = get_post_meta($post->ID, 'homey_allow_additional_guests', true);
      $allowed_guests = get_post_meta($post->ID, 'homey_guests', true);
      $is_singular_listing = 'yes';
      
      $booking_start_hour = get_post_meta($post->ID, 'homey_start_hour',true );
      $booking_end_hour = get_post_meta($post->ID, 'homey_end_hour',true );
      $homey_min_book_days = get_post_meta($post->ID, 'homey_min_book_days',true );
      $booked_hours_array = homey_get_booked_hours_slots($post->ID);
      $pending_hours_array = homey_get_pending_hours_slots($post->ID);
      
      if(empty($booking_start_hour)) {
        $booking_start_hour = '01:00';
      }
      
      if(empty($booking_end_hour)) {
        $booking_end_hour = '24:00';
      }
      
      $is_listing_detail = 'yes';
      
    }
    
    $homey_logged_in = 'yes';
    if (!is_user_logged_in()) {
      $homey_logged_in = 'no';
    }
    
    $markerPricePins = homey_option('markerPricePins');
    if(isset($_GET['marker']) && $_GET['marker'] == 'pricePins') {
      $markerPricePins = 'yes';
    }
    
    $protocol = is_ssl() ? 'https' : 'http';
    
    if (is_rtl()) {
      $homey_rtl = "yes";
    } else {
      $homey_rtl = "no";
    }
    
    $enable_reCaptcha = homey_option('enable_reCaptcha');
    $recaptha_site_key = homey_option('recaptha_site_key');
    $recaptha_secret_key = homey_option('recaptha_secret_key');
    
    //Logos
    $simple_logo = homey_option('custom_logo', '', 'url');
    $retina_logo = homey_option('retina_logo', '', 'url');
    $mobile_logo = homey_option('mobile_logo', '', 'url');
    $mobile_retina_logo = homey_option('mobile_retina_logo', '', 'url');
    $retina_logo_mobile = homey_option('mobile_retina_logo', '', 'url');
    $custom_logo_mobile_splash = homey_option('custom_logo_mobile_splash', '', 'url');
    $retina_logo_mobile_splash = homey_option('retina_logo_mobile_splash', '', 'url');
    $custom_logo_splash = homey_option('custom_logo_splash', '', 'url');
    $retina_logo_splash = homey_option('retina_logo_splash', '', 'url');
    
    $map_cluster = homey_option('map_cluster', '', 'url');
    if (!empty($map_cluster)) {
      $clusterIcon = $map_cluster;
    } else {
      $clusterIcon = get_template_directory_uri() . '/images/cluster-icon.png';
    }
    
    $minify_css = homey_option('minify_css');
    $css_minify_prefix = '';
    if ($minify_css != 0) {
      $css_minify_prefix = '.min';
    }
    
    $minify_js = homey_option('minify_js');
    $js_minify_prefix = '';
    if ($minify_js != 0) {
      $js_minify_prefix = '.min';
    }
    
    
    /* Register Styles
     * ----------------------*/
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css', array(), '3.3.7', 'all');
    wp_enqueue_style('bootstrap-select', get_template_directory_uri() . '/css/bootstrap-select.css', array(), '1.7.2', 'all');
    wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/font-awesome.css', array(), '4.7.0', 'all');
    wp_enqueue_style('swipebox', get_template_directory_uri() . '/css/swipebox.min.css', array(), '1.3.0', 'all');
    wp_enqueue_style('slick', get_template_directory_uri() . '/css/slick.css', array(), '1.0.0', 'all');
    wp_enqueue_style('slick-theme', get_template_directory_uri() . '/css/slick-theme.css', array(), '1.0.0', 'all');
    wp_enqueue_style('jquery-ui', get_template_directory_uri() . '/css/jquery-ui.css', array(), '1.12.0', 'all');
    wp_enqueue_style('radio-checkbox', get_template_directory_uri() . '/css/radio-checkbox.css', array(), '1.0.0', 'all');
    
    
    if( (is_singular('listing') || is_page_template('template/dashboard-submission.php') ) && $homey_booking_type == 'per_hour') {
      wp_enqueue_style('fullcalendar-core', get_template_directory_uri() . '//js/fullcalendar/core/main.min.css', array(), '4.0.2', 'all');
      wp_enqueue_style('fullcalendar-daygrid', get_template_directory_uri() . '//js/fullcalendar/daygrid/main.min.css', array(), '4.0.2', 'all');
      wp_enqueue_style('fullcalendar-timegrid', get_template_directory_uri() . '//js/fullcalendar/timegrid/main.min.css', array(), '4.0.2', 'all');
    }
    
    if (is_rtl()) {
      wp_enqueue_style('homey-rtl', get_template_directory_uri() . '/css/rtl'.$css_minify_prefix.'.css', array(), HOMEY_THEME_VERSION, 'all');
      wp_enqueue_style('bootstrap-rtl.min', get_template_directory_uri() . '/css/bootstrap-rtl.min.css', array(), '3.3.4', 'all');
    } else {
      wp_enqueue_style('homey-main', get_template_directory_uri() . '/css/main'.$css_minify_prefix.'.css', array(), HOMEY_THEME_VERSION, 'all');
    }
    
    wp_enqueue_style('homey-styling-options', get_template_directory_uri() . '/css/styling-options'.$css_minify_prefix.'.css', array(), HOMEY_THEME_VERSION, 'all');
    
    
    if ($minify_css != 0) {
      wp_enqueue_style('homey-style', get_template_directory_uri() . '/style.min.css', array(), HOMEY_THEME_VERSION, 'all');
    } else {
      wp_enqueue_style('homey-style', get_stylesheet_uri(), array(), HOMEY_THEME_VERSION, 'all');
    }
    wp_enqueue_style('jquery.skedTape', get_template_directory_uri() . '/css/jquery.skedTape.css', array(), '1.0', 'all');
    
    /* Register Scripts
     * ----------------------*/
    wp_enqueue_script('moment', get_template_directory_uri() . '/js/moment.min.js', array('jquery'), '2.17.1', true);
    wp_enqueue_script('moment-timezone', get_template_directory_uri() . '/js/moment-timezone.js', array('jquery'), '', true);
    wp_enqueue_script('modernizr-custom', get_template_directory_uri() . '/js/modernizr.custom.js', array('jquery'), '3.2.0', true);
    wp_enqueue_script('bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '3.3.7', true);
    wp_enqueue_script('slick', get_template_directory_uri() . '/js/slick.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('jquery-swipebox', get_template_directory_uri() . '/js/jquery.swipebox.min.js', array('jquery'), '1.4.4', true);
    wp_enqueue_script('bootstrap-select', get_template_directory_uri() . '/js/bootstrap-select.min.js', array('jquery'), '1.12.4', true);
    wp_enqueue_script('bootstrap-slider', get_template_directory_uri() . '/js/bootstrap-slider.min.js', array('jquery'), '10.0.2', true);
    
    if( (is_singular('listing') || is_page_template('template/dashboard-submission.php') ) && $homey_booking_type == 'per_hour') {
      wp_enqueue_script('fullcalendar-core', get_template_directory_uri() . '/js/fullcalendar/core/main.min.js', array('jquery'), '4.0.2', true);
      
      wp_enqueue_script('fullcalendar-local-all', get_template_directory_uri() . '/js/fullcalendar/core/locales-all.min.js', array('jquery'), '4.0.2', true);
      
      wp_enqueue_script('fullcalendar-daygrid', get_template_directory_uri() . '/js/fullcalendar/daygrid/main.min.js', array('jquery'), '4.0.2', true);
      wp_enqueue_script('fullcalendar-timegrid', get_template_directory_uri() . '/js/fullcalendar/timegrid/main.min.js', array('jquery'), '4.0.2', true);
      wp_enqueue_script('interaction', get_template_directory_uri() . '/js/fullcalendar/interaction/main.min.js', array('jquery'), '4.0.2', true);
      wp_enqueue_script('timeline', get_template_directory_uri() . '/js/fullcalendar/timeline/main.min.js', array('jquery'), '4.2.0', true);
      wp_enqueue_script('resource-common', get_template_directory_uri() . '/js/fullcalendar/resource-common/main.min.js', array('jquery'), '4.2.0', true);
      wp_enqueue_script('resource-timeline', get_template_directory_uri() . '/js/fullcalendar/resource-timeline/main.min.js', array('jquery'), '4.2.0', true);
    }
    
    wp_enqueue_script('jquery.skedTape', get_template_directory_uri() . '/js/jquery.skedTape.js', array('jquery'), '1.0', true);
    wp_enqueue_script('mustache', get_template_directory_uri() . '/js/mustache.min.js', array(), '3.0', true);
    /**
     * Import react
     * Change react.development.js to react.production.js on prod
     */
    wp_enqueue_script('reactjs', 'https://unpkg.com/react@16/umd/react.development.js', array(), '16', TRUE);
    function add_react_attributes($tag, $handle){
      if($handle == 'reactjs'){
        return str_replace("src", "crossorigin src", $tag);
      }
      return $tag;
    }
    add_filter( 'script_loader_tag', "add_react_attributes", 10, 2 );
    wp_enqueue_script('reactjs-dom', 'https://unpkg.com/react-dom@16/umd/react-dom.development.js', array(), '16', TRUE);
    function add_react_dom_attributes($tag, $handle){
      if($handle == 'reactjs-dom'){
        return str_replace("src", "crossorigin src", $tag);
      }
      return $tag;
    }
    add_filter( 'script_loader_tag', "add_react_dom_attributes", 11, 2 );
    /**
     * Import babel
     */
    wp_enqueue_script('babeljs', 'https://unpkg.com/babel-standalone@6/babel.min.js', array(), '6', TRUE);
    /**
     * Import Google Chart
     */
    wp_enqueue_script('google-chart', 'https://www.gstatic.com/charts/loader.js', array(), NULL, TRUE);
    wp_enqueue_script( "notification", get_template_directory_uri() . '/js/oolnanotification/notification.js', array(), '1', TRUE);
    
    if(is_user_logged_in()){
      $user = wp_get_current_user();
      $roles = $user->roles;
      
      $role = $roles[0];
      wp_localize_script('notification', 'role', $roles);
    }
    
    /**
     * Import oolna-timeline
     */
    wp_enqueue_style( "components.css", get_template_directory_uri() . '/js/oolnatimeline/components.css', array(), '1', 'all');
    wp_enqueue_script('oolna-agenda', get_template_directory_uri() . '/js/oolnatimeline/agenda.js', array(), '1', TRUE);
    wp_enqueue_script('oolna-controls', get_template_directory_uri() . '/js/oolnatimeline/Controls.js', array(), '1', TRUE);
    wp_enqueue_script('oolna-weekselect', get_template_directory_uri() . '/js/oolnatimeline/weekselect.js', array(), '1', TRUE);
    wp_enqueue_script('oolna-timeline', get_template_directory_uri() . '/js/oolnatimeline/timeline.js', array(), '1', TRUE);
    wp_enqueue_script('oolna-calendar', get_template_directory_uri() . '/js/oolnatimeline/calendar.js', array(), '1', TRUE);
    function add_oolnatimeline_attributes($tag, $handle){
      $babels = [
        'oolna-calendar', 'oolna-timeline', 'oolna-weekselect', 'oolna-agenda', 'oolna-controls'
      ];
      if(in_array($handle, $babels)){
        return str_replace("text/javascript", "text/babel", $tag);
      }
      return $tag;
    }
    add_filter( 'script_loader_tag', "add_oolnatimeline_attributes", 11, 2 );
    
    wp_enqueue_script('parallax-background', get_template_directory_uri() . '/js/parallax-background.min.js', array('jquery'), '1.2', true);
    wp_enqueue_script('jquery-matchHeight', get_template_directory_uri() . '/js/jquery.matchHeight-min.js', array('jquery'), '0.7.2', true);
    wp_enqueue_script('jquery-vide', get_template_directory_uri() . '/js/jquery.vide.min.js', array('jquery'), '0.5.1', true);
    wp_enqueue_script('theia-sticky-sidebar', get_template_directory_uri() . '/js/theia-sticky-sidebar.js', array('jquery'), '0.5.1', true);
    
    wp_enqueue_script('jquery-effects-core');
    wp_enqueue_script('jquery-ui-datepicker');
    
    // Ajax Calls
    wp_enqueue_script('homey-ajax-calls', get_template_directory_uri() . '/js/homey-ajax'.$js_minify_prefix.'.js', array('jquery'), HOMEY_THEME_VERSION, true);
    wp_localize_script('homey-ajax-calls', 'HOMEY_ajax_vars',
      array(
        'admin_url' => get_admin_url(),
        'homey_is_rtl' => $homey_rtl,
        'redirect_type' => $after_login_redirect,
        'login_redirect' => $login_redirect,
        'login_loading' => esc_html__('Sending user info, please wait...', 'homey'),
        'direct_pay_text' => esc_html__('Processing, Please wait...', 'homey'),
        'user_id' => $userID,
        'is_singular_listing' => $is_singular_listing,
        'process_loader_refresh' => 'fa fa-spin fa-refresh',
        'process_loader_spinner' => 'fa fa-spin fa-spinner',
        'process_loader_circle' => 'fa fa-spin fa-circle-o-notch',
        'process_loader_cog' => 'fa fa-spin fa-cog',
        'success_icon' => 'fa fa-check',
        'add_compare' => $homey_local['add_compare'],
        'remove_compare' => $homey_local['remove_compare'],
        'compare_limit' => $homey_local['compare_limit'],
        'prev_text' => $homey_local['prev_text'],
        'next_text' => $homey_local['next_text'],
        'are_you_sure_text' => $homey_local['are_you_sure_text'],
        'delete_btn_text' => $homey_local['delete_btn'],
        'cancel_btn_text' => $homey_local['cancel_btn'],
        'paypal_connecting' => esc_html__('Connecting to paypal, Please wait... ', 'homey'),
        'currency_updating_msg' => esc_html__('Updating Currency, Please wait...', 'homey'),
        'agree_term_text' => $homey_local['agree_term_text'],
        'choose_gateway_text' => $homey_local['choose_gateway_text'],
        'homey_tansparent' => homey_is_transparent_logo(),
        'homey_is_transparent' => homey_is_transparent(),
        'homey_is_top_header' => homey_is_top_header(),
        'simple_logo' => $simple_logo,
        'retina_logo' => $retina_logo,
        'mobile_logo' => $mobile_logo,
        'retina_logo_mobile' => $retina_logo_mobile,
        'custom_logo_mobile_splash' => $custom_logo_mobile_splash,
        'retina_logo_mobile_splash' => $retina_logo_mobile_splash,
        'custom_logo_splash' => $custom_logo_splash,
        'retina_logo_splash' => $retina_logo_splash,
        'no_more_listings' => $homey_local['no_more_listings'],
        'allow_additional_guests' => $homey_allow_additional_guests,
        'allowed_guests_num' => $allowed_guests,
        'compare_url' => homey_get_template_link_2('template/template-compare.php'),
        'homey_reCaptcha' => $enable_reCaptcha,
        'calendar_link' => $edit_listing_calendar,
        'pricing_link' => $edit_listing_pricing,
        'search_position' => $search_position,
        'replytocom' => $replytocom,
        'homey_is_dashboard' => homey_is_dashboard(),
        'is_listing_detail' => $is_listing_detail,
        'homey_booking_type' => $homey_booking_type,
        'booked_hours_array' => json_encode($booked_hours_array),
        'pending_hours_array' => json_encode($pending_hours_array),
        'booking_start_hour' => $booking_start_hour,
        'booking_end_hour' => $booking_end_hour,
        'hc_reserved_label' => $homey_local['hc_reserved_label'],
        'hc_pending_label' => $homey_local['hc_pending_label'],
        'hc_hours_label' => $homey_local['hc_hours_label'],
        'hc_today_label' => $homey_local['hc_today_label'],
        'homey_timezone' => get_option('timezone_string'),
        'homey_current_lang' => $homey_current_lang,
        'homey_date_format' => homey_option('homey_date_format'),
        'geo_country_limit' => $geo_country_limit,
        'geocomplete_country' => $geocomplete_country,
        'homey_min_book_days' => $homey_min_book_days,
      
      )
    ); // end ajax calls
    
    wp_enqueue_script('homey-custom', get_template_directory_uri() . '/js/custom'.$js_minify_prefix.'.js', array('jquery'), HOMEY_THEME_VERSION, true);
    
    
    // Google Map API and Ajax Calls for map
    if (is_ssl()) {
      wp_enqueue_script('google-map', 'https://maps-api-ssl.google.com/maps/api/js?libraries=places&language=' . get_locale() . '&key='.esc_html($map_api_key).'', array('jquery'), '1.0', false);
    } else {
      wp_enqueue_script('google-map', 'http://maps.googleapis.com/maps/api/js?libraries=places&language=' . get_locale() . '&key='.esc_html($map_api_key).'', array('jquery'), '1.0', false);
    }
    
    if($markerPricePins == 'yes') {
      wp_enqueue_script('richmarker-compiled', get_template_directory_uri() . '/js/richmarker-compiled.js', array(), '1.0.0', true);
    }
    wp_enqueue_script('infobox-packed', get_template_directory_uri() . '/js/infobox_packed.js', array('jquery'), '1.1.19', false);
    
    
    $bedrooms_icon = homey_option('lgc_bedroom_icon');
    $bathroom_icon = homey_option('lgc_bathroom_icon');
    $guests_icon = homey_option('lgc_guests_icon');
    
    if(!empty($bedrooms_icon)) {
      $bedrooms_icon = '<i class="'.esc_attr($bedrooms_icon).'"></i>';
    }
    if(!empty($bathroom_icon)) {
      $bathroom_icon = '<i class="'.esc_attr($bathroom_icon).'"></i>';
    }
    if(!empty($guests_icon)) {
      $guests_icon = '<i class="'.esc_attr($guests_icon).'"></i>';
    }
    
    $arrive = isset($_GET['arrive']) ? $_GET['arrive'] : '';
    $depart = isset($_GET['depart']) ? $_GET['depart'] : '';
    $guests = isset($_GET['guest']) ? $_GET['guest'] : '';
    $pets = isset($_GET['pets']) ? $_GET['pets'] : '';
    $bedrooms = isset($_GET['bedrooms']) ? $_GET['bedrooms'] : '';
    $rooms = isset($_GET['rooms']) ? $_GET['rooms'] : '';
    $room_size = isset($_GET['room_size']) ? $_GET['room_size'] : '';
    $search_country = isset($_GET['search_country']) ? $_GET['search_country'] : '';
    $search_city = isset($_GET['search_city']) ? $_GET['search_city'] : '';
    $search_area = isset($_GET['search_area']) ? $_GET['search_area'] : '';
    $listing_type = isset($_GET['listing_type']) ? $_GET['listing_type'] : '';
    $min_price = isset($_GET['min-price']) ? $_GET['min-price'] : '';
    $max_price = isset($_GET['max-price']) ? $_GET['max-price'] : '';
    $area = isset($_GET['area']) ? $_GET['area'] : '';
    $amenity = isset($_GET['amenity']) ? $_GET['amenity'] : '';
    $facility = isset($_GET['facility']) ? $_GET['facility'] : '';
    $country = isset($_GET['country']) ? $_GET['country'] : '';
    $state = isset($_GET['state']) ? $_GET['state'] : '';
    $city = isset($_GET['city']) ? $_GET['city'] : '';
    $area = isset($_GET['area']) ? $_GET['area'] : '';
    $start_time = isset($_GET['start']) ? $_GET['start'] : '';
    $end_time = isset($_GET['end']) ? $_GET['end'] : '';
    
    
    
    $pin_cluster = homey_option('pin_cluster_enable');
    $pin_cluster_icon = homey_option('pin_cluster', false, 'url');
    $pin_cluster_zoom = homey_option('pin_cluster_zoom');
    wp_enqueue_script('homey-maps', get_template_directory_uri() . '/js/homey-maps'.$js_minify_prefix.'.js', array('jquery'), HOMEY_THEME_VERSION, true);
    wp_enqueue_script('markerclusterer-min', get_template_directory_uri() . '/js/markerclusterer.min.js', array('jquery'), '2.1.1', true);
    wp_localize_script('homey-maps', 'HOMEY_map_vars',
      array(
        'admin_url' => get_admin_url(),
        'user_id' => $userID,
        'homey_is_rtl' => $homey_rtl,
        'is_singular_listing' => $is_singular_listing,
        'header_map_city' => $header_map_selected_city,
        'markerPricePins' => $markerPricePins,
        'pin_cluster' => $pin_cluster,
        'pin_cluster_icon' => $pin_cluster_icon,
        'pin_cluster_zoom' => $pin_cluster_zoom,
        'infoboxClose' => get_template_directory_uri() . '/images/close.gif',
        'google_map_style' => homey_option('googlemap_stype'),
        'not_found' => esc_html__("We didn't find any results", 'homey'),
        'arrive' => $arrive,
        'depart' => $depart,
        'guests' => $guests,
        'pets' => $pets,
        'search_country' => $search_country,
        'search_city' => $search_city,
        'search_area' => $search_area,
        'listing_type' => $listing_type,
        'country' => $country,
        'state' => $state,
        'city' => $city,
        'area' => $area,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'min_price' => $min_price,
        'max_price' => $max_price,
        'bedrooms' => $bedrooms,
        'rooms' => $rooms,
        'room_size' => $room_size,
        'area' => $area,
        'amenity' => $amenity,
        'facility' => $facility,
        'bedrooms_icon' => $bedrooms_icon,
        'bathroom_icon' => $bathroom_icon,
        'guests_icon' => $guests_icon
      )
    ); // end ajax calls
    
    
    
    //Listing Submission
    if ( homey_is_dashboard() ) {
      
      $booked_hours_array2 = $pending_hours_array2 = $booking_start_hour2 = $booking_end_hour2 = array();
      
      $edit_listing_id = isset($_GET['edit_listing']) ? $_GET['edit_listing'] : '';
      
      if(!empty($edit_listing_id)) {
        $edit_listing_id = intval( trim( $edit_listing_id ) );
        
        $booking_start_hour2 = get_post_meta($edit_listing_id, 'homey_start_hour',true );
        $booking_end_hour2 = get_post_meta($edit_listing_id, 'homey_end_hour',true );
        $booked_hours_array2 = homey_get_booked_hours_slots($edit_listing_id);
        $pending_hours_array2 = homey_get_pending_hours_slots($edit_listing_id);
        
        if(empty($booking_start_hour2)) {
          $booking_start_hour2 = '01:00';
        }
        
        if(empty($booking_end_hour2)) {
          $booking_end_hour2 = '24:00';
        }
      }
      
      
      wp_enqueue_script('plupload');
      wp_enqueue_script('jquery-ui-sortable');
      
      wp_enqueue_script('jquery-validate-min', get_template_directory_uri() . '/js/jquery.validate.min.js', array('jquery'), '1.15.0', true);
      wp_enqueue_script('bootbox-min', get_template_directory_uri() . '/js/bootbox.min.js', array('jquery'), '4.4.0', true);
      
      wp_enqueue_script('homey-listing', get_template_directory_uri() . '/js/homey-listing.js', array('jquery', 'plupload', 'jquery-ui-sortable'), HOMEY_THEME_VERSION, true);
      
      $listing_data = array(
        'ajaxURL' => admin_url('admin-ajax.php'),
        'verify_nonce' => wp_create_nonce('verify_gallery_nonce'),
        'verify_file_type' => esc_html__('Valid file formats', 'homey'),
        'msg_digits' => esc_html__('Please enter only digits', 'homey'),
        'homey_is_rtl' => $homey_rtl,
        'max_prop_images' => '',
        'image_max_file_size' => '',
        'homey_logged_in' => $homey_logged_in,
        'process_loader_refresh' => 'fa fa-spin fa-refresh',
        'process_loader_spinner' => 'fa fa-spin fa-spinner',
        'process_loader_circle' => 'fa fa-spin fa-circle-o-notch',
        'process_loader_cog' => 'fa fa-spin fa-cog',
        'success_icon' => 'fa fa-check',
        'are_you_sure_text' => $homey_local['are_you_sure_text'],
        'delete_btn_text' => $homey_local['delete_btn'],
        'cancel_btn_text' => $homey_local['cancel_btn'],
        'login_loading' => esc_html__('Sending user info, please wait...', 'homey'),
        'processing_text' => esc_html__('Processing, Please wait...', 'homey'),
        'add_listing_msg' => esc_html__('Submitting, Please wait...', 'homey'),
        'both_required' => esc_html__('Both fields required.', 'homey'),
        'btn_save' => esc_html__('Save', 'homey'),
        'acc_bedroom_name' => homey_option('ad_acc_bedroom_name'),
        'acc_bedroom_name_plac' => homey_option('ad_acc_bedroom_name_plac'),
        'acc_guests' => homey_option('ad_acc_guests'),
        'acc_guests_plac' => homey_option('ad_acc_guests_plac'),
        'acc_no_of_beds' => homey_option('ad_acc_no_of_beds'),
        'acc_no_of_beds_plac' => homey_option('ad_acc_no_of_beds_plac'),
        'acc_bedroom_type' => homey_option('ad_acc_bedroom_type'),
        'acc_bedroom_type_plac' => homey_option('ad_acc_bedroom_type_plac'),
        'acc_btn_remove_room' => homey_option('ad_acc_btn_remove_room'),
        
        'service_name' => homey_option('ad_service_name'),
        'service_name_plac' => homey_option('ad_service_name_plac'),
        'service_price' => homey_option('ad_service_price'),
        'service_price_plac' => homey_option('ad_service_price_plac'),
        'service_des' => homey_option('ad_service_des'),
        'service_des_plac' => homey_option('ad_service_des_plac'),
        'btn_remove_service' => homey_option('ad_btn_remove_service'),
        'calendar_link' => $edit_listing_calendar,
        'pricing_link' => $edit_listing_pricing,
        'geo_coding' => esc_html__('Geocode was not successful for the following reason', 'homey'),
        'avail_label' => $homey_local['avail_label'],
        'unavail_label' => $homey_local['unavail_label'],
        'add_ical_feeds' => esc_html__('Please add feeds first.', 'homey'),
        'geo_country_limit' => $geo_country_limit,
        'geocomplete_country' => $geocomplete_country,
        'homey_booking_type' => $homey_booking_type,
        'booked_hours_array' => json_encode($booked_hours_array2),
        'pending_hours_array' => json_encode($pending_hours_array2),
        'booking_start_hour' => $booking_start_hour2,
        'booking_end_hour' => $booking_end_hour2,
        'hc_reserved_label' => $homey_local['hc_reserved_label'],
        'hc_pending_label' => $homey_local['hc_pending_label'],
        'hc_hours_label' => $homey_local['hc_hours_label'],
        'hc_today_label' => $homey_local['hc_today_label'],
        'homey_timezone' => get_option('timezone_string'),
        'homey_current_lang' => $homey_current_lang,
      );
      wp_localize_script('homey-listing', 'Homey_Listing', $listing_data);
    }
    
    // Edit profile template
    if (is_page_template('template/dashboard-profile.php') || homey_is_dashboard()) {
      wp_enqueue_script('plupload');
      
      wp_register_script('homey-profile', get_template_directory_uri() . '/js/homey-profile.js', array('jquery', 'plupload'), HOMEY_THEME_VERSION, true);
      $profile_data = array(
        'ajaxURL' => admin_url('admin-ajax.php'),
        'user_id' => $userID,
        'homey_upload_nonce' => wp_create_nonce('homey_upload_nonce'),
        'verify_file_type' => esc_html__('Valid file formats', 'homey'),
        'homey_site_url' => site_url(),
        'process_loader_refresh' => 'fa fa-spin fa-refresh',
        'process_loader_spinner' => 'fa fa-spin fa-spinner',
        'process_loader_circle' => 'fa fa-spin fa-circle-o-notch',
        'process_loader_cog' => 'fa fa-spin fa-cog',
        'success_icon' => 'fa fa-check',
        'processing_text' => esc_html__('Processing, Please wait...', 'homey'),
        'gdpr_agree_text' => esc_html__('Please Agree with GDPR', 'homey'),
        'sending_info' => esc_html__('Sending info', 'homey'),
      );
      wp_localize_script('homey-profile', 'homeyProfile', $profile_data);
      wp_enqueue_script('homey-profile');
    } // end edit profile
    
    
    if ($enable_reCaptcha != 0 && !empty($recaptha_site_key) && !empty($recaptha_secret_key)) {
      wp_enqueue_script('google-reCaptcha', 'https://www.google.com/recaptcha/api.js?onload=homeyReCaptchaLoad&hl=' . get_locale() . '&render=explicit', array('jquery'), HOMEY_THEME_VERSION, true);
      wp_enqueue_script('homey-reCaptcha', get_template_directory_uri() . '/js/homey-reCapthca.js', array('jquery', 'google-reCaptcha'), HOMEY_THEME_VERSION, true);
      
      $reCaptcha_data = array(
        'site_key' => $recaptha_site_key,
        'secret_key' => $recaptha_secret_key,
        'is_singular_listing' => $is_singular_listing,
        //'homey_show_captcha' => $homey_show_captcha,
        'homey_logged_in' => $homey_logged_in,
      
      );
      wp_localize_script('homey-reCaptcha', 'homey_reCaptcha', $reCaptcha_data);
    }
    
    
    if (is_singular('post') && comments_open() && get_option('thread_comments')) {
      wp_enqueue_script('comment-reply');
    }
    
    
    
    
  }
}
add_action( 'wp_enqueue_scripts', 'homey_scripts' );

if (is_admin() ){
  function homey_admin_scripts(){
    global $pagenow, $typenow;
    
    wp_enqueue_script('homey-admin-init', get_template_directory_uri() .'/js/admin/init.js', array('jquery'));
    wp_enqueue_style( 'homey-admin.css', get_template_directory_uri(). '/css/admin/admin.css', array(), HOMEY_THEME_VERSION, 'all' );
    
    
    if ( isset( $_GET['taxonomy'] ) && ( $_GET['taxonomy'] == 'listing_type' ) ) {
      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_script( 'homey_taxonomies', get_template_directory_uri().'/js/admin/metaboxes-taxonomies.js', array( 'jquery', 'wp-color-picker' ), 'homey' );
    }
    
    /*$homey_site_mode = homey_option('homey_site_mode');

    if($homey_site_mode == 'per_hour') {
        echo '<style type="text/css">
            .homey_hourly {
                display:none !important;
              }
        </style>';
    } elseif($homey_site_mode == 'per_day') {
        echo '<style type="text/css">
            .homey_daily {
                display:none !important;
              }
        </style>';
    }*/
    
    
  }
  add_action('admin_enqueue_scripts', 'homey_admin_scripts');
}


// Header custom JS
function homey_header_scripts(){
  
  $custom_js_header = homey_option('custom_js_header');
  
  if ( $custom_js_header != '' ){
    echo ''.$custom_js_header;
  }
  
}
if(!is_admin()){
  add_action('wp_head', 'homey_header_scripts');
}

// Footer custom JS
function homey_footer_scripts(){
  $custom_js_footer = homey_option('custom_js_footer');
  
  if ( $custom_js_footer != '' ){
    echo ''.$custom_js_footer;
  }
}
if(!is_admin()){
  add_action( 'wp_footer', 'homey_footer_scripts', 100 );
}