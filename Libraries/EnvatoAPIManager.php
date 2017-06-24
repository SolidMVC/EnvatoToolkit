<?php
/**
 * Envato API Manager
 * The purpose of this class is to handle both - Envato Market API and Envato Edge API
 *
 * @package Envato Toolkit
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License
 */
require_once('EnvatoEdgeAPI.php');
require_once('EnvatoMarketAPI.php');

if(!class_exists('EnvatoAPIManager')):
class EnvatoAPIManager
{
    protected $debugMode 	            = 0;
    protected $savedDebugMessages       = array();
    protected $savedOkayMessages        = array();
    protected $savedErrorMessages       = array();
    protected $username                 = '';
    protected $apiKey                   = '';
    protected $personalToken            = '';
    protected $cachedUsers              = array();
    protected $cachedFilters            = array();
    protected $cachedItems              = array();
    protected $cachedLicenses           = array();
    protected $cachedDownloadURLs       = array();

    /**
     * @param array $paramSettings
     */
    public function __construct(array $paramSettings)
    {
        $this->username = isset($paramSettings['envato_username']) ? sanitize_text_field($paramSettings['envato_username']) : '';
        $this->apiKey = isset($paramSettings['envato_api_key']) ? sanitize_text_field($paramSettings['envato_api_key']) : '';
        $this->personalToken = isset($paramSettings['envato_personal_token']) ? sanitize_text_field($paramSettings['envato_personal_token']) : '';
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    public function getSavedDebugMessages()
    {
        return $this->savedDebugMessages;
    }

    public function getSavedOkayMessages()
    {
        return $this->savedOkayMessages;
    }

    public function getSavedErrorMessages()
    {
        return $this->savedErrorMessages;
    }


    /* -------------------------------------------------------------------------------------- */
    /* Default methods                                                                        */
    /* -------------------------------------------------------------------------------------- */

    /**
     * Normalizes a string to do a value check against.
     *
     * Strip all HTML tags including script and style & then decode the
     * HTML entities so `&amp;` will equal `&` in the value check and
     * finally lower case the entire string. This is required because some
     * themes & plugins add a link to the Author field or ampersands to the
     * names, or change the case of their files or names, which will not match
     * the saved value in the database causing a false negative.
     *
     * @param string $string The string to normalize.
     * @return string
     */
    private function normalize($string)
    {
        $normalizedString = strtolower(html_entity_decode(wp_strip_all_tags($string)));

        return $normalizedString;
    }


    /* -------------------------------------------------------------------------------------- */
    /* Methods, that are using Envato Edge API                                                */
    /* -------------------------------------------------------------------------------------- */

    /**
     * @uses EnvatoEdgeAPI
     * @param string $paramPurchaseCode
     * @return array|FALSE
     */
    public function getLicenseDetails($paramPurchaseCode)
    {
        $licenseDetails = FALSE;
        $validPurchaseCode = !is_array($paramPurchaseCode) ? preg_replace('[^-_0-9a-zA-Z]', '', $paramPurchaseCode) : '';

        if($validPurchaseCode != '' && isset($this->cachedLicenses[$validPurchaseCode]))
        {
            // Take purchase details from cache
            $licenseDetails = $this->cachedLicenses[$validPurchaseCode];
        } else if($validPurchaseCode != '' && !isset($this->cachedLicenses[$validPurchaseCode]))
        {
            // Call Edge API - this is a quicker path, and does not require personal token
            $objEnvatoAPI = new EnvatoEdgeAPI($this->username, $this->apiKey);
            $licenseDetails = $objEnvatoAPI->getLicenseDetails($paramPurchaseCode);

            if($licenseDetails != FALSE)
            {
                // Add to cache, but only if it is not there yet
                $this->cachedLicenses[$validPurchaseCode] = $licenseDetails;
            }

            // Save okay/error messages
            $this->savedDebugMessages = $objEnvatoAPI->getDebugMessages();
            $this->savedOkayMessages = $objEnvatoAPI->getErrorMessages();
            $this->savedErrorMessages = $objEnvatoAPI->getErrorMessages();
        }

        return $licenseDetails;
    }


    /* -------------------------------------------------------------------------------------- */
    /* Methods, that are using Envato Market API                                              */
    /* -------------------------------------------------------------------------------------- */

    /**
     * Get any Envato user details
     * @note Username here, of course, can be different from
     * @uses EnvatoMarketAPI
     * @param $paramUsername
     * @return array|false
     */
    public function getUserDetails($paramUsername)
    {
        $userDetails = FALSE;
        $validUsername = !is_array($paramUsername) ? preg_replace('[^-_0-9a-zA-Z]', '', $paramUsername) : '';

        if($validUsername != '' && isset($this->cachedUsers[$validUsername]))
        {
            // Take user details from cache
            $userDetails = $this->cachedUsers[$validUsername];
        } else if($this->personalToken != '' && $validUsername != '')
        {
            // Call Market API
            $objEnvatoAPI = new EnvatoMarketAPI($this->personalToken);

            // Get user details for specified username
            $userDetails = $objEnvatoAPI->getUser($validUsername);

            // Save okay/error messages
            $this->savedDebugMessages = $objEnvatoAPI->getDebugMessages();
            $this->savedOkayMessages = $objEnvatoAPI->getErrorMessages();
            $this->savedErrorMessages = $objEnvatoAPI->getErrorMessages();
        }

        return $userDetails;
    }

    public function getPurchasedPluginsWithDetails()
    {
        return $this->getPurchasedItemsWithDetails('wordpress-plugins');
    }

    public function getPurchasedThemesWithDetails()
    {
        return $this->getPurchasedItemsWithDetails('wordpress-themes');
    }

    /**
     * Get the list of plugins or themes for specified personal token, that were purchased in Envato Store
     * @uses EnvatoMarketAPI
     * @param $paramFilterBy - 'wordpress-plugins' or 'wordpress-themes'
     * @return array
     */
    private function getPurchasedItemsWithDetails($paramFilterBy)
    {
        $purchasedItemsWithDetails = array();
        // Protect from hitting the same filter more than once
        $sanitizedFilterBy = sanitize_text_field($paramFilterBy);
        if(!in_array($sanitizedFilterBy, $this->cachedFilters) && $this->personalToken != '')
        {
            $this->cachedFilters[] = $sanitizedFilterBy;

            // Call Market API
            $objEnvatoAPI = new EnvatoMarketAPI($this->personalToken);

            // Get all purchased plugins of this customer by his token
            // NOTE: Username parameter here is optional, we use it only to match the output with Edge API
            $itemsAndTheirPurchases = $objEnvatoAPI->getItemsAndTheirPurchases($paramFilterBy, $this->username);
            $purchasedItemsWithDetails = isset($itemsAndTheirPurchases['items']) ? $itemsAndTheirPurchases['items'] : array();
            $purchases = isset($itemsAndTheirPurchases['purchases']) ? $itemsAndTheirPurchases['purchases'] : array();

            // Add to items cache if needed
            foreach($purchasedItemsWithDetails AS $envatoItemId => $itemDetails)
            {
                if(!isset($this->cachedItems[$envatoItemId]))
                {
                    // No data exist for this purchase - add it
                    $this->cachedItems[$envatoItemId] = $itemDetails;
                }
            }

            // Add to licenses cache if needed
            foreach($purchases AS $purchaseCode => $licenseDetails)
            {
                if(!isset($this->cachedLicenses[$purchaseCode]))
                {
                    // No data exist for this purchase - add it
                    $this->cachedLicenses[$purchaseCode] = $licenseDetails;
                }
            }

            // Save okay/error messages
            $this->savedDebugMessages = $objEnvatoAPI->getDebugMessages();
            $this->savedOkayMessages = $objEnvatoAPI->getErrorMessages();
            $this->savedErrorMessages = $objEnvatoAPI->getErrorMessages();
        } else
        {
            // Load from cache
            $typeToMatch = $sanitizedFilterBy == 'wordpress-themes' ? 'theme' : 'plugin';

            // Add to items cache if needed
            foreach($this->cachedItems AS $envatoItemId => $itemDetails)
            {
                if(isset($itemDetails['type']) && $itemDetails['type'] == $typeToMatch)
                {
                    $purchasedItemsWithDetails[$envatoItemId] = $itemDetails;
                }
            }
        }

        return $purchasedItemsWithDetails;
    }

    /**
     * Get details of single Envato item for specified personal token.
     * @note returns item details only if it was purchased (that can be either theme or a plugin)
     * @uses EnvatoMarketAPI
     * @param int $paramEnvatoItemId
     * @return array|FALSE
     */
    public function getItemDetailsIfPurchased($paramEnvatoItemId)
    {
        $itemDetails = FALSE;
        $validEnvatoItemId = !is_array($paramEnvatoItemId) ? intval($paramEnvatoItemId) : 0;

        if($validEnvatoItemId > 0 && isset($this->cachedItems[$validEnvatoItemId]))
        {
            // Take user details from items cache
            $itemDetails = $this->cachedItems[$validEnvatoItemId];
        } else if($this->personalToken != '' && $validEnvatoItemId > 0)
        {
            // Call Market API
            $objEnvatoAPI = new EnvatoMarketAPI($this->personalToken);

            $itemDetails = $objEnvatoAPI->getItem($validEnvatoItemId);

            // Add item to cache
            $this->cachedItems[$validEnvatoItemId] = $itemDetails;

            // Save okay/error messages
            $this->savedDebugMessages = $objEnvatoAPI->getDebugMessages();
            $this->savedOkayMessages = $objEnvatoAPI->getErrorMessages();
            $this->savedErrorMessages = $objEnvatoAPI->getErrorMessages();
        }

        return $itemDetails;
    }

    /**
     * @uses EnvatoMarketAPI
     * @param $paramEnvatoItemId        - required, unless the purchase code is provided
     * @param string $paramPurchaseCode - usually we don't need that,  but in case if somebody will need
     *                                    to download this way, we keep this parameter here
     * @return string
     */
    public function getDownloadUrlIfPurchased($paramEnvatoItemId = 0, $paramPurchaseCode = '')
    {
        $downloadURL = '';
        $validEnvatoItemId = !is_array($paramEnvatoItemId) ? intval($paramEnvatoItemId) : 0;

        if($validEnvatoItemId > 0 && isset($this->cachedDownloadURLs[$validEnvatoItemId]))
        {
            // Take download url from cache
            $downloadURL = $this->cachedDownloadURLs[$validEnvatoItemId];
        } else if($this->personalToken != '' && ($validEnvatoItemId > 0 || $paramPurchaseCode != ''))
        {
            // Call Market API
            $objEnvatoAPI = new EnvatoMarketAPI($this->personalToken);

            $downloadURL = $objEnvatoAPI->getDownload($validEnvatoItemId, $paramPurchaseCode);

            // Add to cache
            $this->cachedDownloadURLs[$validEnvatoItemId] = $downloadURL;

            // Save okay/error messages
            $this->savedDebugMessages = $objEnvatoAPI->getDebugMessages();
            $this->savedOkayMessages = $objEnvatoAPI->getErrorMessages();
            $this->savedErrorMessages = $objEnvatoAPI->getErrorMessages();
        }

        return $downloadURL;
    }

    /* -------------------------------------------------------------------------------------- */
    /* Extended methods                                                                       */
    /* -------------------------------------------------------------------------------------- */

    public function isValidLicense($paramPurchaseCode)
    {
        return $this->getLicenseDetails($paramPurchaseCode) !== FALSE ? TRUE : FALSE;
    }

    public function getItemName($paramEnvatoItemId)
    {
        $itemDetails = $this->getItemDetailsIfPurchased($paramEnvatoItemId);
        $itemName = isset($itemDetails['name']) ? $itemDetails['name'] : '';

        return $itemName;
    }

    public function getAvailableVersion($paramEnvatoItemId)
    {
        $itemDetails = $this->getItemDetailsIfPurchased($paramEnvatoItemId);
        $availableVersion = isset($itemDetails['version']) ? $itemDetails['version'] : '';

        return $availableVersion;
    }

    public function isPurchased($paramEnvatoItemId)
    {
        $purchased = $this->getItemDetailsIfPurchased($paramEnvatoItemId) !== FALSE ? TRUE : FALSE;

        return $purchased;
    }

    public function checkPurchaseIsPlugin($paramEnvatoItemId)
    {
        $purchaseIsPlugin = FALSE;
        $itemDetails = $this->getItemDetailsIfPurchased($paramEnvatoItemId);
        if(isset($itemDetails['type']) && $itemDetails['type'] == "plugin")
        {
            $purchaseIsPlugin = TRUE;
        }

        return $purchaseIsPlugin;
    }

    public function checkPurchaseIsTheme($paramEnvatoItemId)
    {
        $purchaseIsTheme = FALSE;
        $itemDetails = $this->getItemDetailsIfPurchased($paramEnvatoItemId);
        if(isset($itemDetails['type']) && $itemDetails['type'] == "theme")
        {
            $purchaseIsTheme = TRUE;
        }

        return $purchaseIsTheme;
    }


    /* -------------------------------------------------------------------------------------- */
    /* Search methods                                                                         */
    /* -------------------------------------------------------------------------------------- */

    public function getLicensesByItemId($paramEnvatoItemId)
    {
        // Load license codes
        $this->getPurchasedItemsWithDetails('wordpress-plugins');
        $this->getPurchasedItemsWithDetails('wordpress-themes');

        $licenses = array();
        foreach($this->cachedLicenses AS $purchaseCode => $licenseDetails)
        {
            if(isset($licenseDetails['envato_item_id']) && $licenseDetails['envato_item_id'] == $paramEnvatoItemId)
            {
                $licenses[] = $licenseDetails;
            }
        }

        return $licenses;
    }

    /**
     * Get item id by plugin name and plugin author, but only if that plugin was purchased (based on personal token)
     * @param string $paramPluginName
     * @param string $paramPluginAuthor
     * @return int
     */
    public function getItemIdByPluginAndAuthorIfPurchased($paramPluginName, $paramPluginAuthor)
    {
        $envatoItemId = 0;
        $purchasedPlugins = $this->getPurchasedItemsWithDetails('wordpress-plugins');
        foreach($purchasedPlugins AS $purchasedPlugin)
        {
            if (isset($purchasedPlugin['name']) && $this->normalize($purchasedPlugin['name']) === $this->normalize($paramPluginName) &&
                isset($purchasedPlugin['author']) && $this->normalize($purchasedPlugin['author']) === $this->normalize($paramPluginAuthor)
            ) {
                $envatoItemId = isset($purchasedPlugin['envato_item_id']) ? $purchasedPlugin['envato_item_id'] : 0;
            }
        }

        return $envatoItemId;
    }

    /**
     * Get item id by theme name and theme author, but only if that theme was purchased (based on personal token)
     * @param string $paramThemeName
     * @param string $paramThemeAuthor
     * @return int
     */
    public function getItemIdByThemeAndAuthorIfPurchased($paramThemeName, $paramThemeAuthor)
    {
        $envatoItemId = 0;
        $purchasedThemes = $this->getPurchasedItemsWithDetails('wordpress-themes');
        foreach($purchasedThemes AS $purchasedTheme)
        {
            if (isset($purchasedTheme['name']) && $this->normalize($purchasedTheme['name']) === $this->normalize($paramThemeName) &&
                isset($purchasedTheme['author']) && $this->normalize($purchasedTheme['author']) === $this->normalize($paramThemeAuthor)
            ) {
                $envatoItemId = isset($purchasedTheme['envato_item_id']) ? $purchasedTheme['envato_item_id']: 0;
            }
        }

        return $envatoItemId;
    }
}
endif;