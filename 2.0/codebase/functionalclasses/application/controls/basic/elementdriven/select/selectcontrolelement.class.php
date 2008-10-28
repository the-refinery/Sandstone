<?php
/**
 * Select Control Element Class File
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

class SelectControlElement extends ElementBase
{

	protected $_defaultSelectedValue;
	
	protected $_selectedValue;

	protected $_isGroup;


	/**
	 * DefaultChecked property
	 * 
	 * @return boolean
	 * 
	 * @param boolean $Value
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

	/**
	 * IsChecked property
	 * 
	 * @return boolean
	 * 
	 * @param boolean $Value
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

	/**
	 * IsGroup property
	 *
	 * @return boolean
	 *
	 * @param boolean $Value
	 */
	public function getIsGroup()
	{
		return $this->_isGroup;
	}

	public function setIsGroup($Value)
	{
		$this->_isGroup = $Value;
	}

	/**
	 * OptionParameters property
	 * 
	 * @return string
	 */
	public function getOptionParameters()
	{
		$value = "value=\"{$this->_value}\"";

		switch ($this->_selectedValue)
		{
			case "selected":
				$selected = "selected=\"selected\"";
				break;
				
			case "unselected":
				$selected = "";
				break;
				
			default:
				if ($this->_defaultSelectedValue == "selected")
				{
					$selected = "selected=\"selected\"";
				}
				else 
				{
					$selected = "";
				}
				break;
		}

		$returnValue = "{$value} {$selected} {$this->_JS->CallList}";
				
		return $returnValue;
		
	}

	public function ClearSelectedValue()
	{
		$this->_selectedValue = null;
	}
}
?>