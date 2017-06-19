<?php
/**
 * Envato Toolkit AutoLoader to load classes for Envato Toolkit plugin
 * @note: Do not use static:: in this class, as it is maximum backwards compatible class for version check,
 *   and should work on Php 5.2, or even 5.0. All other classes can support Php 5.3+ or so.
 * @package EnvatoToolkit
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License
 */
namespace EnvatoToolkit\Models;

class AutoLoad
{
    protected $debugMode = 0;
    protected $conf = NULL;

    public function __construct(Configuration &$paramConf)
    {
        $this->conf = $paramConf;
    }

    /**
     * Load the model, view or controller from plugin folder (normal or test)
     * @param $paramClassOrInterface
     * @return bool
     * @throws \Exception
     */
    public function includeClassFile($paramClassOrInterface)
    {
        if (substr($paramClassOrInterface, 0, 14) !== 'EnvatoToolkit\\')
        {
            /* If the class does not lie under the "EnvatoToolkit" namespace,
             * then we can exit immediately.
             */
            return FALSE;
        }

        // Otherwise - process further
        $pluginsFolderPath = str_replace('\\', DIRECTORY_SEPARATOR, WP_PLUGIN_DIR).DIRECTORY_SEPARATOR;
        $relativeFolderAndFilePath = $this->getFilePathAndNameFromNamespaceAndClass($paramClassOrInterface);

        // DEBUG
        if($this->debugMode == 1)
        {
            echo '<br /><br />'.$paramClassOrInterface.' class/interface is called. It&#39;s relative path is '.$relativeFolderAndFilePath;
            echo '<br />Plugin folder path: '.$pluginsFolderPath.$relativeFolderAndFilePath;
        }

        // Load classes by path
        if(is_readable($pluginsFolderPath.$relativeFolderAndFilePath))
        {
            // Check for main folder in local plugin folder
            // It's a regular class / interface
            require_once ($pluginsFolderPath.$relativeFolderAndFilePath);
            return TRUE;
        } else
        {
            // File do not exist or is not readable
            $validClassOrInterface = sanitize_text_field($paramClassOrInterface);
            throw new \Exception(sprintf(
                __('Unable to load &#39;%s&#39; class/interface nor from plugin root folder with &#39;%s&#39; path provided.', 'envato-toolkit'),
                $validClassOrInterface, $relativeFolderAndFilePath));
        }
    }

    /**
     * Example:
     *   Org class name: EnvatoToolkit\Models\class.EnvatoAPIManager
     *   Class name: class.EnvatoAPIManager
     *   File name: EnvatoToolkit\Models\class.class.EnvatoAPIManager.php
     * @param $paramClassOrInterface - a namespace
     * @return string
     */
    private function getFilePathAndNameFromNamespaceAndClass($paramClassOrInterface)
    {
        $validClassOrInterface = sanitize_text_field($paramClassOrInterface);

        $className = ltrim($validClassOrInterface, '\\');
        $filePath  = "";
        $lastNamespacePosition = strripos($className, '\\');
        // If namespace is used
        if ($lastNamespacePosition !== FALSE)
        {
            // Then separate namespace and class name
            $namespace = substr($className, 0, $lastNamespacePosition);

            // Replace 'EnvatoToolkit' folder name with $this->pluginDirname
            // Note: we need that for the scenario in case if the plugin stays in 'plugins/envato-toolkit' folder instead of 'plugins/EnvatoToolkit'
            $namespace = str_replace('EnvatoToolkit\\', $this->conf->getPluginDirname().'\\', $namespace);

            $className = substr($className, $lastNamespacePosition + 1);
            $filePath  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
        }

        // Check if this is an interface or a class and set specific prefix to it
        if(isset($className[0]) && $className[0] == "i")
        {
            // This is an interface (i.e. iNSModel) - add "interface." prefix to the filename
            $fileName = 'interface.'.$className.'.php';
        } else
        {
            // This is a class (i.e. NSModel) - add "class." prefix to the filename
            $fileName = 'class.'.$className.'.php';
        }

        // DEBUG
        if($this->debugMode == 2)
        {
            echo "<br /><br />Org class name: {$validClassOrInterface}";
            echo "<br />Class name: {$className}<br />";
            echo "File path and name: {$filePath}{$fileName}";
        }

        return $filePath.$fileName;
    }
}