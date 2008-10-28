<?php
/**
 * Control Styles Class File
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

class ControlStyles extends Module
{

	protected $_classes = Array();
	protected $_style = Array();

	/**
	 * Classes property
	 *
	 * @return array
	 */
	public function getClasses()
	{
		if (count($this->_classes) > 0)
		{
			$returnValue = "class=\"";
			$returnValue .= implode(" ", $this->_classes);
			$returnValue .= "\" ";
		}

		return $returnValue;
	}
	
	/**
	 * ControlStyle property
	 *
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getStyle()
	{

		if (count($this->_style) > 0)
		{
			$returnValue = "style=\"";
			$returnValue .= implode(" ", $this->_style);
			$returnValue .= "\" ";
		}

		return $returnValue;
	}

	public function setControlStyle($Value)
	{
		$this->ClearStyle();
		$this->AddStyle($Value);
	}

	public function AddClass($ClassName)
	{
		if (strlen($ClassName) > 0)
		{
			$this->_classes[strtolower($ClassName)] = $ClassName;
		}
	}

	public function RemoveClass($ClassName)
	{
		unset($this->_classes[strtolower($ClassName)]);
	}

	public function AddStyle($Style)
	{

		if (strlen($Style)> 0)
		{
			$this->_style[strtolower($Style)] = $Style;
		}

	}

	public function RemoveStyle($Style)
	{
		unset($this->_style[strtolower($Style)]);
	}

	public function ClearStyle()
	{
		$this->_style = Array();
	}

}
?>