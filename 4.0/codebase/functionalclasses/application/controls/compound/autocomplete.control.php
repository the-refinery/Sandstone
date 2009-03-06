<?php
/*
AutoComplete Control Class File

@package Sandstone
@subpackage Application
*/

class AutoCompleteControl extends BaseControl
{

	protected $_associatedEntityType;
	protected $_lookupFunctionParameters;

	protected $_waitingImageFileName;

	protected $_defaultValue;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('autocomplete_general');
		$this->_bodyStyle->AddClass('autocomplete_body');

		$this->_lookupFunctionParameters = Array();

		$this->_waitingImageFileName = "autocomplete_wait.gif";
	}

	/*
	AssociatedEntityType property

	@return string
	@param string $Value
	 */
	public function getAssociatedEntityType()
	{
		return $this->_associatedEntityType;
	}

	public function setAssociatedEntityType($Value)
	{
		$this->_associatedEntityType = $Value;
	}

	/*
	LookupFunctionParameters property

	@return array
	@param array $Value
	 */
	public function getLookupFunctionParameters()
	{
		return $this->_lookupFunctionParameters;
	}

	public function setLookupFunctionParameters($Value)
	{
		$this->_lookupFunctionParameters = $Value;
	}

	/*
	WaitingImageFileName property

	@return string
	@param string $Value
	 */
	public function getWaitingImageFileName()
	{
		return $this->_waitingImageFileName;
	}

	public function setWaitingImageFileName($Value)
	{
		$this->_waitingImageFileName = $Value;
	}

	public function getValue()
	{
		if (is_set($this->_value) && is_numeric($this->_value))
		{
			$returnValue = new $this->_associatedEntityType ($this->_value);
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	/*
	DefaultValue property

	@return object
	@param object $Value
	 */
	public function getDefaultValue()
	{
		return $this->_defaultValue;
	}

	public function setDefaultValue($Value)
	{
		if ($Value instanceof $this->_associatedEntityType && $Value->IsLoaded)
		{
			$this->_defaultValue = $Value;
		}
		else
		{
			$this->_defaultValue = null;
		}

	}

	public function AJAX_AutoComplete($Processor)
	{
		$Processor->Template->IsMasterLayoutUsed = false;
		$Processor->Template->FileName = "autocomplete";

		$searchString = $Processor->EventParameters['q'];

		if (strlen($searchString) > 0 && is_set($this->_associatedEntityType))
		{
			$objectSetCommand = "\$objectSet = {$this->_associatedEntityType}::AutoComplete(\$searchString, \$this->_lookupFunctionParameters);";

			eval($objectSetCommand);

			if ($objectSet->Count > 0)
			{
				$this->_template->FileName = strtolower($this->_name) . "_matchitem";
				$this->_template->IsMasterLayoutUsed = false;

				foreach ($objectSet->ItemsByIndex as $tempElement)
				{
					$this->_template->Element = $tempElement;

					$matchID = $tempElement->PrimaryIDproperty->Value;
					$matchContent = $this->_template->Render();

					$matchItems .= "$matchContent|$matchID\n";
				}
			}

			$Processor->Template->MatchItems = $matchItems;
		}
	}

    public function RenderObservers($Javascript)
	{

		$ajaxURL = Routing::GetFileTypeURL("ajax");

		$returnValue .= "\tif (\$('#{$this->Name}'),length) ";

		$parameters[] = "callback: {$this->Name}_AutoComplete_Callback";
		$parameters[] = "afterUpdateElement: {$this->Name}_AutoComplete_afterUpdateElement";
		$parameters[] = "indicator: '{$this->Name}_AutoComplete_Waiting'";
		$parameters[] = "minChars: 3";

		$parameterString = implode(", ", $parameters);

		$returnValue .= "new Ajax.Autocompleter(\"{$this->Name}_AutoComplete_Text\", \"{$this->Name}_AutoComplete_Matches\", \"{$ajaxURL}\", {{$parameterString}});\n";

		return $returnValue;
	}

	public function Render()
	{
		$this->_template->WaitingImageFileName = $this->_waitingImageFileName;

		if (is_set($this->_value))
		{
			$currentValue = $this->Value;

			$this->_template->DefaultIDvalue = $currentValue->PrimaryIDproperty->Value;
			$this->_template->TextBoxValue = $this->GenerateTextBoxContentsFromValue($currentValue);
		}
		else if (is_set($this->_defaultValue))
		{
			$this->_template->DefaultIDvalue = $this->_defaultValue->PrimaryIDproperty->Value;
			$this->_template->TextBoxValue = $this->GenerateTextBoxContentsFromValue($this->_defaultValue);
		}

		return parent::Render();
	}

	protected function GenerateTextBoxContentsFromValue($Value)
	{

		$tempControlContainer = new ControlContainer();
		$tempControlContainer->ParentContainer = $this;
		$tempControlContainer->Template->FileName = strtolower($this->_name) . "_matchitem";
		$tempControlContainer->Template->RequestFileType = "ajax";
		$tempControlContainer->Template->Element = $Value;

		$returnValue = $tempControlContainer->Render();

		return $returnValue;
	}

}
?>
