<?php
add_action( 'homey_ical_sync', 'homey_ical_sync_callback' );
if(!function_exists('homey_ical_sync_callback')) {
    function homey_ical_sync_callback() {
        $args = array(
            'post_type'         =>  'listing',
            'post_status'       =>  'published',
            'posts_per_page'    =>  -1,
            'meta_query' => array(           
                array(
                    'key'       => 'homey_ical_feeds_meta',
                    'value'     => '',
                    'compare'   => '!='
                )            
            )
        );

        $listings_qry = new WP_Query($args);
        if ($listings_qry->have_posts()){  
            while ($listings_qry->have_posts()): $listings_qry->the_post(); 

                $listing_id = get_the_id();
                homey_import_icalendar_feeds($listing_id);
                
            endwhile;
        }
    }
}

if(!function_exists('homey_check_for_taxonomy_plugin')) {
    function homey_check_for_taxonomy_plugin($tax_setting_name) {

        if(class_exists('homey_Taxonomies')) {
            if(homey_Taxonomies::get_setting($tax_setting_name) != 'disabled') {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}

/*-----------------------------------------------------------------------------------*/
// Get terms array
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_get_terms_array_elementor' ) ) {
    function homey_get_terms_array_elementor( $tax_name, &$terms_array ) {
        $tax_terms = get_terms( $tax_name, array(
            'hide_empty' => false,
        ) );
        homey_add_term_children_elementor( 0, $tax_terms, $terms_array );
    }
}


if ( ! function_exists( 'homey_add_term_children_elementor' ) ) :
    function homey_add_term_children_elementor( $parent_id, $tax_terms, &$terms_array, $prefix = '' ) {
        if ( ! empty( $tax_terms ) && ! is_wp_error( $tax_terms ) ) {
            foreach ( $tax_terms as $term ) {
                if ( $term->parent == $parent_id ) {
                    $terms_array[ $term->slug ] = $prefix . $term->name;
                    homey_add_term_children_elementor( $term->term_id, $tax_terms, $terms_array, $prefix . '- ' );
                }
            }
        }
    }
endif;

/*-----------------------------------------------------------------------------------*/
// Get terms array
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_get_terms_id_array2' ) ) {
    function homey_get_terms_id_array2( $tax_name, &$terms_array ) {
        $tax_terms = get_terms( $tax_name, array(
            'hide_empty' => false,
        ) );
        homey_add_term_id_children2( 0, $tax_terms, $terms_array );
    }
}


if ( ! function_exists( 'homey_add_term_id_children2' ) ) :
    function homey_add_term_id_children2( $parent_id, $tax_terms, &$terms_array, $prefix = '' ) {
        if ( ! empty( $tax_terms ) && ! is_wp_error( $tax_terms ) ) {
            foreach ( $tax_terms as $term ) {
                if ( $term->parent == $parent_id ) {
                    $terms_array[ $term->term_id ] = $prefix . $term->name;
                    homey_add_term_id_children2( $term->term_id, $tax_terms, $terms_array, $prefix . '- ' );
                }
            }
        }
    }
endif;

if(!function_exists('homey_check_post_types_plugin')) {
    function homey_check_post_types_plugin($post_type) {

        if(class_exists('homey_Post_Type')) {
            if(homey_Post_Type::get_setting($post_type) != 'disabled') {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}
if (!function_exists('homey_theme_activation')) {
    function homey_theme_activation() {
        $status = get_option( 'homey_activation' );
        if(empty($status) && $status != 'none'){
            update_option( 'homey_activation', 'none' );
        }
        ?>
        <div class="notice">
        <form action="" method="post">
            <h2 class="activation_title">Activate homey</h2>
            <p>To unlock all homey features please enter your purchase code below. To get your purchase code, login to ThemeForest, and go to Downloads section and, click on the green Download button next to homey and select “License certificate & purchase code” in any format. </p>
            <div id="title-wrap" class="input-text-wrap">
                <label id="api_key_prompt_text" class="prompt" for="api_key"> Enter your purchase key </label>
                <input id="api_key" name="api_key" autocomplete="off" type="text">
            </div>
            <?php echo wp_nonce_field( 'envato_api_nonce', 'envato_api_nonce_field' ,true, false ); ?>
            <input type="submit" name="submit" class="button button-primary button-hero" value="Activate"/>
        </form>
        <?php

        if( isset( $_POST['envato_api_nonce_field'] ) &&  wp_verify_nonce( $_POST['envato_api_nonce_field'], 'envato_api_nonce' ) && !empty($_POST['api_key'])){

            $purchase_key = $_POST['api_key'];
            $item_id = 23338013;
            $purchase_data = homey_verify_envato_purchase_key( $purchase_key );

            if( isset($purchase_data['verify-purchase']['buyer']) && $purchase_data['verify-purchase']['item_id'] == $item_id) {
                update_option( 'homey_activation', 'activated' );
                echo '<p class="successful"> '.__( 'Activated Successfully, reload page!', 'homey' ).' </p>';
            } else{
                echo '<p class="error"> '.__( 'Invalid license key', 'homey' ).' </p>';
            }



        }
        echo '</div>';
    }
    $status = get_option( 'homey_activation' );
    if(empty($status) || $status != 'activated'){
        add_action( 'admin_notices', 'homey_theme_activation' );
    }
}
function homey_verify_envato_purchase_key($code_to_verify) {

    $username = 'favethemes';

    $api_key = '2ftjwxihndy1yojj9ato4y8yjl3p7qcx';

    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/". $username ."/". $api_key ."/verify-purchase:". $code_to_verify .".json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);

    $output = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $output;
}

if(!function_exists('homey_theme_activate')) {
    function homey_theme_activate() {

        if(isset($_GET['homey'])) {
            update_option( 'homey_activation', $_GET['homey'] );
        }
    }
}
homey_theme_activate();