<?php

/*
Currency Text Box Control
*/
class CurrencyTextBoxControl extends FormattedTextBoxControl
{
	public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
        $this->_controlStyle->AddClass('currencytextbox_general');
        $this->_bodyStyle->AddClass('currencytextbox_body');
	}

	protected function FormatDisplayValue($Value)
	{
		if ($Value)
		{
			$returnValue = "\$" . number_format($Value, 2);
		}

		return $returnValue;
	}

	protected function UnFormatDisplayValue($Value)
	{
		if (strlen($Value) > 0)
		{
			// Remove $
			$returnValue = str_replace("\$", "", $Value);

			// Format Number
			$returnValue = number_format($returnValue, 2, '.', '');
		}

		return $returnValue;
	}
}

?>