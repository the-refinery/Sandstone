<?php
/**
 * AutoComplete Control Class File
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

class AutoCompleteControl extends BaseControl
{

	protected $_defaultValue;

	protected $_targetClass;
	protected $_targetFunction;
	protected $_targetFunctionParameters;

	protected $_valueFormat;
	protected $_textFormat;

	protected $_valueProperties;
	protected $_textProperties;


    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_controlStyle->AddClass('autocomplete_general');
		$this->_bodyStyle->AddClass('autocomplete_body');

		$this->Message->BodyStyle->AddClass('autocomplete_message');
		$this->Label->BodyStyle->AddClass('autocomplete_label');

		//Setup some defaults
		$this->_targetFunction = "AutoComplete";
		$this->_targetFunctionParameters = Array();

		$this->_isRawValuePosted = false;
	}

	/**
	 * TargetClass Property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getTargetClass()
	{
		return $this->_targetClass;
	}

	public function setTargetClass($Value)
	{
		$this->_targetClass = $Value;
	}

	/**
	 * TargetFunction property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getTargetFunction()
	{
		return $this->_targetFunction;
	}

	public function setTargetFunction($Value)
	{
		if (strlen($Value) > 0)
		{
			$this->_targetFunction = $Value;
		}
		else
		{
			$this->_targetFunction = "AutoComplete";
		}
	}

	/**
	 * TargetFunctionParameters property
	 *
	 * @return array
	 *
	 * @param array $Value
	 */
	public function getTargetFunctionParameters()
	{
		return $this->_targetFunctionParameters;
	}

	public function setTargetFunctionParameters($Value)
	{
		if (is_array($Value))
		{
			$this->_targetFunctionParameters = $Value;
		}
		else
		{
			$this->_targetFunctionParameters = Array();
		}
	}

	/**
	 * ValueFormat Property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getValueFormat()
	{
		return $this->_valueFormat;
	}

	public function setValueFormat($Value)
	{
		$this->_valueFormat = $Value;
		$this->_valueProperties = $this->ParseFormatProperties($Value);
	}

	/**
	 * TextFormat Property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getTextFormat()
	{
		return $this->_textFormat;
	}

	public function setTextFormat($Value)
	{
		$this->_textFormat = $Value;
		$this->_textProperties = $this->ParseFormatProperties($Value);
	}

	/**
	 * Value property
	 *
	 * @return variant
	 */
	public function getValue()
	{
		//Since the most important part of this is the selected ID,
		//we'll just reflect that sub-control's value
		return $this->SelectedID->Value;
	}

	protected function RenderControlBody()
	{
		$returnValue = $this->RenderLabel();
		$returnValue .= $this->TextBox->__toString();
		$returnValue .= $this->Matches->__toString();
		$returnValue .= $this->SelectedID->__toString();

		return $returnValue;
	}

	protected function RenderLabel()
	{

		$this->Label->TargetControlName = $this->TextBox->Name;

		$returnValue = $this->Label->__toString();

		return $returnValue;
	}

    protected function SetupControls()
	{
		parent::SetupControls();

   		$this->TextBox = new TitleTextBoxControl();
		$this->TextBox->ControlStyle->AddClass("autocomplete_body");
		$this->TextBox->Label->Text = "Search...";
		$this->TextBox->Effects->Scope = $this->_effects->Scope;

		$this->Matches = new DIVcontrol();
		$this->Matches->BodyStyle->AddClass("autocomplete_matchesitem");
		$this->Matches->BodyStyle->AddStyle("display:none;");
		$this->Matches->Effects->Scope = $this->_effects->Scope;

		$this->Matches->List = new ULcontrol();
		$this->Matches->List->BodyStyle->AddClass("autocomplete_listitem");
		$this->Matches->List->Effects->Scope = $this->_effects->Scope;

		$this->SelectedID = new HiddenControl();

	}

	protected function SetupControlJavascript()
    {

    	$this->GenerateOnKeyUpJavascript();
    	$this->GenerateMatchSelectionJavascript();

        parent::SetupControlJavascript();

    }

	protected function GenerateOnKeyUpJavascript()
	{
		$this->TextBox->JS->OnKeyUp->Add("if ($('{$this->TextBox->Name}').value.length > 2)");
		$this->TextBox->JS->OnKeyUp->Add("{");
		$this->TextBox->JS->OnKeyUp->AddControlEvent("AutoComplete", false, $this);
		$this->TextBox->JS->OnKeyUp->Add("}");
		$this->TextBox->JS->OnKeyUp->Add("else");
		$this->TextBox->JS->OnKeyUp->Add("{");
		$this->TextBox->JS->OnKeyUp->Add("\tif ($('{$this->Matches->Name}').style.display != \"none\")");
		$this->TextBox->JS->OnKeyUp->Add("\t{");
		$this->TextBox->JS->OnKeyUp->Add("\t\t\$('{$this->Matches->Name}').innerHTML = '';");
		$this->TextBox->JS->OnKeyUp->Add("\t\tnew Element.hide('{$this->Matches->Name}');");
		$this->TextBox->JS->OnKeyUp->Add("\t}");
		$this->TextBox->JS->OnKeyUp->Add("}");
		$this->TextBox->JS->OnKeyUp->Add("\$('{$this->SelectedID->Name}').value = '';");

	}

	protected function GenerateMatchSelectionJavascript()
	{

		$this->_JS->MatchSelect->Add("var textBoxContent = new String(SelectedText);");
		$this->_JS->MatchSelect->Add("\$('{$this->SelectedID->Name}').value = SelectedID;");
		$this->_JS->MatchSelect->Add("\$('{$this->TextBox->Name}').value = textBoxContent.replace(/{apos}/,\"'\");");
		$this->_JS->MatchSelect->Add("new Effect.BlindUp('{$this->Matches->Name}');");

        $this->_JS->MatchSelect->AddParameter("SelectedID");
        $this->_JS->MatchSelect->AddParameter("SelectedText");
	}

	protected function AutoComplete_Handler($EventParameters)
	{
		$returnValue = new EventResults();

        //We have to do it this way since you can't call a static method on a class name stored
		//in a variable.
		$methodCall = "\$dataSet = {$this->_targetClass}::{$this->_targetFunction}(\"{$this->TextBox->Value}\", \$this->_targetFunctionParameters);";
		eval($methodCall);

		//Clear the list of any previous items
		$this->Matches->List->ClearItems();

		if ($dataSet->Count > 0)
		{
			//Load our list

			foreach($dataSet->ItemsByIndex as $tempItem)
			{
				$value = DIescape($this->FillFormatValues($this->_valueFormat, $this->_valueProperties, $tempItem));
				$text = DIescape($this->FillFormatValues($this->_textFormat, $this->_textProperties, $tempItem));

				$tempJSlink = new JavascriptLinkControl();
				$tempJSlink->ParentContainer = $this;
				$tempJSlink->AnchorText = $text;

				//Since we can't handle a single quote in the string, replace it here
				$functionText = str_replace("'", "{apos}", $text);
				$tempJSlink->JS->OnClick->AddFunctionCall($this->JS->MatchSelect, Array($value, "'{$functionText}'"));

				$this->Matches->List->AddItem($value, $tempJSlink->__toString());

			}

			//Set the new data in the div
			echo $this->Matches->Effects->InnerHTMLblock;

			//Now, make sure the matches DIV is showing
			echo $this->Matches->Effects->BlindDownBlock;

			//Clear the validation message
			$this->ValidationMessage = null;
			echo $this->ValidationJavascript;

			$returnValue->Value = true;

		}
		else
		{
			$this->ValidationMessage = "No Matches Found";

			//Show the validation message
			echo $this->ValidationJavascript;

        	//Now, make sure the matches DIV is hidden
			echo $this->Matches->Effects->BlindUpBlock;

			$returnValue->Value = false;
		}


		$returnValue->Complete();

		return $returnValue;

	}

}
?>
