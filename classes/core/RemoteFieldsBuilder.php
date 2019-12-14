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

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Class RemoteFieldsBuilder - extend stoutlogic acf builder with
 * addCustom function to load any custom fields using acf builder
 * classes
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class RemoteFieldsBuilder extends FieldsBuilder
{
    /**
     * Create new Fieldsbuilder
	 * 
	 * @param string $name - the name of the builder
     */
    public function __construct(string $name)
    {
        // do not delete!
        parent::__construct($name);
    }

	/**
     * @param string $name
     * @param array $args field configuration
     */
    public function addCustom($name, array $args = [])
    {
        return $this->addField($name, 'RemoteMessage', $args);
    }
}
