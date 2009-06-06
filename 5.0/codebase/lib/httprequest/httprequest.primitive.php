<?php

class HTTPRequest extends Component
{
	protected $_server = array();

	public function __construct(array $Server)
	{
		// $Server is the content of the php global $_SERVER variable
		$this->_server = $Server;
	}

	public function getMethod()
	{
		return $this->_server['REQUEST_METHOD'];
	}

	public function getIsHttps()
	{
		return strlen($this->_server['HTTPS']) > 0;	
	}

	public function getClientIP()
	{
		return $this->_server['REMOTE_ADDR'];
	}
}
