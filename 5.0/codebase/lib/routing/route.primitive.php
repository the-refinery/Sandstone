<?php

class Route extends Component
{
	protected $_parameters;
	
	public function __construct($Path)
	{
		$sanitize = new SanitizePath($Path);
		$Path = $sanitize->Path;

		$this->_parameters = $this->ConvertPathToParameters($Path);
	}

	public function getParameters()
	{
		return $this->_parameters;
	}

	public function getPath()
	{
		return implode("/", $this->_parameters);
	}

	protected function ConvertPathToParameters($Path)
	{
		return explode("/",$Path);
	}
}
