<?php
/*
Javascript Button Control Class File

@package Sandstone
@subpackage Application
*/

class JavascriptButtonControl extends ButtonBaseControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "button";

		//Setup the default style classes
		$this->_controlStyle->AddClass('javascriptbutton_general');
		$this->_bodyStyle->AddClass('javascriptbutton_body');

		//Default to a button caption of "Reset"
		$this->_defaultValue = "Go";

	}

}
?>