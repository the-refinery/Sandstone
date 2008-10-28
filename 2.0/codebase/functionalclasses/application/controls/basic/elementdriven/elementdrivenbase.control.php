<?php
/**
 * Base Element Driven Control Class File
 * @package Sandstone
 * @subpackage Application
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 */

class ElementDrivenBaseControl extends BaseControl
{

	protected $_elements;

	protected $_valueFormat;
	protected $_labelFormat;

   	protected $_valueProperties;
	protected $_labelProperties;

	protected $_elementJStemplate;

	public function __construct()
	{
		parent::__construct();

		$this->_elements = Array();

		$this->_elementJStemplate = new JavascriptFunctions($this);

	}

	/**
	 * Elements property
	 *
	 * @return array
	 */
	public function getElements()
	{
		return $this->_elements;
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
	 * LabelFormat Property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getLabelFormat()
	{
		return $this->_labelFormat;
	}

    /**
	 * RequiredJavascriptFunctions property
	 *
	 * @return string
	 */
	public function getRequiredJavascriptFunctions()
	{
		$returnValue = $this->_JS->__toString();

		foreach($this->_elements as $tempElement)
		{
			$returnValue .= $tempElement->JS->__toString();
		}

		return $returnValue;
	}

	public function setLabelFormat($Value)
	{
		$this->_labelFormat = $Value;
		$this->_labelProperties = $this->ParseFormatProperties($Value);
	}

    protected function RenderBufferDIV()
    {

        $isBufferNeeded = false;

        //Does the main control have JS?
        if ($this->_JS->FunctionCount > 0)
        {
            $isBufferNeeded = true;
        }

        //If not, check the elements
        if ($isBufferNeeded == false)
        {
            foreach($this->_elements as $tempElement)
            {
                if ($tempElement->JS->FunctionCount > 0)
                {
                    $isBufferNeeded = true;
                }
            }
        }

        if ($isBufferNeeded)
        {
            $returnValue = $this->Buffer->__toString();
        }

        return $returnValue;
    }


	protected function AddElementToArray($Key, $Element)
	{
		$this->_elements[$Key] = $Element;

		//Add any existing JS code to the element
		$this->_elementJStemplate->Duplicate($this->_elements[$Key]->JS);
	}

	public function RemoveElement($Value)
	{
		unset($this->_elements[$Value]);
	}

	public function ClearElements()
	{
		$this->_elements = Array();
	}

	public function ElementsJSadd($JSeventName, $Code)
	{

		//Loop through existing elements and add the code
		foreach($this->_elements as $tempElement)
		{
			$tempElement->JS->$JSeventName->Add($Code);
		}

		//Save this to add to future elements
		$this->_elementJStemplate->$JSeventName->Add($Code);
	}

	public function ElementsJSaddPageEvent($JSeventName, $PageEventName)
	{

		//Loop through existing elements and add the event
		foreach($this->_elements as $tempElement)
		{
			$tempElement->JS->$JSeventName->AddPageEvent($PageEventName);
		}

		//Save this to add to future elements
		$this->_elementJStemplate->$JSeventName->AddPageEvent($PageEventName);
	}

	public function ElementsJSaddControlEvent($JSeventName, $ControlEventName, $IsAllValuesPassed = false, $TargetControl = null, $TargetSubControl = null)
	{

		//Loop through existing elements and add the event
		foreach($this->_elements as $tempElement)
		{
			$tempElement->JS->$JSeventName->AddControlEvent($ControlEventName);
		}

		//Save this to add to future elements
		$this->_elementJStemplate->$JSeventName->AddControlEvent($ControlEventName);

	}

}
?>
