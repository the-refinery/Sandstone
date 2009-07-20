<?php

class MatchRoute extends BasePrimitive
{
	static function CheckMatch($Route, $Path)
	{
		$matchPattern = self::GenerateMatchPattern($Route);
		$returnValue = (bool)preg_match($matchPattern, $Path);

		return $returnValue;
	}

	public function GenerateMatchPattern($Route)
	{
		foreach ($Route->Parameters as $tempParameter)
		{
			$patternParts[] = self::DetermineParameterMatchPattern($tempParameter);
		}

		$pattern = implode("/", $patternParts);
		$pattern = '@^' . $pattern . '$@i';

		return $pattern;
	}

	protected function IsVariableParameter($Parameter)
	{
		return strpos($Parameter, ":") === 0;
	}

	protected function DetermineParameterMatchPattern($Parameter)
	{
		if (self::IsVariableParameter($Parameter))
		{
			$Parameter = "[a-zA-Z0-9_-]+";
		}

		return $Parameter;
	}
}
