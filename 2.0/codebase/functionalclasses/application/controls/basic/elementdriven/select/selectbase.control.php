<?php
/**
 * Base Select Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 */

class SelectBaseControl extends ElementDrivenBaseControl
{

	protected $_isMultiselect;

	protected $_groupFormat;

   	protected $_groupProperties;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('select_general');
		$this->_bodyStyle->AddClass('select_body');

		$this->Message->BodyStyle->AddClass('select_message');
		$this->Label->BodyStyle->AddClass('select_label');

		$this->_elements = Array();
		
	}

	/**
	 * GroupFormat Property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getGroupFormat()
	{
		return $this->_groupFormat;
	}

	public function setGroupFormat($Value)
	{
		$this->_groupFormat = $Value;
		$this->_groupProperties = $this->ParseFormatProperties($Value);
	}

	/**
	 * Value property
	 * 
	 * @return variant
	 */
	public function getValue()
	{
		if (count($this->_elements) > 0)
		{
			//since there are elements loaded, we'll base our value from those elements
			foreach($this->_elements as $tempElement)
			{
				if ($tempElement->IsSelected)
				{
					if ($this->_isMultiselect)
					{
						//For multi selects we return an array
						$returnValue[] = $tempElement->Value;
					}
					else
					{
						//Otherwise, just the value.
						$returnValue = $tempElement->Value;
					}
				}
			}
		}
		else
		{
			//We don't have any elements loaded, so we'll just
			//return whatever might be in our _value field
			if (is_set($this->_value))
			{
        		if ($this->_isMultiselect)
				{
					//For multi selects we return the array
					$returnValue = $this->_value;
				}
				else
				{
					//Otherwise, just the first value.
					$returnValue = $this->_value[0];
				}
			}
		}

		return $returnValue;
	}

	protected function ParseEventParameters()
	{
		//We'll set the base value to whatever is in our Event Parameters
		$this->_value = DIunescape($this->_eventParameters[strtolower($this->Name)]);

		if (is_set($this->_value))
		{
			if (is_array($this->_value) == false)
			{
				$this->_value = explode(",", $this->_value);
			}

			foreach($this->_elements as $tempElement)
			{
				$this->SetElementSelectedFromControlValue($tempElement);
			}
		}
		else
		{
			$this->ClearAllSelections();
		}

	}

	protected function SetElementSelectedFromControlValue(&$TargetElement)
	{

		if (is_set($this->_value))
		{
			if (in_array($TargetElement->Value, $this->_value))
			{
				$TargetElement->IsSelected = true;
			}
			else
			{
				$TargetElement->IsSelected = false;
			}
		}

	}

	public function AddElementGroup($GroupName)
	{
		$this->AddElement($GroupName, $GroupName, false, true);
	}

	public function AddElement($Value, $Label, $IsSelected = false, $IsGroup = false)
	{
		$newElement = new SelectControlElement($Value, $Label, $this);

		if ($IsGroup == true)
		{
			$newElement->IsDefaultSelected = false;
			$newElement->IsGroup = true;
		}
		else
		{
			$newElement->IsDefaultSelected = $IsSelected;
			$newElement->IsGroup = false;
		}

		$this->SetElementSelectedFromControlValue($newElement);

		$this->AddElementToArray($Value, $newElement);
	}

	protected function ClearAllSelections()
	{
		foreach($this->_elements as $tempElement)
		{
			if ($tempElement->IsGroup == false)
			{
				$tempElement->ClearSelectedValue();
			}
		}
	}

	protected function RenderControlBody()
	{
		$id = "id=\"{$this->Name}\"";


		if ($this->_isMultiselect)
		{
			$multi = "multiple=\"multiple\"";
			$name = "name=\"{$this->Name}[]\"";
		}
		else 
		{
			$multi = null;
			$name = "name=\"{$this->Name}\"";
		}

		$returnValue = $this->RenderLabel();
		$returnValue .= "<select {$id} {$name} {$multi} {$this->_JS->CallList} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style}>";

		$isInGroup = false;

		foreach($this->_elements as $tempElement)
		{
			if ($tempElement->IsGroup)
			{
				if ($isInGroup)
				{
					$returnValue .= "</optgroup>";
				}

				$returnValue .= "<optgroup label=\"{$tempElement->Label}\">";
			}
			else
			{
				$returnValue .= "<option {$tempElement->OptionParameters} /> {$tempElement->Label}  &nbsp; </option>";
			}

		}

		if ($isInGroup)
		{
			$returnValue .= "</optgroup>";
		}

		$returnValue .= "</select>";
		
		return $returnValue;
	}	
	
	protected function RenderLabel()
	{

		$this->Label->TargetControlName = $this->Name;

		$returnValue = $this->Label->__toString();

		return $returnValue;

	}

	public function Bind()
	{
		
		$this->ClearElements();
		
		if(is_set($this->_labelFormat) && is_set($this->_valueFormat) && is_set($this->_dataset) && $this->_dataset->IsLoaded)
		{

			$currentGroupValue = null;

			while ($tempItem = $this->_dataset->FetchItem())
			{

				if (is_set($this->_groupFormat )== true)
				{
					$group = $this->FillFormatValues($this->_groupFormat, $this->_groupProperties, $tempItem);

					if ($group != $currentGroupValue)
					{
						//We have a new group
						$currentGroupValue = $group;

						$this->AddElementGroup($group);
					}

				}

				$value = $this->FillFormatValues($this->_valueFormat, $this->_valueProperties, $tempItem);
				$label = $this->FillFormatValues($this->_labelFormat, $this->_labelProperties, $tempItem);
				
				$this->AddElement($value, $label);
			}
			
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;
	}
	
	public function SelectElement($ElementID)
	{
		if (array_key_exists($ElementID, $this->_elements))
		{
			$this->_elements[$ElementID]->IsDefaultSelected = true;
		}

	}
	
}
?>