<?php
/*
Base Control Element Class File

@package Sandstone
@subpackage Application
*/

class ElementBase extends Renderable
{
   	protected $_control;

	protected $_value;
	protected $_label;

	public function __construct($Value, $Label, $Control)
	{
		parent::__construct();

		$this->_control = $Control;
		$this->_value = $Value;
		$this->_label = $Label;
	}

	/*
	Value property

	@return variant
	@param variant $Value
	*/
	public function getValue()
	{
		return $this->_value;
	}

	public function setValue($Value)
	{
		$this->_value = $Value;
	}

	/*
	Label property

	@return string
	@param string $Value
	*/
	public function getLabel()
	{
		return $this->_label;
	}

	public function setLabel($Value)
	{
		$this->_label = $Value;
	}

	/*
	IDtext property

	@return string
	*/
	public function getIDtext()
	{
		$returnValue = str_replace(" ", "", $this->_value);

		return $returnValue;
	}

}
?>
