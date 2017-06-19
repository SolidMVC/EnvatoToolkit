<?php
/**
 * Envato Market API class
 * @note - Keep in mind that the ApiKey here is different to the Personal Tokens
 *         The Api Key are generated in Envato Profile Settings
 * @package Envato Toolkit
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License
 */

if(!class_exists('EnvatoMarketAPI')):
class EnvatoMarketAPI
{
    //const API_AGENT = 'WordPress - Envato Market 1.0.0-RC2'; // Original Agent
    const API_AGENT = 'EnvatoToolkit/1.0';

    protected $debugMode 	            = 0;
    protected $debugMessages            = array();
    protected $okayMessages             = array();
    protected $errorMessages            = array();

    /**
     * The Envato API personal token.
     * It can be generated at https://build.envato.com/create-token/
     * Keep in mind that this is a different thing than API
     * For documentation and instructions we can give this link to customers:
     * <a href="https://build.envato.com/create-token/?purchase:download=t&purchase:verify=t&purchase:list=t" target="_blank">generate a personal token</a>'
     * @var string
     */
    protected $token;

    /**
     * EnvatoMarketAPI constructor.
     * @param string $paramToken
     */
    public function __construct($paramToken)
    {
        $this->token = sanitize_text_field($paramToken);
    }

    /**
     * You cannot clone this class.
     * @codeCoverageIgnore
     */
    public function __clone()
    {
        _doing_it_wrong( __FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'envato-market-api'), '1.0');
    }

    /**
     * You cannot unserialize instances of this class.
     * @codeCoverageIgnore
     */
    public function __wakeup()
    {
        _doing_it_wrong( __FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'envato-market-api'), '1.0');
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    public function flushMessages()
    {
        $this->debugMessages = array();
        $this->okayMessages = array();
        $this->errorMessages = array();
    }

    public function getDebugMessages()
    {
        return $this->debugMessages;
    }

    public function getOkayMessages()
    {
        return $this->okayMessages;
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * Query the Envato API.
     * @uses wp_remote_get() To perform an HTTP request.
     * @param  string $paramApiRequestURL API request URL, including the request method, parameters, & file type.
     * @param  array  $paramArgs The arguments passed to `wp_remote_get`.
     * @return array|\WP_Error  The HTTP response.
     */
    public function request($paramApiRequestURL, $paramArgs = array())
    {
        if($this->debugMode)
        {
            $debugMessage = '(Market API Hit) Request Url: '.esc_url_raw($paramApiRequestURL);
            echo '<br />'.$debugMessage;
            $this->debugMessages[] = $debugMessage;
        }

        $defaults = array(
            'headers' => array(
                'Authorization' => 'Bearer '.$this->token,
                'User-Agent' => static::API_AGENT,
            ),
            'timeout' => 20,
        );
        $paramArgs = wp_parse_args($paramArgs, $defaults);

        $token = trim(str_replace('Bearer', '', $paramArgs['headers']['Authorization']));
        if(empty( $token))
        {
            return new \WP_Error('api_token_error', __( 'An API token is required.', 'envato-market-api'));
        }

        // Make an API request.
        $response = wp_remote_get(esc_url_raw($paramApiRequestURL), $paramArgs);

        // Check the response code.
        $response_code    = wp_remote_retrieve_response_code($response);
        $response_message = wp_remote_retrieve_response_message($response);

        if(200 !== $response_code && ! empty( $response_message))
        {
            return new \WP_Error($response_code, $response_message);
        } elseif(200 !== $response_code )
        {
            return new \WP_Error($response_code, __('An unknown API error occurred.', 'envato-market-api'));
        } else
        {
            $return = json_decode(wp_remote_retrieve_body($response), TRUE);
            if(null === $return )
            {
                return new \WP_Error('api_error', __('An unknown API error occurred.', 'envato-market-api'));
            }
            return $return;
        }
    }

    /**
     * Get an user by username.
     * @action get /user:{username}.json
     * @note   Example - https://build.envato.com/api/#!/market/User
     * @param  string   $paramUsername The username.
     * @param  array $paramArgs The arguments passed to `wp_remote_get`.
     * @return array|FALSE The HTTP response.
     */
    public function getUser($paramUsername, $paramArgs = array())
    {
        $sanitizedUsername = sanitize_text_field($paramUsername);
        $url = 'https://api.envato.com/v1/market/user:'.$sanitizedUsername.'.json';
        $response = $this->request($url, $paramArgs);

        if(is_wp_error($response) || empty($response))
        {
            return FALSE;
        }

        if(!empty($response['user']))
        {
            return $this->normalizeUser($response['user']);
        }

        return FALSE;
    }

    /**
     * Get an item by ID and type.
     * @action get /catalog/item
     * @note   Example - https://build.envato.com/api/#!/market_0/Catalog_Item
     * @param  int   $paramItemId The item ID.
     * @param  array $paramArgs The arguments passed to `wp_remote_get`.
     * @return array|FALSE The HTTP response.
     */
    public function getItem($paramItemId, $paramArgs = array())
    {
        $validItemId = !is_array($paramItemId) ? intval($paramItemId) : 0;
        $url = 'https://api.envato.com/v3/market/catalog/item?id='.$validItemId;
        $response = $this->request($url, $paramArgs);

        if(is_wp_error($response) || empty($response))
        {
            return FALSE;
        }

        return $this->normalizeItem($response);
    }

    /**
     * Get the list of all purchased plugins or items for specific token.
     * @action get /buyer/list-purchases
     * @note   Example - https://build.envato.com/api/#!/market_0/Buyer_ListPurchases
     * @param  string $paramFilterBy - 'wordpress-plugins' or 'wordpress-themes'
     * @param  string (optional) $paramBuyerUsername
     * @param  array $paramArgs The arguments passed to `wp_remote_get`.
     * @return array The HTTP response.
     */
    public function getItemsAndTheirPurchases($paramFilterBy, $paramBuyerUsername = '', $paramArgs = array())
    {
        $items = array();
        $purchases = array();

        $validFilterBy = $paramFilterBy == 'wordpress-themes' ? 'wordpress-themes' : 'wordpress-plugins';
        $url = 'https://api.envato.com/v3/market/buyer/list-purchases?filter_by='.$validFilterBy;
        $response = $this->request($url, $paramArgs);

        if(is_wp_error($response) || empty($response) || empty($response['results']))
        {
            return $items;
        }

        foreach($response['results'] AS $item)
        {
            $itemId = isset($item['item']['id']) ? intval($item['item']['id']) : 0;
            $validPurchaseCode = isset($item['code']) && !is_array($item['code']) ? preg_replace('[^-_0-9a-zA-Z]', '', $item['code']) : '';

            if($itemId > 0)
            {
                $items[$itemId] = $this->normalizeItem($item['item']);
            }
            if($validPurchaseCode != "")
            {
                $purchases[$validPurchaseCode] = $this->normalizeLicense($item, $paramBuyerUsername);
            }
        }

        return array(
            'items' => $items,
            'purchases' => $purchases,
        );
    }

    /**
     * Get the item download by either item id or purchase code.
     * @action get /buyer/download
     * @note   Example - https://build.envato.com/api/#!/market_0/Buyer_Download
     * @param  int $paramItemId The item ID.
     * @param  string $paramPurchaseCode
     * @param  array $paramArgs The arguments passed to `wp_remote_get`.
     * @return string The HTTP response.
     */
    public function getDownload($paramItemId = 0, $paramPurchaseCode = '', $paramArgs = array())
    {
        $validItemId = !is_array($paramItemId) ? intval($paramItemId) : 0;
        $sanitizedPurchaseCode = sanitize_text_field($paramPurchaseCode);
        if($validItemId <= 0 && $sanitizedPurchaseCode == '')
        {
            return FALSE;
        }

        $url = '';
        if($validItemId > 0)
        {
            // Download by item id
            $url = 'https://api.envato.com/v3/market/buyer/download?item_id='.$validItemId.'&shorten_url=TRUE';
        } else if($sanitizedPurchaseCode != '')
        {
            // Download by purchase code
            $url = 'https://api.envato.com/v3/market/buyer/download?purchase_code='.$sanitizedPurchaseCode.'&shorten_url=true';
        }
        $response = $this->request($url, $paramArgs);

        // Example response no. 1 ('wordpress_plugin' is the smaller, plugin-only download):
        // {
        //      "download_url": "https://codecanyon.net/short-dl?hash=b300b500-1111-2222-3333-77400dba2222",
        //      "wordpress_plugin": "https://codecanyon.net/short-dl?hash=100e3000-1111-2222-3333-8abc20007000"
        // }
        //
        // Example response no. 2 ('wordpress_theme' is the smaller, theme-only download):
        // {
        //      "download_url": "https://themeforest.net/short-dl?hash=b300b500-1111-2222-3333-77400dba2222",
        //      "wordpress_theme": "https://themeforest.net/short-dl?hash=100e3000-1111-2222-3333-8abc20007000"
        // }

        if($this->debugMode)
        {
            // We use this debug to track better the reach of daily download limit (which is 20)
            $debugMessage = '(Market API Response) Download response: '.print_r($response, TRUE);
            echo '<br />'.$debugMessage;
            $this->debugMessages[] = $debugMessage;
        }

        if(is_wp_error($response) || empty($response) || !empty($response['error']))
        {
            return FALSE;
        }

        if(!empty($response['wordpress_plugin']))
        {
            return $response['wordpress_plugin'];
        }

        if(!empty($response['wordpress_theme']))
        {
            return $response['wordpress_theme'];
        }


        return FALSE;
    }

    /**
     * Normalize an author.
     * @param  array $paramUser An array of API request values.
     * @return array A normalized array of values.
     */
    public function normalizeUser(array $paramUser)
    {
        return array(
            'type' => 'user',
            'username' => (!empty($paramUser['username'] ) ? $paramUser['username'] : ''),
            'country' => (!empty($paramUser['country'] ) ? $paramUser['country'] : ''),
            'city' => (!empty($paramUser['location'] ) ? $paramUser['location'] : ''),
            'sales' => (!empty($paramUser['sales'] ) ? $paramUser['sales'] : ''),
            'followers' => (!empty($paramUser['followers'] ) ? $paramUser['followers'] : ''),
            'avatar_url' => (!empty($paramUser['image'] ) ? $paramUser['image'] : ''),
            'banner_url' => (!empty($paramUser['homepage_image'] ) ? $paramUser['homepage_image'] : ''),
       );
    }

    /**
     * Normalize a license.
     * @note   it should match the EdgeAPI normalizeLicense method
     * @param  array $paramPurchase An array of API request values.
     * @param  string $paramBuyerUsername Buyers Username - we need it to match the output with Edge API
     * @return array A normalized array of values.
     */
    public function normalizeLicense(array $paramPurchase, $paramBuyerUsername)
    {
        $normalizedLicense = array(
            'buyer_username' => sanitize_text_field($paramBuyerUsername),
            'envato_item_id' => (!empty($paramPurchase['item']['id']) ? $paramPurchase['item']['id'] : ''),
            'envato_item_name' => (!empty($paramPurchase['item']['name']) ? $paramPurchase['item']['name'] : ''),
            'license_type' => (!empty($paramPurchase['license']) ? $paramPurchase['license'] : ''),
            'license_sold' => (!empty($paramPurchase['sold_at']) ? $paramPurchase['sold_at'] : ''),
            'license_supported' => (!empty($paramPurchase['supported_until']) ? $paramPurchase['supported_until'] : ''),
            'purchase_code' => (!empty($paramPurchase['code']) ? $paramPurchase['code'] : ''),
        );
        $normalizedLicense['license_purchase_date'] = (new \DateTime($normalizedLicense['license_sold']))->format('Y-m-d');
        $normalizedLicense['support_expiration_date'] = (new \DateTime($normalizedLicense['license_supported']))->format('Y-m-d');
        if((new \DateTime($normalizedLicense['license_supported']))->getTimestamp() < (new \DateTime('now'))->getTimestamp())
        {
            // Already expired
            $normalizedLicense['support_active'] = 0;
        } else
        {
            // Not yet expired
            $normalizedLicense['support_active'] = 1;
        }

        return $normalizedLicense;
    }

    /**
     * Normalize a plugin or a theme.
     * @param  array $paramItem An array of API request values.
     * @return array A normalized array of values.
     */
    public function normalizeItem(array $paramItem)
    {
        $arrNormalizedItem = FALSE;
        $requiredWPVersion = null;
        $testedWPVersion = null;
        $versions = array();

        // Set the required and tested WordPress version numbers.
        foreach($paramItem['attributes'] AS $key => $value)
        {
            if('compatible-software' === $value['name'])
            {
                foreach($value['value'] AS $version)
                {
                    $versions[] = str_replace('WordPress ', '', trim($version));
                }
                if(!empty($versions))
                {
                    $requiredWPVersion = $versions[count($versions)-1];
                    $testedWPVersion = $versions[0];
                }
                break;
            }
        }

        $type = '';
        if(!empty($paramItem['wordpress_plugin_metadata']))
        {
            $type = "plugin";
        } else if(!empty($paramItem['wordpress_theme_metadata']))
        {
            $type = "theme";
        }

        if($type == "plugin")
        {
            $arrNormalizedItem = array(
                'type' => 'plugin',
                'name' => (!empty($paramItem['wordpress_plugin_metadata']['plugin_name']) ? $paramItem['wordpress_plugin_metadata']['plugin_name'] : ''),
                'author' => (!empty($paramItem['wordpress_plugin_metadata']['author']) ? $paramItem['wordpress_plugin_metadata']['author'] : ''),
                'version' => (!empty($paramItem['wordpress_plugin_metadata']['version']) ? $paramItem['wordpress_plugin_metadata']['version'] : ''),
                'description' => (!empty($paramItem['wordpress_plugin_metadata']['description']) ? static::removeNonUnicode($paramItem['wordpress_plugin_metadata']['description']) : ''),
                'required_wp_version' => $requiredWPVersion,
                'tested_wp_version' => $testedWPVersion,
                'envato_author_username' => (!empty($paramItem['author_username']) ? $paramItem['author_username'] : ''),
                'envato_author_url' => (!empty($paramItem['author_url']) ? $paramItem['author_url'] : ''),
                'envato_author_image' => (!empty($paramItem['author_image']) ? $paramItem['author_image'] : ''),
                'envato_currency_symbol' => '$',
                'envato_currency_code' => 'USD',
                'envato_item_id' => (!empty($paramItem['id']) ? $paramItem['id'] : ''),
                'envato_item_name' => (!empty($paramItem['name']) ? $paramItem['name'] : ''),
                'envato_item_description' => (!empty($theme['description']) ? $theme['description'] : ''),
                'envato_item_price' => (!empty($paramItem['price_cents']) ? ($paramItem['price_cents'] * 0.01) : 0.0 ),
                'envato_item_sales' => (!empty($paramItem['number_of_sales']) ? $paramItem['number_of_sales'] : 0 ),
                'envato_item_rating' => (!empty($paramItem['rating']) ? $paramItem['rating'] : 0.0 ),
                'envato_item_rates' => (!empty($theme['rating_count']) ? $theme['rating_count'] : 0 ),
                'envato_item_published' => (!empty($paramItem['updated_at']) ? $paramItem['updated_at'] : ''),
                'envato_item_updated' => (!empty($paramItem['updated_at']) ? $paramItem['updated_at'] : ''),
                'envato_item_url' => (!empty($paramItem['url']) ? $paramItem['url'] : ''),
                'envato_item_thumb_url' => (!empty($paramItem['thumbnail_url']) ? $paramItem['thumbnail_url'] : ''),
                'envato_item_image_url' => (!empty($paramItem['previews']['landscape_preview']['landscape_url']) ? $paramItem['previews']['landscape_preview']['landscape_url'] : ''),
            );
        } else if($type == "theme")
        {
            $arrNormalizedItem = array(
                'type' => 'theme',
                'name' => (!empty($paramItem['wordpress_theme_metadata']['theme_name']) ? $paramItem['wordpress_theme_metadata']['theme_name'] : ''),
                'author' => (!empty($paramItem['wordpress_theme_metadata']['author_name']) ? $paramItem['wordpress_theme_metadata']['author_name'] : ''),
                'version' => (!empty($paramItem['wordpress_theme_metadata']['version']) ? $paramItem['wordpress_theme_metadata']['version'] : ''),
                'description' => (!empty($paramItem['wordpress_theme_metadata']['description']) ? static::removeNonUnicode($paramItem['wordpress_theme_metadata']['description']) : ''),
                'required_wp_version' => $requiredWPVersion,
                'tested_wp_version' => $testedWPVersion,
                'envato_author_username' => (!empty($paramItem['author_username'] ) ? $paramItem['author_username'] : ''),
                'envato_author_url' => (!empty($paramItem['author_url'] ) ? $paramItem['author_url'] : ''),
                'envato_author_image_url' => (!empty($paramItem['author_image'] ) ? $paramItem['author_image'] : ''),
                'envato_currency_symbol' => '$',
                'envato_currency_code' => 'USD',
                'envato_item_id' => (!empty($paramItem['id']) ? $paramItem['id'] : ''),
                'envato_item_name' => (!empty($paramItem['name'] ) ? $paramItem['name'] : ''),
                'envato_item_description' => (!empty($paramItem['description'] ) ? $paramItem['description'] : ''),
                'envato_item_price' => (!empty($paramItem['price_cents'] ) ? ($paramItem['price_cents'] * 0.01) : 0.0 ),
                'envato_item_sales' => (!empty($paramItem['number_of_sales'] ) ? $paramItem['number_of_sales'] : 0 ),
                'envato_item_rating' => (!empty($paramItem['rating'] ) ? $paramItem['rating'] : 0.0 ),
                'envato_item_rates' => (!empty($paramItem['rating_count'] ) ? $paramItem['rating_count'] : 0 ),
                'envato_item_published' => (!empty($paramItem['published_at'] ) ? $paramItem['published_at'] : ''),
                'envato_item_updated' => (!empty($paramItem['updated_at'] ) ? $paramItem['updated_at'] : ''),
                'envato_item_url' => (!empty($paramItem['url'] ) ? $paramItem['url'] : ''),
                'envato_item_thumb_url' => (!empty($paramItem['thumbnail_url'] ) ? $paramItem['thumbnail_url'] : ''),
                'envato_item_image_url' => (!empty($paramItem['previews']['landscape_preview']['landscape_url'] ) ? $paramItem['previews']['landscape_preview']['landscape_url'] : ''),
            );
        }

        return $arrNormalizedItem;
    }

    /**
     * Remove all non unicode characters in a string
     *
     * @since 1.0
     *
     * @param string $paramText The string to fix.
     * @return string
     */
    static private function removeNonUnicode($paramText)
    {
        $cleanText = preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $paramText);

        return $cleanText;
    }
}
endif;
