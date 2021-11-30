<?php
/*
Plugin Name: IP Geolocation
Description: This plugin is showing IP Geolocation using api service
Version: 1.0.1
Author: Rasool Vahdati
Author URI: https://tidaweb.com
Textdomain: ipgeo
*/


define( 'IP_GEOLOCATION', '1.0' );
define( 'IP_GEOLOCATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( IP_GEOLOCATION_PLUGIN_DIR . 'class.ipgeo.php' );
require_once( IP_GEOLOCATION_PLUGIN_DIR .'classes/class.settings.php');

register_activation_hook( __FILE__, array( 'IP_Geo_Location', 'ipgeo_activate' ) );
register_deactivation_hook( __FILE__, array( 'IP_Geo_Location', 'ipgeo_deactivate' ) );