<?php
/**
 * Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 */

class BaseControl extends ControlContainer
{
	protected $_validationMessage;
	protected $_value;

	protected $_styles;

	protected $_controlStyle;
	protected $_bodyStyle;

	protected $_effects;

	protected $_validators;

	protected $_dataset;

	protected $_isRendered;

	protected $_isManuallyRendered;

	protected $_isTopLevelControl;

	public function __construct()
	{

		parent::__construct();

		$this->_controlStyle = new ControlStyles();
		$this->_bodyStyle = new ControlStyles();

		//Setup the default style classes
		$this->_controlStyle->AddClass('control_general');
		$this->_bodyStyle->AddClass('control_body');

		$this->_effects = new ControlEffects($this);

		$this->SetupControls();

		//Default to being Rendered
		$this->_isRendered = true;

	}

	public function __toString()
	{
		if ($this->_isRendered)
		{
			//Make sure any internal control Javascript is setup
	        if ($this->_isControlJavascriptSetup == false)
	        {
	            $this->SetupControlJavascript();
	        }

	        //Start the Master control DIV
			$id = "id=\"{$this->MasterControlDOMid}\"";
			$returnValue = "<div {$id} {$this->_controlStyle->Classes} {$this->_controlStyle->Style}>";

			//Dump our InnerHTML
			$returnValue .= $this->InnerHTML;

			//Close the Master control DIV
			$returnValue .= "</div>";
		}

		return $returnValue;
	}

	/**
	 * ValidationMessage property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getValidationMessage()
	{
		return $this->_validationMessage;
	}

	public function setValidationMessage($Value)
	{
		$this->_validationMessage = $Value;
	}

	/**
	 * Value property
	 *
	 * @return variant
	 */
	public function getValue()
	{
		return $this->_value;
	}

	/**
	 * ControlStyle property
	 *
	 * @return ControlStyles
	 */
	public function getControlStyle()
	{
		return $this->_controlStyle;
	}

	/**
	 * BodyStyle property
	 *
	 * @return ControlStyles
	 */
	public function getBodyStyle()
	{
		return $this->_bodyStyle;
	}

	/**
	 * Effects property
	 *
	 * @return ControlEffects
	 */
	public function getEffects()
	{
		return $this->_effects;
	}

	/**
	 * Validators property
	 * 
	 * @return array
	 */
	public function getValidators()
	{
		return $this->_validators;
	}

	/**
	 * Dataset property
	 * 
	 * @return Dataset
	 * 
	 * @param Dataset $Value
	 */
	public function getDataset()
	{
		return $this->_dataset;
	}

	public function setDataset($Value)
	{
		if ($Value instanceof Dataset && $Value->IsLoaded)
		{
			$this->_dataset = $Value;
			$this->Bind();
		}
	}

	/**
	 * IsRendered property
	 *
	 * @return boolean
	 *
	 * @param boolean $Value
	 */
	public function getIsRendered()
	{
		return $this->_isRendered;
	}

	public function setIsRendered($Value)
	{
		$this->_isRendered = $Value;
	}

	/**
	 * IsManuallyRendered property
	 *
	 * @return boolean
	 *
	 * @param boolean $Value
	 */
	public function getIsManuallyRendered()
	{
		return $this->_isManuallyRendered;
	}

	public function setIsManuallyRendered($Value)
	{
		$this->_isManuallyRendered = $Value;
	}

	/**
	 * InnerHTML property
	 *
	 * @return string
	 */
	public function getInnerHTML()
	{

		$returnValue = $this->RenderMessageDIV();
		$returnValue .= $this->RenderControlBody();
		$returnValue .= $this->RenderBufferDIV();

		return $returnValue;
	}

    /**
	 * MasterControlDOMid property
	 *
	 * @return string
	 */
	public function getMasterControlDOMid()
	{
		return "{$this->Name}_Control";
	}

    /**
	 * HighlightDOMids property
	 *
	 * @return array
	 */
	public function getHighlightDOMids()
	{

		$returnValue = Array();

		if ($this->IsCompoundControl)
		{
			foreach($this->AllActiveControls as $tempControl)
			{
				$returnValue = array_merge($returnValue, $tempControl->HighlightDOMids);
			}
		}
		else
		{
			//Basic Control
			$returnValue[] = $this->Name;
		}

		return $returnValue;
	}

