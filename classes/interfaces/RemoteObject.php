<?php
/*phpcs:disable*/
/**
 * Remote Nyan Automation automation
 *  
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace remote\interfaces;

/**
 * Class RemoteObject - object interface for Remote objects
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
interface RemoteObject {
    /**
     * used for basic object loading
     * like post type registration and alike
     * running on WP init
     */
    public function loadObject(): void;

    /**
     * used for object meta field loading
     * running on acf/init
     */
    public function loadObjectMeta(): void;
}
