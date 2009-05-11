<?php

class Component
{
	static $_mixins = array();

	function __call($Method, $Args) 
	{
		if ($Method = @self::$_mixins[$Method]) 
		{
			array_unshift($Args, $this);
			return call_user_func_array($Method, $Args);
		}
	}
}
