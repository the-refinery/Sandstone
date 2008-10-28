<?php
/**
 * Base Group Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
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

		$this->Message->BodyStyle->AddClass('group_message');
		$this->Label->BodyStyle->AddClass('group_label');

	}

    /**
	 * HighlightDOMids property
	 *
	 * @return array
	 */
	public function getHighlightDOMids()
	{
		$returnValue = Array();

		foreach($this->_elements as $tempElement)
		{
			if ($tempElement->IsChangedElement)
			{
				$targetID = $tempElement->ListItemID;
				$returnValue = array_merge($returnValue, $this->UL->$targetID->HighlightDOMids);
			}
		}

		return $returnValue;
	}

	/**
	 * PostControlValue property
	 *
	 * @return string
	 */
	public function getPostControlValue()
	{
		$returnValue = "+{$this->Name}ControlValue";

		return $returnValue;
	}

	/**
	 * ControlValueSnippet property
	 *
	 * @return string
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

		//Now add a placeholder item to our UL
		$this->UL->AddItem($newElement->ListItemID);
	}

	protected function ClearAllChecks()
	{
		foreach($this->_elements as $tempElement)
		{
			$tempElement->ClearCheckedValue();
		}
	}

	protected function RenderControlBody()
	{

		$name = "name=\"{$this->InputName}\"";
		$type = "type=\"{$this->_inputType}\"";

		$returnValue = $this->RenderLabel();

		$this->UL->ClearItems();

		//Build the List Items
		foreach($this->_elements as $tempElement)
		{
			$tempItemInnerHTML = "<input {$type} {$name} {$tempElement->InputParameters} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style} /> {$tempElement->Label}";

			$this->UL->AddItem($tempElement->ListItemID, $tempItemInnerHTML);
		}


		$returnValue .= $this->UL->__toString();

		return $returnValue;

	}

	protected function RenderLabel()
	{

		$this->Label->TargetControlName = $this->UL->Name;

		$returnValue = $this->Label->__toString();

		return $returnValue;

	}

	protected function SetupControls()
	{
		parent::SetupControls();

		$this->UL = new ULcontrol();
	}

	public function Bind()
	{
		
		$this->ClearElements();
		
		if(is_set($this->_labelFormat) && is_set($this->_valueFormat) && is_set($this->_dataset) && $this->_dataset->IsLoaded)
		{

			while ($tempItem = $this->_dataset->FetchItem())
			{
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
	
}
?>