	/**
	 * IsCompoundControl property
	 *
	 * @return boolean
	 */
	public function getIsCompoundControl()
	{
		if (count($this->_activeControls) > 0 || count($this->_hiddenControls) > 0)
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	/**
	 * IsValidated property
	 * 
	 * @return array
	 */
	public function getIsValidated()
	{
		if (is_set($this->_validationMessage))
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
	 * Validation Javascript property
	 * 
	 * @return string
	 */
	public function getValidationJavascript()
	{

		$this->Message->InnerHTML = DIescape($this->_validationMessage);

		if ($this->IsValidated)
		{
			$returnValue = $this->ValidationSuccessJavascript;
		}
		else
		{
			$returnValue = $this->ValidationFailureJavascript;
		}

		return $returnValue;
	}

	/**
	 * Validation Failure Javascript property
	 * 
	 * @return string
	 */
	public function getValidationFailureJavascript()
	{

		$returnValue = $this->Message->Effects->InnerHTMLblock;
		$returnValue .= $this->Message->Effects->BlindDownBlock;

		return $returnValue;
	}

	/**
	 * Validation Success Javascript property
	 *
	 * @return string
	 */
	public function getValidationSuccessJavascript()
	{

		$returnValue .= $this->Message->Effects->BlindUpBlock;

		return $returnValue;
	}

	/**
	 * Top Level Control property
	 *
	 * @return Control
	 */
	public function getTopLevelControl()
	{
		$tempControl = $this;

		if ($this->_isTopLevelControl == false)
		{
			while ($tempControl->ParentContainer instanceof BaseControl)
			{
				$tempControl = $tempControl->ParentContainer;
			}
		}

		return $tempControl;
	}

	final public function RaiseEvent($EventName, $EventParameters)
	{
		if (strlen($EventName) > 0)
		{

			//First, is there a sub control specified that isn't myself?
			if (is_set($EventParameters['subcontrol']) && $EventParameters['subcontrol'] != $this->Name)
			{

                //This is an event for one of our sub controls

                //Attempt to find a sub control with this name.
                $targetControl = null;
                $i = 0;

                while(is_set($targetControl) == false && $i < count($this->_allChildControls) - 1)
                {
					$allActiveControls = $this->AllActiveControls;

                    if ($allActiveControls[$i]->Name == $EventParameters['subcontrol'])
                    {
                        $targetControl = $allActiveControls[$i];
                    }

                    $i++;
                }

                //If we found one, raise the event.
				if (is_set($targetControl))
				{
					$returnValue = $targetControl->RaiseEvent($EventName, $EventParameters);
				}
				else
				{
					//Unknown sub control
					$returnValue = null;
				}
			}
			else
			{
				//This is an event for this control.

				//Build the name of the Handler function
				$handlerFunctionName = $EventName . "_Handler";

				//Do we have a handler for this event?
				if (method_exists($this, $handlerFunctionName))
				{
					//Handle the Event
					$returnValue = $this->$handlerFunctionName($EventParameters);
				}
				else
				{
					//We don't have a handler for this event
					$returnValue = null;
				}

			}
		}
		else
		{
			//No Event passed
			$returnValue = null;
		}

		return $returnValue;
	}

	final public function AddValidator($ClassName, $FunctionName)
	{
		$functionSpec = $ClassName . "->" . $FunctionName;

		$this->_validators[] = $functionSpec;
	}

	protected function ParseEventParameters()
	{
		//Is there a value for the current name?
		if (is_set($this->_eventParameters[strtolower($this->Name)]))
		{
			if (strlen($this->_eventParameters[strtolower($this->Name)])> 0)
			{
				$this->_value = DIunescape($this->_eventParameters[strtolower($this->Name)]);
			}
			else
			{
				$this->_value = null;
			}

		}
	}

	final public function Validate()
	{

		if (count($this->_validators) > 0)
		{			
			//We don't for each here because we want to break out on the first failed validation
			$i = 0;
			while ($i < count($this->_validators) && $this->IsValidated)
			{
				//Get the Class and function names
				$tempFunctionSpec = $this->_validators[$i];
				$tempBreakout = explode("->", $tempFunctionSpec);
				
				//Build a class of the specific type
				$tempValidator = new $tempBreakout[0] ();

				//Execute the function
				$this->_validationMessage = $tempValidator->$tempBreakout[1] ($this);
				
				$i += 1;
			}
			
			$returnValue = $this->IsValidated;			
		}
		else 
		{
			//No validators, so it's validated by default
			$returnValue = true;
		}
		
		return $returnValue;
	}

	protected function RenderControlScript()
	{
		
	}

	final protected function RenderMessageDIV()
	{
		if ($this->IsValidated)
		{
			$this->Message->InnerHTML = "{$this->Name} Message Area";
			$this->Message->BodyStyle->AddStyle("display: none;");
		}
		else
		{
			$this->Message->InnerHTML = DIescape($this->_validationMessage);
		}

		$returnValue = $this->Message->__toString();

		return $returnValue;		
	}

	protected function RenderControlBody()
	{
		//By default, if we have sub controls, render them in order, otherwise
		//display a generic text
		if (count($this->_controlOrder) > 0)
		{
			$returnValue = $this->RenderControls();
		}
		else
		{
			$returnValue = "Sandstone Base Control: {$this->Name} = {$this->_value}";
		}

		return $returnValue;
	}

	protected function RenderBufferDIV()
	{
		if ($this->_JS->FunctionCount > 0)
		{
			$returnValue = $this->Buffer->__toString();
		}

		return $returnValue;
	}

	public function Bind()
	{
		return false;	
	}

	final protected function ParseFormatProperties($Format)
	{
		$pattern = '/({(?:[^{}]+(?:"[^"]*"|\'[^\']*\')?)+})/';
		$split = preg_split ($pattern, trim ($Format), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		foreach ($split as $code)
		{
			if (substr($code, 0, 1) == "{")
			{
				$propertyName = str_replace(Array("{", "}"), "", $code);
				$returnValue[$code] = $propertyName;
			}
		}

		return $returnValue;
	}
	
	final protected function FillFormatValues($Format, $Properties, $Object)
	{
		$returnValue = $Format;
		
		foreach($Properties as $code=>$name)
		{
			$returnValue = str_replace($code, $Object->$name, $returnValue);
		}
			
		return $returnValue;	
	}

	final protected function ParseFormat($Format, $Object)
	{
		$properties = $this->ParseFormatProperties($Format);
		
		$returnValue = $this->FillFormatValues($Format, $properties, $Object);
		
		return $returnValue;
		
	}

	protected function SetupControls()
	{

		//Message DIV
		$this->Message = new DIVcontrol();
		$this->Message->IsManuallyRendered = true;

		//We remove these two classes so that we just start with the control_message class
		$this->Message->BodyStyle->RemoveClass('control_body');
		$this->Message->BodyStyle->RemoveClass('div_body');
		$this->Message->BodyStyle->AddClass('control_message');

		//Label
		$this->Label = new LabelControl();

	}

	public function SetupBuffer()
	{
		if (array_key_exists("Buffer", $this->_staticControls) == false)
		{

			$this->Buffer = new DIVcontrol();
			$this->Buffer->IsManuallyRendered = true;

			//Since this div is only for use as the AJAX return buffer, we'll take off
			//the normal control classes
			$this->Buffer->BodyStyle->RemoveClass('control_body');
			$this->Buffer->BodyStyle->RemoveClass('div_body');

			//And set it to not display at all.
			$this->Buffer->BodyStyle->AddStyle("display: none;");
		}
	}

	protected function Validate_Handler($EventParameters)
	{
		$returnValue = new EventResults();

		//Validate the control
		$returnValue->Value = $this->Validate();

		//Return the javascript to either show or hide the message depending on
		//the validation status.
		echo $this->ValidationJavascript;

		$returnValue->Complete();

		return $returnValue;

	}

	protected function Save_Handler($EventParameters)
	{

		$returnValue = new EventResults();

		$saveEvent = $this->Name . "Save";

		//Automatically validate this control, and add the results of the validation
		//to the event parameters
		$validationResults = $this->RaiseEvent("Validate", $EventParameters);
		$EventParameters['ValidationResults'] = $validationResults;

		//Was validation successful?
		if ($validationResults->Value == true)
		{
			//Raise the specific save event
			$saveResults = 	$this->Page->RaiseEvent($saveEvent, $EventParameters);

			$saveResults->Flush();
			$returnValue->Value = $saveResults->Value;

			if ($saveResults->Value == true)
			{
				//We have a successful save, so show the highlight effect
				//and close the error message area.
				echo $this->Effects->HighlightBlock;
				echo $this->ValidationSuccessJavascript;
			}
		}
		else
		{
			//Validation Failed.  Just flush it's buffer
			$validationResults->Flush();
			$returnValue->Value = false;
		}

		$returnValue->Complete();

		return $returnValue;

	}

}

?>