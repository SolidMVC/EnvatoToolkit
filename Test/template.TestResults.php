<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<head>
    <title>Envato Toolkit Library - Test Results</title>
    <style type="text/css">
        body {
            text-align: center;
            width: 100%;
        }
        h1 {
            margin: 20px;
        }
        div.content {
            text-align: center;
            margin: 0 auto;
            max-width: 840px;
        }
        .clear {
            display:block;
            clear:both;
        }
        .results-box {
            text-align: left;
            margin: 20px;
            padding: 10px 20px 10px 20px;
            border-radius: 13px 13px 13px 13px;
            -moz-border-radius: 13px 13px 13px 13px;
            -webkit-border-radius: 13px 13px 13px 13px;
            border: 1px solid #000000;
            background-color: #e9e7d0;
        }
        .results-box p em {
            font-size: 12px;
            color: gray;
        }
        .results-box .action-buttons button {
            height: 40px;
            text-align: center;
            min-width: 200px;
            font-size: 16px;
            font-weight: bold;
            background-color: white;
            cursor: pointer;
        }
        .results-box .action-buttons button:hover {
            background-color: #8ab16f;
        }
        div.action-buttons {
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>
<body>
<h1>Envato Toolkit - Test Results</h1>
<div class="content">
    <div class="results-box">
        <h2>Details about you</h2>
        <p>
            <strong>List of all different plugins you bought:</strong><br />
            <?php foreach($plugins AS $pluginId => $plugin): ?>
                <?php print('Plugin Id: '.$pluginId.', Name: '.$plugin['name']); ?>, Licenses:<br />
                <?php foreach($plugin['licenses'] AS $license): ?>
                    <em>
                    Code: <?php print($license['purchase_code']); ?>,
                    Type: <?php print($license['license_type']); ?>,
                    Purchased: <?php print($license['license_purchase_date']); ?>,
                    Expires: <?php print($license['support_expiration_date']); ?>,
                    Status: <?php print($license['support_active'] == 1 ? "Supported" : "Support Expired"); ?>
                    </em>
                    <br />
                <?php endforeach; ?>
                <br />
            <?php endforeach; ?>
        </p>
        <p>
            <strong>List of all different themes you bought:</strong><br />
            <?php foreach($themes AS $themeId => $theme): ?>
                <?php print('Theme Id: '.$themeId.', Name: '.$theme['name']); ?>, Licenses:<br />
                <?php foreach($theme['licenses'] AS $license): ?>
                    <em>
                    Code: <?php print($license['purchase_code']); ?>,
                    Type: <?php print($license['license_type']); ?>,
                    Purchased: <?php print($license['license_purchase_date']); ?>,
                    Expires: <?php print($license['support_expiration_date']); ?>,
                    Status: <?php print($license['support_active'] == 1 ? "Supported" : "Support Expired"); ?>
                    </em>
                    <br />
                <?php endforeach; ?>
                <br />
            <?php endforeach; ?>
        </p>
        <p>
            <strong>Your summary:</strong><br />
            Your location is <strong><?php print($authorCity); ?></strong>, <strong><?php print($authorCountry); ?></strong>.
            You&#39;ve sold your items <?php print($authorSales); ?> times
            and you have <?php print($authorFollowers); ?> followers on Envato.
        </p>
        <div class="clear">&nbsp;</div>

        <!-- ---------------------------------------------------------- -->
        <?php if($targetPurchaseCode != ''): ?>
            <h2>1. Your Customer&#39;s License Details</h2>
            <?php if($showLicenseDetails): ?>
                <ul>
                    <li>Purchase Code: <?php print($targetPurchaseCode); ?></li>
                    <li>Is Valid License: <?php print($isValidTargetLicense ? 'Yes' : 'No'); ?></li>
                    <li>Buyer Username: <?php print($targetLicenseBuyer); ?></li>
                    <li>License Type: <?php print($targetLicenseType); ?></li>
                    <li>Purchased At: <?php print($targetLicensePurchaseDate); ?></li>
                    <li>Supported Until: <?php print($targetLicenseSupportExpiration); ?></li>
                    <li>Status: <?php print($targetLicenseSupportActive == 1 ? "Supported" : "Support Expired"); ?></li>
                </ul>
            <?php endif; ?>
            <div class="clear">&nbsp;</div>
        <?php endif; ?>

        <!-- ---------------------------------------------------------- -->
        <?php if($targetUsername != ''): ?>
            <h2>2. Details About Target Envato User - <?php print($targetUsername); ?></h2>
            <p>
                <strong><?php print($targetUsername); ?></strong> is located in <strong><?php print($targetUserCity); ?></strong>,
                <strong><?php print($targetUserCountry); ?></strong>. He sold his items <?php print($targetUserSales); ?> times
                and has <?php print($targetUserFollowers); ?> followers on Envato.
            </p>
            <div class="clear">&nbsp;</div>
        <?php endif; ?>

        <!-- ---------------------------------------------------------- -->
        <?php if($targetPluginId > 0): ?>
            <h2>3. Status of Purchased Plugin ID - <?php print($targetPluginId); ?></h2>
            <ul>
                <li>Plugin Name: <?php print($nameOfTargetPluginId); ?></li>
                <li>Plugin Update Available: <?php print($pluginUpdateAvailable ? 'Yes' : 'No'); ?></li>
                <li>Installed Plugin Version: <?php print($installedPluginVersion); ?></li>
                <li>Available Plugin Version: <?php print($availablePluginVersion); ?></li>
                <?php if($pluginUpdateDownloadUrl != ''): ?>
                    <li>Plugin Update Download URL:
                        <a href="<?php print($pluginUpdateDownloadUrl); ?>" target="_blank" title="Download newest version">Download newest version</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="clear">&nbsp;</div>
        <?php endif; ?>

        <!-- ---------------------------------------------------------- -->
        <?php if($targetThemeId > 0): ?>
            <h2>4. Status of Purchased Theme ID - <?php print($targetThemeId); ?></h2>
            <ul>
                <li>Theme Name: <?php print($nameOfTargetThemeId); ?></li>
                <li>Theme Update Available: <?php print($themeUpdateAvailable ? 'Yes' : 'No'); ?></li>
                <li>Installed Theme Version: <?php print($installedThemeVersion); ?></li>
                <li>Available Theme Version: <?php print($availableThemeVersion); ?></li>
                <?php if($themeUpdateDownloadUrl != ''): ?>
                    <li>Theme Update Download URL:
                        <a href="<?php print($themeUpdateDownloadUrl); ?>" target="_blank" title="Download newest version">Download newest version</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="clear">&nbsp;</div>
        <?php endif; ?>

        <!-- ---------------------------------------------------------- -->
        <?php if($targetPluginName != '' && $targetPluginAuthor != ''): ?>
            <h2>5. Envato Item Id of Purchased Plugin</h2>
            <ul>
                <li>Searched for Name: <?php print($targetPluginName); ?></li>
                <li>Searched for Author: <?php print($targetPluginAuthor); ?></li>
                <li>Found Plugin Id: <?php print($foundPluginId); ?></li>
            </ul>
            <div class="clear">&nbsp;</div>
        <?php endif; ?>

        <!-- ---------------------------------------------------------- -->
        <?php if($targetThemeName != '' && $targetThemeAuthor != ''): ?>
            <h2>6. Envato Item Id of Purchased Theme</h2>
            <ul>
                <li>Searched for Name: <?php print($targetThemeName); ?></li>
                <li>Searched for Author: <?php print($targetThemeAuthor); ?></li>
                <li>Found Theme Id: <?php print($foundThemeId); ?></li>
            </ul>
        <?php endif; ?>

        <div class="action-buttons">
            <button type="submit" class="back-button" onclick="window.location.href='<?php print($goBackUrl); ?>'">Back</button>
        </div>
    </div>
</div>
</body>