<?php
/**
 * Envato Toolkit Search
 *
 * @package EnvatoToolkit
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License
 */

namespace EnvatoToolkit\Controllers;

use EnvatoToolkit\Models\Configuration;
use EnvatoToolkit\Models\EnvatoAPIManager;
use EnvatoToolkit\Views\PageView;

final class SearchController
{
    protected $conf = NULL;

    public function __construct(Configuration &$paramConf)
    {
        $this->conf = $paramConf;
    }

    public function printContent()
    {
        // Initialize the page view
        $view = new PageView();

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
            $plugins = array();
            foreach($purchasedPlugins AS $pluginId => $purchasedPlugin)
            {
                $purchasedPlugin['licenses'] = $objToolkit->getLicensesByItemId($pluginId);
                $plugins[$pluginId] = $purchasedPlugin;
            }

            $purchasedThemes = $objToolkit->getPurchasedThemesWithDetails();
            $themes = array();
            foreach($purchasedThemes AS $themeId => $purchasedTheme)
            {
                $purchasedTheme['licenses'] = $objToolkit->getLicensesByItemId($themeId);
                $themes[$themeId] = $purchasedTheme;
            }

            $authorDetails = $objToolkit->getUserDetails($sanitizedEnvatoUsername);
            // View vars
            $view->plugins = $plugins;
            $view->themes = $themes;
            if($authorDetails != FALSE)
            {
                $view->authorCity = $authorDetails['city'];
                $view->authorCountry = $authorDetails['country'];
                $view->authorSales = $authorDetails['sales'];
                $view->authorFollowers = $authorDetails['followers'];
            } else
            {
                $view->authorCity = '';
                $view->authorCountry = '';
                $view->authorSales = 0;
                $view->authorFollowers = 0;
            }

            // 1. Details About Target Purchase Code
            $targetLicenseDetails = $objToolkit->getLicenseDetails($sanitizedTargetPurchaseCode);
            // View vars
            $view->showLicenseDetails = ($sanitizedEnvatoUsername != '' && $sanitizedEnvatoAPIKey != '');
            $view->targetPurchaseCode = esc_html($sanitizedTargetPurchaseCode); // Ready for print
            $view->isValidTargetLicense = $objToolkit->isValidLicense($sanitizedTargetPurchaseCode);
            if($targetLicenseDetails != FALSE)
            {
                $view->targetLicenseBuyer = $targetLicenseDetails['buyer_username'];
                $view->targetLicenseType = $targetLicenseDetails['license_type'];
                $view->targetLicensePurchaseDate = $targetLicenseDetails['license_purchase_date'];
                $view->targetLicenseSupportExpiration = $targetLicenseDetails['support_expiration_date'];
                $view->targetLicenseSupportActive = $targetLicenseDetails['support_active'];
            } else
            {
                $view->targetLicenseBuyer = '';
                $view->targetLicenseType = '';
                $view->targetLicensePurchaseDate = '';
                $view->targetLicenseSupportExpiration = '';
                $view->targetLicenseSupportActive = 0;
            }

            // 2. Details About Target Envato User
            $targetUserDetails = $objToolkit->getUserDetails($sanitizedTargetUsername);
            // View vars
            $view->targetUsername = esc_html($sanitizedTargetUsername); // Ready for print
            if($targetUserDetails != FALSE)
            {
                $view->targetUserCity = $targetUserDetails['city'];
                $view->targetUserCountry = $targetUserDetails['country'];
                $view->targetUserSales = $targetUserDetails['sales'];
                $view->targetUserFollowers = $targetUserDetails['followers'];
            } else
            {
                $view->targetUserCity = '';
                $view->targetUserCountry = '';
                $view->targetUserSales = 0;
                $view->targetUserFollowers = 0;
            }

            // 3. Status of Purchased Plugin ID
            $pluginUpdateAvailable = $objToolkit->checkPurchasedItemUpdateAvailable($sanitizedTargetPluginId, $sanitizedInstalledPluginVersion);
            // View vars
            $view->targetPluginId = intval($sanitizedTargetPluginId); // Ready for print
            $view->installedPluginVersion = esc_html($sanitizedInstalledPluginVersion); // Ready for print
            $view->nameOfTargetPluginId = esc_html($objToolkit->getItemName($sanitizedTargetPluginId));
            $view->pluginUpdateAvailable = $pluginUpdateAvailable;
            $view->availablePluginVersion = $objToolkit->getAvailableVersion($sanitizedTargetPluginId);
            $view->pluginUpdateDownloadUrl = $pluginUpdateAvailable ? $objToolkit->getDownloadUrlIfPurchased($sanitizedTargetPluginId) : '';

            // 4. Status of Purchased Theme ID
            $themeUpdateAvailable = $objToolkit->checkPurchasedItemUpdateAvailable($sanitizedTargetThemeId, $sanitizedInstalledThemeVersion);
            // View vars
            $view->targetThemeId = intval($sanitizedTargetThemeId); // Ready for print
            $view->installedThemeVersion = esc_html($sanitizedInstalledThemeVersion); // Ready for print
            $view->nameOfTargetThemeId = esc_html($objToolkit->getItemName($sanitizedTargetThemeId));
            $view->themeUpdateAvailable = $themeUpdateAvailable;
            $view->availableThemeVersion = $objToolkit->getAvailableVersion($sanitizedTargetThemeId);
            $view->themeUpdateDownloadUrl = $themeUpdateAvailable ? $objToolkit->getDownloadUrlIfPurchased($sanitizedTargetThemeId) : '';

            // 5. Envato Item Id of Purchased Plugin
            $view->targetPluginName = esc_html($sanitizedTargetPluginName); // Ready for print
            $view->targetPluginAuthor = esc_html($sanitizedTargetPluginAuthor); // Ready for print
            $view->foundPluginId = $objToolkit->getItemIdByPluginAndAuthorIfPurchased($sanitizedTargetPluginName, $sanitizedTargetPluginAuthor);

            // 6. Envato Item Id of Purchased Theme
            $view->targetThemeName = esc_html($sanitizedTargetThemeName); // Ready for print
            $view->targetThemeAuthor = esc_html($sanitizedTargetThemeAuthor); // Ready for print
            $view->foundThemeId = $objToolkit->getItemIdByThemeAndAuthorIfPurchased($sanitizedTargetThemeName, $sanitizedTargetThemeAuthor);

            $view->goBackUrl = admin_url('admin.php?page=envato-toolkit-menu');

            // Print the template
            $templatePathAndFileName = $this->conf->getTemplatesPath().'template.SearchResults.php';
            echo $view->render($templatePathAndFileName);
        } else
        {
            // Your details
            $view->envatoUsername = isset($_SESSION['conf_envato_username']) ? esc_attr(stripslashes($_SESSION['conf_envato_username'])) : '';
            $view->envatoAPIKey = isset($_SESSION['conf_envato_api_key']) ? esc_attr(stripslashes($_SESSION['conf_envato_api_key'])) : '';
            $view->envatoPersonalToken = isset($_SESSION['conf_envato_personal_token']) ? esc_attr(stripslashes($_SESSION['conf_envato_personal_token'])) : '';

            // Check target purchase code
            $view->targetPurchaseCode = isset($_SESSION['target_purchase_code']) ? esc_attr(stripslashes($_SESSION['target_purchase_code'])) : '';

            // Check target username
            $view->targetUsername = isset($_SESSION['target_username']) ? esc_attr(stripslashes($_SESSION['target_username'])) : '';

            // Check for plugin updates
            $view->targetPluginId = isset($_SESSION['target_plugin_id']) ? esc_attr(stripslashes($_SESSION['target_plugin_id'])) : '';
            $view->installedPluginVersion = isset($_SESSION['installed_plugin_version']) ? esc_attr(stripslashes($_SESSION['installed_plugin_version'])) : '';

            // Check for theme updates
            $view->targetThemeId = isset($_SESSION['target_theme_id']) ? esc_attr(stripslashes($_SESSION['target_theme_id'])) : '';
            $view->installedThemeVersion = isset($_SESSION['installed_theme_version']) ? esc_attr(stripslashes($_SESSION['installed_theme_version'])) : '';

            // Get id of installed plugin
            $view->targetPluginName = isset($_SESSION['target_plugin_name']) ? esc_attr(stripslashes($_SESSION['target_plugin_name'])) : '';
            $view->targetPluginAuthor = isset($_SESSION['target_plugin_author']) ? esc_attr(stripslashes($_SESSION['target_plugin_author'])) : '';

            // Get id of installed plugin
            $view->targetThemeName = isset($_SESSION['target_theme_name']) ? esc_attr(stripslashes($_SESSION['target_theme_name'])) : '';
            $view->targetThemeAuthor = isset($_SESSION['target_theme_author']) ? esc_attr(stripslashes($_SESSION['target_theme_author'])) : '';

            if(isset($_POST['fill_demo_data']))
            {
                $view->targetUsername = 'ThemeFusion';
                $view->targetPluginId  = '2201708';
                $view->installedPluginVersion = '1.6.0';
                $view->targetThemeId = '2833226';
                $view->installedThemeVersion = '3.9.3';
                $view->targetPluginName = 'WordPress Social Stream';
                $view->targetPluginAuthor = 'Lee Chestnutt';
                $view->targetThemeName = 'Avada';
                $view->targetThemeAuthor = 'ThemeFusion';
            }

            // Print the template
            $templatePathAndFileName = $this->conf->getTemplatesPath().'template.SearchInput.php';
            echo $view->render($templatePathAndFileName);
        }
    }
}



