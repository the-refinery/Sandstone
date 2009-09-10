<?php
/*
SQL Exception Class File

@package Sandstone
@subpackage Database
*/

Namespace::Using("Sandstone.Exception");

class SQLexception extends DIexception
{

	protected $_errorNumber;
	protected $_sql;

	public function __construct($Message, $ErrorNumber, $SQL)
	{
		parent::__construct($Message, 0);

		$this->_errorNumber = $ErrorNumber;
		$this->_sql = $SQL;
	}

	public function __toString()
	{

		//We'll make it report where the query was executed rather than the
		//actual execute line in the query class.
		$originalTraceArray = $this->getTrace();

		$line = $originalTraceArray[0]['line'];
		$file = $originalTraceArray[0]['file'];

		$returnValue .=
		"
					<h2><b>Error: </b>{$this->getMessage()}</h2>
					<h3><b>Line: </b>{$line}</h3>
					<h3><b>File: </b>{$file}</h3>
					<h4><b>SQL</b></h4>
					<pre>
					{$this->_sql}
					</pre>
		";

		$returnValue .= $this->DItraceAsString();

		return $returnValue;
	}


	/*
	ErrorNumber property

	@return string
	 */
	public function getErrorNumber()
	{
		return $this->_errorNumber;
	}


	/*
	SQL property

	@return string
	 */
	public function getSQL()
	{
		return $this->_sql;
	}


}
?>