<?php
/**
 * Remote
 *  
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace remote\core;

use remote\interfaces\RemoteAsset;
use remote\core\RemoteObjects;
use remote\Remote;

/**
 * Class RemoteAssets - Load all required assets
 * 
 * @category Theme
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class RemoteAssets extends RemoteObjects implements RemoteAsset
{
    public static $assets = [];

    /**
     * Empty
     */
    public function __construct()
    {
    }

    /**
     * Enqueue all plugin backend assets
     * 
     * @return void
     */
    public function enqueueBackendAssets(): void
    {
        $uiEnabled        = get_field('use_nice_dashboard', 'option');
        $adminBarEnabled  = get_field('admin_bar_enabled', 'option');
        $adminMenuEnabled = get_field('admin_menu_enabled', 'option');
        $postTypes        = get_post_types();

        unset($postTypes['revision']);
        unset($postTypes['nav_menu_item']);
        unset($postTypes['custom_css']);
        unset($postTypes['customize_changeset']);
        unset($postTypes['oembed_cache']);
        unset($postTypes['user_request']);
        unset($postTypes['wp_block']);
		unset($postTypes['acf-field']);

		// current post or page permalink
		$permalink = get_the_permalink();
		
        $data = [
            'adminBarEnabled' => $adminBarEnabled,
            'adminMenuEnabled' => $adminMenuEnabled,
            'uiEnabled' => $uiEnabled,
			'postTypes' => array_values($postTypes),
            'postUrl' => $permalink === false ? '/' : $permalink,
            'siteUrl' => get_site_url()
        ];
        
        if (true !== $adminMenuEnabled && true === $uiEnabled) {
            $data['logo'] = get_field('logo', 'option')['url'];
            $data['adminTextColor'] = get_field('admin_text_color', 'option');
            $data['adminBgColor'] = get_field('admin_background_color', 'option');
        }

        if (true !== $adminBarEnabled) {
            wp_enqueue_style(
                'remote-hideadminbar',
                Remote::$pluginAssetUri . '/css/hideadminbar.css',
                [],
                false
            );
        }

        if (true !== $adminMenuEnabled) {
            wp_enqueue_style(
                'remote-hideadminmenu',
                Remote::$pluginAssetUri . '/css/hideadminmenu.css',
                [],
                false
            );
		}
		
		if (true === $uiEnabled) {
            // use remote menu for backend instead
            wp_enqueue_style(
                'remote-menu',
                Remote::$pluginAssetUri . '/css/menu.css',
                [],
                false
            );

			wp_enqueue_style(
				'remote-backend',
				Remote::$pluginAssetUri . '/css/admin.css',
				[],
				false
			);

            wp_register_script(
                'remote-backend-menu',
                Remote::$pluginAssetUri . '/js/menu.js',
                ['remote-backend'],
                false,
                true
            );

            wp_localize_script(
                'remote-backend-menu',
                'REMOTE',
                $data
            );

            wp_enqueue_script('remote-backend-menu');
		}

        wp_register_script(
            'remote-backend',
            Remote::$pluginAssetUri . '/js/admin.js',
            [],
            false,
            true
        );

        wp_localize_script(
            'remote-backend',
            'REMOTE',
            $data
        );
        
        wp_enqueue_script('remote-backend');
    }

    /**
     * Load objects custom fields
     * 
     * @return void
     */
    public function registerAsset(): void
    {
        /**
         * Plugin general settings
         */
    }

    /**
     * Load options page
     * 
     * @return void
     */
    public function localizeAsset(): void
    {
        
    }

    /**
     * Load options page
     * 
     * @return void
     */
    public function enqueueAsset(): void
    {
        
    }
}