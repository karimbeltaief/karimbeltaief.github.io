<?php
/**
 * File Name: Email Functions
 */
if (!function_exists('homey_email_composer')) {
    function homey_email_composer( $email, $email_type, $args ) {

        $value_message = homey_option('homey_' . $email_type);
        $value_subject = homey_option('homey_subject_' . $email_type);
        $value_subject = homey_option('homey_subject_' . $email_type);

        do_action( 'wpml_register_single_string', 'homey', 'homey_email_' . $value_message, $value_message );
        do_action( 'wpml_register_single_string', 'homey', 'homey_email_subject_' . $value_subject, $value_subject );

        $filters = homey_emails_filter_replace( $email, $value_message, $value_subject, $args);
        return $filters;
    }
}

if( !function_exists('homey_emails_filter_replace')):
    function  homey_emails_filter_replace( $email, $message, $subject, $args ) {
        $args ['site_url'] = get_option('siteurl');
        $args ['site_title'] = get_option('blogname');
        $args ['user_email'] = $email;
        $user = get_user_by( 'email',$email );
        $args ['user_login'] = $user->user_login;

        foreach( $args as $key => $val){
            $subject = str_replace('{'.$key.'}', $val, $subject );
            $message = str_replace('{'.$key.'}', $val, $message );
        }

        $homey_send_emails = homey_send_emails( $email, $subject, $message );
        return $homey_send_emails;
        
    }
endif;

if( !function_exists('homey_send_emails') ):
    function homey_send_emails( $user_email, $subject, $message ){
        $headers = 'From: No Reply <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        $enable_html_emails = homey_option('enable_html_emails');
        $enable_email_header = homey_option('enable_email_header');
        $enable_email_footer = homey_option('enable_email_footer');

        if( $enable_html_emails != 0 ) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        }

        $enable_html_emails = homey_option('enable_html_emails');
        $email_head_logo = homey_option('email_head_logo', false, 'url');
        $email_head_bg_color = homey_option('email_head_bg_color');
        $email_foot_bg_color = homey_option('email_foot_bg_color');
        $email_footer_content = homey_option('email_footer_content');

        $social_1_icon = homey_option('social_1_icon', false, 'url');
        $social_1_link = homey_option('social_1_link');
        $social_2_icon = homey_option('social_2_icon', false, 'url');
        $social_2_link = homey_option('social_2_link');
        $social_3_icon = homey_option('social_3_icon', false, 'url');
        $social_3_link = homey_option('social_3_link');
        $social_4_icon = homey_option('social_4_icon', false, 'url');
        $social_4_link = homey_option('social_4_link');

        $socials = $email_content = '';
        if( !empty($social_1_icon) || !empty($social_2_icon) || !empty($social_3_icon) || !empty($social_4_icon) ) {
            $socials = '<div style="font-size: 0; text-align: center; padding-top: 20px;">';
            $socials .= '<p style="margin:0;margin-bottom: 10px; text-align: center; font-size: 14px; color:#777777;">'.esc_html__('Follow us on', 'homey-core').'</p>';

            if( !empty($social_1_icon) ) {
                $socials .= '<a href="'.esc_url($social_1_link).'" style="margin-right: 5px"><img src="'.esc_url($social_1_icon).'"> </a>';
            }
            if( !empty($social_2_icon) ) {
                $socials .= '<a href="'.esc_url($social_2_link).'" style="margin-right: 5px"><img src="'.esc_url($social_2_icon).'"> </a>';
            }
            if( !empty($social_3_icon) ) {
                $socials .= '<a href="'.esc_url($social_3_link).'" style="margin-right: 5px"><img src="'.esc_url($social_3_icon).'"> </a>';
            }
            if( !empty($social_4_icon) ) {
                $socials .= '<a href="'.esc_url($social_4_link).'" style="margin-right: 5px"><img src="'.esc_url($social_4_icon).'"> </a>';
            }

            $socials .= '</div>';
        }

        if( $enable_email_header != 0 ) {
            $email_content = '<div style="text-align: center; background-color: ' . esc_attr($email_head_bg_color) . '; padding: 16px 0;">
                            <img src="' . esc_url($email_head_logo) . '" alt="logo">
                        </div>';
        }

        $email_content .= '<div style="background-color: #F6F6F6; padding: 30px;">
                            <div style="margin: 0 auto; width: 620px; background-color: #fff;border:1px solid #eee; padding:30px;">
                                <div style="font-family:\'Helvetica Neue\',\'Helvetica\',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;display:block;max-width:600px;margin:0 auto;padding:0">
                                '.$message.'
                                </div>
                            </div>
                        </div>';

        if( $enable_email_footer != 0 ) {
            $email_content .= '<div style="padding-top: 30px; text-align:center; padding-bottom: 30px; font-family:\'Helvetica Neue\',\'Helvetica\',Helvetica,Arial,sans-serif;">

                            <div style="width: 640px; background-color: ' . $email_foot_bg_color . '; margin: 0 auto;">
                                ' . $email_footer_content . '
                            </div>
                            ' . $socials . '
                        </div>';
        }

        if( $enable_html_emails != 0 ) {
            $email_messages = $email_content;
        } else {
            $email_messages = $message;
        }

        $email_sent = @wp_mail($user_email, $subject, $email_messages, $headers);

        if($email_sent) {
            return true;
        }
        return false;
    };
