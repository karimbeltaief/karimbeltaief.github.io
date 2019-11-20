<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 16/01/16
 * Time: 6:11 PM
 */

/*-----------------------------------------------------------------------------------*/
// Login
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_login', 'homey_login' );
add_action( 'wp_ajax_nopriv_homey_login', 'homey_login' );

if( !function_exists('homey_login') ) {
    function homey_login() {


        $allowed_html = array();

        $allowed_html_array = array('strong' => array());
        $username = wp_kses( $_POST['username'], $allowed_html );
        $pass = $_POST['password'];
        $is_submit_listing = isset($_POST['is_submit_listing']) ? wp_kses( $_POST['is_submit_listing'], $allowed_html ) : '';

        if( $is_submit_listing == 'yes' ) {
            check_ajax_referer('homey_register_nonce2', 'homey_register_security2');
        } else {
            check_ajax_referer( 'homey_login_nonce', 'homey_login_security' );
        }

        if( isset( $_POST['remember'] ) ) {
            $remember = wp_kses( $_POST['remember'], $allowed_html );
        } else {
            $remember = '';
        }

        if( empty( $username ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The username or email field is empty.', 'homey-login-register') ) );
            wp_die();
        }
        if( empty( $pass ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The password field is empty.', 'homey-login-register') ) );
            wp_die();
        }
        if( !username_exists( $username ) && !email_exists($username)) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid username or email', 'homey-login-register') ) );
            wp_die();
        }


        homey_google_recaptcha_callback();

        wp_clear_auth_cookie();

        $remember = ($remember == 'on') ? true : false;

        if(is_email($username)) {
            $user = get_user_by( 'email', $username );
            $username = $user->user_login;
        }

        $creds = array();
        $creds['user_login'] = $username;
        $creds['user_password'] = $pass;
        $creds['remember'] = $remember;
        $user = wp_signon( $creds, false );

        if ( is_wp_error( $user ) ) {

            echo json_encode( array(
                'success' => false,
                'msg' => sprintf( wp_kses(__('The password you entered for the username <strong>%s</strong> is incorrect.', 'homey-login-register'), $allowed_html_array), $username )
                ) );

            wp_die();
        } else {

            //wp_set_current_user($user->ID);
            do_action('set_current_user');
            wp_set_auth_cookie( $user->ID );
            global $current_user;
            $current_user = wp_get_current_user();

            echo json_encode( array( 'success' => true, 'msg' => esc_html__('Login successful, redirecting...', 'homey-login-register') ) );

        }
        wp_die();
    }
}


/*-----------------------------------------------------------------------------------*/
// Register
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_register', 'homey_register' );
add_action( 'wp_ajax_homey_register', 'homey_register' );

if( !function_exists('homey_register') ) {
    function homey_register() {
        //$local = homey_get_localization();

        check_ajax_referer('homey_register_nonce', 'homey_register_security');

        $allowed_html = array();

        $usermane          = trim( sanitize_text_field( wp_kses( $_POST['username'], $allowed_html ) ));
        $email             = trim( sanitize_text_field( wp_kses( $_POST['useremail'], $allowed_html ) ));
        $term_condition    = wp_kses( $_POST['term_condition'], $allowed_html );
        $enable_password = homey_option('enable_password');
        //$response = $_POST["g-recaptcha-response"];

        $user_role = get_option( 'default_role' );

        if( isset( $_POST['role'] ) && $_POST['role'] != '' ){
            $user_role = isset( $_POST['role'] ) ? sanitize_text_field( wp_kses( $_POST['role'], $allowed_html ) ) : $user_role;
        } else {
            $user_role = $user_role;
        }

        $term_condition = ( $term_condition == 'on') ? true : false;

        if( !$term_condition ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('You need to agree with terms & conditions.', 'homey-login-register') ) );
            wp_die();
        }

        if( empty( $usermane ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The username field is empty.', 'homey-login-register') ) );
            wp_die();
        }
        if( strlen( $usermane ) < 3 ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Minimum 3 characters required', 'homey-login-register') ) );
            wp_die();
        }
        if (preg_match("/^[0-9A-Za-z_]+$/", $usermane) == 0) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid username (do not use special characters or spaces)!', 'homey-login-register') ) );
            wp_die();
        }
        if( username_exists( $usermane ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('This username is already registered.', 'homey-login-register') ) );
            wp_die();
        }
        if( empty( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The email field is empty.', 'homey-login-register') ) );
            wp_die();
        }

        if( email_exists( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('This email address is already registered.', 'homey-login-register') ) );
            wp_die();
        }

        if( !is_email( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid email address.', 'homey-login-register') ) );
            wp_die();
        }

        if( $enable_password == 'yes' ){
            $user_pass         = trim( sanitize_text_field(wp_kses( $_POST['register_pass'] ,$allowed_html) ) );
            $user_pass_retype  = trim( sanitize_text_field(wp_kses( $_POST['register_pass_retype'] ,$allowed_html) ) );

            if ($user_pass == '' || $user_pass_retype == '' ) {
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('One of the password field is empty!', 'homey-login-register') ) );
                wp_die();
            }

            if ($user_pass !== $user_pass_retype ){
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('Passwords do not match', 'homey-login-register') ) );
                wp_die();
            }
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

        if($enable_password == 'yes' ) {
            $user_password = $user_pass;
        } else {
            $user_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        }
        $user_id = wp_create_user( $usermane, $user_password, $email );

        if ( is_wp_error($user_id) ) {
            echo json_encode( array( 'success' => false, 'msg' => $user_id ) );
            wp_die();
        } else {

            wp_update_user( array( 'ID' => $user_id, 'role' => $user_role ) );

            if( $enable_password =='yes' ) {
                echo json_encode( array( 'success' => true, 'msg' => esc_html__('Your account was created and you can login now!', 'homey-login-register') ) );
            } else {
                echo json_encode( array( 'success' => true, 'msg' => esc_html__('An email with the generated password was sent!', 'homey-login-register') ) );
            }
            homey_wp_new_user_notification( $user_id, $user_password );
        }
        wp_die();

    }
}

