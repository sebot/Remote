<?php

// Define path and URL to the ACF plugin.
define('REMOTE_ACF_PATH', $pluginRootPath . '/vendor/acf/');
define('REMOTE_ACF_URL', $pluginRootUri . '/vendor/acf/');

// Include the ACF plugin.
include_once REMOTE_ACF_PATH . 'acf.php';

// Customize the url setting to fix incorrect asset URLs.
add_filter('acf/settings/url', 'remoteAcfSettingsUrl');
function remoteAcfSettingsUrl($url)
{
    return REMOTE_ACF_URL;
}
