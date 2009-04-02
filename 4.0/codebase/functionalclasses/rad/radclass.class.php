<?php
/*
RAD Class Class File

@package Sandstone
@subpackage RAD
 */

class RADclass extends Module
{
	protected $_name;
	protected $_package;
	protected $_subPackage;
	protected $_primaryTable;
	protected $_isAccountBased;
    
    protected $_properties;
    
    protected $_primaryIDproperty;
    protected $_isSearchNeeded;
    
    public function __construct()
    {
        $this->_properties = new DIarray();
        
        $this->_isSearchNeeded = false;
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
	Package property
	
	@return string
	@param string $Value
	*/
	public function getPackage()
	{
		return $this->_package;
	}

	public function setPackage($Value)
	{
		$this->_package = $Value;
	}

	/*
	SubPackage property
	
	@return string
	@param string $Value
	*/
	public function getSubPackage()
	{
		return $this->_subPackage;
	}

	public function setSubPackage($Value)
	{
		$this->_subPackage = $Value;
	}

	/*
	PrimaryTable property
	
	@return string
	@param string $Value
	*/
	public function getPrimaryTable()
	{
		return $this->_primaryTable;
	}

	public function setPrimaryTable($Value)
	{
		$this->_primaryTable = $Value;
	}

	/*
	IsAccountBased property
	
	@return boolean
	@param boolean $Value
	*/
	public function getIsAccountBased()
	{
		return $this->_isAccountBased;
	}

	public function setIsAccountBased($Value)
	{
		$this->_isAccountBased = $Value;
	}

    public function AddProperty($PostData)
    {
        $newProperty = new RADproperty($PostData);
        $this->_properties[] = $newProperty;
        
        if ($newProperty->IsPrimaryID)
        {
            $this->_primaryIDproperty = $newProperty;
        }
        
        if ($newProperty->IsSearchable)
        {
            $this->_isSearchNeeded = true;
        }
    }

    public function Generate()
    {
        $output = $this->SetupRenderable("class");
        
        //Main Stuff
        $output->Template->ClassName = $this->_name;
        $output->Template->Package = $this->_package;
        $output->Template->SubPackage = $this->_subPackage;
        $output->Template->PrimaryTable= $this->_primaryTable;
        $output->Template->IsNotAccountBased = !($this->_isAccountBased);
        
        //Setup Properties
        $output->Template->SetupProperties = $this->GenerateSetupProperties();
        
        //Save New
        $output->Template->InsertFieldList = $this->GenerateInsertFieldList();
        $output->Template->InsertDataList = $this->GenerateInsertDataList();
        
        //Save Update
        $output->Template->UpdateDataList = $this->GenerateUpdateDataList();
        $output->Template->PrimaryIDfieldName = $this->_primaryIDproperty->DBfieldName;
        $output->Template->PrimaryIDvalue = "{\$this->{$this->_primaryIDproperty->ProtectedFieldName}}";
                        
        //Load Functions
        $output->Template->LoadFunctions = $this->GenerateLoadFunctions();
        
        //Base SQL
        $output->Template->BaseSelectClause = $this->GenerateBaseSelectClause();
        
        //Search
        $output->Template->SearchFunctions = $this->GenerateSearchFunctions();
                
        $returnValue = $output->Render();
        
        return $returnValue;
    }

    protected function SetupRenderable($TemplateFileName)
    {
        $returnValue = new Renderable();
        $returnValue->Template->Filename = $TemplateFileName;
        $returnValue->Template->RequestFileType = "rad";

        return $returnValue;
    }

    protected function GenerateSetupProperties()
    {
        $output = $this->SetupRenderable("addproperty");

        $adds = Array();

        foreach ($this->_properties as $tempProperty)
        {
            $parameters = Array();
            
            $output->Template->PropertyName = $tempProperty->Name;            
            $output->Template->DataType = $tempProperty->DataType;
            
            if (is_set($tempProperty->DBfieldName))
            {
                $output->Template->DBfieldName = "\"{$tempProperty->DBfieldName}\"";
            }
            else
            {
                $output->Template->DBfieldName = "null";
            }
            
            if ($tempProperty->IsPrimaryID)
            {
                $parameters[] = "PROPERTY_PRIMARY_ID";
            }
            else
            {
                if ($tempProperty->IsReadOnly)
                {
                    $parameters[] = "PROPERTY_READ_ONLY";
                }
                else
                {
                    if ($tempProperty->IsRequired)
                    {
                        $parameters[] = "PROPERTY_REQUIRED";
                    }
                    
                    if ($tempProperty->IsLoadedRequired)
                    {
                        $parameters[] = "PROPERTY_LOADED_REQUIRED";
                    }
                    
                    if (count($parameters) == 0)
                    {
                        $parameters[] = "PROPERTY_READ_WRITE";
                    }
                }
            }
            
            $formattedParameters = implode("+", $parameters);
            
            if (is_set($tempProperty->LoadFunction))
            {
                $formattedParameters .= ",\"{$tempProperty->LoadFunction}\"";
            }
            
            $output->Template->Parameters = $formattedParameters;
            
            $adds[] = $output->Render();
        }
        
        $returnValue = implode("\n", $adds);
        
        return $returnValue;
    }

    protected function GenerateInsertFieldList()
    {
        $fields = Array();
        
        if ($this->_isAccountBased)
        {
            $fields[] = "AccountID";
        }
        
        foreach ($this->_properties as $tempProperty)
        {
            if (is_set($tempProperty->DBfieldName) && $tempProperty->IsPrimaryID == false)
            {
                $fields[] = $tempProperty->DBfieldName;
            }
        }
        
        $tabs = "\t\t\t\t\t\t\t";
        
        $returnValue = implode(",\n" . $tabs, $fields);
        
        $returnValue = $tabs . $returnValue;
        
        return $returnValue;
    }

    protected function GenerateInsertDataList()
    {
        $data= Array();
        
        if ($this->_isAccountBased)
        {
            $data[] = "{\$this->AccountID}";
        }
        
        foreach ($this->_properties as $tempProperty)
        {
            if (is_set($tempProperty->DBfieldName) && $tempProperty->IsPrimaryID == false)
            {
                $data[] = $tempProperty->QueryDataFormat;
            }
        }
        
        $tabs = "\t\t\t\t\t\t\t";
        
        $returnValue = implode(",\n" . $tabs, $data);
        
        $returnValue = $tabs . $returnValue;
        
        return $returnValue;
    }

    protected function GenerateUpdateDataList()
    {
        $data= Array();

        foreach ($this->_properties as $tempProperty)
        {
            if (is_set($tempProperty->DBfieldName) && $tempProperty->IsPrimaryID == false)
            {
                $data[] = "{$tempProperty->DBfieldName} = {$tempProperty->QueryDataFormat}";
            }
        }

        $tabs = "\t\t\t\t\t\t\t";
        
        $returnValue = implode(",\n" . $tabs, $data);
        
        $returnValue = $tabs . $returnValue;
        
        return $returnValue;
    }

    protected function GenerateLoadFunctions()
    {
        $output = $this->SetupRenderable("loadfunction");  
        
        $functions = Array();
        
        foreach ($this->_properties as $tempProperty)
        {
            if (is_set($tempProperty->LoadFunction))
            {
                $output->Template->FunctionName = $tempProperty->LoadFunction;
                
                $output->Template->EntityChildLoadCode = $this->GenerateLoadChildEntityCode($tempProperty);
                
                $loadFunction = $output->Render();
                
                $callBackFuncation = $this->GenerateLoadFunctionCallback($tempProperty);
                
                $functions[] = $loadFunction . $callBackFuncation;
            }
        }        
        
        $returnValue = implode("\n", $functions);
        
        return $returnValue;
    }

    protected function GenerateLoadChildEntityCode($Property)
    {
        if (is_set($Property->ChildEntityClass) && is_set($Property->EntityIDproperty))
        {
            $output = $this->SetupRenderable("loadchildentity");
            
            $output->Template->ProtectedFieldName = $Property->ProtectedFieldName;
            $output->Template->ChildEntityClass = $Property->ChildEntityClass;
            $output->Template->PrimaryIDfieldName = $this->_primaryIDproperty->DBfieldName;
            $output->Template->PrimaryIDvalue = "{\$this->{$this->_primaryIDproperty->ProtectedFieldName}}";
            
            if (is_set($Property->EntityLinkProperty))
            {
                //We'll need a callback
                $output->Template->CallbackInfo = ", \$this, \"{$this->GenerateLoadFunctionCallbackName($Property)}\"";
            }

            $returnValue = $output->Render();
        }
        
        return $returnValue;
    }
    
    protected function GenerateLoadFunctionCallbackName($Property)
    {
        $returnValue = $Property->LoadFunction . "Callback";
        
        return $returnValue;
    }
    
    protected function GenerateLoadFunctionCallback($Property)
    {
        if (is_set($Property->EntityLinkProperty))
        {
            $output = $this->SetupRenderable("loadfunctioncallback");
            
            $output->Template->FunctionName = $this->GenerateLoadFunctionCallbackName($Property);
            $output->Template->ChildEntityClass = $Property->ChildEntityClass;
            $output->Template->EntityLinkProperty = $Property->EntityLinkProperty;
            
            $returnValue = $output->Render();
        }        
        
        return $returnValue;
    }

    protected function GenerateBaseSelectClause()
    {
        
        $fields = Array();
        
        foreach ($this->_properties as $tempProperty)
        {
            if (is_set($tempProperty->DBfieldName))
            {
                $fields[] = $tempProperty->DBfieldName;    
            }            
        }
        
        $tabs = "\t\t\t\t\t\t\t\t\t";
        
        $returnValue = "SELECT \t" . implode(",\n" . $tabs, $fields);
        
        return $returnValue;
    }

    protected function GenerateSearchFunctions()
    {
        if ($this->_isSearchNeeded)
        {
            
            $output = $this->SetupRenderable("singleentitysearch");
            
            $output->Template->ClassName = $this->_name;
            $output->Template->PrimaryIDpropertyName = $this->_primaryIDproperty->Name;
            $output->Template->LimitClause = "LIMIT {\$MaxResults}";
            $output->Template->WhereClauseAddition = "AND {\$searchClause}";
            
            $singleEntitySearches = Array();
            $multiEntitySearches = Array();

            foreach ($this->_properties as $tempProperty)
            {    
                if ($tempProperty->IsSearchable)
                {
                    $propertyClause = "LOWER({$tempProperty->DBfieldName}) {\$likeClause} ";
                    
                    $singleEntitySearches[] = $propertyClause;
                    
                    if ($tempProperty->IsSearchableMultiEntity)
                    {
                        $multiEntitySearches[] = $propertyClause;
                        
                        $output->Template->IsMultiSearchNeeded = true;
                    }
                }
            }

            $tabs = "\t\t\t\t\t\t\t";
                       
            $output->Template->SingleSearchClause = "\t" . implode("\n" . $tabs . "OR ", $singleEntitySearches);
            $output->Template->MultiSearchClause = "\t" . implode("\n" . $tabs . "OR ", $multiEntitySearches);
            
            $returnValue = $output->Render();
        }
        
        return $returnValue;
    }

}
?>