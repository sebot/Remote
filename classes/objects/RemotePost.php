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
 * Class RemotePost - factory objects for Remote posts
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class RemotePost extends RemoteObjects implements RemoteObject
{
    /**
     * New Page Object
     */
    public function __construct()
    {
        add_action('acf/save_post_remotepost', [$this, 'sendPageToRemote'], 15, 1);
    }

    /**
     * Load invoice post type
     * 
     * @return void
     */
    public function loadObject(): void
    {
        register_extended_post_type('remotepost', [
            'supports'           => ['title', 'author', 'editor'],
            'public'             => true,
            'show_in_rest'       => true
        ]);
    }

    /**
     * Load objects custom fields
     * 
     * @return void
     */
    public function loadObjectMeta(): void
    {  
        $PageSettings = new FieldsBuilder('remote_post_settings');
        $PageSettings
        ->addPostObject('target_sites', [
            'label' => 'Select sites to show this page on.',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'post_type' => ['site'],
            'taxonomy' => [],
            'allow_null' => 1,
            'multiple' => 1,
            'return_format' => 'object',
            'ui' => 1,
        ])
        ->addTrueFalse('is_home', [
            'label' => 'Is this the homepage?',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [],
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
            'ui_on_text' => 'Yes',
            'ui_off_text' => 'No',
        ])

        ->setGroupConfig('position', 'side')
        ->setLocation('post_type', '==', 'remotepost');

        acf_add_local_field_group($PageSettings->build());
    }

    /**
     * Send a page to remote site
     */
    public function sendPageToRemote($post_id): void
    {
        var_dump($post_id);die;
        // TODO: abstract
        if (wp_is_post_revision($post_id) || 
            wp_doing_cron()) {
            return;
        }

        // ensure site is connected
        $isConnected = false != get_post_meta($post_id, 'isConnected', true);
        if (true == $isConnected) {
            // get data
            $targetSites = get_field('target_sites', $post_id);
            $url = get_field('site_url');
            $secret = get_field('remote_secret');

            // TODO: get meta 'target_sites' for page and get secret as well as url foreach
            // then update each site one by one
            // send page to site
            $remoteId = $this->api($url)->sendPostToRemote($secret, $post_id);
        }
    }
}