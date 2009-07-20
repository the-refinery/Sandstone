<?php

class Routing extends BasePrimitive
{
	protected $_routes = array();

	public function GetRoutes()
	{
		return $this->_routes;
	}

	public function AddRoute($Path)
	{
		$routeParameters = ConvertPathToParameters::Convert($Path);
		$tempRoute = new Route($routeParameters);

		// prepended to the start of the array so that they are matched last
		array_unshift($this->_routes, $tempRoute);
	}

	public function FindMatchingRoute($Path)
	{
		$returnValue = false;

		foreach ($this->_routes as $tempRoute)
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
