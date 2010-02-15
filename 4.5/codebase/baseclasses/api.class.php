<?php

class API
{
	public function __call($Method, $Parameters)
	{
    Benchmark::Log('API',$Method);

		if (class_exists($Method, true))
		{
			$class = new $Method ();

			if ($class instanceof APIbase)
			{
				$returnValue = call_user_func_array(array($class, "Main"), $Parameters);
			}
			else
			{
				$returnValue = call_user_func_array(array($class, "_"), $Parameters);
			}
		}
		else
		{
			throw new InvalidMethodException("Unknown API Method: {$Method}()", get_class($this), $Method);
		}

		return $returnValue;
	}

}
