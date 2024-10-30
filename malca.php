<?php
/*
Plugin Name: Malca-Amit Shipping Services
Plugin URI: http://www.malca-amit.com
Description: Full Shipping Integration with FedEx, UPS and USPS.
Version: 1.03
Author: Malca Amit
Author URI: http://www.malca-amit.com/
License: GPLv2 or later
License URI: http://www.malca-amit.com/
Text Domain: malca-amit-shipping-services
WC requires at least: 2.2.0
WC tested up to: 3.4.0
*/

/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

if ( ! defined( 'ABSPATH' ) ) exit;


// If this file is called directly, abort.
if (!defined( 'WPINC')) die;

require( 'include/autoload.php');
require( 'include/ajax.php');
require( 'include/functions.php');

/* Define Constant*/
define('MALCA_STORE_URL', Malca_remove_http(site_url()));
define('MALCA_DIR', plugin_dir_url(__FILE__));
define('MALCA_FOLDER',ABSPATH . 'wp-content/plugins/malca-amit-shipping-services');


/* intialization */
register_activation_hook(__FILE__,'ActivateMalca');
register_deactivation_hook( __FILE__, 'DeactivateMalca' );

if(!function_exists('ActivateMalca')){
    function ActivateMalca(){
        if(class_exists("SOAPClient")){
            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                $StoreDetails = array(
                    'PlatformType' => 'WordPress',
                    'StoreId' => MALCA_STORE_URL,
                    'Token' => '0000000000',
                    'AppStatus' => 'installed',
                    'AppStatusChangeDate' => date('Y-m-d'),
                    'AppStatusCreatedDate' => date('Y-m-d'),
                );

                $params = array('StoreDetails' => $StoreDetails,'ApplicationId' => 969);
                $result = Malca_SoapResponse('SetMAEXEcommerceStoreDetails',$params);
                $result = $result->SetMAEXEcommerceStoreDetailsResult;
                if($result->NotificationType != 'Success'){
                    echo '<h3>'.__('Malca Service Error, Contact us for more information.', 'ap').'</h3>';
                    //Adding @ before will prevent XDebug output
                    @trigger_error(__('Malca Service Error, Contact us for more information.', 'ap'), E_USER_ERROR);
                }
            }else{
                echo '<h3>'.__('Woocommerce is compulsory for activate this plugin!', 'ap').'</h3>';
                //Adding @ before will prevent XDebug output
                @trigger_error(__('Please install woocommerce before activating.', 'ap'), E_USER_ERROR);
            }
        }else{
            echo '<h3>'.__('Please contact your server administrator to enable SOAP client!', 'ap').'</h3>';
            //Adding @ before will prevent XDebug output
            @trigger_error(__('Please contact your server administrator to enable SOAP client!', 'ap'), E_USER_ERROR);
        }
    }
}

if(!function_exists('DeactivateMalca')){
    function DeactivateMalca(){
        delete_option( 'malcaLoginUser' );
        delete_option( 'malcaLoginPswd' );
        delete_option( 'malcaLoginCode' );

        $StoreDetails = array(
            'PlatformType' => 'WordPress',
            'StoreId' => MALCA_STORE_URL,
            'Token' => '0000000000',
            'AppStatus' => 'uninstalled',
            'AppStatusChangeDate' => date('Y-m-d'),
            'AppStatusCreatedDate' => date('Y-m-d'),
        );

        $params = array('StoreDetails' => $StoreDetails,'ApplicationId' => 969);
        Malca_SoapResponse('SetMAEXEcommerceStoreDetails',$params);
    }
}

/* Creating Menus */
if(!function_exists('Malca_Menu')){
    function Malca_Menu(){

        /* Adding menus */
        add_menu_page(__('Malca_amit'),'Malca Amit', 'edit_pages','Malca_Orders', 'Malca_amit_orders',MALCA_DIR . 'assets/images/malca_logo.png',1);

        /* Adding Sub menus */
        
        add_submenu_page('Malca_amit', 'Orders', 'Orders', 'edit_pages', 'Malca_Orders', 'Malca_amit_orders');

        /* Adding css and Js */ 
        wp_register_style('Malcafonts.css', MALCA_DIR . 'assets/css/fonts.css');
        wp_enqueue_style('Malcafonts.css');

        wp_register_style('font-awesome.min.css', MALCA_DIR . 'assets/css/font-awesome.min.css');
        wp_enqueue_style('font-awesome.min.css');

        wp_register_style('Malcacommon.css', MALCA_DIR . 'assets/css/common.css');
        wp_enqueue_style('Malcacommon.css');

        wp_register_style('Malcastyle.css', MALCA_DIR . 'assets/css/style.css');
        wp_enqueue_style('Malcastyle.css');

        wp_register_style('MalcadatePicker.css', MALCA_DIR . 'assets/css/jquery.datetimepicker.css');
        wp_enqueue_style('MalcadatePicker.css');

        wp_register_script('Malcacaptcha.js', 'https://www.google.com/recaptcha/api.js', array('jquery'));
        wp_enqueue_script('Malcacaptcha.js');

        wp_register_script('MalcaDate.full.js', MALCA_DIR . 'assets/js/jquery.datetimepicker.full.js', array('jquery'));
        wp_enqueue_script('MalcaDate.full.js');  
    }
}
add_action('admin_menu', 'Malca_Menu');

/* Malca Orders List */
if(!function_exists('Malca_amit_orders')){
    function Malca_amit_orders() {
        /* get the option to check login user */
        
        $StoreDetails = array(
            'StoreId' => MALCA_STORE_URL,
            'PlatformType' => 'WordPress',
            'ApplicationId' => 969
        );
        
        $is_install = Malca_SoapResponse('GetMAEXEcommerceStoreDetails',$StoreDetails);
        $result = $is_install->GetMAEXEcommerceStoreDetailsResult;
        $flag = false;
        if($result->NotificationType == 'Success'){
            $StoreDetails = $result->StoreDetails;
            if($StoreDetails->AppStatus == 'installed'){
                $flag = true;
                if ( get_option( 'malcaLoginUser' ) !== false && get_option( 'malcaLoginPswd' ) !== false && get_option( 'malcaLoginCode' ) !== false ) {
                    include "template/orders.php";
                }else{
                    include "template/login.php";
                }
            }
        }
        if(!$flag){
            echo "<h3>Plugin isn't fully installed..Please reactivate this plugin...</h3>";
        }
    }
}
?>