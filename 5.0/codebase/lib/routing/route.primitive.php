<?php

class Route extends BasePrimitive
{
	protected $_parameters;

	public function __construct($Parameters = array())
	{
		$this->_parameters = $Parameters;
	}

	public function getParameters()
	{
		return $this->_parameters;
	}

	public function getPath()
	{
		return implode("/", $this->_parameters);
	}
}
