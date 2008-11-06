<?php
/*
Select Control Element Class File

@package Sandstone
@subpackage Application
*/

class SelectControlElement extends ElementBase
{

	protected $_defaultSelectedValue;

	protected $_selectedValue;

	public function __construct($Value, $Label, $Control)
	{
		parent::__construct($Value, $Label, $Control);

		$this->_template->FileName = "selectelement";
		$this->_template->RequestFileType = $Control->Template->RequestFileType;
	}

	/*
	DefaultChecked property

	@return boolean
	@param boolean $Value
	*/
	public function getIsDefaultSelected()
	{
		if ($this->_defaultSelectedValue == "selected")
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function setIsDefaultSelected($Value)
	{
		if ($Value == true)
		{
			$this->_defaultSelectedValue = "selected";
		}
		else
		{
			$this->_defaultSelectedValue = "unselected";
		}

	}

	/*
	IsChecked property

	@return boolean
	@param boolean $Value
	*/
	public function getIsSelected()
	{
		if ($this->_selectedValue == "selected")
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	public function setIsSelected($Value)
	{
		if ($Value == true)
		{
			$this->_selectedValue = "selected";
		}
		else
		{
			$this->_selectedValue = "unselected";
		}

	}

	public function ClearSelectedValue()
	{
		$this->_selectedValue = null;
	}

	public function Render()
	{

		$this->_template->Value = $this->_value;
		$this->_template->Label = $this->_label;

		if ($this->_selectedValue == "selected")
		{
			$this->_template->Selected = "selected=\"selected\"";
		}
		elseif (is_set($this->_selectedValue) == false && $this->_defaultSelectedValue == "selected")
		{
			//No selected setting at all, but the default selected value is true
			$this->_template->Selected = "selected=\"selected\"";
		}

		$returnValue = parent::Render();

		return $returnValue;
	}
}
?>