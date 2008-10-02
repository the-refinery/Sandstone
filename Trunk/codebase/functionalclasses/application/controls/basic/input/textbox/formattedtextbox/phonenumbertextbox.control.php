<?php
/*
Phone Number Textbox Control
*/
class PhoneNumberTextBoxControl extends FormattedTextBoxControl
{
	public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
        $this->_controlStyle->AddClass('phonenumbertextbox_general');
        $this->_bodyStyle->AddClass('phonenumbertextbox_body');
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