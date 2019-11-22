<?php
/**
 * Fixes HTTPS issues with wp_get_attachment_url()
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/wp_get_attachment_url
 * https://github.com/syamilmj/Aqua-Resizer/issues/21
 *
 * @package WordPress
 * @subpackage Homey
 * @since Homey 1.5.0
*/

if ( ! function_exists( 'homey_honor_ssl_for_attachments' ) ) {
	function homey_honor_ssl_for_attachments($url) {
		$http = site_url(FALSE, 'http');
		$https = site_url(FALSE, 'https');
		$isSecure = false;
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
		|| $_SERVER['SERVER_PORT'] == 443) {
			$isSecure = true;
		}
		return ( $isSecure ) ? str_replace($http, $https, $url) : $url;
	}
}
add_filter('wp_get_attachment_url', 'homey_honor_ssl_for_attachments');

/*
 * Google reCaptcha filter
 * */
if(!function_exists('homey_google_recaptcha_callback')) {
    function homey_google_recaptcha_callback() {

        $recaptha_site_key = homey_option('recaptha_site_key');
        $recaptha_secret_key = homey_option('recaptha_secret_key');
        $enable_reCaptcha = homey_option('enable_reCaptcha');

        if( $enable_reCaptcha != 1 ) {
            return true;
        }

        // include library https://github.com/google/recaptcha
        include_once(  HOMEY_PLUGIN_PATH . '/includes/recaptcha/src/autoload.php' );

        // If the form submission includes the "g-captcha-response" field
        // Create an instance of the service using your secret

        $recaptcha = new \ReCaptcha\ReCaptcha( $recaptha_secret_key, new \ReCaptcha\RequestMethod\CurlPost() );

        // If file_get_contents() is locked down on your PHP installation to disallow
        // its use with URLs, then you can use the alternative request method instead.
        // This makes use of fsockopen() instead.

        // Make the call to verify the response and also pass the user's IP address
        $resp = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER['REMOTE_ADDR']);


        if ($resp->isSuccess()):
            return true;
        else:

            $error_codes   = $resp->getErrorCodes();

            //Error codes - https://developers.google.com/recaptcha/docs/verify
            $captach_errors  = array(
                'missing-input-secret'   => esc_html__('The secret parameter is missing.', 'homey'),
                'invalid-input-secret'   => esc_html__('The secret parameter is invalid or malformed.', 'homey'),
                'missing-input-response' => esc_html__('The response parameter is missing.', 'homey'),
                'invalid-input-response' => esc_html__('The response parameter is invalid or malformed.', 'homey'),
                'bad-request' => esc_html__('The request is invalid or malformed.', 'homey'),
            );
            $error_message = $captach_errors[ $error_codes[ 0 ]];
            echo json_encode( array(
                'success' => false,
                'msg' => esc_html__( 'reCAPTCHA Failed:', 'homey' ) . ' ' . $error_message
            ) );
            wp_die();
        endif;
    }
}