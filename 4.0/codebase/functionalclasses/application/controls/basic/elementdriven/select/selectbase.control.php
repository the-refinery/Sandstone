<?php
/*
Base Select Control Class File

@package Sandstone
@subpackage Application
*/

class SelectBaseControl extends ElementDrivenBaseControl
{

	protected $_isMultiselect;

	protected $_groupFormat;
   	protected $_groupProperties;

	protected $_elementGroups;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('select_general');
		$this->_bodyStyle->AddClass('select_body');

		$this->_elements = Array();
		$this->_elementGroups = Array();

		$this->_template->FileName = "select";

	}

	/*
	GroupFormat Property

	@return string
	@param string $Value
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

	/*
	Value property

	@return variant
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

	public function AddElement($Value, $Label, $IsSelected = false, $GroupName = null)
	{

		$newElement = new SelectControlElement($Value, $Label, $this);
		$newElement->IsDefaultSelected = $IsSelected;

		$this->SetElementSelectedFromControlValue($newElement);

		$this->AddElementToArray($Value, $newElement);

		if (is_set($GroupName))
		{
			$groupKey = strtolower($GroupName);

			if (array_key_exists($groupKey, $this->_elementGroups))
			{
				$targetGroup = $this->_elementGroups[$groupKey];
			}
			else
			{
				$targetGroup = new SelectControlElementGroup($GroupName, $this);

				$this->_elementGroups[$groupKey] = $targetGroup;
			}

			$targetGroup->AddElement($Value, $newElement);

		}
	}

	public function ClearElements()
	{
		$this->_elements = Array();
		$this->_elementGroups = Array();
	}

	protected function ClearAllSelections()
	{

		foreach($this->_elements as $tempElement)
		{
			$tempElement->ClearSelectedValue();
		}

	}

	public function Bind()
	{

		$this->ClearElements();

		if(is_set($this->_labelFormat) && is_set($this->_valueFormat) && is_set($this->_objectSet) && $this->_objectSet->IsLoaded)
		{
			$currentGroupValue = null;

			while ($tempItem = $this->_objectSet->FetchItem())
			{

				$value = $this->FillFormatValues($this->_valueFormat, $this->_valueProperties, $tempItem);
				$label = $this->FillFormatValues($this->_labelFormat, $this->_labelProperties, $tempItem);

				if (is_set($this->_groupFormat )== true)
				{
					$group = $this->FillFormatValues($this->_groupFormat, $this->_groupProperties, $tempItem);
				}
				else
				{
					$group = null;
				}

				$this->AddElement($value, $label, false, $group);
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

	public function Render()
	{

		$this->_template->BodyStyle = $this->_bodyStyle;

		//Are we multiselect?
		if ($this->_isMultiselect)
		{
			$this->_template->Multiple = "multiple=\"multiple\"";
			$this->_template->Name = "{$this->Name}[]";
		}
		else
		{
			$this->_template->Name = "{$this->Name}";
		}

		//Are we in group mode?
		if (count($this->_elementGroups) > 0)
		{
			//Group Mode
	        foreach($this->_elementGroups as $tempGroup)
			{
				$tempGroup->Template->RequestFileType = $this->_template->RequestFileType;
				$options .= $tempGroup->Render() . "\n";
			}

		}
		else
		{
			//Non-Group Mode
	        foreach($this->_elements as $tempElement)
			{
				$tempElement->Template->RequestFileType = $this->_template->RequestFileType;
				$options .= $tempElement->Render() . "\n";
			}
		}
		
		//Which template should we use?
		if (is_set($this->_labelText))
		{
		    $this->_template->FileName = "selectandlabel";
		}
		else
		{
		    $this->_template->FileName = "select";
		}

		$this->_template->Elements = $options;

		$returnValue = parent::Render();

		return $returnValue;
	}

}
?>