<?php

class TranslateRestVerb extends BaseService
{
	static function Translate($ServerMethod, $InjectedMethod = null) 
	{
		if ($ServerMethod == "GET")
		{
			$verb = "GET";
		}
		elseif ($InjectedMethod == "PUT")
		{
			$verb = "PUT";
		}
		elseif ($InjectedMethod == "DELETE")
		{
			$verb = "DELETE";
		}
		elseif ($ServerMethod == "POST")
		{
			$verb = "POST";
		}

		return $verb;
	}
}
