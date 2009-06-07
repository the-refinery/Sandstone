<?php

class DispatchApplication extends Component
{
	protected $_route;

	public function __construct(array $Request)
	{
		$this->_route = $Request['routing'];
	}

	public function getRoute()
	{
		return $this->_route;
	}
}
