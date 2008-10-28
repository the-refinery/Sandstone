<?php
/**
 * Group Control Element Class File
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

class GroupControlElement extends ElementBase
{

	protected $_parentControl;

	protected $_defaultCheckedValue;

	protected $_checkedValue;

	protected $_isChangedElement;


	/**
	 * ParentControl property
	 *
	 * @return GroupBaseControl
	 *
	 * @param GroupBaseControl $Value
	 */
	public function getParentControl()
	{
		return $this->_parentControl;
	}

	public function setParentControl($Value)
	{
		if ($Value instanceof GroupBaseControl)
		{
			$this->_parentControl = $Value;
		}
		else
		{
			$this->_parentControl = null;
		}
	}

	/**
	 * DefaultChecked property
	 *
	 * @return boolean
	 * 
	 * @param boolean $Value
	 */
	public function getIsDefaultChecked()
	{
		if ($this->_defaultCheckedValue == "checked")
		{
			$returnValue = true;
		}
		else 
		{
			$returnValue = false;
		}
		
		return $returnValue;
	}

	public function setIsDefaultChecked($Value)
	{
		if ($Value == true)
		{
			$this->_defaultCheckedValue = "checked";	
		}
		else 
		{
			$this->_defaultCheckedValue = "unchecked";
		}
		
	}
		
	/**
	 * IsChecked property
	 * 
	 * @return boolean
	 * 
	 * @param boolean $Value
	 */
	public function getIsChecked()
	{
		if ($this->_checkedValue == "checked")
		{
			$returnValue = true;
		}
		else 
		{
			$returnValue = false;
		}

		return $returnValue;
		
	}

	public function setIsChecked($Value)
	{
		//Determine our current displayed value		
		if ($Value == true)
		{
			$this->_checkedValue = "checked";
		}
		else 
		{
			$this->_checkedValue = "unchecked";
		}
	}

	/**
	 * IsChangedElement property
	 *
	 * @return boolean
	 */
	public function getIsChangedElement()
	{
		if ($this->_checkedValue == $this->_defaultCheckedValue)
		{
			$returnValue = false;
		}
		else
		{
			$returnValue = true;
		}

		return $returnValue;
	}

	/**
	 * IsControlChecked property
	 * 
	 * @return boolean
	 */
	public function getIsControlChecked()
	{
		switch ($this->_checkedValue)
		{
			case "checked":
				$returnValue = true;
				break;
				
			case "unchecked":
				$returnValue = false;
				break;

			default:
				if ($this->_defaultCheckedValue == "checked")
				{
					$returnValue = true;
				}
				else 
				{
					$returnValue = false;
				}
				break;
		}

		return $returnValue;
		
	}
	
	/**
	 * InputParameters property
	 *
	 * @return string
	 */
	public function getInputParameters()
	{
		$id = "id=\"{$this->InputItemID}\"";
		$value = "value=\"{$this->_value}\"";
		
		if ($this->IsControlChecked)
		{
			$checked = "checked=\"checked\"";
		}
		else 
		{
			$checked = "";
		}

		$returnValue = "{$id} {$value} {$checked} {$this->_JS->CallList}";

		return $returnValue;
		
	}

	/**
	 * ListItemID property
	 *
	 * @return string
	 */
	 public function getListItemID()
	 {
	 	 return "{$this->IDtext}_LI";
	 }

	/**
	 * InputItemID property
	 *
	 * @return string
	 */
	 public function getInputItemID()
	 {
	 	 return "{$this->_parentControl->Name}_{$this->IDtext}";
	 }


	public function ClearCheckedValue()
	{
		$this->_checkedValue = null;
	}
}
?>