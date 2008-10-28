<?php
/**
 * TextArea Control Class File
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

class TextAreaControl extends BaseControl
{
	protected $_defaultValue;
	
	protected $_rows;
	protected $_columns;

	protected $_isValueReturned;
	
	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('textarea_general');
		$this->_bodyStyle->AddClass('textarea_body');

		$this->Message->BodyStyle->AddClass('textarea_message');
		$this->Label->BodyStyle->AddClass('textarea_label');

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

	/**
	 * Rows property
	 *
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getRows()
	{
		return $this->_rows;
	}

	public function setRows($Value)
	{
		$this->_rows = $Value;
	}

	/**
	 * Columns property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getColumns()
	{
		return $this->_columns;
	}

	public function setColumns($Value)
	{
		$this->_columns = $Value;
	}
	
	protected function RenderControlBody()
	{
		
		//Define the label
		$returnValue = $this->RenderLabel();
		
		//Do we have Rows defined?
		if (is_set($this->_rows) && $this->_rows > 0)
		{
			$rows = "rows=\"{$this->_rows}\"";
		}
		
		//Do we have columns defined?
		if (is_set($this->_columns) && $this->_columns > 0)
		{
			$cols = "cols=\"{$this->_columns}\"";
		}
		
		//Do we have a value?
		if (is_set($this->_value))
		{
			$value = urlencode($this->_value);
		}
		else 
		{
			//Do we have a default value?
			if (is_set($this->_defaultValue))
			{
				$value = "{$this->_defaultValue}";
			}
		}

		//Set these standard parameters
		$name = "name=\"{$this->Name}\"";
		$id = "id=\"{$this->Name}\"";
		
		$returnValue .= "<textarea {$id} {$this->_JS->CallList} {$rows} {$cols} {$name} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style}>{$value}</textarea>";

		return $returnValue;
	}
		
	protected function RenderLabel()
	{

		$this->Label->TargetControlName = $this->Name;

		$returnValue = $this->Label->__toString();

		return $returnValue;

	}
	
}
?>