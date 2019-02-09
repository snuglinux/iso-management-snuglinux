<?php
/*
* Plugin Name: ISO Management Snuglinux
* Description: Control panel ISO images SnugLinux
* Version: 0.0.1
* Author: Khomenko Valery
* Author URI: http://khv.pp.ua
* Domain Path: /lang/
* License: GPL3
*/

/*
Copyright 2010-2016  Khomenko Valery  (email: khvalera@ukr.net)
*/

ini_set('display_errors',1);
error_reporting(E_ALL);

global $user;
global $db_version_plug;
global $wpdb;


// Show and Hide SQL Errors
$wpdb -> show_errors();

$db_version_plug = "0.0.1";

/*************************** localization ********************************/
function iso_init() {
    load_plugin_textdomain( 'iso-management-snuglinux', false, plugin_basename( dirname( __FILE__ ) .'/lang' ) );
}

add_action( 'init', 'iso_init' );

require_once( 'includes/iso-common-functions.php' );

/****************** Управление сессиями **********************/
function management_session($sess) {
  //обработка сессий
   session_start();
   if ( session_id() != $sess) {
      if (session_status() == PHP_SESSION_ACTIVE) {
         session_destroy();
      }
   }
   if (session_status() != PHP_SESSION_ACTIVE) {
       session_id($sess);
       session_start();
   }
}

/****************** Открыть страницу опций **********************/
function iso_options_page() {
   $_name = 'iso-options';
   management_session($_name);
   require_once($_name.'.php');
}

/****************** Открыть страницу info **********************/
function iso_info_page() {
   $_name = 'iso-info';
   management_session($_name);
   require_once($_name.'.php');
}

/****************** OPEN PAGE MAIN **********************/
function iso_page() {
   $_name = 'iso-main';
   management_session($_name);
   require_once($_name.'.php');
}

/********************* REGISTER THE PAGE ************************/
function iso_add_menu_items(){
    // если создается класс не в add_action() не меняется ширина полей таблицы
    global $TableISO;

    require_once( 'includes/iso-class-table.php' );
    $TableISO = new class_iso_table();

    // Add a new top-level menu:
    add_menu_page('ISO Management', __( 'ISO Management', 'iso-management-snuglinux' ), 'iso_list', 'iso-page', 'iso_page', plugins_url( '/images/iso.png', __FILE__ ) );

    // Add a new submenu under info:
    add_submenu_page('iso-page', __( 'Info', 'iso-management-snuglinux' ), __( 'Info', 'iso-management-snuglinux' ), 'iso_list', 'info', 'iso_info_page');
}

add_action('admin_menu', 'iso_add_menu_items');

//при активации и регистрации плагина
function register_activation_plugin () {
   require_once( 'iso-register-activation.php');
}

register_activation_hook(__FILE__, 'register_activation_plugin');
add_action('plugins_loaded', 'register_activation_plugin');
add_action( 'init', 'iso_init' );
