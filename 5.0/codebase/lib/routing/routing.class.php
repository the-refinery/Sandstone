<?php

class Routing extends Component
{
	protected $_routes = array();

	public function GetRoutes()
	{
		return $this->_routes;
	}

	public function AddRoute($Path)
	{
		$this->_routes[] = new Route($Path);
	}

	public function FindMatchingRoute($Path)
	{
		$returnValue = false;

		foreach ($this->_routes as $tempRoute)
		{
			if ($tempRoute->CheckRoutingMatch($Path))
			{
				$returnValue = $tempRoute;
				break;
			}
		}

		return $returnValue;
	}
}