/*-----------------------------------------------------------------------------------*/
// New register user notification
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_wp_new_user_notification') ) {

    function homey_wp_new_user_notification( $user_id, $randonpassword = '' ) {

        $user = new WP_User( $user_id );

        $user_login = stripslashes( $user->user_login );
        $user_email = stripslashes( $user->user_email );

        // Send notification to admin
        $args = array(
            'user_login_register' => $user_login,
            'user_email_register' => $user_email
        );
        homey_email_composer( get_option('admin_email'), 'admin_new_user_register', $args );


        // Return if password in empty
        if ( empty( $randonpassword ) ) {
            return;
        }

        $profile_url = homey_get_template_link_dash('template/dashboard-profile.php');

        // Send notification to registered user
        $args = array(
            'user_login_register'  =>  $user_login,
            'user_email_register'  =>  $user_email,
            'user_password'   => $randonpassword,
            'profile_url'   => $profile_url,
        );
        homey_email_composer( $user_email, 'new_user_register', $args );

    }
}

add_action( 'wp_ajax_nopriv_homey_reset_password', 'homey_reset_password' );
add_action( 'wp_ajax_homey_reset_password', 'homey_reset_password' );

if( !function_exists('homey_reset_password') ) {
    function homey_reset_password() {
        check_ajax_referer('homey_resetpassword_nonce', 'security');

        $allowed_html = array();
        $user_login = wp_kses( $_POST['user_login'], $allowed_html );

        if ( empty( $user_login ) ) {
            echo json_encode(array( 'success' => false, 'msg' => esc_html__('Enter a username or email address.', 'homey-login-register') ) );
            wp_die();
        }

        if ( strpos( $user_login, '@' ) ) {
            $user_data = get_user_by( 'email', trim( $user_login ) );
            if ( empty( $user_data ) ) {
                echo json_encode(array('success' => false, 'msg' => esc_html__('There is no user registered with that email address.', 'homey-login-register')));
                wp_die();
            }
        } else {
            $login = trim( $user_login );
            $user_data = get_user_by('login', $login);

            if ( !$user_data ) {
                echo json_encode(array( 'success' => false, 'msg' => esc_html__('Invalid username', 'homey-login-register') ) );
                wp_die();
            }
        }

        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        $key = get_password_reset_key( $user_data );

        if ( is_wp_error( $key ) ) {
            echo json_encode(array( 'success' => false, 'msg' => $key ) );
            wp_die();
        }



        $message = esc_html__('Someone has requested a password reset for the following account:', 'homey-login-register' ) . "\r\n\r\n";
        $message .= network_home_url( '/' ) . "\r\n\r\n";
        $message .= sprintf(esc_html__('Username: %s', 'homey-login-register'), $user_login) . "\r\n\r\n";
        $message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.', 'homey-login-register') . "\r\n\r\n";
        $message .= esc_html__('To reset your password, visit the following address:', 'homey-login-register') . "\r\n\r\n";
        $message .= '(' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ")\r\n";

        if ( is_multisite() )
            $blogname = $GLOBALS['current_site']->site_name;
        else
            /*
             * The blogname option is escaped with esc_html on the way into the database
             * in sanitize_option we want to reverse this for the plain text arena of emails.
             */
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

            $title = sprintf( esc_html__('[%s] Password Reset', 'homey-login-register'), $blogname );

        /**
         * Filter the subject of the password reset email.
         *
         * @since 2.8.0
         * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
         *
         * @param string  $title      Default email title.
         * @param string  $user_login The username for the user.
         * @param WP_User $user_data  WP_User object.
         */
        $title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

        /**
         * Filter the message body of the password reset mail.
         *
         * @since 2.8.0
         * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
         *
         * @param string  $message    Default mail message.
         * @param string  $key        The activation key.
         * @param string  $user_login The username for the user.
         * @param WP_User $user_data  WP_User object.
         */

        $message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );
        $headers = 'From: No Reply <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
        if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message, $headers ) ) {
            echo json_encode(array('success' => false, 'msg' => esc_html__('The email could not be sent.', 'homey-login-register') . "<br />\n" . esc_html__('Possible reason: your host may have disabled the mail() function.', 'homey-login-register')));
            wp_die();
        } else {
            echo json_encode(array('success' => true, 'msg' => esc_html__('Check your email', 'homey-login-register') ));
            wp_die();
        }
        return true;


    }
}