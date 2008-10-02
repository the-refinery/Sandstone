<?php

class ActiveRecord extends Debug
{
	// Database table for this record
	protected $_tableName; 
	protected $_primaryKey;
	protected $_isLoaded;
	protected $_dataRow; 
	
	// Fields in the database, and their type
	// $_field['Name']['text'];
	protected $_fields = array();
	
	// Read Only Properties that are passed in by the loads Datarow
	protected $_extraFields = array();
	
	public function __construct($Lookup = null)
	{
		$this->ReverseEngineerTableStructure();
		
		if (is_numeric($Lookup))
		{
			$this->LoadByID($Lookup);
		}
		elseif (is_array($Lookup))
		{
			$this->Load($Lookup);
		}
	}
	
	public function __get($Name)
	{
		$Name = strtolower($Name);
		$methodName = "get" . $Name;
		
		if (method_exists($this, $methodName))
		{
			$returnValue = $this->$methodName();
		}
		elseif(array_key_exists($Name, $this->_fields))
		{
			$returnValue = $this->_fields[$Name]['Value'];
		}
		elseif(array_key_exists($Name, $this->_extraFields))
		{
			$returnValue = $this->_extraFields[$Name];
		}
		else
		{
			throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
		}
		
		return $returnValue;
	}
	
	public function __set($Name, $Value)
	{
		$Name = strtolower($Name);
		$methodName = 'set'.$Name;
		
		if(method_exists($this, $methodName))
		{
			$this->$methodName($Value);
		}
		elseif(array_key_exists($Name, $this->_fields))
		{
			$this->_fields[$Name]['Value'] = $Value;
		}
		else
		{
			throw new InvalidPropertyException("No Writeable Property: $Name", get_class($this), $Name);
		}
	}
	
	public function getIsLoaded()
	{
		return $this->_isLoaded;
	}
	
	public function setIsLoaded($Value)
	{
		$this->_isLoaded = $Value;
	}
	
	public function Load($dr)
	{
		$this->_dataRow = $dr;
		
		foreach ($dr as $key => $value)
		{
			$fieldName = strtolower($key);
			
			// Only add value is key is a property of this object
			if (array_key_exists($fieldName, $this->_fields))
			{
				$this->_fields[$fieldName]['Value'] = $this->GetDatabaseValue($this->_fields[$fieldName], $value);				
			}
			// Otherwise, add it to our array of read-only, not savable properties
			// Skip numeric
			elseif (is_numeric($key) == false)
			{
				$this->_extraFields[$fieldName] = $value;
			}
		}
		
		$this->_isLoaded = true;
	}
	
	public function LoadByID($ID)
	{
		$conn = GetConnection();
		
		foreach ($this->_fields as $field)
		{
			$fields[] = $field['DBName'];
		}
		
		$query = "SELECT " . implode(", ", $fields) . " FROM {$this->_tableName} WHERE {$this->_primaryKey} = {$ID}";

		$ds = $conn->Execute($query);
		$dr = $ds->FetchRow($query);
		
		$this->Load($dr);
	}
	
	public function Save()
	{
		// New or Updated record?
		if ($this->_fields[strtolower($this->_primaryKey)]['Value'] == "")
		{
			$this->SaveNewRecord();
		}
		else
		{
			$this->SaveUpdatedRecord();
		}
	}
	
	protected function SaveUpdatedRecord()
	{
		$conn = GetConnection();
			
		foreach($this->_fields as $key => $field)
		{
			// Only do this for fields that aren't the primary key
			if ($key != strtolower($this->_primaryKey))
			{
				$fieldString[] = $field['DBName'] . " = " . $this->SetDatabaseValue($field);
			}
		}
		
		$query = "UPDATE {$this->_tableName} SET " . implode(", ", $fieldString) . " WHERE " . $this->_fields[strtolower($this->_primaryKey)]['DBName'] . " = " . $this->_fields[strtolower($this->_primaryKey)]['Value'];
		
		$conn->Execute($query);
	}
	
	protected function SaveNewRecord()
	{
		$conn = GetConnection();
			
		foreach($this->_fields as $key => $field)
		{
			// Only do this for fields that aren't the primary key
			if ($key != strtolower($this->_primaryKey))
			{
				$fieldNames[] = $field['DBName'];
				$values[] = $this->SetDatabaseValue($field);
			}
		}
		
		$query = "INSERT INTO {$this->_tableName} ( " . implode(", ", $fieldNames) . ") VALUES (" . implode(", ", $values) . ")";
		
		$conn->Execute($query);
		
		// Set Insert ID
		$this->_fields[strtolower($this->_primaryKey)]['Value'] = $conn->Insert_ID();
		$this->_isLoaded = true;
	}
	
