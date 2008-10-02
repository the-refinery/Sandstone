<?php
/*
Property Class File

@package Sandstone
@subpackage EntityBase
*/

class Property extends Module
{

	protected $_parentClass;
	protected $_name;
	protected $_dataType;

	protected $_isReadOnly;
	protected $_isRequired;
	protected $_isPrimaryID;
	protected $_isLoadedRequired;

	protected $_isLoadOnDemand;
	protected $_loadOnDemandFunctionName;

	protected $_dbFieldName;

	protected $_value;

	public function __construct($ParentClass, $Name, $DataType, $DBfieldName = null, $IsReadOnly = false, $IsRequired = false, $IsPrimaryID = false, $IsLoadedRequired = false, $IsLoadOnDemand = false, $LoadOnDemandFunctionName = null)
	{
		if ($ParentClass instanceof EntityBase)
		{
			$this->_parentClass = $ParentClass;
		}

		$this->_name = $Name;
		$this->_dataType = strtolower($DataType);

		$this->_isReadOnly = $IsReadOnly;
		$this->_isRequired = $IsRequired;

		$this->_isPrimaryID = $IsPrimaryID;
		$this->_dbFieldName = $DBfieldName;

		$this->_isLoadedRequired = $IsLoadedRequired;

		$this->_isLoadOnDemand = $IsLoadOnDemand;
		$this->_loadOnDemandFunctionName = $LoadOnDemandFunctionName;

		//Setup the value (depending on data type)
		switch ($this->_dataType)
		{
			case "array":
				$this->_value = new DIarray();
				break;

			case "entitychildren":
				$this->_value = new EntityChildren();
				break;

			case "boolean":
				$this->_value = false;
				break;
		}

	}

    public function Destroy()
    {
        $this->_parentClass = null;

        if ($this->_value instanceof EntityBase || $this->_value instanceof DIarray || $this->_value instanceof EntityChildren)
        {
        	$this->_value->Destroy();
        }
    }

	public function __toString()
	{
		$returnValue = "<li style=\"border: 1px solid #fcc; margin: 2px; padding: 4px; background-color: #ffc;\">";

		$returnValue .= "<strong>{$this->_name}</strong>: ";

		if ($this->_dataType == "array")
		{
            $returnValue .= $this->_value->__toString();
		}
		else
		{
			if (is_object($this->_value))
			{
				$returnValue .= $this->FormatObjectValue($this->_value);
			}
			else
			{
				if (is_set($this->_value))
				{
					switch($this->_dataType )
					{
						case "string":
							$returnValue .= "\"{$this->_value}\"";
							break;

						case "boolean":
							if ($this->_value == true)
							{
								$returnValue .= "True";
							}
							else
							{
								$returnValue .= "False";
							}
							break;

						default:
							$returnValue .= "{$this->_value}";
							break;
					}

				}
				else
				{
					$returnValue .= "<em>null</em>";
				}
			}
		}

		$returnValue .= "</li>";

		return $returnValue;
	}

	/*
	ParentClass property

	@return EntityBase
	*/
	public function getParentClass()
	{
		return $this->_parentClass;
	}

	/*
	Name property

	@return string
	@param string $Value
	*/
	public function getName()
	{
		return $this->_name;
	}

	public function setName($Value)
	{
		$this->_name = $Value;
	}

	/*
	DataType property

	@return string
	@param string $Value
	*/
	public function getDataType()
	{
		return $this->_dataType;
	}

	public function setDataType($Value)
	{
		$this->_dataType = strtolower($Value);

		if ($this->_dataType == "array")
		{
			$this->_value = new ArrayObject();
		}

	}

	/*
	IsReadOnly property

	@return boolean
	@param boolean $Value
	*/
	public function getIsReadOnly()
	{
		return $this->_isReadOnly;
	}

	public function setIsReadOnly($Value)
	{
		$this->_isReadOnly = $Value;
	}

	/*
	IsRequired property

	@return boolean
	@param boolean $Value
	*/
	public function getIsRequired()
	{
		return $this->_isRequired;
	}

	public function setIsRequired($Value)
	{
		$this->_isRequired = $Value;
	}

	/*
	IsPrimaryID property

	@return boolean
	@param boolean $Value
	*/
	public function getIsPrimaryID()
	{
		return $this->_isPrimaryID;
	}

	public function setIsPrimaryID($Value)
	{
		$this->_isPrimaryID = $Value;
	}

	/*
	IsLoadedRequired property

	@return boolean
	@param boolean $Value
	*/
	public function getIsLoadedRequired()
	{
		return $this->_isLoadedRequired;
	}

	public function setIsLoadedRequired($Value)
	{
		$this->_isLoadedRequired = $Value;
	}

	/*
	IsLoadOnDemand property

	@return boolean
	@param boolean $Value
	*/
	public function getIsLoadOnDemand()
	{
		return $this->_isLoadOnDemand;
	}

	public function setIsLoadOnDemand($Value)
	{
		$this->_isLoadOnDemand = $Value;
	}

	/*
	LoadOnDemandFunctionName property

	@return string
	@param string $Value
	*/
	public function getLoadOnDemandFunctionName()
	{
		return $this->_loadOnDemandFunctionName;
	}

	public function setLoadOnDemandFunctionName($Value)
	{
		$this->_loadOnDemandFunctionName = $Value;
	}

