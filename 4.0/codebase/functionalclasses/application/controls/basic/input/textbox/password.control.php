<?php
/**
Password Control Class File

@package Sandstone
@subpackage Application
*/

class PasswordControl extends TextBoxControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "password";
		$this->_isValueReturned = false;

        //Setup the default style classes
        $this->_controlStyle->AddClass('password_general');
        $this->_bodyStyle->AddClass('password_body');
	}

}
?>