endif;


add_action( 'wp_ajax_nopriv_homey_host_contact', 'homey_host_contact' );
add_action( 'wp_ajax_homey_host_contact', 'homey_host_contact' );

if( !function_exists('homey_host_contact') ) {
    function homey_host_contact() {

        $nonce = $_POST['host_contact_security'];
        if (!wp_verify_nonce( $nonce, 'host-contact-nonce') ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Invalid Nonce!', 'homey-core')
            ));
            wp_die();
        }

        $sender_phone = sanitize_text_field( $_POST['phone'] );
        $permalink = esc_url( $_POST['permalink'] );
        $listing_title = sanitize_text_field( $_POST['listing_title'] );
        $response = isset($_POST["g-recaptcha-response"]) ? $_POST["g-recaptcha-response"] : "";

        $target_email = $_POST['target_email'];
        if ( !is_array( $target_email ) ) {
            $target_email = is_email($target_email);
        }
        if (!$target_email) {
            echo json_encode(array(
                'success' => false,
                'msg' => sprintf( esc_html__('%s Email address is not configured!', 'homey-core'), $target_email )
            ));
            wp_die();
        }

        $sender_name = sanitize_text_field($_POST['name']);
        if ( empty($sender_name) ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Name field is empty!', 'homey-core')
            ));
            wp_die();
        }

        
        if ( empty($sender_phone) ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Phone field is empty!', 'homey-core')
            ));
            wp_die();
        }

        $sender_email = sanitize_email($_POST['email']);
        $sender_email = is_email($sender_email);
        if (!$sender_email) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Invalid email address!', 'homey-core')
            ));
            wp_die();
        }

        $sender_msg = wp_kses_post( $_POST['message'] );
        if ( empty($sender_msg) ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Your message is empty!', 'homey-core')
            ));
            wp_die();
        }

        $enable_forms_gdpr = homey_option('enable_forms_gdpr');

        if( $enable_forms_gdpr != 0 ) {
            $privacy_policy = isset($_POST['privacy_policy']) ? $_POST['privacy_policy'] : '';
            if ( empty($privacy_policy) ) {
                echo json_encode(array(
                    'success' => false,
                    'msg' => homey_option('forms_gdpr_validation')
                ));
                wp_die();
            }
        }

        homey_google_recaptcha_callback();


        $subject = sprintf( esc_html__('New message sent by %s using host contact form at %s', 'homey-core'), $sender_name, get_bloginfo('name') );

        $body = esc_html__("You have received new message from: ", 'homey-core') . $sender_name . " <br/>";

        if ( ! empty( $listing_title ) ) {
            $body .= "<br/>" . esc_html__("Listing Title : ", 'homey-core') . $listing_title . " <br/>";
        }

        if ( ! empty( $permalink ) ) {
            $body .= esc_html__("Listing URL : ", 'homey-core') . '<a href="'. $permalink. '">' . $permalink . "</a><br/>";
        }

        if (! empty($sender_phone)) {
            $body .= esc_html__("Phone Number : ", 'homey-core') . $sender_phone . " <br/>";
        }

        $body .= "<br/>" . esc_html__("Additional message is.", 'homey-core') . " <br/>";
        $body .= wpautop( $sender_msg ) . " <br/>";
        $body .= sprintf( esc_html__( 'You can contact %s via email %s', 'homey-core'), $sender_name, $sender_email );

        $header = 'Content-type: text/html; charset=utf-8' . "\r\n";

        $header  .= "From: $sender_name <$sender_email>\r\n";
        $header .= "MIME-Version: 1.0\r\n";

        if ( wp_mail( $target_email, $subject, $body, $header ) ) {
            echo json_encode( array(
                'success' => true,
                'msg' => esc_html__("Email Sent Successfully!", 'homey-core')
            ));
            wp_die();
        } else {
            echo json_encode(array(
                    'success' => false,
                    'msg' => esc_html__("Server Error: Make sure Email function working on your server!", 'homey-core')
                )
            );
            wp_die();
        }
        wp_die();

    }
}

