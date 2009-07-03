<?php

class MatchRoute extends Component
{
	protected $_route;
	protected $_keys;

	public function __construct(Route $Route)
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
		return (bool)preg_match($this->getMatchPattern(), $Path);
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
}
