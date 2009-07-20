<?php

class FindMatchingRoute extends BaseService
{
	static function Find($Path, $Routes)
	{
		$returnValue = false;

		foreach ($Routes as $tempRoute)
		{
			if (MatchRoute::CheckMatch($tempRoute, $Path))
			{
				$returnValue = $tempRoute;
				break;
			}
		}

		return $returnValue;
	}
}
