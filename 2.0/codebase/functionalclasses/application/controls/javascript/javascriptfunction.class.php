<?php
/**
 * Javascript Function Class File
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

class JavascriptFunction extends Module
{

	protected $_parentFunctions;

	protected $_code;

	protected $_parameters;

	protected $_name;

	public function __construct($ParentFunctions, $Name)
	{

		$this->_parentFunctions = $ParentFunctions;
		$this->_name = $Name;

		$this->_code = Array();
		$this->_parameters = Array();
	}

	public function __toString()
	{
   		if (count($this->_code) > 0 && is_set($this->_name) && $this->IsSingleFunctionCall == false)
        {
            $returnValue = "function {$this->FunctionSignature} \n";
            $returnValue .= "{\n";

            foreach($this->_code as $tempCode)
            {
            	$returnValue .= $tempCode->Render();
            }

            $returnValue .= "} \n";
        }
        else
        {
        	$returnValue = null;
        }

        return $returnValue;

	}

	/**
	 * ParentFunctions property
	 *
	 * @return JavascriptFunctions
	 */
	public function getParentFunctions()
	{
		return $this->_parentFunctions;
	}

	/**
	 * Code property
	 *
	 * @return Array
	 */
	public function getCode()
	{
		return $this->_code;
	}

	/**
	 * Parameters property
	 *
	 * @return Array
	 */
	public function getParameters()
	{
		return $this->_parameters;
	}

	/**
	 * Name property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getName()
	{

        if (is_set($this->_parentFunctions->PartName))
        {
        	if ($this->_parentFunctions->Control instanceof ElementDrivenBaseControl)
        	{
        		$returnValue = "{$this->_parentFunctions->Control->Name}_{$this->_parentFunctions->PartName}";
        	}
        	else
        	{
        		$returnValue = $this->_parentFunctions->PartName;
        	}

        }
        else if (is_set($this->_parentFunctions->Control))
        {
			$returnValue = $this->_parentFunctions->Control->Name;
        }
        else if (is_set($this->_parentFunctions->Form))
        {
        	$returnValue = $this->_parentFunctions->Form->Name;
        }
		else
		{
			$returnValue = "Unknown";
		}

        $returnValue .= "_{$this->_name}";

		return $returnValue;
	}

	public function setName($Value)
	{
		$this->_name = $Value;
	}

	/**
	 * FunctionSignature property
	 *
	 * @return string
	 */
	public function getFunctionSignature()
	{

		if ($this->IsSingleFunctionCall)
		{
			$returnValue = $this->_code[0]->Render();
		}
		else
		{
	        $parameterList = implode(", ", $this->_parameters);

	        $returnValue = "{$this->Name}({$parameterList})";
		}

		return $returnValue;
	}

	/**
	 * IsSingleFunctionCall property
	 *
	 * @return boolean
	 */
	public function getIsSingleFunctionCall()
	{
		if (count($this->_code) == 1 && $this->_code[0]->IsFunctionCall)
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
	 * CallString property
	 *
	 * @return boolean
	 */
	public function getCallString()
	{
		$eventName = strtolower($this->_name);

		if (substr($eventName, 0, 2) == "on")
		{
			if ($this->IsSingleFunctionCall)
			{
				$returnValue = "{$eventName}=\"return {$this->_code[0]->Render(true)}\" ";
			}
			else if (count($this->_code > 0))
			{
				$returnValue = "{$eventName}=\"return {$this->FunctionSignature};\" ";
			}
		}

		return $returnValue;
	}

    public function Add($Code)
    {
        if (strlen($Code) > 0)
        {
            $this->_code[] = new JavascriptLine($this, $Code);
        }
    }

    public function AddEvent($EventName, $IsAllValuesPassed = false)
    {
    	$this->AddPageEvent($EventName, $IsAllValuesPassed);
    }

    public function AddPageEvent($EventName, $IsAllValuesPassed = false)
    {
        if (strlen($EventName) > 0)
        {
             $this->_code[] = new JavascriptLine($this, null, $EventName, null, null, $IsAllValuesPassed);
        }
    }

    public function AddControlEvent($EventName, $IsAllValuesPassed = false, $TargetControl = null, $TargetSubControl = null)
    {
        if (strlen($EventName) > 0)
        {


			if (is_set($TargetControl) == false && is_set($TargetSubControl) == false)
			{
				//Start off looking at this control as the Target.
				$TargetControl = $this->_parentFunctions->Control;

				//Does this control have a parent control?
				if ($TargetControl->ParentContainer instanceof BaseControl)
				{

					//Yes, so it's the TargetSubControl
					$TargetSubControl = $this->_parentFunctions->Control;

					//Set the target to the top level parent
					$TargetControl = $TargetControl->TopLevelControl;
				}
			}

             $this->_code[] = new JavascriptLine($this, null, $EventName, $TargetControl, $TargetSubControl, $IsAllValuesPassed);
        }
    }

	public function AddFunctionCall($JavascriptFunction, $ParameterList = Array())
	{
		if ($JavascriptFunction instanceof JavascriptFunction)
		{
			$this->_code[] = new JavascriptLine($this, null, null, null, null, null, $JavascriptFunction, $ParameterList);
		}
	}

	public function AddParameter($ParameterName)
	{
		if (strlen($ParameterName) > 0)
		{
			$this->_parameters[] = $ParameterName;
		}
	}

	public function AddJavascriptLine($JSline)
	{
		if ($JSline instanceof JavascriptLine)
		{
			$this->_code[] = $JSline->Duplicate($this);
		}
	}

	public function ClearLines()
	{
		$this->_code = Array();
	}

}
?>
