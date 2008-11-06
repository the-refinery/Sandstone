<?php
/*
Dropdown Control Class File

@package Sandstone
@subpackage Application
*/

class DropdownControl extends SelectBaseControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_isMultiselect = false;

		//Setup the default style classes
		$this->_controlStyle->AddClass('dropdown_general');
		$this->_bodyStyle->AddClass('dropdown_body');

	}

}
?>