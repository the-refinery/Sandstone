<?php
/*
Radio Button Control Class File
@package Sandstone
@subpackage Application
*/

class RadioButtonControl extends GroupBaseControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "radio";

		//Setup the default style classes
		$this->_controlStyle->AddClass('radiobutton_general');
		$this->_bodyStyle->AddClass('radiobutton_body');

	}

	public function getValue()
	{
	    return $this->_value[0];
	}

    /*
	HighlightDOMids property

	@return array
	*/
	public function getHighlightDOMids()
	{
		$returnValue = Array();

		$returnValue[] = $this->UL->Name;

		return $returnValue;
	}


	/*
	InputName property

	@return string
	@param string $Value
	*/
	public function getInputName()
	{

		$returnValue = $this->Name;

		return $returnValue;
	}

	/* 
	In data bound situations, it's often handy to have the first element selected by default
	*/
	public function SelectFirstElement()
	{
		reset($this->_elements);
		$tempElement = current($this->_elements);
		$tempElement->IsDefaultChecked = true;
	}
}
?>