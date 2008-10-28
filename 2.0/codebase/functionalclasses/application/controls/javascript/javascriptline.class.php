<?php
/**
 * Javascript Line Class File
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

class JavascriptLine extends Module
{

	protected $_parentFunction;

	protected $_textLine;
	protected $_eventName;

	protected $_referenceFunction;
	protected $_referenceFunctionParameters;

	protected $_targetControl;
	protected $_targetSubControl;

	protected $_isAllValuesPassed;

	public function __construct($ParentFunction, $TextLine = null, $EventName = null, $TargetControl = null, $TargetSubControl = null, $IsAllValuesPassed = false, $ReferenceFunction = null, $ReferenceFunctionParameters = Array())
	{

		$this->_parentFunction = $ParentFunction;

		$this->_referenceFunctionParameters = Array();

		if (is_set($EventName))
		{
			$this->_eventName = $EventName;
			$this->_targetControl = $TargetControl;
			$this->_targetSubControl = $TargetSubControl;
			$this->_isAllValuesPassed = $IsAllValuesPassed;
		}
		else if (is_set($ReferenceFunction))
		{
			if ($ReferenceFunction instanceof JavascriptFunction)
			{
				$this->_referenceFunction = $ReferenceFunction;

				if (is_array($ReferenceFunctionParameters))
				{
					$this->_referenceFunctionParameters = $ReferenceFunctionParameters;
				}
			}
		}
		else
		{
			$this->_textLine = $TextLine;
		}
	}

	/**
	 * ParentFunction property
	 *
	 * @return JavascriptFunction
	 */
	public function getParentFunction()
	{
		return $this->_parentFunction;
	}

	/**
	 * TextLine property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getTextLine()
	{
		return $this->_textLine;
	}

	public function setTextLine($Value)
	{
		$this->_textLine = $Value;

		if (is_set($Value))
		{
			$this->_eventName = null;
			$this->_targetControl = null;
			$this->_targetSubControl = null;
		}
	}

	/**
	 * EventName property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getEventName()
	{
		return $this->_eventName;
	}

	public function setEventName($Value)
	{
		$this->_eventName = $Value;

		if (is_set($Value))
		{
			$this->_textLine = null;
		}
	}

	/**
	 * ReferenceFunction property
	 *
	 * @return JavascriptFunction
	 *
	 * @param JavascriptFunction $Value
	 */
	public function getReferenceFunction()
	{
		return $this->_referenceFunction;
	}

	public function setReferenceFunction($Value)
	{
		if ($Value instanceof JavascriptFunction)
		{
			$this->_referenceFunction = $Value;
		}
		else
		{
			$this->_referenceFunction = null;
		}

	}

	/**
	 * ReferenceFunctionParameters property
	 *
	 * @return array
	 *
	 * @param array $Value
	 */
	public function getReferenceFunctionParameters()
	{
		return $this->_referenceFunctionParameters;
	}

	public function setReferenceFunctionParameters($Value)
	{
		if (is_array($Value))
		{
			$this->_referenceFunctionParameters = $Value;
		}
		else
		{
			$this->_referenceFunctionParameters = Array();
		}

	}

	/**
	 * TargetControl property
	 *
	 * @return Control
	 *
	 * @param Control $Value
	 */
	public function getTargetControl()
	{
		return $this->_targetControl;
	}

	public function setTargetControl($Value)
	{
		$this->_targetControl = $Value;
	}

	/**
	 * TargetSubControl property
	 *
	 * @return Control
	 *
	 * @param Control $Value
	 */
	public function getTargetSubControl()
	{
		return $this->_targetSubControl;
	}

	public function setTargetSubControl($Value)
	{
		$this->_targetSubControl = $Value;
	}

	/**
	 * IsAllValuesPassed property
	 *
	 * @return boolean
	 *
	 * @param boolean $Value
	 */
	public function getIsAllValuesPassed()
	{
		return $this->_isAllValuesPassed;
	}

	public function setIsAllValuesPassed($Value)
	{
		$this->_isAllValuesPassed = $Value;
	}

	/**
	 * IsAJAXevent property
	 *
	 * @return boolean
	 */
	public function getIsAJAXevent()
	{
		if (is_set($this->_eventName))
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
	 * IsFunctionCall property
	 *
	 * @return boolean
	 */
	public function getIsFunctionCall()
	{
		if (is_set($this->_referenceFunction))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function Render($SupressFormatting = false)
	{

		if ($SupressFormatting == false)
		{
			$returnValue .= "\t";
		}

		if (is_set($this->_eventName))
		{
			$returnValue .= $this->RenderEvent();
		}
		else if ($this->_referenceFunction instanceof JavascriptFunction)
		{

			$parameterList = implode(", ", $this->_referenceFunctionParameters);
			$parameterList = str_replace("'", "\'", $parameterList);

			$returnValue .= "{$this->_referenceFunction->Name}({$parameterList});";

		}
		else
		{
			$returnValue .= $this->_textLine;
		}

		if ($SupressFormatting == false)
		{
			$returnValue .= " \n";
		}

		return $returnValue;

	}

	protected function RenderEvent()
	{
		//Get an easy reference to our currently associated control or form
		$associatedControl = $this->_parentFunction->ParentFunctions->Control;

		if (is_set($associatedControl))
		{
			//We have a control, get a reference to it's form
			$associatedForm = $associatedControl->Form;

			//We always use the buffer of the control we are associated with.
			$buffer = "'{$associatedControl->Buffer->Name}'";
		}
		else
		{
			//We don't have a control, go up the chain and find the form.
			$associatedForm = $this->_parentFunction->ParentFunctions->Form;

			//We always use the buffer of the form we are associated with.
			$buffer = "'{$associatedForm->Buffer->Name}'";
		}

		//Target Controller
		$postString = "'ajax.php";

		//Target Page
		$postString .= "?page={$associatedForm->Page->ActivePageName}";

		//Event
		$postString .= $this->CalculateEventParameter();

		//ID the form
		$postString .= "&formname={$associatedForm->Name}";

		//ID this control
		$postString .= $this->CalculateControlName();

		//Pass any Environment
		$env = $associatedForm->Page->BuildEnvironmentString($associatedControl->EventParameters);
		$postString .= "&env={$env}";
		$postString .= "'";

		//Are we to pass all form control values?
		if ($this->_isAllValuesPassed)
		{
			$postString .= $associatedForm->PostControlValue;
			$snippet .= $associatedForm->ControlValueSnippet;
		}
		else
		{
            //Pass the current Control Value or values. (if associated)  Make sure we start at the top
            //level control and pass its value(s) in case this is a sub control
			if (is_set($associatedControl))
			{
				$topLevelControl = $this->CalculateTopLevelControl();

				$postString .= $topLevelControl->PostControlValue;
				$snippet .= $topLevelControl->ControlValueSnippet;
			}

			//Always post any hidden controls from the form
			$postString .= $associatedForm->PostHiddenControlValues;
		}

		if (is_set($snippet))
		{
			$returnValue = $snippet . "\t";
		}

		$returnValue .= "new Ajax.Updater({$buffer},{$postString},{evalScripts:true, method:'post'});";

		return $returnValue;

	}

	protected function CalculateEventParameter()
	{

		if (is_set($this->_targetControl))
		{
			//Control Level Event
			$returnValue .= "&event=ControlEvent";
			$returnValue .= "&controlevent={$this->_eventName}";
		}
		else
		{
			//Page Level Event
			$returnValue = "&event={$this->_eventName}";
		}

		return $returnValue;
	}

	protected function CalculateControlName()
	{

		if (is_set($this->_targetControl))
		{
			$returnValue = "&control={$this->_targetControl->Name}";

			if (is_set($this->_targetSubControl))
			{
				$returnValue .= "&subcontrol={$this->_targetSubControl->Name}";
			}
		}

		return $returnValue;
	}

	protected function CalculateTopLevelControl()
	{
		if (is_set($this->_targetControl))
		{
			$returnValue = $this->_targetControl->TopLevelControl;
		}
		else
		{
			$returnValue = $this->_parentFunction->ParentFunctions->Control->TopLevelControl;
		}

		return $returnValue;

	}

	public function Duplicate($NewParentFunction)
	{
		if ($NewParentFunction instanceof JavascriptFunction)
		{
			$returnValue = new JavascriptLine($NewParentFunction, $this->_textLine, $this->_eventName, $this->_targetControl, $this->_targetSubControl, $this->_isAllValuesPassed);
		}

		return $returnValue;
	}

}
?>
