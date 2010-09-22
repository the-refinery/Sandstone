<?php

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
