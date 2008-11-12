<?php

Namespace::Using("Sandstone.Date");
Namespace::Using("Sandstone.Exception");

class Query extends Module
{
	protected $_conn;

	protected $_sql;
	protected $_results;

	protected $_executionTime;

	protected $_selectedRows;
	protected $_affectedRows;

	public function __construct($DBconfig = null)
	{
		$this->_conn = Application::NewDatabaseConnection($DBconfig);

		$this->_results = new DIarray();

		$this->_executionTime = 0;

		$this->_selectedRows = 0;
		$this->_affectedRows = 0;
	}

	/*
	SQL property

	@return string
	@param string $Value
	 */
	public function getSQL()
	{
		return $this->_sql;
	}

	public function setSQL($Value)
	{
		$this->_sql = $Value;
	}

	/*
	Results property

	@return DIarray
	 */
	public function getResults()
	{
		return $this->_results;
	}

	public function getSingleRowResult()
	{
		if ($this->_selectedRows == 1)
		{
			$returnValue = $this->_results[0];
		}

		return $returnValue;
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
	SelectedRows property

	@return integer
	 */
	public function getSelectedRows()
	{
		return $this->_selectedRows;
	}

	/*
	AffectedRows property

	@return integer
	 */
	public function getAffectedRows()
	{
		return $this->_affectedRows;
	}

	public function Execute($ResultType = MYSQLI_ASSOC)
	{
		$this->_results->Clear();

		if (strlen($this->_sql) > 0)
		{
			//Fire the SQL command on our current Connection
			$result = $this->_conn->query($this->_sql);

			if ($this->_conn->errno == 0)
			{
				//Capture some values
				$this->_selectedRows = $result->num_rows;
				$this->_affectedRows = $this->_conn->affected_rows;

				if ($this->_selectedRows > 0)
				{
					//Loop through the results and build our results array
					while($row = $result->fetch_array($ResultType))
					{
						$this->_results[] = $row;
					}

	                //Release memory used by the result
					$result->free();
				}

				$this->_executionTime = $this->_conn->LastQueryExecuteTime;

			}
			else
			{
				throw new SQLexception($this->_conn->error, $this->_conn->error, $this->_sql);
			}
		}
		else
		{
			throw new SQLexception("Empty SQL Query", -1, $this->_sql);
		}
	}

	public function LoadEntityArray($TargetArray, $Class, $KeyProperty, $CallBackObject = null, $CallBackMethod = null)
	{
		$TargetArray->Clear();

		if ($this->_selectedRows > 0)
		{
			foreach($this->_results as $dr)
			{
				$tempItem = new $Class ($dr);

				if (is_set($CallBackObject) && is_set($CallBackMethod))
				{
					$newTempItem = $CallBackObject->$CallBackMethod ($tempItem);

					if (is_set($newTempItem))
					{
						$tempItem = $newTempItem;
					}
				}

				$TargetArray[$tempItem->$KeyProperty] = $tempItem;
			}
		}
	}

	public function LoadEntity($Entity)
	{

		$returnValue = false;

		if ($this->SelectedRows == 1)
		{
			$returnValue = $Entity->Load($this->SingleRowResult);
		}

		return $returnValue;
	}

	/*
	Allows for easy setting of database text fields

	@param string $Value
	@return string
	*/
	public function SetTextField($Value)
	{
		$returnValue = "'" . $this->_conn->real_escape_string($Value) . "'";
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
		if ($Value instanceof Date)
		{
			$returnValue = "'{$Value->MySQLtimestamp}'";
		}
		else
		{
			$this->SetDateField(new Date());
		}

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
			$returnValue = mysql_real_escape_string("{$Value}");
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
	public function SetBooleanField($Value)
	{

		if ($Value == true)
		{
			$returnValue = 1;
		}
		else
		{
			$returnValue = 0;
		}

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