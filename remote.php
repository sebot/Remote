<?php
/**
 * Remote bootstrap file
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 *
 * @wordpress-plugin
 * Plugin Name: Remote
 * Plugin URI:  https://42geeks.gg/
 * Description: a Plugin to handle all sites using Fetcher Theme remotely.
 * Author:      Sebo <sebo@42geeks.gg>
 * Version:     1.0
 * Author URI:  https://42geeks.gg/
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: remote
 */

// If this file is accessed directory, then abort.
if (! defined('WPINC')) {
    die;
}

// composer psr-4
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    include_once dirname(__FILE__) . '/vendor/autoload.php';
}

if (class_exists('remote\\Remote')) {
    define('REMOTE_VERSION', '1.0');

    // ensure path is only set once
    $pluginRootPath = plugin_dir_path(__FILE__);
    $pluginRootUri  = plugin_dir_url(__FILE__);

    // init acf
    if (! function_exists('acf_add_options_page')) {
        include_once dirname(__FILE__) . '/helper/acfinit.php';
        add_filter('acf/settings/show_admin', '__return_false');
    }

    // run plugin
    $plugin = new \remote\Remote($pluginRootPath, $pluginRootUri);
    
    register_activation_hook(__FILE__, [$plugin, 'activatePlugin']);
    register_deactivation_hook(__FILE__, [$plugin, 'deactivatePlugin']);
}