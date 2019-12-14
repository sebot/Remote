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
 * Class Page - factory objects for Remote posts
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class Page extends RemoteObjects implements RemoteObject
{
    const CPT_NAME = 'page';

    /**
     * New Page Object
     */
    public function __construct()
    {
        add_action('acf/save_post', [$this, 'sendPageToRemote'], 15, 1);
    }

    /**
     * Load invoice post type
     * 
     * @return void
     */
    public function loadObject(): void
    {
        register_extended_post_type(self::CPT_NAME, [
            'supports'           => ['title', 'editor', 'thumbnail'],
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
        ->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($PageSettings->build());
    }

    /**
     * Send a post object to remote site which will
     * return the remote id. Images will be transfered
     * using base64 encoding - the Post will be updated
     * if it already exists
     * 
     * @param int $post_id - the id of the object
     * 
     * @return void
     */
    public function sendPageToRemote($post_id): void
    {
        // TODO: abstract
        if (wp_is_post_revision($post_id) || 
            wp_doing_cron() || self::CPT_NAME !== get_post_type($post_id)) {
            return;
        }

        // get target sites
        $targetSites = get_field('target_sites', $post_id);
        if (false !== $targetSites) {
            foreach ($targetSites as $targetSite) {
                $siteId = $targetSite->ID;
                $url = get_field('site_url', $siteId);
                $secret = get_field('remote_secret', $siteId);

                $remoteId = $this->api($url)->sendPostToRemote($secret, $post_id, $siteId);
                var_dump($remoteId);
            }
        }
    }
}
