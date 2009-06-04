<?php

class Component
{
	static $_mixins = array();

	public function __get($Name)
	{
		$getter='get'.$Name;
		
		if(method_exists($this,$getter))
		{
			$returnValue = $this->$getter();
		}
		else
		{
			throw new Exception('Invalid Property');
		}
		
		return $returnValue;
	}
	
	public function __set($Name, $Value)
	{
		$setter='set'.$Name;
		
		if(method_exists($this,$setter))
		{
			$this->$setter($Value);
		}
	}

	public function HasProperty($PropertyName)
	{
		$getter = 'get' . $PropertyName;
		$setter = 'set' . $PropertyName;

		return method_exists($this, $getter) || method_exists($this, $setter);
	}

	// Mixins
	function __call($Func, $Args) 
	{
		if ($function = @self::$_mixins[$Func]) 
		{
			array_unshift($Args, $this);
			$returnValue = call_user_func_array($function, $Args);
		}

		return $returnValue;
	}
}
