<?php
/*
Checkbox Control Class File

@package Sandstone
@subpackage Application
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

	}

	/*
	InputName property

	@return string
	@param string $Value
	*/
	public function getInputName()
	{

		$returnValue = "{$this->Name}[]";

		return $returnValue;
	}

}
?>