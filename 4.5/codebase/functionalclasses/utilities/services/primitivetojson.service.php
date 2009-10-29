<?

class PrimitiveToJson extends Service
{
	static public function _($Primitive)
	{
		$returnValue = "{";
		$returnValue .= self::OutputProperties($Primitive);
		$returnValue .= "}";

		return $returnValue;
	}

	protected static function OutputProperties($Primitive)
	{
		foreach ($Primitive->AllProperties as $name=>$value)
		{
			$name = self::FormatPropertyName($name);
			$value = self::FormatPropertyValue($value);

			$returnValue .= "{$name}: {$value}, ";
		}

		$returnValue = substr($returnValue, 0, -2);

		return $returnValue;
	}

	protected static function FormatPropertyName($Name)
	{
		$returnValue = ucfirst(substr($Name, 1));

		return $returnValue;
	}

	protected static function FormatPropertyValue($Value)
	{
		if (is_array($Value) || $Value instanceof DIArray)
		{
			$returnValue = self::FormatArray($Value);
		}
		elseif (is_set($Value))
		{
			$returnValue = "'$Value'";
		}
		else
		{
			$returnValue = "''";
		}

		return $returnValue;
	}

	protected static function FormatArray($Array)
	{
		foreach ($Array as $value)
		{
			$returnValue .= $value . ", ";
		}

		$returnValue = substr($returnValue, 0 , -2);
		$returnValue = "[{$returnValue}]";

		return $returnValue;
	}

}