	public function Delete()
	{
		$conn = GetConnection();
			
		$query = "DELETE FROM {$this->_tableName}  WHERE " . $this->_fields[strtolower($this->_primaryKey)]['DBName'] . " = " . $this->_fields[strtolower($this->_primaryKey)]['Value'];
		
		$conn->Execute($query);
	}
	
	protected function ReverseEngineerTableStructure()
	{
		$conn = GetConnection();
		
		$query = "DESCRIBE {$this->_tableName}";
		$ds = $conn->Execute($query);
		
		while ($dr = $ds->FetchRow())
		{		
			// Check if Primary Key
			if ($dr['Key'] == "PRI")
			{
				$this->_primaryKey = $dr['Field'];
			}
			
			unset($tempField);
			$tempField['DBName'] = $dr['Field'];
			$tempField['Type'] = $this->GetFieldType($dr['Type']);
			$tempField['Length'] = $this->GetFieldLength($dr['Type']);
			$tempField['Nullable'] = $dr['Null'];
			$tempField['Value'] = null;
			
			$fieldName = strtolower($dr['Field']);
			$this->_fields[$fieldName] = $tempField;
		}
	}
	
	protected function GetFieldType($FieldMetaData)
	{
		$firstParenthesis = strpos($FieldMetaData, "(");
		
		if ($firstParenthesis)
		{
			$rowType = substr($FieldMetaData, 0, $firstParenthesis);
		}
		else
		{
			$rowType = $FieldMetaData;
		}
		
		switch ($rowType)
		{
			case "int":
				$returnValue = "integer";
				break;
				
			case "decimal":
				$returnValue = "decimal";
				break;
				
			case "float":
				$returnValue = "decimal";
				break;
				
			case "varchar":
				$returnValue = "text";
				break;
				
			case "text":
				$returnValue = "memo";
				break;
				
			case "datetime":
				$returnValue = "date";
				break;
				
			case "date":
				$returnValue = "date";
				break;
				
			case "tinyint":
				$returnValue = "boolean";
				break;
				
			default:
				$returnValue = "unknown";
				break;
		}
		
		return $returnValue;
	}
	
	protected function GetFieldLength($FieldMetaData)
	{
		// Using RegEx it's easy to get type and size at once, so I left this code in,
		// but it may be worth refactoring the GetFieldType using this methodology.
		list($type, $size) = preg_split('/[()]/', $FieldMetaData);
		
		$returnValue = $size; 
		
		return $returnValue;
	}
	
	// Accepts a field array
	protected function SetDatabaseValue($Field)
	{
		$conn = GetConnection();
		
		switch (1)
		{
			// NUMERICS
			case ($Field['Type'] == "integer" || $Field['Type'] == "decimal") && $Field['Nullable'] == "":
				// Regular, non-nullable number
				$returnValue = $Field['Value'];
				break;
				
			case ($Field['Type'] == "integer" || $Field['Type'] == "decimal") && $Field['Nullable'] == "YES":
				$returnValue = $conn->SetNullNumericField($Field['Value']);
				break;	
				
			// TEXT
			case ($Field['Type'] == "text" || $Field['Type'] == "memo") && $Field['Nullable'] == "":
				// non-nullable text
				$returnValue = $conn->SetTextField($Field['Value']);
				break;	
				
			case ($Field['Type'] == "text" || $Field['Type'] == "memo") && $Field['Nullable'] == "YES":
				// nullable text
				$returnValue = $conn->SetNullTextField($Field['Value']);
				break;	
				
			// DATES
			case $Field['Type'] == "date" && $Field['Nullable'] == "":
				// non-nullable date
				$returnValue = $conn->SetDateField($Field['Value']);
				break;	
			
			case $Field['Type'] == "date" && $Field['Nullable'] == "YES":
				// nullable date
				$returnValue = $conn->SetNullDateField($Field['Value']);
				break;	
				
			// BOOLEAN
			case $Field['Type'] == "boolean":
				$returnValue = $conn->SetBooleanField($Field['Value']);
				break;	
		}
		
		return $returnValue;
	}
	
	protected function GetDatabaseValue($Field, $Value)
	{
		$conn = GetConnection();
		
		switch (1)
		{
			// BOOLEAN
			case $Field['Type'] == "boolean":
				$returnValue = $conn->GetBooleanField($Value);
				break;	
				
			// Date
			case $Field['Type'] == "date":
				$returnValue = new Date($Value);
				break;
				
			default:
				$returnValue = $Value;
				break;
		}
		
		return $returnValue;
	}
	
}

?>