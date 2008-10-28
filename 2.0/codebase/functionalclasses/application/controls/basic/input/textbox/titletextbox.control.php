<?php
/**
 * TitleTextBox Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
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

        $this->Message->BodyStyle->AddClass('titletextbox_message');
        $this->Label->BodyStyle->AddClass('titletextbox_label');

	}

	/**
	 * Value property
	 *
	 * @return variant
	 */
	public function getValue()
	{

		if (is_set($this->_value))
		{
			//Dump the value if it's the same as our Label
			if ($this->_value == $this->Label->Text)
			{
				$returnValue = $this->_defaultValue;
				$this->_value = null;
			}
			else
			{
				$returnValue = $this->_value;
			}
		}
		else
		{
			$returnValue = $this->_defaultValue;
		}

		return $returnValue;
	}

	protected function RenderControlBody()
	{
		//Has a size been defined?
		if (is_set($this->_size) && is_numeric($this->_size))
		{
			$size = "size=\"{$this->_size}\" ";
		}

		//Has a max length been defined?
		if (is_set($this->_maxLength) && is_numeric($this->_maxLength))
		{
			$maxLength = "maxlength=\"{$this->_maxLength}\" ";
		}

		//Do we have a value?
		if (is_set($this->_value) && $this->_isValueReturned)
		{
			$displayValue = DIescape($this->_value);
		}
		else
		{
			//Do we have a default value?
			if (is_set($this->_defaultValue) && $this->_isValueReturned)
			{
				$displayValue = DIescape($this->_defaultValue);
			}
			else
			{
				$this->_bodyStyle->AddClass('titletextbox_blank');
				$displayValue = DIescape($this->Label->Text);
			}
		}
		$value = "value=\"{$displayValue}\"";

		//Set these standard parameters
		$type = "type=\"{$this->_inputType}\"";
		$name = "name=\"{$this->Name}\"";
		$id = "id=\"{$this->Name}\"";

		$returnValue = "<input {$id} {$type} {$this->JS->CallList} {$size} {$maxLength} {$value} {$name} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style} />";

		return $returnValue;
	}

    protected function SetupControlJavascript()
	{

		if (count($this->_JS->OnFocus->Code) == 0)
		{
			$this->_JS->OnFocus->Add("if (\$('{$this->Name}').value == '{$this->Label->Text}')");
			$this->_JS->OnFocus->Add("{");
			$this->_JS->OnFocus->Add("\t\$('{$this->Name}').removeClassName('titletextbox_blank'); ");
			$this->_JS->OnFocus->Add("\t\$('{$this->Name}').value='';");
			$this->_JS->OnFocus->Add("}");
		}

		if (count($this->_JS->OnBlur->Code) == 0)
		{
			$this->_JS->OnBlur->Add("if (\$('{$this->Name}').value == '')");
			$this->_JS->OnBlur->Add("{");
			$this->_JS->OnBlur->Add("\t\$('{$this->Name}').addClassName('titletextbox_blank');");
			$this->_JS->OnBlur->Add("\t\$('{$this->Name}').value='{$this->Label->Text}';");
			$this->_JS->OnBlur->Add("}");
		}

        parent::SetupControlJavascript();

	}

}
?>