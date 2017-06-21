<?php
/*
 * Plugin Name: Envato Toolkit
 * Plugin URI: https://wordpress.org/plugins/toolkit-for-envato/
 * Description: It is a 3 files library + Visual UI, to validate the purchase codes of your customers, get details about specific Envato user (country, city, total followers, total sales, avatar), get his license purchase and support expiration dates, license type he bought, check for updates of purchased plugins and themes and get the download links for them. Plus - this library has Envato Item Id search feature by providing plugin's or theme's name and author.
 * Version: 1.1
 * Author: KestutisIT
 * Author URI: https://profiles.wordpress.org/KestutisIT
 * Text Domain: envato-toolkit
 * Domain Path: /Languages
*/
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

// Require mandatory model
require_once ('Models/class.Configuration.php');

// Require autoloader and main ET controller
require_once ('Models/class.AutoLoad.php');
require_once ('Controllers/class.MainController.php');

// Create an instance of ET configuration model
$objETConfiguration = new \EnvatoToolkit\Models\Configuration(
    $GLOBALS['wpdb'], get_current_blog_id(), '5.4.0', phpversion(), 4.6, $GLOBALS['wp_version'], 1.1, __FILE__
);

// Create an instance of ET main controller
$objEnvatoToolkit = new \EnvatoToolkit\Controllers\MainController($objETConfiguration);

// Run the plugin
$objEnvatoToolkit->run();