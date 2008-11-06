<?php
/*
Reset Button Control Class File

@package Sandstone
@subpackage Application
*/

class ResetButtonControl extends ButtonBaseControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "reset";

		//Setup the default style classes
		$this->_controlStyle->AddClass('resetbutton_general');
		$this->_bodyStyle->AddClass('resetbutton_body');

		//Default to a button caption of "Reset"
		$this->_defaultValue = "Reset";

	}

}
?>