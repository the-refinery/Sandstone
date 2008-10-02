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
	}

	/*
	Elements property

	@return array
	*/
	public function getElements()
	{
		return $this->_elements;
	}

	/*
	ValueFormat Property

	@return string
	@param string $Value
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

	/*
	LabelFormat Property

	@return string
	@param string $Value
	*/
	public function getLabelFormat()
	{
		return $this->_labelFormat;
	}

	public function setLabelFormat($Value)
	{
		$this->_labelFormat = $Value;
		$this->_labelProperties = $this->ParseFormatProperties($Value);
	}

	protected function AddElementToArray($Key, $Element)
	{
		$this->_elements[$Key] = $Element;
	}

	public function RemoveElement($Value)
	{
		unset($this->_elements[$Value]);
	}

	public function ClearElements()
	{
		$this->_elements = Array();
	}

}
?>
