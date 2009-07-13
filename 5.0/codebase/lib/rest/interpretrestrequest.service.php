<?php

class InterpretRestRequest extends Component
{
	protected $_verb;

	public function __construct($ServerMethod, $InjectedMethod = null) 
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

		$this->_verb = $verb;
	}

	public function getVerb()
	{
		return $this->_verb;
	}
}