	/*
	DbFieldName property

	@return string
	@param string $Value
	*/
	public function getDBfieldName()
	{
		return $this->_dbFieldName;
	}

	public function setDBfieldName($Value)
	{
		$this->_dbFieldName = $Value;
	}

	/*
	Value property

	@return variant
	@param variant $Value
	*/
	public function getValue()
	{
		return $this->_value;
	}

	public function setValue($Value)
	{
		$this->_value = $Value;
	}

	/*
	PropertyValue property

	@return variant
	@param variant $Value
	*/
	public function getPropertyValue()
	{
		if ($this->_isLoadOnDemand)
		{

			switch ($this->_dataType)
			{
				case "array":
					if (count($this->_value) == 0)
					{
						$isLoadNeeded = true;
					}
					break;

				case "entitychildren":
					if ($this->_value->Count == 0)
					{
						$isLoadNeeded = true;
					}
					break;

				default:
					if (is_set($this->_value) == false)
					{
						$isLoadNeeded = true;
					}
					break;

			}

			if ($isLoadNeeded)
			{
	            $functionName = $this->_loadOnDemandFunctionName;
	            $this->_parentClass->$functionName ();
			}

		}

		return $this->_value;
	}

	public function setPropertyValue($Value)
	{
		$this->_value = $this->InboundValidation($Value);
	}

	/*
	IsValidForSave property

	@return boolean
	*/
	public function getIsValidForSave()
	{

		if ($this->_isRequired)
		{
			if (is_set($this->_value))
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
			$returnValue = true;
		}

		return $returnValue;
	}

	protected function InboundValidation($Value)
	{

        if (is_set($Value))
        {
            switch ($this->_dataType)
            {
                case "boolean":
                    //Ensure that we return a valid boolean value
                    if ($Value == true)
                    {
                        $returnValue = true;
                    }
                    else
                    {
                        $returnValue = false;
                    }
                    break;

                case "integer":
                case "int":
                case "decimal":
                    if (is_numeric($Value))
                    {
                        $returnValue = $Value;
                    }
                    else
                    {
                        $returnValue = null;
                    }
                    break;

                case "string":
                    if (is_string($Value))
                    {
                        $returnValue = $Value;
                   	}
                    else
                    {
                        $returnValue = null;
                    }
					break;

                case "variant":
                    //Allow any data type here
                    $returnValue = $Value;
                    break;

                default:
                    if (class_exists($this->_dataType, true))
                    {
                        if ($Value instanceof $this->_dataType)
                        {
                        	if ($this->_isLoadedRequired)
                        	{
                        		if ($Value->IsLoaded)
                        		{
									$returnValue = $Value;
                        		}
                        		else
                        		{
                        			$returnValue = null;
                        		}
                        	}
                        	else
                        	{
                        		$returnValue = $Value;
                        	}

                        }
                        else
                        {
                            $returnValue = null;
                        }

                    }
                    else
                    {
                        $returnValue = null;
                    }
                    break;
            }
        }
        else
        {
            $returnValue = null;
        }


		return $returnValue;

	}

	public function Load($dr)
	{

		if (is_set($this->_dbFieldName))
		{
			switch ($this->_dataType)
			{
				case "boolean":
					$this->_value = Connection::GetBooleanField($dr[$this->_dbFieldName]);
					break;

				case "integer":
				case "decimal":
				case "string":
					$this->_value = $dr[$this->_dbFieldName];
					break;

				case "date":
					if (is_set($dr[$this->_dbFieldName]))
					{
						$this->_value = new Date($dr[$this->_dbFieldName]);
					}
					break;

				default:
					if (class_exists($this->_dataType, true))
					{
						if (is_set($dr[$this->_dbFieldName]))
						{
							$className = $this->_dataType;
							$this->_value = new $className ($dr[$this->_dbFieldName]);
						}
					}
					else
					{
						$this->_value = $dr[$this->_dbFieldName];
					}
					break;
			}
		}

	}

    protected function FormatArrayValue($Array)
    {

        $arrayCount = Count($Array);
        $returnValue .= "Array - Count= {$arrayCount}";

        if ($arrayCount > 0)
        {
            $returnValue .= "<ul style=\"margin-left: 6px;\">";

            foreach ($Array as $key=>&$value)
            {
                $returnValue .= "<li>";

                $returnValue .= "[{$key}]: ";

                if (is_object($value))
                {
                    $returnValue .= $this->FormatObjectValue($value);
                }
                else
                {
                    if (is_string($value))
                    {
                        $returnValue .= "\"{$value}\"";
                    }
                    else
                    {
                        $returnValue .= "{$value}";
                    }
                }

                $returnValue .= "</li>";

            }

            $returnValue .= "</ul>";
        }

        return $returnValue;

    }

	protected function FormatObjectValue($Value)
	{

		$valueClassName = get_class($Value);

		if ($Value instanceof EntityBase || $Value instanceof EntityChildren)
		{
			$returnValue .= $Value->__toString();
		}
		else if ($Value instanceof date)
		{
			$returnValue .= "{$Value->MySQLtimestamp}";
		}
		else if ($Value instanceof Tags)
		{
			$returnValue .= $Value->__toString();
		}
		else
		{

			$returnValue .= "{$valueClassName} object";
		}

		return $returnValue;
	}

}
?>