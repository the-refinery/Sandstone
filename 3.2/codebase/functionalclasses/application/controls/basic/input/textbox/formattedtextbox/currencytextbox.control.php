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
		if (strlen($Value) > 0)
		{
			$returnValue = "\$" . number_format($Value, 2);
		}

		return $returnValue;
	}

	protected function UnFormatDisplayValue($Value)
	{
		if (strlen($Value) > 0)
		{
			// Remove $ and thousand separators
			$returnValue = str_replace(Array("\$", ","), "", $Value);
		}

		return $returnValue;
	}
}

?>