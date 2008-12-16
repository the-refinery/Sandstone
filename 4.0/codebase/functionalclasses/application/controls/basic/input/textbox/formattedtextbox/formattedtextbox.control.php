<?php
/*
Formatted Text Box Control
*/
class FormattedTextBoxControl extends TextBoxControl
{
	protected function RenderDisplayValue()
	{
		$tempValue = parent::RenderDisplayValue();
		$tempValue = $this->FormatDisplayValue($tempValue);

		return $tempValue;
	}

	protected function ParseEventParameters()
	{		
		$tempValue = DIunescape($this->_eventParameters[strtolower($this->Name)]);

		if (is_set($tempValue))
		{
			$tempValue = $this->UnFormatDisplayValue($tempValue);
			$this->_value = $tempValue;
		}
	}

	protected function FormatDisplayValue($Value)
	{
		return $tempValue;
	}

	protected function UnFormatDisplayValue($Value)
	{
		return $tempValue;
	}
}

?>
