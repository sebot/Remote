<?php
/*phpcs:disable*/
/**
 * Remote
 *  
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace remote\objects;

use StoutLogic\AcfBuilder\FieldsBuilder;
use remote\interfaces\RemoteObject;
use remote\core\RemoteObjects;

/**
 * Class Settings - access to plugin settings
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class Settings extends RemoteObjects implements RemoteObject
{
    /**
     * Init settings
     */
    public function __construct()
    {
    }

    /**
     * Load objects custom fields
     * 
     * @return void
     */
    public function loadObjectMeta(): void
    {
        /**
         * Plugin general settings
         */
		$remoteSettings = new FieldsBuilder('remote_settings');
        $remoteSettings
			->addText('remote_api_key')
			->setLocation('options_page', '==', 'remote-set');

        acf_add_local_field_group($remoteSettings->build());
    }

    /**
     * Load options page
     * 
     * @return void
     */
    public function loadObject(): void
    {
        $args = [
            'page_title' => 'Fresh Content for your blog',
            'menu_title' => 'Remote',
            'menu_slug'  => 'remote-set',
            'capability' => 'manage_options',
            'position'   => false,
            'icon_url'   => false,// 'http://127.0.0.1/wp-content/uploads/2019/10/cat.jpg',
            'redirect'   => true,
            'post_id'    => 'options',
            'autoload'   => false,
        ];

        acf_add_options_page($args);
    }
}