<?php

SandstoneNamespace::Using("Sandstone.Date");

class QueryLog extends Module
{

	protected $_class;
	protected $_callingClass;
	protected $_method;
	protected $_line;
	protected $_sql;
	protected $_executionTime;
	protected $_recordCount;

	public function __construct($Class, $CallingClass, $Method, $Line, $SQL, $ExecutionTime, $RecordCount)
	{
		$this->_class = $Class;
		$this->_callingClass = $CallingClass;
		$this->_method = $Method;
		$this->_line = $Line;
		$this->_sql = $SQL;
		$this->_executionTime = $ExecutionTime;
		$this->_recordCount = $RecordCount;
	}

	/*
	Class property

	@return string
	 */
	public function getClass()
	{
		return $this->_class;
	}

	/*
	CallingClass property

	@return string
	 */
	public function getCallingClass()
	{
		return $this->_callingClass;
	}

	/*
	Method property

	@return string
	 */
	public function getMethod()
	{
		return $this->_method;
	}

	/*
	Line property

	@return integer
	 */
	public function getLine()
	{
		return $this->_line;
	}

	/*
	SQL property

	@return string
	 */
	public function getSQL()
	{
		return $this->_sql;
	}

	/*
	ExecutionTime property

	@return decimal
	 */
	public function getExecutionTime()
	{
		return $this->_executionTime;
	}

	/*
	RecordCount property

	@return integer
	 */
	public function getRecordCount()
	{
		return $this->_recordCount;
	}

}
?>