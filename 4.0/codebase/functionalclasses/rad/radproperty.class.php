<?php
/*
RAD Property Class File

@package Sandstone
@subpackage RAD
 */

class RADproperty extends Module
{
	protected $_name;
	protected $_dataType;
	protected $_dbFieldName;
	protected $_loadFunction;
    
	protected $_isPrimaryID;
	protected $_isReadOnly;
	protected $_isRequired;
	protected $_isLoadedRequired;
	
    protected $_isSearchable;
	protected $_isSearchableMultiEntity;
	
    protected $_childEntityClass;
	protected $_entityIDproperty;
	protected $_entityLinkProperty;

    public function __construct($PostData = null)
    {
        if (is_set($PostData))
        {
            $parts = explode("|", $PostData);
            
            $this->Name = $parts[0];
            $this->DataType = $parts[1];
            $this->DBfieldName = $this->FormatStringPost($parts[2]);
            $this->LoadFunction = $this->FormatStringPost($parts[3]);
            
            $this->IsPrimaryID = $this->FormatBooleanPost($parts[4]);
            $this->IsReadOnly = $this->FormatBooleanPost($parts[5]);
            $this->IsRequired = $this->FormatBooleanPost($parts[6]);
            $this->IsLoadedRequired = $this->FormatBooleanPost($parts[7]);
            
            $this->IsSearchable = $this->FormatBooleanPost($parts[8]);
            $this->IsSearchableMultiEntity = $this->FormatBooleanPost($parts[9]);

            $this->ChildEntityClass = $this->FormatStringPost($parts[10]);
            $this->EntityIDproperty = $this->FormatStringPost($parts[11]);
            $this->EntityLinkProperty = $this->FormatStringPost($parts[12]);

        }
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
        switch (strtolower($Value))
        {
            case "int":
                $Value = "integer";
                break;
            
            case "varchar":
            case "text":
                $Value = "string";
                break;
            
            case "tinyint":
                $Value = "boolean";
                break;
            
            case "datetime":
                $Value = "date";
                break;
        }
        
		$this->_dataType = $Value;
	}

	/*
	DbFieldName property
	
	@return string
	@param string $Value
	*/
	public function getDbFieldName()
	{
		return $this->_dbFieldName;
	}

	public function setDbFieldName($Value)
	{
		$this->_dbFieldName = $Value;
	}

	/*
	LoadFunction property
	
	@return string
	@param string $Value
	*/
	public function getLoadFunction()
	{
		return $this->_loadFunction;
	}

	public function setLoadFunction($Value)
	{
        $this->_loadFunction = $Value;    
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
	IsSearchable property
	
	@return boolean
	@param boolean $Value
	*/
	public function getIsSearchable()
	{
		return $this->_isSearchable;
	}

	public function setIsSearchable($Value)
	{
		$this->_isSearchable = $Value;
	}

	/*
	IsSearchableMultiEntity property
	
	@return boolean
	@param boolean $Value
	*/
	public function getIsSearchableMultiEntity()
	{
		return $this->_isSearchableMultiEntity;
	}

	public function setIsSearchableMultiEntity($Value)
	{
		$this->_isSearchableMultiEntity = $Value;
	}

	/*
	ChildEntityClass property
	
	@return string
	@param string $Value
	*/
	public function getChildEntityClass()
	{
		return $this->_childEntityClass;
	}

	public function setChildEntityClass($Value)
	{
		$this->_childEntityClass = $Value;
	}

	/*
	EntityIDproperty property
	
	@return string
	@param string $Value
	*/
	public function getEntityIDproperty()
	{
		return $this->_entityIDproperty;
	}

	public function setEntityIDproperty($Value)
	{
		$this->_entityIDproperty = $Value;
	}

	/*
	EntityLinkProperty property
	
	@return string
	@param string $Value
	*/
	public function getEntityLinkProperty()
	{
		return $this->_entityLinkProperty;
	}

	public function setEntityLinkProperty($Value)
	{
		$this->_entityLinkProperty = $Value;
	}
  
    public function getProtectedFieldName()
    {
        $firstChar = substr($this->_name, 0, 1);
        $otherChars = substr($this->_name, 1);
        
        $returnValue = "_" . strtolower($firstChar) . $otherChars;
    
        return $returnValue;
    }
  
    public function getQueryDataFormat()
    {
        switch($this->_dataType)
        {
            case "string":
                $returnValue = $this->BuildQueryFunctionCall("Text");
                break;
            
            case "boolean":
                $returnValue = $this->BuildQueryFunctionCall("Boolean");
                break;
            
            case "integer":
            case "decimal":
                if ($this->_isRequired == false)
                {
                    $returnValue = $this->BuildQueryFunctionCall("Numeric");
                }
                else
                {
                    $returnValue =  "{\$this->{$this->ProtectedFieldName}}";
                }
                break;
            
            case "date":
                $returnValue = $this->BuildQueryFunctionCall("Date");                
                break;
            
            default:
                //Going to guess this is an entity object.
                
                $dataReference = "\$this->{$this->ProtectedFieldName}->{$this->_dbFieldName}";
                
                if ($this->_isRequired)
                {
                    $returnValue = "{{$dataReference}}";
                }
                else
                {
                    $returnValue = "{\$query->SetNullNumericField({$dataReference})}";
                }
                break;
        }
        
        
        return $returnValue;
    }
  
    public function getHash()
    {
		$values[] = $this->Name;
		$values[] = $this->DataType;	
		$values[] = $this->HashOptionalStringValue($this->DBfieldName);
		$values[] = $this->HashOptionalStringValue($this->LoadFunction);
		
		$values[] = $this->HashBoolenValue($this->IsPrimaryID);
		$values[] = $this->HashBoolenValue($this->IsReadOnly);
		$values[] = $this->HashBoolenValue($this->IsRequired);
		$values[] = $this->HashBoolenValue($this->IsLoadedRequired);
		
		$values[] = $this->HashBoolenValue($this->IsSearchable);
		$values[] = $this->HashBoolenValue($this->IsSearchableMultiEntity);
		
		$values[] = $this->HashOptionalStringValue($this->ChildEntityClass);
		$values[] = $this->HashOptionalStringValue($this->EntityIDproperty);
		$values[] = $this->HashOptionalStringValue($this->EntityLinkProperty);
		
		$returnValue = implode("|", $values);

        return $returnValue;
    }
  
	protected function HashOptionalStringValue($Value)
	{
		if (is_set($Value))
		{
			$returnValue = $Value;	
		}
		else
		{
			$returnValue = " ";
		}
	
		return $returnValue;
	}
	
	protected function HashBoolenValue($Value)
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
  
  
    protected function BuildQueryFunctionCall($Type)
    {
        $returnValue = "{\$query->Set";
        
        if ($this->_isRequired == false)
        {
            $returnValue .= "Null";
        }
        
        $returnValue .= "{$Type}Field(\$this->{$this->ProtectedFieldName})}";
        
        return $returnValue;
    }
  
    protected function FormatBooleanPost($Value)
    {
        if ($Value == 0)
        {
            $returnValue = false;
        }
        elseif ($Value == 1)
        {
            $returnValue = true;
        }
        else
        {
            $returnValue = $Value;
        }
        
        return $returnValue;
    }

    protected function FormatStringPost($Value)
    {
        if (strlen($Value) > 0 && $Value != " ")
        {
            $returnValue = $Value;    
        }
		else
        {
            $returnValue = null;
        }
        
        return $returnValue;
    }
    
    
}
?>