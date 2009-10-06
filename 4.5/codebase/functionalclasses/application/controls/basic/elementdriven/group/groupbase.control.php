<?php

/*
Base Group Control Class File

@package Sandstone
@subpackage Application
*/

class GroupBaseControl extends ElementDrivenBaseControl
{
	protected $_inputType;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('group_general');
		$this->_bodyStyle->AddClass('group_body');

        $this->_template->FileName = "group";
    }

    /*
	HighlightDOMids property

	@return array
	*/
	public function getHighlightDOMids()
	{
		$returnValue = Array();

		foreach($this->_elements as $tempElement)
		{
			if ($tempElement->IsChangedElement)
			{
				$returnValue[] = $tempElement->ListItemID;
			}
		}

		return $returnValue;
	}

	/*
	PostControlValue property

	@return string
	*/
	public function getPostControlValue()
	{
		$returnValue = "+{$this->Name}ControlValue";

		return $returnValue;
	}

	/*
	ControlValueSnippet property

	@return string
	*/
	public function getControlValueSnippet()
	{
		$returnValue = "var {$this->Name}ControlValue = ''; \n\n";

		foreach($this->_elements as $tempElement)
		{
			$returnValue .= "\tif (\$F('{$tempElement->InputItemID}'))\n";
			$returnValue .= "\t{\n";
			$returnValue .= "\t\t{$this->Name}ControlValue = {$this->Name}ControlValue + '&{$this->InputName}=' + \$F('{$tempElement->InputItemID}');\n";
			$returnValue .= "\t}\n";
		}

		return $returnValue;
	}

	protected function ParseEventParameters()
	{
		//We'll set the base value to whatever is in our Event Parameters
		$this->_value = DIunescape($this->_eventParameters[strtolower($this->Name)]);

		$changedElementID = DIunescape($this->_eventParameters['elementid']);

		if (is_set($this->_value))
		{

			if (is_array($this->_value) == false)
			{
				$this->_value = Array($this->_value);
			}

			foreach($this->_elements as $tempElement)
			{
				if (in_array($tempElement->Value, $this->_value))
				{
					$tempElement->IsChecked = true;
				}
				else
				{
					$tempElement->IsChecked = false;
				}
			}
		}
		else
		{
			$this->ClearAllChecks();
		}
	}

	public function AddElement($Value, $Label, $IsChecked = false)
	{
		$newElement = new GroupControlElement($Value, $Label, $this);
		$newElement->ParentControl = $this;

		$newElement->IsDefaultChecked = $IsChecked;

		$this->AddElementToArray($Value, $newElement);

	}

	protected function ClearAllChecks()
	{
		foreach($this->_elements as $tempElement)
		{
			$tempElement->ClearCheckedValue();
		}
	}

	public function Bind()
	{
		$returnValue = false;

		$this->ClearElements();

		if (is_set($this->_labelFormat) && is_set($this->_valueFormat) && is_set($this->_objectSet))
		{
			if ($this->_objectSet instanceof ObjectSet)
			{
				$returnValue = $this->BindObjectSet();
			}
			else
			{
				$returnValue = $this->BindArray();
			}
		}

		return $returnValue;
	}

	protected function BindObjectSet()
	{
		if ($this->_objectSet->IsLoaded)
		{
			while ($tempItem = $this->_objectSet->FetchItem())
			{
				$this->AddElementFromBind($tempItem);
			}
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function BindArray()
	{
		if(count($this->_objectSet) > 0)
		{
			foreach ($this->_objectSet as $tempItem)
			{
				$this->AddElementFromBind($tempItem);
			}
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function AddElementFromBind($Item)
	{
		$value = $this->FillFormatValues($this->_valueFormat, $this->_valueProperties, $Item);
		$label = $this->FillFormatValues($this->_labelFormat, $this->_labelProperties, $Item);

		$this->AddElement($value, $label);
	}

    public function Render()
    {

		$this->_template->BodyStyle = $this->_bodyStyle;

        foreach($this->_elements as $tempElement)
		{
			$tempElement->Template->InputType = $this->_inputType;
			$tempElement->Template->InputName = $this->InputName;

			$items .= $tempElement->Render();
		}

		$this->_template->Elements = $items;

        $returnValue = parent::Render();

        return $returnValue;
    }

}
?>
