<?php

class MatchRoute extends BasePrimitive
{
	protected $_route;
	protected $_keys = array();
	protected $_matchValues = array();

	public function __construct($Route)
	{
		$this->_route = $Route;
	}

	public function getKeys()
	{
		if (count($this->_keys) == 0)
		{
			$this->ConvertParametersToKeys($this->_route->Parameters);
		}

		return $this->_keys;
	}

	public function GetParameter($Key)
	{
		$keyIndex = array_search($Key, $this->getKeys());

		if ($keyIndex !== false)
		{
			$returnValue = $this->_matchValues[$keyIndex];
		}

		return $returnValue;
	}

	public function getMatchPattern()
	{
		foreach ($this->_route->Parameters as $tempParameter)
		{
			$patternParts[] = $this->DetermineParameterMatchPattern($tempParameter);
		}

		$pattern = implode("/", $patternParts);
		$pattern = '@^' . $pattern . '$@i';

		return $pattern;
	}

	public function CheckMatch($Path)
	{
		$returnValue = (bool)preg_match($this->getMatchPattern(), $Path);

		if ($returnValue)
		{
			$this->ConvertMatchPathToValues($Path);
		}

		return $returnValue;
	}

	protected function ConvertParametersToKeys($Parameters)
	{
		foreach ($Parameters as $tempParameter)
		{
			$this->_keys[] = $this->DetermineKeyName($tempParameter);
		}		
	}

	protected function DetermineKeyName($Parameter)
	{
		if ($this->IsVariableParameter($Parameter))
		{
			$Parameter = substr($Parameter, 1);
		}

		return $Parameter;
	}

	protected function IsVariableParameter($Parameter)
	{
		return strpos($Parameter, ":") === 0;
	}

	protected function DetermineParameterMatchPattern($Parameter)
	{
		if ($this->IsVariableParameter($Parameter))
		{
			$Parameter = "[a-zA-Z0-9_-]+";
		}

		return $Parameter;
	}

	protected function ConvertMatchPathToValues($Path)
	{
		$this->_matchValues = explode("/", $Path);
	}
}
