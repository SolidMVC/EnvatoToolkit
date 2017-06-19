<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
wp_enqueue_style('envato-toolkit-admin');
?>
<div class="envato-toolkit-wrapper">
<h1>Envato Toolkit - Search Input</h1>
<div class="content">
<form name="envato_form" action="" method="POST" class="envato-form">
    <h2>Enter Your Envato Details</h2>
    <p>
        <label for="conf_envato_username">Your Envato Username:</label><br />
        <input name="conf_envato_username" id="conf_envato_username" type="text" value="<?php print($envatoUsername); ?>"
               onfocus="if(this.value === 'YOUR_ENVATO_USERNAME') {this.value=''}"
               onblur="if(this.value === ''){this.value ='YOUR_ENVATO_USERNAME'}"
        /><br />
        <em>(Your username can be found in Envato User Menu -&gt; Profile, i.e. TheGreatUser)</em>
    </p>
    <p>
        <label for="conf_envato_api_key">Your Envato API Key:</label><br />
        <input name="conf_envato_api_key" id="conf_envato_api_key" type="text" value="<?php print($envatoAPIKey); ?>"
               onfocus="if(this.value === 'YOUR_ENVATO_API_KEY') {this.value=''}"
               onblur="if(this.value === ''){this.value ='YOUR_ENVATO_API_KEY'}"
        /><br />
        <em>(You can create an API Key from your Envato &quot;User Menu&quot; -&gt; &quot;Settings&quot; -&gt; &quot;API Keys&quot;
            in any marketplace (i.e. CodeCanyon.net).<br />
            If you don&#39;t have any API keys listed there, you need to create a new one, by entering a new label,
            i.e. &#39;Native Envato API&#39; and click the &#39;Generate API Key&#39; button.
            API Key Example - 0v11zxcv2000asdf3000ghjk4qwer000)</em>
    </p>
    <p>
        <label for="conf_envato_personal_token">Your Envato Personal Token:</label><br />
        <input name="conf_envato_personal_token" id="conf_envato_personal_token" type="text" value="<?php print($envatoPersonalToken); ?>"
               onfocus="if(this.value === 'YOUR_ENVATO_PERSONAL_TOKEN') {this.value=''}"
               onblur="if(this.value === ''){this.value ='YOUR_ENVATO_PERSONAL_TOKEN'}"
        /><br />
        <em>(You can create your personal token
            <a href="https://build.envato.com/create-token/?purchase:download=t&purchase:verify=t&purchase:list=t" target="_blank">here</a>.
            Make sure that these permission are allowed for token:<br />
            &quot;View and search Envato sites&quot;, &quot;Download your purchased items&quot;,
            &quot;List purchases you&#39;ve made&quot; and &quot;Verify purchases you&#39;ve made&quot;<br />
            Personal token example - pAA0aBCdeFGhiJKlmNOpqRStuVWxyZ44)</em>
    </p>
    <div class="clear">&nbsp;</div>

    <!-- ---------------------------------------------------------- -->
    <h2>1. Get Details About Purchase Code</h2>
    <p>
        <label for="target_purchase_code">Target Purchase Code:</label><br />
        <input type="text" id="target_purchase_code" name="target_purchase_code" value="<?php print($targetPurchaseCode); ?>" /><br />
        <em>(Leave blank to skip, ex. one of your customer purchase code that you want to validate)</em>
    </p>
    <div class="clear">&nbsp;</div>

    <!-- ---------------------------------------------------------- -->
    <h2>2. Get Details About Other Envato User</h2>
    <p>
        <label for="target_username">Target Username:</label><br />
        <input type="text" id="target_username" name="target_username" value="<?php print($targetUsername); ?>" /><br />
        <em>(Leave blank to skip, ex. &quot;ThemeFusion&quot; - the team behind &quot;Avada&quot; - best-selling theme)</em>
    </p>
    <div class="clear">&nbsp;</div>

    <!-- ---------------------------------------------------------- -->
    <h2>3. Check for Update of Plugin You Bought</h2>
    <p>
        <label for="target_plugin_id">Plugin ID:</label><br />
        <input type="text" id="target_plugin_id" name="target_plugin_id" value="<?php print($targetPluginId); ?>" /><br />
        <em>(Leave blank to skip, ex. &quot;2201708&quot; - ID of &quot;WordPress Social Stream&quot; - best-selling social plugin)</em>
    </p>
    <p>
        <label for="installed_plugin_version">Installed Plugin Version:</label><br />
        <input type="text" id="installed_plugin_version"  name="installed_plugin_version" value="<?php print($installedPluginVersion); ?>" /><br />
        <em>(Leave blank to skip, ex. &quot;1.6.0&quot; - previous version number of &quot;WordPress Social Stream&quot;)</em>
    </p>
    <div class="clear">&nbsp;</div>

    <!-- ---------------------------------------------------------- -->
    <h2>4.Check for Update of Theme You Bought</h2>
    <p>
        <label for="target_theme_id">Theme ID:</label><br />
        <input type="text" id="target_theme_id" name="target_theme_id" value="<?php print($targetThemeId); ?>" /><br />
        <em>(Leave blank to skip, ex. &quot;2833226&quot; - ID of &quot;Avada&quot; - best-selling theme)</em>
    </p>
    <p>
        <label for="installed_theme_version">Installed Theme Version:</label><br />
        <input type="text" id="installed_theme_version" name="installed_theme_version" value="<?php print($installedThemeVersion); ?>" /><br />
        <em>(Leave blank to skip, ex. &quot;3.9.3&quot; - previous version number of &quot;Avada&quot; - best-selling theme)</em>
    </p>
    <div class="clear">&nbsp;</div>

    <!-- ---------------------------------------------------------- -->
    <h2>5. Get Envato Item Id of Purchased Plugin</h2>
    <p>
        <label for="target_plugin_name">Target Plugin Name:</label><br />
        <input type="text" id="target_plugin_name" name="target_plugin_name" value="<?php print($targetPluginName); ?>" /><br />
        <em>(Leave blank to skip, ex. &quot;WordPress Social Stream&quot;)</em>
    </p>
    <p>
        <label for="target_plugin_author">Target Plugin Author:</label><br />
        <input type="text" id="target_plugin_author" name="target_plugin_author" value="<?php print($targetPluginAuthor); ?>" /><br />
        <em>(Leave blank to skip, ex. &quot;Lee Chestnutt&quot; - the Author of &quot;WordPress Social Stream&quot;)</em><br />
        <em>(<strong>Note:</strong> Keep in mind that the Envato API here checks ONLY (!) for plugin&#39;s install .php file&#39;s
            &quot;Author:&quot; metadata value, and not for Envato Author Username. They can be different.</em>
    </p>
    <div class="clear">&nbsp;</div>

    <!-- ---------------------------------------------------------- -->
    <h2>6. Get Envato Item Id of Purchased Theme</h2>
    <p>
        <label for="target_theme_name">Target Theme Name:</label><br />
        <input type="text" id="target_theme_name" name="target_theme_name" value="<?php print($targetThemeName); ?>" /><br />
        <em>(Leave blank to skip, ex. &quot;Avada&quot;)</em>
    </p>
    <p>
        <label for="target_theme_author">Target Theme Author:</label><br />
        <input type="text" id="target_theme_author" name="target_theme_author" value="<?php print($targetThemeAuthor); ?>" /><br />
        <em>(Leave blank to skip, ex. &quot;ThemeFusion&quot; - the Author of &quot;Avada&quot;)</em><br />
        <em>(<strong>Note:</strong> Keep in mind that the Envato API here checks ONLY (!) for theme&#39;s style.css file&#39;s
            &quot;Author:&quot; metadata value, and not for Envato Author Username. They can be different.</em>
    </p>
    <div class="submit-buttons">
        <input type="submit" name="envato_check" value="Check for details" />
        <input type="submit" name="fill_demo_data" value="Fill demo data" />
    </div>
</form>
</div>
</div>