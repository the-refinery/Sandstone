<?php
/*
Database Connection Exception Class File

@package Sandstone
@subpackage Database
*/

SandstoneNamespace::Using("Sandstone.Exception");

class DatabaseConnectionException extends DIexception
{

	protected $_errorNumber;
	protected $_dbConfig;


	public function __construct($Message, $ErrorNumber, $DBconfig)
	{
		parent::__construct($Message, 0);

		$this->_errorNumber = $ErrorNumber;
		$this->_dbConfig = $DBconfig;
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
	DBConfig property

	@return array
	 */
	public function getDBConfig()
	{
		return $this->_dbConfig;
	}

}
?>