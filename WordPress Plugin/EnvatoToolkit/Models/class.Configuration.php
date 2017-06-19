<?php
/**
 * Envato Toolkit Configuration
 * The purpose of this class is to handle both - Envato Market API and Envato Edge API
 *
 * @package Envato Toolkit
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License
 */
namespace EnvatoToolkit\Models;

class Configuration
{
    private $internalWPDB                 = NULL;
    private $blogId                       = 1;

    private $requiredPHPVersion           = '5.4.0';
    private $currentPHPVersion            = '0.0.0';
    private $requiredWPVersion            = 4.6;
    private $currentWPVersion             = 0.0;
    private $pluginVersion                = 0.0;
    private $networkEnabled               = FALSE;

    private $pluginPathWithFilename       = "";
    private $pluginPath                   = "";
    private $pluginBasename               = "";
    private $pluginDirname                = "";
    private $pluginLangRelPath            = "";
    private $globalLangPath               = "";
    private $pluginURL                    = "";

	public function __construct(
        \wpdb &$paramWPDB, $paramBlogId, $paramRequiredPHPVersion, $paramCurrentPHPVersion, $paramRequiredWPVersion,
        $paramCurrentWPVersion, $paramPluginVersion, $paramPluginPathWithFilename
    ) {
        // Makes sure the plugin is defined before trying to use it, because by default it is available only for admin section
        if(!function_exists('is_plugin_active_for_network'))
        {
            require_once(ABSPATH.'/wp-admin/includes/plugin.php');
        }

        $this->internalWPDB = $paramWPDB;
        $this->blogId = absint($paramBlogId);

        $this->requiredPHPVersion   = !is_array($paramRequiredPHPVersion) ? preg_replace('[^0-9\.,]', '', $paramRequiredPHPVersion) : '5.4.0';
        $this->currentPHPVersion    = !is_array($paramCurrentPHPVersion) ? preg_replace('[^0-9\.,]', '', $paramCurrentPHPVersion) : '0.0.0';
        $this->requiredWPVersion    = !is_array($paramRequiredWPVersion) ? preg_replace('[^0-9\.,]', '', $paramRequiredWPVersion) : 4.6;
        $this->currentWPVersion     = !is_array($paramCurrentWPVersion) ? preg_replace('[^0-9\.,]', '', $paramCurrentWPVersion) : 0.0;
        $this->pluginVersion        = !is_array($paramPluginVersion) ? preg_replace('[^0-9\.,]', '', $paramPluginVersion) : 0.0;

        // We must use plugin_basename here, despite that we used full path for activation hook, because in database the plugin is still saved UNIX like:
        // network_db_prefix_options:
        //      Row: active_plugins
        //      Value (in JSON): <..>;i:0;s:31:"EnvatoToolkit/EnvatoToolkit.php";<..>
        $this->networkEnabled        = is_plugin_active_for_network(plugin_basename($paramPluginPathWithFilename));

        // Note 1: It's ok to use 'sanitize_text_field' function here,
        //       because this function does not escape or remove the '/' char in path.
        // Note 2: We use __FILE__ to make sure that we are not dependant on plugin folder name
        // Note 3: WordPress constants overview - http://wpengineer.com/2382/wordpress-constants-overview/
        // Demo examples (__FILE__ = $this->pluginFolderAndFile):
        // 1. __FILE__ => /GitHub/EnvatoToolkit/wp-content/plugins/EnvatoToolkit/CarRentalSystem.php
        // 2. plugin_dir_path(__FILE__) => /GitHub/EnvatoToolkit/wp-content/plugins/EnvatoToolkit/ (with trailing slash at the end)
        // 3. plugin_basename(__FILE__) => EnvatoToolkit/EnvatoToolkit.php (used for active plugins list in WP database)
        // 4. dirname(plugin_basename((__FILE__)) => EnvatoToolkit
        // 5. pluginLangRelPath used for load_textdomain, i.e. EnvatoToolkit/Languages/ (the correct example is WITH the ending trailing slash)
        $this->pluginPathWithFilename = sanitize_text_field($paramPluginPathWithFilename); // Leave directory separator UNIX like here, used in WP hooks
        $this->pluginPath = str_replace('\\', DIRECTORY_SEPARATOR, plugin_dir_path($this->pluginPathWithFilename));
        $this->pluginBasename = plugin_basename($this->pluginPathWithFilename); // Leave directory separator UNIX like here, used in WP database
        $this->pluginDirname = dirname(plugin_basename($this->pluginPathWithFilename));
        $this->pluginLangRelPath = $this->pluginDirname.'/Languages/';
        $this->globalLangPath = WP_LANG_DIR.DIRECTORY_SEPARATOR.'EnvatoToolkit'.DIRECTORY_SEPARATOR;

        // esc_url replaces ' and & chars with &#39; and &amp; - but because we know that exact path,
        // we know it does not contains them, so we don't need to have two versions esc_url and esc_url_raw
        // Demo examples (__FILE__ = $this->pluginFolderAndFile):
        // 1. plugin_dir_url(__FILE__) => http://envatotoolkit.com/wp-content/plugins/EnvatoToolkit/
        $this->pluginURL = esc_url(plugin_dir_url($this->pluginPathWithFilename));
	}

    public function getInternalWPDB()
    {
        return $this->internalWPDB;
    }

    public function getBlogId()
    {
        return $this->blogId;
    }

    public function getRequiredPHPVersion()
    {
        return $this->requiredPHPVersion;
    }

    public function getCurrentPHPVersion()
    {
        return $this->currentPHPVersion;
    }

    public function getRequiredWPVersion()
    {
        return $this->requiredWPVersion;
    }

    public function getCurrentWPVersion()
    {
        return $this->currentWPVersion;
    }

    public function getPluginVersion()
    {
        return $this->pluginVersion;
    }

    public function isNetworkEnabled()
    {
        return $this->networkEnabled;
    }


    /*** PATH METHODS: START ***/
    public function getPluginPathWithFilename()
    {
        return $this->pluginPathWithFilename;
    }

    public function getPluginPath()
    {
        return $this->pluginPath;
    }

    public function getPluginBasename()
    {
        return $this->pluginBasename;
    }

    public function getPluginDirname()
    {
        return $this->pluginDirname;
    }

    /**
     * pluginLangRelPath used for load_textdomain, i.e. EnvatoToolkit/Languages
     * @note - Do not use DIRECTORY_SEPARATOR for this file, as it used for WP-TEXT-DOMAIN definition and always should be the same
     * @return string
     */
    public function getPluginLangRelPath()
    {
        return $this->pluginLangRelPath;
    }

    public function getGlobalLangPath()
    {
        return $this->globalLangPath;
    }

    public function getAssetsPath()
    {
        return $this->pluginPath.'Assets'.DIRECTORY_SEPARATOR;
    }

    public function getTemplatesPath()
    {
        return $this->pluginPath.'Templates'.DIRECTORY_SEPARATOR;
    }


    /*** URL METHODS: START ***/
    public function getPluginURL()
    {
        return $this->pluginURL;
    }

    public function getAssetsURL()
    {
        return $this->pluginURL.'Assets/';
    }
}