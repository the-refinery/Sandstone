<?php
/*
Repeater Item Class File

@package Sandstone
@subpackage Application
*/

class RepeaterItem extends ControlContainer
{

	protected $_element;

	protected $_destroyTVsOnRender;

	/*
	Element property

	@return variant
	@param variant $Value
	 */
	public function getElement()
	{
		return $this->_template->Element;
	}

	public function setElement($Value)
	{
		//What do we add as the Element TV to the template?
		if (is_array($Value))
		{
			$this->_template->Element = new ArrayAsObject($Value);
		}
		else
		{
			$this->_template->Element = $Value;
		}
	}

	/*
	DestroyTVsOnRender property

	@return boolean
	@param boolean $Value
	 */
	public function getDestroyTVsOnRender()
	{
		return $this->_destroyTVsOnRender;
	}

	public function setDestroyTVsOnRender($Value)
	{
		$this->_destroyTVsOnRender = $Value;
	}

	public function Render()
	{
		$this->_template->ControlName = $this->Name;

		$returnValue = parent::Render();

		if ($this->_destroyTVsOnRender)
		{
			$this->_template->DestroyTemplateVariables();
		}

		return $returnValue;
	}

}

//This breaks our standard of only 1 class per file,
//but it is ONLY used here as a helper class for the above RepeaterItem
class ArrayAsObject
{
	protected $_data;
	protected $_rawData;

	public function __construct($Data)
	{
		$this->_rawData = $Data;
		$this->_data = DIarray::ForceLowercaseKeys($Data);
	}

	public function __get($Name)
	{
		$Name = strtolower($Name);

		if ($Name == "rawdata")
		{
			$returnValue = $this->_rawData;
		}
		else
		{
			$returnValue = $this->_data[$Name];
		}

		return $returnValue;
	}

	public function __toString()
	{
		return "<strong>ERROR:</strong> There's something wrong with your repeater, or repeater item template.";
	}

	public function HasProperty($Name)
	{

		$Name = strtolower($Name);

		if (array_key_exists($Name, $this->_data))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

}

?>