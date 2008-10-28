<?php
/**
 * Javascript Functions  Class File
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

class JavascriptFunctions extends Module
{

	protected $_control;
	protected $_form;

	protected $_partName;

	protected $_functions;

	static public function FormatJavascriptBlock($Code)
	{
		if (strlen($Code) > 0 )
		{
			$returnValue = "<script type=\"javascript\">\n";

			//$returnValue .= DIescape($Code);
			$returnValue .= $Code;

			$returnValue .= "</script>\n";
		}

		return $returnValue;
	}

	public function __construct($Control, $PartName = null)
	{
		if ($Control instanceof BaseControl)
		{
			$this->_control = $Control;
		}
		else if ($Control instanceof PageForm)
		{
			$this->_form = $Control;
		}

		$this->_partName = $PartName;

		$this->_functions = Array();
	}

    public function __get($Name)
    {
        $getter='get'.$Name;

        if(method_exists($this,$getter))
        {
            $returnValue =  $this->$getter();
        }
        else
        {
            if (array_key_exists($Name, $this->_functions) == false)
            {
                $this->_functions[$Name] = new JavascriptFunction($this, $Name);

                //Make sure we have a buffer if this is a control
                if (is_set($this->_control))
                {
                	$this->_control->SetupBuffer();
                }
            }

            $returnValue = $this->_functions[$Name];
        }

        return $returnValue;
    }

    public function __toString()
    {
		foreach($this->_functions as $tempFunction)
		{
			$returnValue .= $tempFunction->__toString();
			$returnValue .= "\n";
		}

		return $returnValue;
    }

	/**
	 * Control property
	 *
	 * @return Control
	 */
	public function getControl()
	{
		return $this->_control;
	}

	/**
	 * Form property
	 *
	 * @return PageForm
	 */
	public function getForm()
	{
		return $this->_form;
	}

	/**
	 * PartName property
	 *
	 * @return string
	 */
	public function getPartName()
	{
		return $this->_partName;
	}

    /**
     * CallList property
     *
     * @return string
     */
    public function getCallList()
    {
        foreach($this->_functions as $tempName=>$tempFunction)
        {
			$returnValue .= $tempFunction->CallString;
        }

		$returnValue = rtrim($returnValue);

        return $returnValue;
    }

    /**
     * FunctionCount property
     *
     * @return integer
     */
	public function getFunctionCount()
	{
		return count($this->_functions);
	}

	public function Duplicate($TargetJS)
	{
		if ($TargetJS instanceof JavascriptFunctions)
		{
			foreach ($this->_functions as $tempName=>$tempFunction)
			{
				foreach($tempFunction->Code as $tempLine)
				{
					$TargetJS->$tempName->AddJavascriptLine($tempLine);
				}
			}
		}
	}

}
?>
