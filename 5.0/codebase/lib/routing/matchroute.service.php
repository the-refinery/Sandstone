<?php

class MatchRoute extends BasePrimitive
{
	static public function CheckMatch($Route, $Path)
	{
		$returnValue = false;

		$matchPattern = self::util_GenerateMatchPattern($Route);
		
		$matches = preg_match($matchPattern, $Path);

		if ($matches >= 1)
		{
			$returnValue = true;
		}

		return $returnValue;
	}

	static public function util_GenerateMatchPattern($Route)
	{
		foreach ($Route->Parameters as $tempParameter)
		{
			$patternParts[] = self::util_DetermineParameterMatchPattern($tempParameter);
		}

		$pattern = implode("/", $patternParts);
		$pattern = '@^' . $pattern . '$@i';

		return $pattern;
	}

	static public function util_IsVariableParameter($Parameter)
	{
		return strpos($Parameter, ":") === 0;
	}

	static public function util_DetermineParameterMatchPattern($Parameter)
	{
		if (self::util_IsVariableParameter($Parameter))
		{
			$Parameter = "[a-zA-Z0-9_-]+";
		}

		return $Parameter;
	}
}
