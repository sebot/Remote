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
use remote\core\RemoteFieldsBuilder;
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
            ->addText('site_server_ip')
            ->addText('remote_secret', 
                [
                    'required' => true,
                    'instructions' => 'enter the Secret visible in your sites "Remote" tab.'
                ]
            )
            ->addTaxonomy('category', [
                'post_type' => 'post',
                'field_type' => 'select'
            ])
            ->setGroupConfig('position', 'acf_after_title')
			->setLocation('post_type', '==', 'site');

        acf_add_local_field_group($SiteInfo->build());

        /**
         * Get the sites connection status and display 
         * as meta msg in post edit screen
         */
        $SiteStatus = new RemoteFieldsBuilder('site_status');
        $SiteStatus->addCustom('site_connection', 
            [
                'label' => 'Site connection status',
                'instructions' => '',
                'new_lines' => 'wpautop',
                'esc_html' => 1,
            ]
        )
        ->setGroupConfig('position', 'side')
        ->setLocation('post_type', '==', 'site');

        acf_add_local_field_group($SiteStatus->build());

        $SiteSettings = new FieldsBuilder('site_settings');
        $SiteSettings
            ->addTrueFalse('show_header', [
                'label' => 'Show header?',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addTrueFalse('show_navigation', [
                'label' => 'Show Navigation?',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addTrueFalse('show_slider', [
                'label' => 'Show slider?',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addSelect('number_columns')
                ->addChoice(1)
                ->addChoice(2)
                ->addChoice(3)
                ->addChoice(4)

            ->setGroupConfig('position', 'side')
			->setLocation('post_type', '==', 'site');

        acf_add_local_field_group($SiteSettings->build());

        // need to use load_field here as load_value does not work for message fields
        add_filter('acf/load_value/name=site_connection', [$this, 'getConnectionStatus'], 10, 3);
    }

    /**
     * Get the current connection status of the current
     * site edited
     * 
     * @param string $value - the value of the field
     * @param int $post_id - the current post_id loaded
     * @param array $field - the current field
     * 
     * @return string - the new value
     */
    public function getConnectionStatus($value, $post_id, $field)
    {
        $isConnected = get_post_meta($post_id, 'isConnected', true);
        $isConnected = false != $isConnected;
        
        $value = __('This site is currently <span class="error bold">not connected</span> to this Remote network.', 'remote');
        if (true === $isConnected) {
            $value = __('This site is currently <span class="success bold">connected</span> to this Remote network.', 'remote');
        }

        return $value;
    }
}