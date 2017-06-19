<?php
/*
 * Plugin Name: Envato Toolkit
 * Plugin URI: http://EnvatoToolkit.com
 * Description: Add custom notes before or after the comment form.
 * Version: 1.0
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
    $GLOBALS['wpdb'], get_current_blog_id(), '5.4.0', phpversion(), 4.6, $GLOBALS['wp_version'], 1.0, __FILE__
);

// Create an instance of ET main controller
$objEnvatoToolkit = new \EnvatoToolkit\Controllers\MainController($objETConfiguration);

// Run the plugin
$objEnvatoToolkit->run();