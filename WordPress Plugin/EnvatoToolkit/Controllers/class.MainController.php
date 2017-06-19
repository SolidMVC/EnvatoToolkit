<?php
/**
 * ET Main controller
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @description This file is the main entry point to the plugin that will handle all requests from WordPress
 * and add actions, filters, etc. as necessary. So we simply declare the class and add a constructor.
 * @note 1: In this class we use full qualifiers (without 'use', except for Configuration and AutoLoad, which are already included).
 *          We do this, to ensure, that nobody will try to use any of these classes before the autoloader is called.
 * @note 2: This class must not depend on any static model
 * @note 3: All Controllers and Models should have full path in the class
 * @note 4: Fatal errors on this file cannot be translated
 * @package EnvatoToolkit
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License
 */
namespace EnvatoToolkit\Controllers;
use EnvatoToolkit\Models\AutoLoad;
use EnvatoToolkit\Models\Configuration;

final class MainController
{
    private static $dependenciesLoaded  = FALSE;
    protected $conf                     = NULL;
    protected $canProcess               = FALSE;
    protected $errors                   = array();

	public function __construct(Configuration &$paramConf)
    {
        $this->conf = $paramConf;

        //
        // 1. Check plug-in requirements - if not passed, then exit
        //
        if (!$this->checkRequirements())
        {
            $this->canProcess = FALSE;
        } else
        {
            $this->canProcess = TRUE;
        }

        //
        // 2. Load dependencies. Autoloader. This must be in constructor to know the file paths.
        // Note: Singleton pattern used.
        //
        if($this->canProcess && self::$dependenciesLoaded === FALSE)
        {
            $this->loadDependencies();
            self::$dependenciesLoaded = TRUE;
        }
	}

    /**
     * Throw error on object clone.
     *
     * Cloning instances of the class is forbidden.
     *
     * @since 1.0
     * @return void
     */
    public function __clone()
    {
        add_action('admin_notices', array(&$this, 'displayCloningIsForbiddenNotice'));
    }

    /**
     * Disable unserializing of the class
     *
     * Unserializing instances of the class is forbidden.
     *
     * @since 1.0
     * @return void
     */
    public function __wakeup()
    {
        add_action('admin_notices', array(&$this, 'displayUnserializingIsForbiddenNotice'));
    }

    /**
     * Display Php version requirement notice.
     *
     * @access static
     */
    public function displayPhpVersionRequirementNotice()
    {
        echo '<div class="envato-toolkit-error"><div id="message" class="error"><p><strong>';
        echo sprintf(
            __('Sorry, Envato Toolkit requires PHP %s or higher. Your current PHP version is %s. Please upgrade your server Php version.', 'envato-toolkit'),
            $this->conf->getRequiredPHPVersion(), $this->conf->getCurrentPHPVersion()
        );
        echo '</strong></p></div></div>';
    }

    /**
     * Display WordPress version requirement notice.
     *
     * @access static
     */
    public function displayWPVersionRequirementNotice()
    {
        echo '<div class="envato-toolkit-error"><div id="message" class="error"><p><strong>';
        echo sprintf(
            __('Sorry, %s requires WordPress %s or higher. Your current WordPress version is %s. Please upgrade your WordPress setup.', 'envato-toolkit'),
            $this->conf->getRequiredWPVersion(), $this->conf->getCurrentWPVersion()
        );
        echo '</strong></p></div></div>';
    }

    /**
     * Display WordPress version requirement notice.
     *
     * @access static
     */
    public function displayCloningIsForbiddenNotice()
    {
        echo '<div class="envato-toolkit-error"><div id="message" class="error"><p><strong>';
        echo __('Error in __clone() method: Cloning instances of the class in the Envato Toolkit is forbidden.', 'envato-toolkit');
        echo '</strong></p></div></div>';
    }

    /**
     * Display WordPress version requirement notice.
     *
     * @access static
     */
    public function displayUnserializingIsForbiddenNotice()
    {
        echo '<div class="envato-toolkit-error"><div id="message" class="error"><p><strong>';
        echo __('Error in __wakeup() method: Unserializing instances of the class in the Envato Toolkit is forbidden.', 'envato-toolkit');
        echo '</strong></p></div></div>';
    }


