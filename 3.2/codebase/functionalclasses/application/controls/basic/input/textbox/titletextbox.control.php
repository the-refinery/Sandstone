<?php
/**
TitleTextBox Control Class File

@package Sandstone
@subpackage Application
*/

class TitleTextBoxControl extends TextBoxControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "text";
		$this->_isValueReturned = true;

        //Setup the default style classes
        $this->_controlStyle->AddClass('titletextbox_general');
        $this->_bodyStyle->AddClass('titletextbox_body');

        $this->_template->FileName = "titletextbox";

	}

	/*
	Value property

	@return variant
	*/
	public function getValue()
	{

		if (is_set($this->_value))
		{
			//Dump the value if it's the same as our Label
			if ($this->_value == $this->_labelText)
			{
				$this->_value = null;
			}
			else
			{
				$returnValue = $this->_value;
			}
		}

		return $returnValue;
	}

	protected function RenderDisplayValue()
	{

		//Do we have a value?
		if (is_set($this->_value) && $this->_isValueReturned)
		{
			$returnValue = DIescape($this->_value);
		}
		else
		{
			//Do we have a default value?
			if (is_set($this->_defaultValue) && $this->_isValueReturned)
			{
				$returnValue = DIescape($this->_defaultValue);
			}
			else
			{
//        		$this->_bodyStyle->AddClass('titletextbox_blank');
//				$returnValue = DIescape($this->_labelText);
			}
		}

		return $returnValue;

	}

}
?>