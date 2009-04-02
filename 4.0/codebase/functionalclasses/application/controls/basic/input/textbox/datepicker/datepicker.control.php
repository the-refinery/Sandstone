<?php
/*
Date Picker Control Class File

@package Sandstone
@subpackage Application
*/

NameSpace::Using("Sandstone.Date");

class DatePickerControl extends TextBoxControl
{
	protected $_dateFormat = 'm/d/Y';
	
	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "text";
		$this->_isValueReturned = true;

        //Setup the default style classes
        $this->_controlStyle->AddClass('datepicker_general');
        $this->_bodyStyle->AddClass('datepicker_body');

        $this->_template->FileName = "datepicker";

	}
	
	protected function ParseEventParameters()
	{		
		$tempValue = DIunescape($this->_eventParameters[strtolower($this->Name)]);

		if (is_set($tempValue))
		{
			$this->_value = new Date($tempValue);
		}
	}
	
	public function getDateFormat()
	{
		return $this->_dateFormat;
	}

	public function setDateFormat($Value)
	{
		$this->_dateFormat = $Value;
	}

	public function getDefaultValue()
	{
		return $this->_defaultValue;
	}
	
	public function setDefaultValue($Value)
	{
		if ($Value instanceof Date)
		{
			$this->_defaultValue = $Value->FormatDate($this->_dateFormat);
		}
	}
}

?>