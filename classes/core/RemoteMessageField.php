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


/**
 * Class RemoteMessageField - a custom message field for acf
 * since acf message field does not support filters
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class RemoteMessageField extends \acf_field
{
	public $settings;
	public $name;
	public $label;
	public $category;
	public $defaults;
	public $l10n;

	public function __construct() {
		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/
		$this->name = 'RemoteMessage';


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/
		$this->label = __( 'Remote Message', 'remote' );


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/
		$this->category = 'Remote';

		// do not delete!
		parent::__construct();
	}

	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
    public function render_field( $field ) 
    {
		echo '<div class="remote-msg">' . $field['value'] . '</div>';
	}
}