add_action( 'wp_ajax_nopriv_homey_contact_host', 'homey_contact_host' );
add_action( 'wp_ajax_homey_contact_host', 'homey_contact_host' );
if( !function_exists( 'homey_contact_host' ) ) {
    function homey_contact_host() {

        $nonce = $_POST['host_detail_ajax_nonce'];
        if (!wp_verify_nonce( $nonce, 'host-contact-nonce') ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Unverified Nonce!', 'homey-core')
            ));
            wp_die();
        }

        $sender_phone = sanitize_text_field( $_POST['phone'] );

        $target_email = sanitize_email($_POST['target_email']);
        $target_email = is_email($target_email);
        if (!$target_email) {
            echo json_encode(array(
                'success' => false,
                'msg' => sprintf( esc_html__('%s Target Email address is not properly configured!', 'homey-core'), $target_email )
            ));
            wp_die();
        }


        $sender_name = sanitize_text_field($_POST['name']);
        if ( empty($sender_name) ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Name field is empty!', 'homey-core')
            ));
            wp_die();
        }

        $sender_email = sanitize_email($_POST['email']);
        $sender_email = is_email($sender_email);
        if (!$sender_email) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Provided Email address is invalid!', 'homey-core')
            ));
            wp_die();
        }

        $sender_msg = wp_kses_post( $_POST['message'] );
        if ( empty($sender_msg) ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Your message empty!', 'homey-core')
            ));
            wp_die();
        }

        $enable_forms_gdpr = homey_option('enable_forms_gdpr');

        if( $enable_forms_gdpr != 0 ) {
            $privacy_policy = isset($_POST['privacy_policy']) ? $_POST['privacy_policy'] : '';
            if ( empty($privacy_policy) ) {
                echo json_encode(array(
                    'success' => false,
                    'msg' => homey_option('forms_gdpr_validation')
                ));
                wp_die();
            }
        }

        homey_google_recaptcha_callback();

        $email_subject = sprintf( esc_html__('New message sent by %s using contact form at %s', 'homey-core'), $sender_name, get_bloginfo('name') );

        $email_body = esc_html__("You have received a message from: ", 'homey-core') . $sender_name . " <br/>";
        if (!empty($sender_phone)) {
            $email_body .= esc_html__("Phone Number : ", 'homey-core') . $sender_phone . " <br/>";
        }
        $email_body .= esc_html__("Additional message is as follows.", 'homey-core') . " <br/>";
        $email_body .= wpautop( $sender_msg ) . " <br/>";
        $email_body .= sprintf( esc_html__( 'You can contact %s via email %s', 'homey-core'), $sender_name, $sender_email );


        $header = 'Content-type: text/html; charset=utf-8' . "\r\n";
        //$header .= 'From: ' . $sender_name . " <" . $sender_email . "> \r\n";

        $header  .= "From: $sender_name <$sender_email>\r\n";
        $header .= "MIME-Version: 1.0\r\n";

        if (wp_mail( $target_email, $email_subject, $email_body, $header)) {
            echo json_encode( array(
                'success' => true,
                'msg' => esc_html__("Message Sent Successfully!", 'homey-core')
            ));
            wp_die();
        } else {
            echo json_encode(array(
                    'success' => false,
                    'msg' => esc_html__("Server Error: Make sure Email function working on your server!", 'homey-core')
                )
            );
            wp_die();
        }

        wp_die();
    }
}
