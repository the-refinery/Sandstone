<?php

class PhoneNumberTextBoxControl extends FormattedTextBoxControl
{
	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "text";
		$this->_isValueReturned = true;
		
        //Setup the default style classes
        $this->_controlStyle->AddClass('phonenumbertextbox_general');
        $this->_bodyStyle->AddClass('phonenumbertextbox_body');

        $this->Message->BodyStyle->AddClass('phonenumbertextbox_message');
        $this->Label->BodyStyle->AddClass('phonenumbertextbox_label');

	}
	
	public function setDefaultValue($Value)
	{
		$Value = $this->UnFormatDisplayValue($Value);
		parent::setDefaultValue($Value);
	}
	
	protected function FormatDisplayValue($Value)
	{		
		$Value = $this->UnFormatDisplayValue($Value);
		
		// Remove country code
		if (strlen($Value) > 10) 
		{
			$Value = substr($Value, -10, 10);
		}
		
		$Area = substr($Value,0,3);
		$Prefix = substr($Value,3,3);
		$Number = substr($Value,6,4);
		
		if (strlen($Area . $Prefix . $Number) > 0)
		{
			$returnValue = "(" . $Area . ") " . $Prefix . "-" . $Number;
		}
		
		return $returnValue;
	}
	
	protected function UnFormatDisplayValue($Value)
	{
		// Remove non-numeric characters
		$tempValue = ereg_replace("[^0-9]", '', $Value);
			
		return $tempValue;
	}
}

?>