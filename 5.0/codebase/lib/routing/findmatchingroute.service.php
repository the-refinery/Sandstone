<?php

class FindMatchingRoute extends BaseService
{
	static function Find($Path, $Routes)
	{
		$returnValue = false;

		foreach ($Routes as $tempRoute)
		{
			$matcher = new MatchRoute($tempRoute);
			if ($matcher->CheckMatch($Path))
			{
				$returnValue = $tempRoute;
				break;
			}
		}

		return $returnValue;
	}
}
