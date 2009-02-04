<?php
/*
String Functions Abstract Class File

@package Sandstone
@subpackage Utilities
*/

class StringFunc
{
	/*
	Removes all non-numeric characters from a string.

	@return void
	*/
	static function MakeDecimal($String)
	{
		$ReturnValue = ereg_replace('[^0-9.]', '', $String);

		return $ReturnValue;
	}
	
	static function CamelCaseToSentance($Subject)
	{
		preg_match_all('/[A-Z][^A-Z]*/', $Subject, $results);

		return implode(' ', $results[0]);
	}

	static function RemovePunctuation($String)
	{
		$returnValue = preg_replace('/[^a-zA-Z0-9]/','',$String);

		return $returnValue;
	}

	static function FormatFilename($String)
	{
		$returnValue = preg_replace('/[^a-zA-Z0-9\.\-\_]/','',$String);

		return $returnValue;
	}
	
	static function FormatCurrency($Value)
	{
		if ($Value > 0)
		{
			$returnValue = "\$" . number_format($Value,2);
		}
		elseif (is_null($Value))
		{
			$returnValue = null;
		}
		else
		{
			$returnValue = "\$0.00";
		}

		return $returnValue;
	}

	static function FormatNumber($Value, $DecimalPlaces = 0)
	{
		return number_format($Value, $DecimalPlaces);
	}

	/*
	Reformat 112233 to 11-22-33 using format ##-##-##
	*/
	static function Reformat($Value, $Format)
	{
		$offset = 0;
		for ($i = 0; $i <= strlen($Format); $i++)
		{
			if ($Format[$i] == "#")
			{
				$returnValue[] = $Value[$i - $offset];
			}
			else
			{
				$returnValue[] = $Format[$i];
				$offset++;
			}
		}

		$returnValue = implode('', $returnValue);

		return $returnValue;
	}

	/*
	Formats a number into it's least significate digit
	4.0000 becomes 4
	4.3500 becomes 4.35
	*/
	static function FormatPrecision($Value)
	{

		//Do I have a decimal point?
		if (strpos($Value, ".") !== false)
		{
			$returnValue = rtrim($Value, "0");

			if (substr($returnValue, -1) == ".")
			{
				$returnValue = substr($returnValue, 0, -1);
			}

			if (strlen($returnValue) == 0)
			{
				$returnValue = 0;
			}
		}
		else
		{
			$returnValue = $Value;
		}

		return $returnValue;
	}


	static function ToHTML($Text)
	{
		$returnValue = "<p>" . str_replace("\n", "</p><p>", $Text) . "</p>";

		return $returnValue;
	}
}


?>