<?php
/**
 * Remote Assets
 *  
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace remote\interfaces;

/**
 * Class RemoteAsset - interface to autoload assets from a single object
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
interface RemoteAsset
{
    /**
     * Register a new asset
     */
    function registerAsset(): void;

    /**
     * Localize an asset (script)
     */
    function localizeAsset(): void;

    /**
     * Enqueue an asset
     */
    function enqueueAsset(): void;
}