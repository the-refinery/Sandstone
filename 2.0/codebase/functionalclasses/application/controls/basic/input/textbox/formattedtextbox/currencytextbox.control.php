<?php

class CurrencyTextBoxControl extends FormattedTextBoxControl
{
	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "text";
		$this->_isValueReturned = true;
		
        //Setup the default style classes
        $this->_controlStyle->AddClass('currencytextbox_general');
        $this->_bodyStyle->AddClass('currencytextbox_body');

        $this->Message->BodyStyle->AddClass('currencytextbox_message');
        $this->Label->BodyStyle->AddClass('currencytextbox_label');
	}

	protected function FormatDisplayValue($Value)
	{
		$returnValue = "\$" . number_format($Value, 2);

		return $returnValue;
	}
	
	protected function UnFormatDisplayValue($Value)
	{
		// Remove $
		$tempValue = str_replace("\$", "", $Value);
	
		// Format Number
		$tempValue = number_format($tempValue, 2, '.', '');
		
		return $tempValue;
	}
}

?>