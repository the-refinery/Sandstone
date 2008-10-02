<?php
/*
ListBox Control Class File

@package Sandstone
@subpackage Application
*/

class ListBoxControl extends SelectBaseControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_isMultiselect = true;

		//Setup the default style classes
		$this->_controlStyle->AddClass('listbox_general');
		$this->_bodyStyle->AddClass('listbox_body');
	}

}
?>