    public function displaySessionsAreDisabledInServerNotice()
    {
        echo '<div class="envato-toolkit-error"><div id="message" class="error"><p><strong>';
        echo __('Warning: Sessions are disabled in your server configuration. Please enabled sessions.
        As a slow workaround you can use virtual session via database tables, but that is not recommended.','envato-toolkit');
        echo '</strong></p></div></div>';
    }

    protected function processError($paramName, $paramErrorMessage)
    {
        if($this->checkDisplayDebug())
        {
            $validName = esc_html($paramName);
            $validErrorMessage = esc_html($paramErrorMessage);
            // Load errors only in local or global debug mode
            $this->errors[] = sprintf(__('Error in %s method: ', 'envato-toolkit'), $validName).$validErrorMessage;

            // Doesn't always work (maybe due to fact, that 'admin_notices' has to be registered not later than X point in code)
            //add_action('admin_notices', array(&$this, 'displayErrors'));

            // Works
            $validErrorMessage = '<div class="envato-toolkit-error"><div id="message" class="error"><p>'.$validErrorMessage.'</p></div></div>';
            _doing_it_wrong($validName, $validErrorMessage, $this->conf->getPluginVersion());
        }
    }

    protected function checkDisplayDebug()
    {
        $inDebug = defined('WP_DEBUG') && WP_DEBUG == TRUE && defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY == TRUE;

        return $inDebug;
    }

    /**
     * Checks that the WordPress setup meets the plugin requirements
     * @return boolean
     */
    protected function checkRequirements()
    {
        // Check Php version
        if(version_compare($this->conf->getCurrentPHPVersion(), $this->conf->getRequiredPHPVersion(), '>=') === FALSE)
        {
            // WordPress version does not meet plugin requirements
            add_action('admin_notices', array(&$this, 'displayPhpVersionRequirementNotice'));

            return FALSE;
        }

        // Check WordPress version
        if(version_compare($this->conf->getCurrentWPVersion(), $this->conf->getRequiredWPVersion(), '>=') === FALSE)
        {
            // WordPress version does not meet plugin requirements
            add_action('admin_notices', array(&$this, 'displayWPVersionRequirementNotice'));

            return FALSE;
        }

        return TRUE;
    }

    /**
     * Load only those classes of which instances are created
     */
    protected function loadDependencies()
    {
        $objAutoload = new AutoLoad($this->conf);
        spl_autoload_register(array(&$objAutoload, 'includeClassFile'));
    }


    /**
     * Note: Do not add try {} catch {} for this block, as this method includes WordPress hooks.
     *   For those hooks handling we have individual methods in this class bellow, where the try {} catch {} is used.
     */
    public function run()
    {
        if($this->canProcess)
        {
            // back end
            add_action('admin_menu', array(&$this, 'loadAdmin'));
        }
    }


	public function loadAdmin()
	{
        if($this->canProcess)
        {
            $menuPosition = 94; // Can be changed if needed

            // Set session cookie before any headers will be sent. Start the session, because:
            // 1. Search system uses session to temporary save your work until you close your tab
            // 2. In case if we will need, we will save admin ok/error messages in sessions for reading after page refresh
            // Note: Requires Php 5.4+
            if(session_status() !== PHP_SESSION_ACTIVE)
            {
                session_start(); // Starts a new session or resumes an existing session
            }

            // Traditional WordPress plugin locale filter
            // Note 1: We don't want to include the rows bellow to language model class, as they are a part of controller
            // Note 2: Keep in mind that, if the translation do not exist, plugin will load a default english translation file
            $locale = apply_filters('plugin_locale', get_locale(), 'envato-toolkit');

            // Load textdomain
            // Loads MO file into the list of domains.
            // Note 1: If the domain already exists, the inclusion will fail. If the MO file is not readable, the inclusion will fail.
            // Note 2: On success, the MO file will be placed in the $l10n global by $domain and will be an gettext_reader object.

            // wp-content/languages/EnvatoToolkit/lt_LT.mo
            load_textdomain('envato-toolkit', $this->conf->getGlobalLangPath().$locale.'.mo');
            // wp-content/plugins/EnvatoToolkit/Languages/lt_LT.mo
            load_plugin_textdomain('envato-toolkit', FALSE, $this->conf->getPluginLangRelPath());

            // Load textdomain
            load_plugin_textdomain('envato-toolkit', FALSE, dirname(plugin_basename( __FILE__ )) . '/Languages/');

            // Plugin design style
            wp_register_style('envato-toolkit-admin', $this->conf->getAssetsURL().'CSS/style.Admin.css');

            add_menu_page(
                __('Envato Toolkit', 'envato-toolkit'), __('Envato Toolkit', 'envato-toolkit'),
                "update_plugins", "envato-toolkit-menu", array(&$this, "printSearch"), '', $menuPosition
            );

            // Print a warning if sessions are not supported in the server, and suggest to use _SESSIONS plugin
            if(session_status() == PHP_SESSION_DISABLED)
            {
                add_action('admin_notices', array(&$this, 'displaySessionsAreDisabledInServerNotice'));
            }
        }
	}

    // Single Status
    public function printSearch()
    {
        try
        {
            // Use full namespace, as we want to load it by autoloader only in this class as-well
            $objSearchController = new \EnvatoToolkit\Controllers\SearchController($this->conf);
            $objSearchController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }
}