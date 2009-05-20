<?php

class Dingus
{
	protected $_properties = array();
	protected $_methods = array();

	public function __get($PropertyName)
	{
		$PropertyName = strtolower($PropertyName);

		if (array_key_exists($PropertyName, $this->_properties))
		{
			$returnValue = $this->_properties[$PropertyName];
		}
		else
		{
			$returnValue = new Dingus();
		}

		return $returnValue;
	}

	public function __set($PropertyName, $Value)
	{
		$PropertyName = strtolower($PropertyName);
		$this->_properties[$PropertyName] = $Value;

		return true;
	}

	public function __call($MethodName, $Parameters)
	{
		$MethodName = strtolower($MethodName);

		if (array_key_exists($MethodName, $this->_methods) == false)
		{
			$this->_methods[$MethodName] = new Dingus();
		}

		return $this->_methods[$MethodName];
	}
}
