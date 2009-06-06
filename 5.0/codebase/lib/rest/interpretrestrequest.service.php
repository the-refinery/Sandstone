<?php

class InterpretRestRequest extends Component
{
	protected $_server;
	protected $_request;

	public function __construct($Server, $Request)
	{
		$this->_server = $Server;
		$this->_request = $Request;
	}

	public function getVerb()
	{
		if ($this->_server->Method == "GET")
		{
			$returnValue = "GET";
		}
		elseif ($this->_request['_method'] == "PUT")
		{
			$returnValue = "PUT";
		}
		elseif ($this->_request['_method'] == "DELETE")
		{
			$returnValue = "DELETE";
		}
		elseif ($this->_server->Method == "POST")
		{
			$returnValue = "POST";
		}

		return $returnValue;
	}
}
