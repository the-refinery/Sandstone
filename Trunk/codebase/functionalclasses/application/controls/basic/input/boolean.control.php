<?php
/*
Boolean Control Class File

@package Sandstone
@subpackage Application
*/

class BooleanControl extends BaseControl
{

	protected $_defaultValue;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('boolean_general');
		$this->_bodyStyle->AddClass('boolean_body');
	}

	/*
	DefaultValue property

	@return variant
	@param variant $Value
	*/
	public function getDefaultValue()
	{
		return $this->_defaultValue;
	}

	public function setDefaultValue($Value)
	{
		$this->_defaultValue = $Value;
	}

	protected function ParseEventParameters()
	{
		//Is there a value for the current name?
		if (is_set($this->_eventParameters[strtolower($this->Name)]))
		{
			if (strlen($this->_eventParameters[strtolower($this->Name)])> 0)
			{
				$inboundValue = DIunescape($this->_eventParameters[strtolower($this->Name)]);

				if ($inboundValue == "on")
				{
					$this->_value = true;
				}
				else
				{
					$this->_value = false;
				}
			}
			else
			{
				$this->_value = false;
			}
		}
		else
		{
			$this->_value = false;
		}
	}

	public function Render()
	{

		if ($this->_defaultValue == true)
		{
			$this->_template->InputValue = "checked=\"checked\"";
		}

		$returnValue = parent::Render();

		return $returnValue;
	}

}