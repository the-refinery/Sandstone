<?php

/*
Module Class File
@package Sandstone
@subpackage BaseClasses

This abstract class provides standard routines necessary for our database access classes.
*/

abstract class Module extends Debug
{
	/*
	A flag that's set once the component is fully loaded.

	@var boolean
	*/
	protected $_isLoaded;

	protected $_exportEntities = Array();

	/*
	IsLoaded Property

	@return boolean
	*/
	public function getIsLoaded()
	{
		return $this->_isLoaded;
	}

	public function Export()
	{
		$returnValue = "<" . strtolower(get_class($this)) . ">";

		if (count($this->_exportEntities) > 0)
		{
			$returnValue .= implode(" ", $this->_exportEntities);
		}

		$returnValue .= "</" . strtolower(get_class($this)) . ">";

		return $returnValue;
	}

	protected function ExportFormatBoolean($Value)
	{
		if ($Value == true)
		{
			$returnValue = "true";
		}
		else
		{
			$returnValue = "false";
		}

		return $returnValue;
	}

	protected function CreateXMLentity($Tag, $Value, $IsBoolean = false)
	{

		$Tag = strtolower($Tag);

		$returnValue = "<{$Tag}>";

		if ($IsBoolean)
		{
			//Boolean values
			$returnValue .= $this->ExportFormatBoolean($Value);
		}
		else if (is_numeric($Value))
		{
			//Numeric Values
			$returnValue .= $Value;
		}
		else
		{
			$returnValue .= htmlentities($Value);
		}

		$returnValue .=  "</{$Tag}>";

		return $returnValue;
	}

	static public function AutoComplete($SearchString)
	{

	}
}

?>