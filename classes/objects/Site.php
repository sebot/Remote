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
 * Class Site - factory objects
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class Site extends RemoteObjects implements RemoteObject
{
    /**
     * New Site Object
     */
    public function __construct()
    {
    }

    /**
     * Load invoice post type
     * 
     * @return void
     */
    public function loadObject(): void
    {
		register_extended_post_type('site', [
            'supports'           => ['title', 'author'],
            'with_front'         => false,
            'public'             => false,
            'publicly_queryable' => false,
            'show_in_nav_menus'  => true,
            'show_ui'            => true
        ]);
    }

    /**
     * Load objects custom fields
     * 
     * @return void
     */
    public function loadObjectMeta(): void
    {  
        /**
         * Site cpt custom fields
         */
		$SiteInfo = new FieldsBuilder('site_info');
        $SiteInfo
            ->addText('site_url', ['required' => true])
            ->addText('site_server_ip', ['required' => true])
            ->addTaxonomy('category', [
                'post_type' => 'post',
                'field_type' => 'select'
            ])

            ->setGroupConfig('position', 'acf_after_title')
			->setLocation('post_type', '==', 'site');

        acf_add_local_field_group($SiteInfo->build());

        $SiteSettings = new FieldsBuilder('site_settings');
        $SiteSettings
            ->addTrueFalse('show_header', ['default_value' => 1])
            ->addTrueFalse('show_navigation', ['default_value' => 0])
            ->addTrueFalse('show_slider', ['default_value' => 0])
            ->addSelect('number_columns')
                ->addChoice(1)
                ->addChoice(2)
                ->addChoice(3)
                ->addChoice(4)

            ->setGroupConfig('position', 'side')
			->setLocation('post_type', '==', 'site');

        acf_add_local_field_group($SiteSettings->build());
    }
}