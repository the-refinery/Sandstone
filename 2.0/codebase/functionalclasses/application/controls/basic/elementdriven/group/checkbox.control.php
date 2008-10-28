<?php
/**
 * Checkbox Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 */

class CheckboxControl extends GroupBaseControl
{

	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "checkbox";

		//Setup the default style classes
		$this->_controlStyle->AddClass('checkbox_general');
		$this->_bodyStyle->AddClass('checkbox_body');

		$this->Message->BodyStyle->AddClass('checkbox_message');
		$this->Label->BodyStyle->AddClass('checkbox_label');

	}

	/**
	 * InputName property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getInputName()
	{

		$returnValue = "{$this->Name}[]";

		return $returnValue;
	}

}
?>