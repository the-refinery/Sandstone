<?php

class Dingus extends Module
{
	protected $_name;
	public $_methods = array();
	protected $_stack = array();
	
	public function __construct($Name)
	{
		$Name = strtolower($Name);
		
		$this->_name = $Name;
		$this->_stack[] = "{$Name} Initialized";
	}
	
	public function Stack()
	{
		return $this->_stack;
	}
	
	public function __toString()
	{
		return '';
	}
	
	public function SetReturnValue($MethodName, $ReturnValue)
	{
		$MethodName = strtolower($MethodName);
		
		if (substr($MethodName, -2, 2) == "()")
		{
			$MethodName = substr($MethodName, 0, strlen($MethodName) - 2);
			$this->_methods[$MethodName] = $ReturnValue;
		}
		else
		{
			$this->_methods['get' . $MethodName] = $ReturnValue;
		}
	}
	
	public function __get($MethodName)
	{
		$MethodName = 'get' . strtolower($MethodName);
		
		$returnValue = $this->__call($MethodName, null);
		
		return $returnValue;
	}
	
	public function __set($Name, $Value)
	{
		return true;
	}
	
	public function __call($MethodName, $Parameters)
	{
		$MethodName = strtolower($MethodName);
		
		if (array_key_exists($MethodName, $this->_methods))
		{
			$returnValue = $this->_methods[$MethodName];
		}
		else
		{
			$returnValue = new Dingus("{$this->_name}->{$MethodName}");
			$this->_methods[$MethodName] = $returnValue;
		}
		
		$this->_stack[] = $MethodName . "()";
		
		return $returnValue;
	}
}

?>