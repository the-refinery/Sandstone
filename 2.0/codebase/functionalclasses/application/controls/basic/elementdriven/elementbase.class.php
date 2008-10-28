<?php
/**
 * Base Control Element Class File
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

class ElementBase extends Module
{
   	protected $_control;

	protected $_value;
	protected $_label;

	protected $_JS;

	public function __construct($Value, $Label, $Control)
	{
		$this->_control = $Control;
		$this->_value = $Value;
		$this->_label = $Label;

		$this->_JS = new JavascriptFunctions($this->_control, $Value);
	}

	/**
	 * Value property
	 *
	 * @return variant
	 *
	 * @param variant $Value
	 */
	public function getValue()
	{
		return $this->_value;
	}

	public function setValue($Value)
	{
		$this->_value = $Value;
	}

	/**
	 * Label property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getLabel()
	{
		return $this->_label;
	}

	public function setLabel($Value)
	{
		$this->_label = $Value;
	}

	/**
	 * JS property
	 *
	 * @return JavascriptFunctions
	 */
	public function getJS()
	{
		return $this->_JS;
	}

	/**
	 * IDtext property
	 *
	 * @return string
	 */
	public function getIDtext()
	{
		$returnValue = str_replace(" ", "", $this->_value);

		return $returnValue;
	}

}
?>
