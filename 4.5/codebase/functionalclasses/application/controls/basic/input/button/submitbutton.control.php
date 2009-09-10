<?php
/**
Submit Button Control Class File

@package Sandstone
@subpackage Application
*/

class SubmitButtonControl extends ButtonBaseControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "submit";

		//Setup the default style classes
		$this->_controlStyle->AddClass('submitbutton_general');
		$this->_bodyStyle->AddClass('submitbutton_body');

		//Default to a button caption of "Submit"
		$this->_defaultValue = "Submit";
	}

}
?>