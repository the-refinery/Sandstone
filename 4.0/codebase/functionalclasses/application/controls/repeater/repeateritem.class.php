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

	public function RenderObservers($Javascript)
	{
		foreach ($this->_controls as $tempControl)
		{
			$returnValue .= $tempControl->RenderObservers($Javascript);
		}

		return $returnValue;
	}

}
