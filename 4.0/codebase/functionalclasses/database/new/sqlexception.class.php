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