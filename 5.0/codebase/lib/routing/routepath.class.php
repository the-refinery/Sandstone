<?php

class RoutePath extends Component
{
	protected $_routes = array();

	public function __construct()
	{
		// This order is important
		$this->AddRoute(':controller/:id', array('action', 'show'));
		$this->AddRoute(':controller/:id/edit', array('action', 'edit'));
		$this->AddRoute(':controller/new', array('action', 'new'));
		$this->AddRoute(':controller', array('action', 'index'));
	}

	public function GetRoutes()
	{
		return $this->_routes;
	}

	public function AddRoute($Path, $Parameters = array())
	{
		$tempRoute = new Route($Path);

		foreach ($Parameters as $key => $value)
		{
			$tempRoute->AddParameter($key, $value);
		}

		// prepended to the start of the array so that they are matched last
		array_unshift($this->_routes, $tempRoute);
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
