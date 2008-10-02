<?php

Namespace::Using("Sandstone.Date");
Namespace::Using("Sandstone.Exception");

class Connection extends Module
{
	protected $_conn;

	public function __construct($conn)
	{
		$this->_conn = $conn;
	}

	public function __call($Name, $Parameters)
	{

		for($i=0; $i < count($Parameters); $i++)
		{
			if ($i == 0)
			{
				$parmList = "\$Parameters[{$i}]";
			}
			else
			{
				$parmList .= ", \$Parameters[{$i}]";
			}
		}

		$methodCall = "\$returnValue = \$this->_conn->{$Name}({$parmList});";

		eval($methodCall);

		return $returnValue;
	}

	public function getDebug()
	{
		return $this->_conn->debug;
	}

	public function setDebug($Value)
	{
		$this->_conn->debug = $Value;
	}

	/*
	Allows for easy setting of database text fields

	@param string $Value
	@return string
	*/
	public function SetTextField($Value)
	{
		$returnValue = "'" . mysql_real_escape_string($Value) . "'";

		return $returnValue;
	}

	/*
	Allows for easy setting of null database text fields

	@param string $Value
	@return variant Value or Null
	*/
	public function SetNullTextField($Value)
	{

		if (is_set($Value) && strlen($Value) > 0)
		{
			$returnValue = $this->SetTextField($Value);
		}
		else
		{
			$returnValue = "NULL";
		}

		return $returnValue;
	}

	/*
	Allows for easy setting of database datetime fields

	@param string $Value
	@return variant Value
	*/
	public function SetDateField($Value)
	{
		$returnValue = "'{$Value->MySQLtimestamp}'";

		return $returnValue;
	}

	/*
	Allows for easy setting of null database datetime fields

	@param string $Value
	@return variant Value or Null
	*/
	public function SetNullDateField($Value)
	{

		if ($Value instanceof Date)
		{
			$returnValue = "'{$Value->MySQLtimestamp}'";
		}
		else
		{
			$returnValue = "NULL";
		}

		return $returnValue;
	}

	public function SetTimestamp()
	{
		return $this->SetDateField(new Date());
	}

	/*
	Allows for easy setting of null database numeric fields

	@param string $Value
	@return variant Value or Null
	*/
	public function SetNullNumericField($Value)
	{
		if (is_set($Value) && strlen($Value) > 0)
		{
			$returnValue = "$Value";
		}
		else
		{
			$returnValue = "NULL";
		}

		return $returnValue;
	}

	/*
	Prepares a PHP boolean field (true or false) for database
	entry (1 or 0)

	@param boolean $BooleanValue
	@return boolean 1 or 0
	*/
	public function SetBooleanField($BooleanValue)
	{
		$returnValue = (INT) $BooleanValue;

		return $returnValue;
	}

	/*
	Takes a boolean database entry (0 or 1), and converts into
	PHP boolean (true or false)

	@param integer $DatabaseValue
	@return boolean
	*/
	static public function GetBooleanField($DatabaseValue)
	{
		if (is_set($DatabaseValue))
		{
			if ($DatabaseValue == 1)
			{
				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}
}


?>