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
namespace remote\core;

use StoutLogic\AcfBuilder\FieldsBuilder;
use remote\interfaces\RemoteObject;

/**
 * Class RemoteObjects - Remote master object
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class RemoteObjects
{
    /**
     * Load all required objects if they
     * have RemoteObject interface implemented
     * 
     * @param array $objects - the objects array containing
     * namespaced FQN to the object
     */
    public function __construct(array $objects)
    {
        // load objects if they exist
        array_map(function($object){
            $interfaces = class_implements($object);
            if (class_exists($object) && isset($interfaces['remote\interfaces\RemoteObject'])) {
                $o = new $object;
                add_action('init', [$o, 'loadObject']);
                add_action('acf/init', [$o, 'loadObjectMeta']);
            }
        }, $objects);
    }
}