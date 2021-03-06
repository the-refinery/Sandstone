<?php

class Mock
{
	protected $_properties = array();
	protected $_methods = array();

	public $Recorder = array();
	public $MockName;

	public function __construct($Name = null)
	{
		if (is_null($Name))
		{
			$this->MockName = 'mock_' . spl_object_hash($this);
		}
		else
		{
			$this->MockName = $Name;
		}
	}

	public function __get($PropertyName)
	{
		$this->Recorder[] = $PropertyName;

		$PropertyName = strtolower($PropertyName);

		if (array_key_exists($PropertyName, $this->_properties))
		{
			$returnValue = $this->_properties[$PropertyName];
		}
		else
		{
			$returnValue = new Mock();
		}

		return $returnValue;
	}

	public function __set($PropertyName, $Value)
	{
		$this->Recorder[] = "{$PropertyName} = {$Value}";

		$PropertyName = strtolower($PropertyName);
		$this->_properties[$PropertyName] = $Value;

		return true;
	}

	public function __call($MethodName, $Arguments)
	{
		$this->RecordMethodCall($MethodName, $Arguments);

		$MethodName = strtolower($MethodName);

		if (array_key_exists($MethodName, $this->_methods) == false)
		{
			$this->SetReturnValue($MethodName, new Mock());
		}

		return $this->_methods[$MethodName];
	}

	public function SetReturnValue($MethodName, $Value)
	{
		$MethodName = strtolower($MethodName);

		$this->_methods[$MethodName] = $Value;

		return true;
	}

	public function SetPropertyValue($PropertyName, $Value)
	{
		$PropertyName = strtolower($PropertyName);

		$this->_properties[$PropertyName] = $Value;

		return true;
	}

	protected function RecordMethodCall($MethodName, $Arguments)
	{
		$argumentOutput = implode($Arguments, ', ');

		$this->Recorder[] = "{$MethodName}({$argumentOutput})";

		return true;
	}
}
