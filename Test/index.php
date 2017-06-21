<?php
/**
 * Envato Toolkit test intro file
 *
 * @note - This test works only if you have WordPress installed, and you can point to working WordPress install 'wp-blog-header.php' site
 * @package EnvatoToolkit Test
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License
 */

// Enable or disable this test. If not testing at the moment - disable the test
$testEnabled = TRUE;
if(!$testEnabled)
{
    die('Test is disabled');
}

// Require Php 5.4 or newer
if (version_compare(phpversion(), '5.4.0', '<'))
{
    die('Php 5.4 or newer is required. Please upgrade your Php version first.');
}

// Start a session
// Note: Requires Php 5.4+
if(session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

define('WP_USE_THEMES', FALSE); // This is a test class, so we don't need to load
require_once('../wp-blog-header.php'); // Note: adapt to match your path
require_once(ABSPATH . '/Libraries/EnvatoAPIManager.php');

// Load the test controller
require_once('EnvatoAPIManagerTest.php');