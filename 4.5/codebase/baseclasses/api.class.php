<?php

class API
{
	public function __call($Method, $Parameters)
	{
		if (class_exists($Method, true))
		{
			$class = new $Method ();
			$returnValue = call_user_func_array(array($class, "Main"), $Parameters);
		}
		else
		{
			throw new InvalidMethodException("Unknown API Method: {$Method}()", get_class($this), $Method);
		}

		return $returnValue;
	}

}
