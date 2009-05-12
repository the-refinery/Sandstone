<?php

class Component
{
	static $_mixins = array();

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
