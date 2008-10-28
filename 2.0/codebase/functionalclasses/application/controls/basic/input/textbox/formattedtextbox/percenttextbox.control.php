<?php

class PercentTextBoxControl extends FormattedTextBoxControl
{
	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "text";
		$this->_isValueReturned = true;
		
        //Setup the default style classes
        $this->_controlStyle->AddClass('percenttextbox_general');
        $this->_bodyStyle->AddClass('percenttextbox_body');

        $this->Message->BodyStyle->AddClass('percenttextbox_message');
        $this->Label->BodyStyle->AddClass('percenttextbox_label');
	}
	
	protected function FormatDisplayValue($Value)
	{
		$returnValue = ($Value * 100) . "%";

		return $returnValue;
	}
	
	protected function UnFormatDisplayValue($Value)
	{
		// Remove %
		$tempValue = str_replace("%", "", $Value);
	
		// Set entered value as decimal
		if ($tempValue > 1)
		{
			$tempValue = $tempValue / 100;
		}
		
		return $tempValue;
	}
}

?>