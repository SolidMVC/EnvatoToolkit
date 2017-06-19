<?php
/**
 * Envato API Manager test
 *
 * @note1 - This test works only if you have WordPress installed, and you can point to working WordPress install 'wp-blog-header.php' site
 * @note2 - When you develop something, you can put this test code to your current active theme's functions.php file
 *          or in some plugin Admin section hook callback class
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

$wpBlogHeaderFilePath = '../';

define('WP_USE_THEMES', FALSE); // This is a test class, so we don't need to load
require_once($wpBlogHeaderFilePath.'wp-blog-header.php'); // Note: adapt to match your path
require_once(ABSPATH . '/Libraries/EnvatoAPIManager.php');

if(isset($_POST['envato_check']))
{
    // Toolkit settings
    $sanitizedEnvatoUsername = isset($_POST['conf_envato_username']) ? sanitize_text_field($_POST['conf_envato_username']) : '';
    $sanitizedEnvatoAPIKey = isset($_POST['conf_envato_api_key']) ? sanitize_text_field($_POST['conf_envato_api_key']) : '';
    $sanitizedEnvatoPersonalToken = isset($_POST['conf_envato_personal_token']) ? sanitize_text_field($_POST['conf_envato_personal_token']) : '';

    // Methods params
    $sanitizedTargetPurchaseCode = isset($_POST['target_purchase_code']) ? sanitize_text_field($_POST['target_purchase_code']) : '';
    $sanitizedTargetUsername = isset($_POST['target_username']) ? sanitize_text_field($_POST['target_username']) : '';
    $sanitizedTargetPluginId = isset($_POST['target_plugin_id']) ? sanitize_text_field($_POST['target_plugin_id']) : '';
    $sanitizedInstalledPluginVersion = isset($_POST['installed_plugin_version']) ? sanitize_text_field($_POST['installed_plugin_version']) : '';
    $sanitizedTargetThemeId = isset($_POST['target_theme_id']) ? sanitize_text_field($_POST['target_theme_id']) : '';
    $sanitizedInstalledThemeVersion = isset($_POST['installed_theme_version']) ? sanitize_text_field($_POST['installed_theme_version']) : '';
    $sanitizedTargetPluginName = isset($_POST['target_plugin_name']) ? sanitize_text_field($_POST['target_plugin_name']) : '';
    $sanitizedTargetPluginAuthor = isset($_POST['target_plugin_author']) ? sanitize_text_field($_POST['target_plugin_author']) : '';
    $sanitizedTargetThemeName = isset($_POST['target_theme_name']) ? sanitize_text_field($_POST['target_theme_name']) : '';
    $sanitizedTargetThemeAuthor = isset($_POST['target_theme_author']) ? sanitize_text_field($_POST['target_theme_author']) : '';

    // Update $_SESSION
    $_SESSION['conf_envato_username'] = $sanitizedEnvatoUsername;
    $_SESSION['conf_envato_api_key'] = $sanitizedEnvatoAPIKey;
    $_SESSION['conf_envato_personal_token'] = $sanitizedEnvatoPersonalToken;

    $_SESSION['target_purchase_code'] = $sanitizedTargetPurchaseCode;
    $_SESSION['target_username'] = $sanitizedTargetUsername;
    $_SESSION['target_plugin_id'] = $sanitizedTargetPluginId;
    $_SESSION['installed_plugin_version'] = $sanitizedInstalledPluginVersion;
    $_SESSION['target_theme_id'] = $sanitizedTargetThemeId;
    $_SESSION['installed_theme_version'] = $sanitizedInstalledThemeVersion;
    $_SESSION['target_plugin_name'] = $sanitizedTargetPluginName;
    $_SESSION['target_plugin_author'] = $sanitizedTargetPluginAuthor;
    $_SESSION['target_theme_name'] = $sanitizedTargetThemeName;
    $_SESSION['target_theme_author'] = $sanitizedTargetThemeAuthor;

    $toolkitSettings = array(
        'conf_envato_username' => $sanitizedEnvatoUsername,
        'conf_envato_api_key' => $sanitizedEnvatoAPIKey,
        'conf_envato_personal_token' => $sanitizedEnvatoPersonalToken,
    );

    $objToolkit = new EnvatoAPIManager($toolkitSettings);

    // Details about you
    $purchasedPlugins = $objToolkit->getPurchasedPluginsWithDetails();
    // View vars
    $plugins = array();
    foreach($purchasedPlugins AS $pluginId => $purchasedPlugin)
    {
        $purchasedPlugin['licenses'] = $objToolkit->getLicensesByItemId($pluginId);
        $plugins[$pluginId] = $purchasedPlugin;
    }

    $purchasedThemes = $objToolkit->getPurchasedThemesWithDetails();
    // View vars
    $themes = array();
    foreach($purchasedThemes AS $themeId => $purchasedTheme)
    {
        $purchasedTheme['licenses'] = $objToolkit->getLicensesByItemId($themeId);
        $themes[$themeId] = $purchasedTheme;
    }

    $authorDetails = $objToolkit->getUserDetails($sanitizedEnvatoUsername);
    // View vars
    if($authorDetails != FALSE)
    {
        $authorCity = $authorDetails['city'];
        $authorCountry = $authorDetails['country'];
        $authorSales = $authorDetails['sales'];
        $authorFollowers = $authorDetails['followers'];
    } else
    {
        $authorCity = '';
        $authorCountry = '';
        $authorSales = 0;
        $authorFollowers = 0;
    }

    // 1. Details About Target Purchase Code
    $targetLicenseDetails = $objToolkit->getLicenseDetails($sanitizedTargetPurchaseCode);
    // View vars
    $showLicenseDetails = ($sanitizedEnvatoUsername != '' && $sanitizedEnvatoAPIKey != '');
    $targetPurchaseCode = esc_html($sanitizedTargetPurchaseCode); // Ready for print
    $isValidTargetLicense = $objToolkit->isValidLicense($sanitizedTargetPurchaseCode);
    if($targetLicenseDetails != FALSE)
    {
        $targetLicenseBuyer = $targetLicenseDetails['buyer_username'];
        $targetLicenseType = $targetLicenseDetails['license_type'];
        $targetLicensePurchaseDate = $targetLicenseDetails['license_purchase_date'];
        $targetLicenseSupportExpiration = $targetLicenseDetails['support_expiration_date'];
        $targetLicenseSupportActive = $targetLicenseDetails['support_active'];
    } else
    {
        $targetLicenseBuyer = '';
        $targetLicenseType = '';
        $targetLicensePurchaseDate = '';
        $targetLicenseSupportExpiration = '';
        $targetLicenseSupportActive = 0;
    }

    // 2. Details About Target Envato User
    $targetUserDetails = $objToolkit->getUserDetails($sanitizedTargetUsername);
    // View vars
    $targetUsername = esc_html($sanitizedTargetUsername); // Ready for print
    if($targetUserDetails != FALSE)
    {
        $targetUserCity = $targetUserDetails['city'];
        $targetUserCountry = $targetUserDetails['country'];
        $targetUserSales = $targetUserDetails['sales'];
        $targetUserFollowers = $targetUserDetails['followers'];
    } else
    {
        $targetUserCity = '';
        $targetUserCountry = '';
        $targetUserSales = 0;
        $targetUserFollowers = 0;
    }


    // 3. Status of Purchased Plugin ID
    $pluginUpdateAvailable = $objToolkit->checkPurchasedItemUpdateAvailable($sanitizedTargetPluginId, $sanitizedInstalledPluginVersion);
    // View vars
    $targetPluginId = intval($sanitizedTargetPluginId); // Ready for print
    $installedPluginVersion = esc_html($sanitizedInstalledPluginVersion); // Ready for print
    $nameOfTargetPluginId = esc_html($objToolkit->getItemName($sanitizedTargetPluginId));
    $availablePluginVersion = $objToolkit->getAvailableVersion($sanitizedTargetPluginId);
    $pluginUpdateDownloadUrl = $pluginUpdateAvailable ? $objToolkit->getDownloadUrlIfPurchased($sanitizedTargetPluginId) : '';

    // 4. Status of Purchased Theme ID
    $themeUpdateAvailable = $objToolkit->checkPurchasedItemUpdateAvailable($sanitizedTargetThemeId, $sanitizedInstalledThemeVersion);
    // View vars
    $targetThemeId = intval($sanitizedTargetThemeId); // Ready for print
    $installedThemeVersion = esc_html($sanitizedInstalledThemeVersion); // Ready for print
    $nameOfTargetThemeId = esc_html($objToolkit->getItemName($sanitizedTargetThemeId));
    $availableThemeVersion = $objToolkit->getAvailableVersion($sanitizedTargetThemeId);
    $themeUpdateDownloadUrl = $themeUpdateAvailable ? $objToolkit->getDownloadUrlIfPurchased($sanitizedTargetThemeId) : '';

    // 5. Envato Item Id of Purchased Plugin
    $targetPluginName = esc_html($sanitizedTargetPluginName); // Ready for print
    $targetPluginAuthor = esc_html($sanitizedTargetPluginAuthor); // Ready for print
    $foundPluginId = $objToolkit->getItemIdByPluginAndAuthorIfPurchased($sanitizedTargetPluginName, $sanitizedTargetPluginAuthor);

    // 6. Envato Item Id of Purchased Theme
    $targetThemeName = esc_html($sanitizedTargetThemeName); // Ready for print
    $targetThemeAuthor = esc_html($sanitizedTargetThemeAuthor); // Ready for print
    $foundThemeId = $objToolkit->getItemIdByThemeAndAuthorIfPurchased($sanitizedTargetThemeName, $sanitizedTargetThemeAuthor);

    $goBackUrl = pathinfo(__FILE__, PATHINFO_FILENAME).'.php';
    require('template.TestResults.php');
} else
{
    // Your details
    $envatoUsername = isset($_SESSION['conf_envato_username']) ? esc_attr(stripslashes($_SESSION['conf_envato_username'])) : '';
    $envatoAPIKey = isset($_SESSION['conf_envato_api_key']) ? esc_attr(stripslashes($_SESSION['conf_envato_api_key'])) : '';
    $envatoPersonalToken = isset($_SESSION['conf_envato_personal_token']) ? esc_attr(stripslashes($_SESSION['conf_envato_personal_token'])) : '';

    // Check target purchase code
    $targetPurchaseCode = isset($_SESSION['target_purchase_code']) ? esc_attr(stripslashes($_SESSION['target_purchase_code'])) : '';

    // Check target username
    $targetUsername = isset($_SESSION['target_username']) ? esc_attr(stripslashes($_SESSION['target_username'])) : '';

    // Check for plugin updates
    $targetPluginId = isset($_SESSION['target_plugin_id']) ? esc_attr(stripslashes($_SESSION['target_plugin_id'])) : '';
    $installedPluginVersion = isset($_SESSION['installed_plugin_version']) ? esc_attr(stripslashes($_SESSION['installed_plugin_version'])) : '';

    // Check for theme updates
    $targetThemeId = isset($_SESSION['target_theme_id']) ? esc_attr(stripslashes($_SESSION['target_theme_id'])) : '';
    $installedThemeVersion = isset($_SESSION['installed_theme_version']) ? esc_attr(stripslashes($_SESSION['installed_theme_version'])) : '';

    // Get id of installed plugin
    $targetPluginName = isset($_SESSION['target_plugin_name']) ? esc_attr(stripslashes($_SESSION['target_plugin_name'])) : '';
    $targetPluginAuthor = isset($_SESSION['target_plugin_author']) ? esc_attr(stripslashes($_SESSION['target_plugin_author'])) : '';

    // Get id of installed plugin
    $targetThemeName = isset($_SESSION['target_theme_name']) ? esc_attr(stripslashes($_SESSION['target_theme_name'])) : '';
    $targetThemeAuthor = isset($_SESSION['target_theme_author']) ? esc_attr(stripslashes($_SESSION['target_theme_author'])) : '';

    if(isset($_POST['fill_demo_data']))
    {
        $targetUsername = 'ThemeFusion';
        $targetPluginId  = '2201708';
        $installedPluginVersion = '1.6.0';
        $targetThemeId = '2833226';
        $installedThemeVersion = '3.9.3';
        $targetPluginName = 'WordPress Social Stream';
        $targetPluginAuthor = 'Lee Chestnutt';
        $targetThemeName = 'Avada';
        $targetThemeAuthor = 'ThemeFusion';
    }

    // Input Form
    require('template.TestInput.php');
}


