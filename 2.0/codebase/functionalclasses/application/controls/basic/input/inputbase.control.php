<?php
/**
 * Input Base Control Class File
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

class InputBaseControl extends BaseControl
{
	protected $_inputType;
	
	protected $_defaultValue;

	protected $_isValueReturned;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('input_general');
		$this->_bodyStyle->AddClass('input_body');

		$this->Message->BodyStyle->AddClass('input_message');
		$this->Label->BodyStyle->AddClass('input_label');
	}

	/**
	 * DefaultValue property
	 * 
	 * @return variant
	 * 
	 * @param variant $Value
	 */
	public function getDefaultValue()
	{
		return $this->_defaultValue;
	}

	public function setDefaultValue($Value)
	{
		$this->_defaultValue = $Value;
	}

	protected function RenderLabel()
	{

		$this->Label->TargetControlName = $this->Name;

		$returnValue = $this->Label->__toString();

		return $returnValue;
		
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
			$returnValue = $this->_value;
		}
		else
		{
			$returnValue = $this->_defaultValue;
		}

		return $returnValue;
	}


	protected function RenderInput($Parameters = null)
	{

		$value = "value=\"{$this->RenderDisplayValue()}\"";

		//Set these standard parameters
		$type = "type=\"{$this->_inputType}\"";
		$name = "name=\"{$this->Name}\"";
		$id = "id=\"{$this->Name}\"";

		$returnValue = "<input {$id} {$type} {$this->_JS->CallList} {$Parameters} {$value} {$name} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style} />";

		return $returnValue;
	}

	protected function RenderDisplayValue()
	{
		
		//Do we have a value?
		if (is_set($this->_value) && $this->_isValueReturned)
		{
			$returnValue = DIescape($this->_value);
		}
		else 
		{
			//Do we have a default value?
			if (is_set($this->_defaultValue) && $this->_isValueReturned)
			{
				$returnValue = DIescape($this->_defaultValue);
			}
			else 
			{
				$returnValue = "";
			}
		}
		
		return $returnValue;
		
	}


}
?>