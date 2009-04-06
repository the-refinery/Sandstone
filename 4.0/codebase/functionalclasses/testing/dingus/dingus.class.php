<?php

class Dingus extends Module
{
	protected $_methods = array();
	protected $_stack = array();
	
	public function Stack()
	{
		return $this->_stack;
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
		
		if (in_array($MethodName, $this->_methods))
		{
			$returnValue = $this->_methods[$MethodName];
		}
		else
		{
			$returnValue = new Dingus();
			$this->_methods[$MethodName] = $returnValue;
		}
		
		if ($Parameters)
		{
			$parameterString = implode(', ', $Parameters);			
		}
		
		$this->_stack[] = "{$MethodName}({$parameterString})";
		
		return $returnValue;
	}
}

?>