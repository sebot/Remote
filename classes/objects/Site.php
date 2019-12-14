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
    const CPT_NAME = 'site';

    /**
     * New Site Object
     */
    public function __construct()
    {
        add_action('acf/save_post', [$this, 'saveSite'], 15, 1);
    }

    /**
     * Load invoice post type
     * 
     * @return void
     */
    public function loadObject(): void
    {
		register_extended_post_type(self::CPT_NAME, [
            'supports'           => ['title'],
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
            ->addImage('site_logo', [
                'label' => 'Site Logo',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'mime_types' => 'png, jpg, gif, svg, webp',
            ])
            ->addText('site_url', 
                [
                    'required' => true,
                ]
            )
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
			->setLocation('post_type', '==', self::CPT_NAME);

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
        ->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($SiteStatus->build());

        $SiteSettings = new FieldsBuilder('site_settings');
        $SiteSettings
            ->addTrueFalse('show_header', [
                'label' => 'Show header?',
                'default_value' => 1,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addTrueFalse('show_navigation', [
                'label' => 'Show Navigation?',
                'default_value' => 1,
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
            ->addTrueFalse('show_featured_image', [
                'label' => 'Show featured image?',
                'default_value' => 1,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])

            ->setGroupConfig('position', 'side')
			->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($SiteSettings->build());

        // load site connection status
        add_filter('acf/load_value/name=site_connection', [$this, 'getConnectionStatus'], 10, 3);

        // set some fields to readonly
        add_filter('acf/load_field/name=site_url', [$this, 'setReadonly'], 10, 1);
        add_filter('acf/load_field/name=remote_secret', [$this, 'setReadonly'], 10, 1);
        add_filter('acf/load_field/name=site_server_ip', [$this, 'setReadonly'], 10, 1);
    }

    /**
     * Set an acf field readonly on load
     */
    public function setReadonly($field): array
    {
        $post_id = get_the_ID();
        $isConnected = false != get_post_meta($post_id, 'isConnected', true);
        if (false !== $isConnected) {
            $field['readonly'] = 1;
        }

        return $field;
    }

    /**
     * WordPress hook save_post_site
     * check if the site is connected and if it's not
     * setup the connection by calling the fetcher 
     * connect endpoint on the provided url
     * 
     * example https://yourdomain.com/wp-json/fetcher/v2/remote/connect/
     * in the request which needs to be aes-128-ctr encrypted AFTER
     * authenticated hmac check is done.
     */
    public function saveSite($post_id): void
    {
        if (wp_is_post_revision($post_id) || 
            wp_doing_cron() || self::CPT_NAME !== get_post_type($post_id)) {
            return;
        }
        
        // get data
        $url = get_field('site_url');
        $secret = get_field('remote_secret');

        // check if site is already connected
        $isConnected = false != get_post_meta($post_id, 'isConnected', true);
        if (true == $isConnected) {
            // update site settings
            $this->api($url)->update($secret, $post_id);
        } else {
            // connect site
            $isConnected = $this->api($url)->connect($secret, $post_id);
            if (!is_bool($isConnected)) $isConnected = boolval($isConnected);
            update_post_meta($post_id, 'isConnected', $isConnected);
        }
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
        $isConnected = false != get_post_meta($post_id, 'isConnected', true);
        $value = __(
            '<p>This site is currently <span class="error bold">not connected</span>' . 
            'to this Remote network. </p><p><span class="bold">WARNING:</span> by connecting' .
            'this Site any settings done on the remote Site will be overwritten!</p>'
            , 
            'remote'
        );
        if (true === $isConnected) {
            $value = __('This site is currently <span class="success bold">connected</span> to this Remote network.', 'remote');
        }

        return $value;
    }
}