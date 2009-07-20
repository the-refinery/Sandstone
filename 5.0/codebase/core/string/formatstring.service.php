<?php

class FormatString extends BaseService
{
	static function FormatSentanceCase($String)
	{
		$returnValue = ucfirst(strtolower($String));
		
		return $returnValue;
	}
	
	static function CamelCaseToSentance($Subject)
	{
		$Subject[0] = strtoupper($Subject[0]);

		if ($Subject[0])
		{	
			preg_match_all('/[A-Z][^A-Z]*/', $Subject, $results);

			$results = implode(' ', $results[0]);
			$results = FormatString::FormatSentanceCase($results);
		}

		return $results;
	}
}
