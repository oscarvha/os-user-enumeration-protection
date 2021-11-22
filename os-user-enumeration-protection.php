<?php
/*
Plugin Name: Os User Enumeration Protection
Plugin URI: http://oscarenginner.com
Description: Protege el formulario de Login ante la enumeración de usuarios
Version: 1.0
Author: Oscar Sánchez
Author URI: http://oscarenginner.com

Domain Path: /lang/
Text Domain: adora-enumeration
Domain Path: /languages/
*/

include 'Util/LoggerEnumeration.php';
include 'Util/ClientUtil.php';

add_filter('login_errors','login_error_messages');
add_filter('rest_authentication_errors' , 'check_admin_api_rest');

add_action( 'plugins_loaded', 'load_plugin_textdomain_adora_enumeration' );
add_action( 'template_redirect', 'author_page_redirect' );

const ERROR_USERNAME = 'invalid_username';
const ERROR_PASSWORD = 'incorrect_password';
const ERROR_EMPTY_PASSWORD = 'empty_password';


function login_error_messages($message) {
    global $errors;
    $errCodes = $errors->get_error_codes();
    $errorLogFile = dirname(__FILE__).'/log/login.error.log';

    $logger = new LoggerEnumeration($errorLogFile);
    $logger->log($errCodes);

    if(in_array(ERROR_USERNAME, $errCodes)
        || in_array(ERROR_PASSWORD,$errCodes)
        || in_array(ERROR_EMPTY_PASSWORD,$errCodes)) {

        $message = __('<strong>ERROR</strong>: Invalid username/password combination.', 'adora-enumeration');
    }

    return $message;

}

function author_page_redirect() {

    global $wp_query;
    $is_author_set = get_query_var( 'author', '' );
    if( $is_author_set != '' && ! current_user_can( 'administrator' )) {
        wp_redirect(get_option('home'), 301);
        exit;
    }
}



function check_admin_api_rest($result) {

    if($_POST) {
        if(isset($_POST['_wpcf7'])) {
            return $result;
        }
    }
    
    if ( ! empty( $result ) ) {
        return $result;
    }
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array( 'status' => 401 ) );
    }
    if ( ! current_user_can( 'administrator' ) ) {
        return new WP_Error( 'rest_not_admin', 'You are not an administrator.', array( 'status' => 401 ) );
    }
    return $result;

}

function load_plugin_textdomain_adora_enumeration() {
    load_plugin_textdomain( 'oscar-enumeration', FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
}