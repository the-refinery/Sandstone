<?php
/*
Entity Base Class File

@package Sandstone
@subpackage EntityBase
*/

NameSpace::Using("Sandstone.Database");
NameSpace::Using("Sandstone.Lookup");
NameSpace::Using("Sandstone.Message");
NameSpace::Using("Sandstone.ObjectSet");
NameSpace::Using("Sandstone.Tag");

define("PROPERTY_READ_WRITE", 0);
define("PROPERTY_READ_ONLY", 1);
define("PROPERTY_REQUIRED", 2);
define("PROPERTY_PRIMARY_ID", 4);
define("PROPERTY_LOADED_REQUIRED", 8);

class EntityBase extends Module
{

	const LOOKUP_TYPE_FIELDS = 1;
	const LOOKUP_TYPE_COUNT = 2;

	protected $_properties;
	protected $_primaryIDproperty;
	protected $_isPropertiesSetup;

	protected $_collectives;
	protected $_collectiveProperties;
	protected $_collectiveMethods;

	protected $_isOutput;
    protected $_inDestroy;

	protected $_isTagsDisabled;
	protected $_isMessagesDisabled;

	protected $_invalidPropertyName;

	protected $_searchResultsAction = "view";

    public function __construct($ID = null)
    {

    	$this->_properties = new DIarray();
		$this->_collectives = new DIarray();
		$this->_collectiveProperties = new DIarray();
		$this->_collectiveMethods = new DIarray();

		$this->_isOutput = false;

		$this->SetupProperties();

        if (is_set($ID))
        {
            if (is_array($ID))
            {
                $this->Load($ID);
            }
            else
            {
                $this->LoadByID($ID);
            }
        }

        //Most entities will support messages, but certian ones may need them
        //disabled.
        if ($this->_isMessagesDisabled != true)
        {
        	//Setup the Messages if we haven't already loaded them
        	if ($this->_isLoaded == false)
        	{
        		$this->_messages = new Messages(get_class($this), 0);
        	}

        }

    }

    public function Destroy()
    {

        if ($this->_inDestroy == false)
        {
            $this->_inDestroy = true;

            $this->_primaryIDproperty = null;

			foreach ($this->_properties as $tempProperty)
			{
				$tempProperty->Destroy();
			}

			$this->_properties->Clear();

			foreach ($this->_collectives as $tempCollective)
			{
				$tempCollective->Destroy();
			}

			$this->_collectives->Clear();
        }
    }

	public function __get($Name)
	{

		//Is this a call to a protected field?
		if (substr($Name, 0, 1) == "_")
		{
			//Is this an internal call?
			if ($this->IsInternalCall())
			{
				//This is an internal call...
				$returnValue = $this->ProcessGetProtectedField($Name);
			}
			else
			{
				//Not an internal call, so this is an exception!
				throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
			}
		}
		else
		{
			//Property call
			$returnValue = $this->ProcessGetPublicProperty($Name);
		}

		return $returnValue;
	}

	public function __set($Name, $Value)
	{

		//Is this a call to a protected field?
		if (substr($Name, 0, 1) == "_")
		{

			//Is this an internal call?
			if ($this->IsInternalCall())
			{
				//This is an internal call...
				$this->ProcessSetProtectedField($Name, $Value);
			}
			else
			{
				//Not an internal call, so this is an exception!
				throw new InvalidPropertyException("No Writeable Property: {$Name}", get_class($this), $Name);
			}
		}
		else
		{
			//Property call
			$this->ProcessSetPublicProperty($Name, $Value);
		}
	}

	public function __call($Name, $Parameters)
	{

		//Look to see if we know what do to with this name...

		if (array_key_exists(strtolower($Name), $this->_collectiveMethods))
		{
			//A method with this name has been registered by a collective.
			$targetCollective = $this->_collectiveMethods[strtolower($Name)];
			$returnValue = $this->ProcessCallCollectiveMethod($targetCollective, $Name, $Parameters);
		}
		elseif (strtolower(substr($Name, 0, 3)) == "add")
		{
			//This matches our "Add..." format for adding elements to a collective...

			$collectiveName = strtolower(substr($Name, 3)) . "s";

			//Do we have a matching collective?
			if (array_key_exists($collectiveName, $this->_collectives))
			{
				$targetCollective = $this->_collectives[$collectiveName];
				$returnValue = $this->ProcessCallCollectiveMethod($targetCollective, "AddElement", $Parameters);
			}
			else
			{
				throw new InvalidMethodException("No Public Method: {$Name}()", get_class($this), $Name);
			}

		}
		elseif (strtolower(substr($Name, 0, 6)) == "remove")
		{
			//This matches our "Remove.." format for Removing elements from a collective...
			$collectiveName = strtolower(substr($Name, 6)) . "s";

			//Do we have a matching collective?
			if (array_key_exists($collectiveName, $this->_collectives))
			{

				$targetCollective = $this->_collectives[$collectiveName];
				$returnValue = $this->ProcessCallCollectiveMethod($targetCollective, "RemoveElement", $Parameters);
			}
			else
			{
				throw new InvalidMethodException("No Public Method: {$Name}()", get_class($this), $Name);
			}
		}
		elseif (strtolower(substr($Name, 0, 5)) == "clear")
		{
			//This matches our "Clear.." format for Clearing elements in a collective...

			$collectiveName = strtolower(substr($Name, 5));

			//Do we have a matching collective?
			if (array_key_exists($collectiveName, $this->_collectives))
			{
				$targetCollective = $this->_collectives[$collectiveName];
				$returnValue = $this->ProcessCallCollectiveMethod($targetCollective, "ClearElements", $Parameters);
			}
			else
			{
				throw new InvalidMethodException("No Public Method: {$Name}()", get_class($this), $Name);
			}
		}
		else
		{
			throw new InvalidMethodException("No Public Method: {$Name}()", get_class($this), $Name);
		}

		return $returnValue;
	}

	public function __toString()
	{

        $divColor = "#9c9";

		$className = get_class($this);

		if (is_set($this->_primaryIDproperty))
		{
			if (is_set($this->_primaryIDproperty->Value))
			{
				$anchorID = "{$className}_{$this->_primaryIDproperty->Value}";
			}
			else
			{
				$anchorID = "{$className}_NEW";
			}

		}
		else
		{
			$randomID = rand();
			$anchorID = "{$className}_{$randomID}";
		}

		if ($this->_isOutput == false)
		{

			$this->_isOutput = true;

			$returnValue = "<a id=\"{$anchorID}\"></a>";

			$returnValue .= "<div id=\"{$anchorID}_summary\" style=\"border: 0; background-color: {$divColor}; padding: 6px;\">";

			$detailJS = "	document.getElementById('{$anchorID}_summary').style.display = 'none';
							document.getElementById('{$anchorID}_detail').style.display = 'block';";

			$summaryJS = "	document.getElementById('{$anchorID}_detail').style.display = 'none';
							document.getElementById('{$anchorID}_summary').style.display = 'block';";

			if (is_set($this->_primaryIDproperty))
			{
				if (is_set($this->_primaryIDproperty->Value))
				{
					$returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$detailJS}\"><b>{$this->_primaryIDproperty->Name}: {$this->_primaryIDproperty->Value}</a></b>";
				}
				else
				{
					$returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$detailJS}\"><b>{$this->_primaryIDproperty->Name}: New</a></b>";
				}

			}
			else
			{
				$returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$detailJS}\"><b>{$className}</a></b>";
			}

			$returnValue .= "</div>";

			$returnValue .= "<div id=\"{$anchorID}_detail\" style=\"border: 0; background-color: {$divColor}; padding: 6px; display:none;\">";

			$returnValue .= "<h1 style=\"padding: 0; margin: 0; border-bottom: 1px solid #000;\">{$className}</h1>";

			if (count($this->_properties) > 0)
			{

				if (is_set($this->_primaryIDproperty))
				{
					if (is_set($this->_primaryIDproperty->Value))
					{
						$returnValue .= "<h2 style=\"padding: 0; margin: 5px 0 5px 10px;\">{$this->_primaryIDproperty->Name}: {$this->_primaryIDproperty->Value}</h2>";
					}
					else
					{
						$returnValue .= "<h2 style=\"padding: 0; margin: 5px 0 5px 10px;\">{$this->_primaryIDproperty->Name}: NEW</h2>";
					}

				}

				$returnValue .= "<ul style=\"list-style: none; margin: 4px;\">";

				foreach ($this->_properties as $tempProperty)
				{
					if ($tempProperty->IsPrimaryID == false)
					{
						$returnValue .= $tempProperty->__toString();
					}
				}

				$returnValue .= "</ul>";
			}

			if (count($this->_collectives) > 0)
			{
				$returnValue .= "<ul style=\"list-style: none; margin: 4px;\">";

				foreach ($this->_collectives as $key=>$value)
				{
					$returnValue .= "<li style=\"border: 1px solid #fcc; margin: 2px; padding: 4px; background-color: #ffc;\">";
					$returnValue .= $value->__toString();
					$returnValue .= "</li>";
				}

				$returnValue .= "</ul>";
			}

			$returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$summaryJS}\">Close</a>";

			$returnValue .= "</div>";

		}
		else
		{
			if (is_set($this->_primaryIDproperty))
			{
				$returnValue .= "<a href=\"#{$anchorID}\"><b>{$this->_primaryIDproperty->Name}: {$this->_primaryIDproperty->Value}</a></b>";
			}
			else
			{
				$returnValue .= "<a href=\"#{$anchorID}\"><b>{$className}</a></b>";
			}

		}

		return $returnValue;
	}

	public function getSearchResultsText()
	{
		$returnValue = get_class($this);

		return $returnValue;
	}

	final public function getSearchResultsURL()
	{

		$returnValue = Routing::BuildURLbyEntity($this, $this->_searchResultsAction);

		return $returnValue;

	}

	/*
	SearchResultAction property

	@return string
	 */
	public function getSearchResultsAction()
	{
		return $this->_searchResultsAction;
	}

	public function getSearchResultsSignalStregnth()
	{

		$resultPercent =  round(($this->_searchRank / $this->_topSearchRank), 2) * 100;

		//Number of bars is 0 to 4
		switch ($resultPercent)
		{
			case 0:
				$returnValue = 0;
				break;

			case 100:
				$returnValue = 4;
				break;

			default:
				$returnValue = floor($resultPercent / 20);
				break;
		}

		return $returnValue;
	}

	protected function ProcessGetProtectedField($RequestedName)
	{

		//Determine the associated property name
		$propertyName = strtolower(substr($RequestedName, 1, strlen($RequestedName) - 1));

		//Can we find anything by this name?
		if (array_key_exists($propertyName, $this->_properties))
		{
			//We have a property!
			$targetProperty = $this->_properties[$propertyName];
			$returnValue = $targetProperty->Value;
		}
		elseif (substr($propertyName, -10) == "collective")
		{
			$returnValue = $this->ProcessGetCollectiveObject($RequestedName, $propertyName);
		}
		elseif (array_key_exists($propertyName, $this->_collectives))
		{
			//We have a collective
			$returnValue = $this->ProcessGetCollectiveValue($propertyName);
		}
		elseif (array_key_exists($propertyName, $this->_collectiveProperties))
		{
			//We have a registered collective property
			$returnValue = $this->ProcessGetCollectivePropertyValue($propertyName);
		}
		else
		{
			//No property by that name exists!
			throw new InvalidPropertyException("No Readable Property: {$RequestedName}", get_class($this), $RequestedName);
		}


		return $returnValue;
	}

	protected function ProcessGetPublicProperty($RequestedName)
	{

		$getter = "get{$RequestedName}";

		//Can we find anything by this name?
		if(method_exists($this, $getter))
		{
			//We have a getter by this name
			$returnValue = $this->$getter();
		}
		else
		{

			$propertyName = strtolower($RequestedName);

			if (array_key_exists($propertyName, $this->_properties))
			{
				//We have a property!
				$targetProperty = $this->_properties[$propertyName];
				$returnValue = $targetProperty->PropertyValue;
			}
			elseif (substr($propertyName, -10) == "collective")
			{
				$returnValue = $this->ProcessGetCollectiveObject($RequestedName, $propertyName);
			}
			elseif (array_key_exists($propertyName, $this->_collectives))
			{
				//We have a collective
				$returnValue = $this->ProcessGetCollectiveValue($propertyName);
			}
			elseif (array_key_exists($propertyName, $this->_collectiveProperties))
			{
				//We have a registered collective property
				$returnValue = $this->ProcessGetCollectivePropertyValue($propertyName);
			}
			else
			{
				//No property by that name exists!
				throw new InvalidPropertyException("No Readable Property: {$RequestedName}", get_class($this), $RequestedName);
			}
		}

		return $returnValue;
	}

	protected function ProcessGetCollectiveObject($RequestedName, $PropertyName)
	{
		//This is a request for a reference to an actual collective object
		$collectiveName = substr($PropertyName, 0, strlen($propertyName) - 10);

		if (array_key_exists($collectiveName, $this->_collectives))
		{
			$returnValue = $this->_collectives[$collectiveName];
		}
		else
		{
			//No collective by that name found
			throw new InvalidPropertyException("No Readable Property: {$RequestedName}", get_class($this), $RequestedName);
		}

		return $returnValue;
	}

	protected function ProcessGetCollectiveValue($PropertyName)
	{

		$targetCollective = $this->_collectives[$PropertyName];

		if ($targetCollective->IsLoaded == false)
		{
			$targetCollective->Load();
		}

		$returnValue = $targetCollective->Elements;

		return $returnValue;
	}

	protected function ProcessGetCollectivePropertyValue($PropertyName)
	{

		$targetCollective = $this->_collectiveProperties[$PropertyName];

		if ($targetCollective->IsLoaded == false)
		{
			$targetCollective->Load();
		}

		$returnValue = $targetCollective->$PropertyName;

		return $returnValue;
	}

	protected function ProcessSetProtectedField($RequestedName, $Value)
	{
		//Determine the associated property name
		$propertyName = strtolower(substr($RequestedName, 1, strlen($RequestedName) - 1));

		//Can we find anything by this name?
		if (array_key_exists($propertyName, $this->_properties))
		{
			//Set it's value
			$targetProperty = $this->_properties[$propertyName];
			$targetProperty->Value = $Value;
		}
		elseif (array_key_exists($propertyName, $this->_collectiveProperties))
		{
			//We have a registered collective property
			$returnValue = $this->ProcessSetCollectivePropertyValue($RequestedName, $propertyName, $Value);
		}
		else
		{
			throw new InvalidPropertyException("No Writeable Property: {$RequestedName}", get_class($this), $RequestedName);
		}

	}

	protected function ProcessSetPublicProperty($RequestedName, $Value)
	{
		$setter="set{$RequestedName}";

		if(method_exists($this, $setter))
		{
			$this->$setter($Value);
		}
		else
		{
        	$propertyName = strtolower($RequestedName);

			//Can we find anything by this name?
			if (array_key_exists($propertyName, $this->_properties))
			{
				//We have a property by this name!

				$targetProperty = $this->_properties[$propertyName];

				if ($targetProperty->IsReadOnly == false)
				{
					$targetProperty->PropertyValue = $Value;
				}
				else
				{
					throw new InvalidPropertyException("Property {$RequestedName} is read only!", get_class($this), $RequestedName);
				}
			}
			elseif (array_key_exists($propertyName, $this->_collectiveProperties))
			{
				//We have a registered collective property
				$returnValue = $this->ProcessSetCollectivePropertyValue($RequestedName, $Value);
			}
			else
			{
				//Is there a getter?
				$getter = "get{$RequestedName}";

				if (method_exists($this, $getter))
				{
					//A read-only property!
					throw new InvalidPropertyException("Property {$RequestedName} is read only!", get_class($this), $RequestedName);
				}
				else
				{
					//Unknown property
					throw new InvalidPropertyException("No Writeable Property: {$RequestedName}", get_class($this), $RequestedName);
				}
			}
		}
	}

	protected function ProcessSetCollectivePropertyValue($RequestedName, $Value)
	{

		if (substr($RequestedName, 0, 1) == "_")
		{
			$propertyName = substr($RequestedName, 1, strlen($RequestedName) - 1);
		}
		else
		{
			$propertyName = $RequestedName;
		}

		$targetCollective = $this->_collectiveProperties[strtolower($propertyName)];

		if ($targetCollective->IsLoaded == false)
		{
			$targetCollective->Load();
		}

		$targetCollective->$propertyName = $Value;

	}

	protected function ProcessCallCollectiveMethod($Collective, $Method, $Parameters)
	{

		if (count($Parameters) > 0)
		{
			foreach ($Parameters as $tempIndex=>$tempParameter)
			{
				if (is_object($tempParameter))
				{
					$paramValue = "\$Parameters[{$tempIndex}]";
				}
				elseif (is_numeric($tempParameter))
				{
					$paramValue = $tempParameter;
				}
				else
				{
					$paramValue = "'{$tempParameter}'";
				}

				if (strlen($args) > 0)
				{
					$args .= ", ";
				}

				$args .= $paramValue;
			}
		}

		$command = "\$returnValue = \$Collective->{$Method}($args);";

		eval($command);

		return $returnValue;
	}

	public function getAccountID()
	{

		$currentLicense = Application::License();

		if (is_set($currentLicense))
		{
			$returnValue = Application::License()->AccountID;
		}

		return $returnValue;
	}

	/*
	PrimaryIDproperty property

	@return Property
	*/
	final public function getPrimaryIDproperty()
	{
		return $this->_primaryIDproperty;
	}

	final public function getPrimaryID()
	{
		return $this->_primaryIDproperty->PropertyValue;
	}

	final public function getSearchRelevance()
	{

		if ($this->_topSearchRank > 0)
		{
			$returnValue = round($this->_searchRank / $this->_topSearchRank, 2);
		}
		else
		{
			$returnValue = 0;
		}

		return $returnValue;
	}

	public function getSearchResultsDisplayString()
	{
		return "{$this->_primaryIDproperty->Name}: {$this->_primaryIDproperty->Value}";
	}

	/*
	InvalidPropertyName property

	@return string
	 */
	final public function getInvalidPropertyName()
	{
		return $this->_invalidPropertyName;
	}

	final protected function IsInternalCall()
	{
    	$callStack = debug_backtrace();

    	//The call context we are interested in will be index 2 in the array.
    	// 0 = this function
    	// 1 = internal function call to this test
    	// 2 = context in question
		$context = $callStack[2];

		if ($context['object'] === $this)
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function SetupProperties()
	{

		$this->AddProperty("Messages", null, null, true, false, false, false, false, null);
		$this->AddProperty("SearchRank", integer, null, false, false, false, false, false, null);
		$this->AddProperty("TopSearchRank", integer, null, false, false, false, false, false, null);

        //Most entities will support tags, but certian ones may need them
        //disabled.
        if ($this->_isTagsDisabled != true)
        {
	        //Setup the tags
	        $this->AddCollective("Tags", "Tags");
        }

		$this->_isPropertiesSetup = true;
	}

	public function Load($dr)
	{

		if (count($this->_properties) > 0)
		{
			foreach ($this->_properties as $tempProperty)
			{
				$tempProperty->Load($dr);
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		if ($returnValue == true)
		{
			//Load any messages -
			//Most entities will support messages, but certian ones may need them
        	//disabled.
			if ($this->_isMessagesDisabled != true)
			{
				$this->_messages = new Messages(get_class($this), $this->_primaryIDproperty->Value);
			}
		}

		$this->_isLoaded = $returnValue;

		return $returnValue;
	}

    public function LoadByID($ID)
    {
        //Build the Select and from clause
        //We have to do it this way so we call the correct static functions
		$currentClassName = get_class($this);

		$cmd = "	\$selectClause = {$currentClassName}::GenerateBaseSelectClause();
					\$fromClause = {$currentClassName}::GenerateBaseFromClause();
					\$whereClause = {$currentClassName}::GenerateBaseWhereClause();";

		eval($cmd);

		if (strlen($whereClause) == 0)
		{
			$whereClause = "WHERE ";
		}
		else
		{
			$whereClause .= "AND ";
		}
        $whereClause .= "a.{$this->_primaryIDproperty->DBfieldName} = {$ID} ";

		$query = new Query();

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

        return $returnValue;

    }

    public function Save()
    {

    	//Are we OK to save?
    	$isOKtoSave = $this->ValidatePropertiesForSave();

    	if ($isOKtoSave)
    	{
			//Do we have a primary ID property?
			if (is_set($this->_primaryIDproperty))
			{
		        if (is_set($this->_primaryIDproperty->Value) || $this->_primaryIDproperty->Value > 0)
		        {
		            $returnValue = $this->SaveUpdateRecord();
		        }
		        else
		        {
		            $returnValue = $this->SaveNewRecord();
		        }
			}
			else
			{
				//No primary property - are we loaded?
				if ($this->IsLoaded)
				{
					$returnValue = $this->SaveUpdateRecord();
				}
				else
				{
					$returnValue = $this->SaveNewRecord();
				}
			}

		}
		else
		{
			$returnValue = false;
		}

        $this->_isLoaded = $returnValue;

        return $returnValue;

    }

    protected function ValidatePropertiesForSave()
    {

        $returnValue = true;

        if (count($this->_properties) > 0)
        {
	        foreach ($this->_properties as $tempProperty)
	        {
				if ($tempProperty->IsValidForSave == false)
				{
					$this->_invalidPropertyName = $tempProperty->Name;
					$returnValue = false;
				}
	        }
		}
		else
		{
			//If there aren't properties defined, we'll default to true
			//as there is probably some custom properties built.
			$returnValue = true;
		}

        return $returnValue;
    }

    protected function SaveNewRecord()
    {
        return true;
    }

    protected function SaveUpdateRecord()
    {
        return true;
    }

	protected function GetNewPrimaryID()
	{
		$query = new Query();

		$query->SQL = "SELECT LAST_INSERT_ID() newID ";

		$query->Execute();

		$this->_primaryIDproperty->Value = $query->SingleRowResult['newID'];
	}

	final protected function AddProperty($Name, $DataType, $DBfieldName = null, $IsReadOnly = false, $IsRequired = false, $IsPrimaryID = false, $IsLoadedRequired = false, $IsLoadOnDemand = false, $LoadOnDemandFunctionName = null)
	{
		
		//Which call mode are we in?
		if (($IsReadOnly === true || $IsReadOnly === false) == false)
		{
			//New style, options parameter is 4th, LoadOnDemand function a name is 5th
			$options = $IsReadOnly;
			
			if (strlen($IsRequired) > 0)
			{
				$LoadOnDemandFunctionName = $IsRequired;
			}
			
			if ($options & PROPERTY_PRIMARY_ID)
			{
				//Primary ID is a special case - always R/O, never required nor loaded required
				$IsPrimaryID = true;
				$IsReadOnly = true;
				$IsRequired = false;
				$IsLoadedRequired = false;
				$IsLoadOnDemand = false;
				$LoadOnDemandFunctionName = null;
			}
			else
			{
				$IsPrimaryID = false;
				
				if ($options & PROPERTY_READ_ONLY)
				{
					$IsReadOnly = true;
				}
				else
				{
					$IsReadOnly = false;
				}
				
				if ($options & PROPERTY_REQUIRED)
				{
					$IsRequired = true;
				}
				else
				{
					$IsRequired = false;
				}
				
				if ($options & PROPERTY_LOADED_REQUIRED)
				{
					$IsLoadedRequired = true;
				}
				else
				{
					$IsLoadedRequired = false;	
				}
				
				if (is_set($LoadOnDemandFunctionName))
				{
					$IsLoadOnDemand = true;
				}
			}
		
		}
		
		$newProperty = new Property($this, $Name, $DataType, $DBfieldName, $IsReadOnly, $IsRequired, $IsPrimaryID, $IsLoadedRequired, $IsLoadOnDemand, $LoadOnDemandFunctionName);		
		
		$this->_properties[strtolower($Name)] = $newProperty;

		if ($IsPrimaryID)
		{
			$this->_primaryIDproperty = $newProperty;
		}
	}

	final protected function AddCollective($Name, $Type)
	{

		$newCollective = new $Type($Name, $this);

		$this->_collectives[strtolower($Name)] = $newCollective;

		$newCollective->Register($this->_collectiveProperties, $this->_collectiveMethods);
	}

	public function AddTag($TagText)
	{
		if ($this->_isTagsDisabled != true && is_string($TagText))
		{
			$newTag = new Tag();
			$newTag->Text = $TagText;

			if ($newTag->IsLoaded == false)
			{
				$newTag->Save();
			}

			$newTag->User = Application::CurrentUser();
			$newTag->AddTimestamp = new Date();

			$returnValue = $this->_collectives['tags']->AddElement($newTag);

		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function RemoveTag($TagText)
	{
		if ($this->_isTagsDisabled != true && is_string($TagText))
		{
			$oldTag = new Tag();
			$oldTag->Text = $TagText;

			if ($oldTag->IsLoaded)
			{
				$this->_collectives['tags']->RemoveElement($oldTag);
			}
		}
	}

	public function hasProperty($Name)
	{
		$getter='get'.$Name;

		if(method_exists($this,$getter))
		{
			$returnValue = true;
		}
		elseif (array_key_exists(strtolower($Name), $this->_properties))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function SortChildren($Children, $OrderArray, $LoadFunction)
	{
		$returnValue = false;
		if ($Children instanceof DIarray && ($OrderArray instanceof DIarray || is_array($OrderArray)))
		{
			//Make sure all the children are Sortable
			$allOK = true;

			foreach ($Children as $tempChild)
			{
				if (($tempChild instanceof SortableEntityBase) == false)
				{
					$allOK = false;
				}
			}

			if ($allOK)
			{
				//Sort them.
				foreach($OrderArray as $index=>$childID)
				{
					$targetChild = $Children[$childID];
					$targetChild->SortOrder = $index;
					$targetChild->Save();
				}

				$returnValue = $this->$LoadFunction ();
			}

		}

		return $returnValue;
	}

	public function Lookup($Class, $Method, $Parameters, $PageSize, $PageNumber)
	{

		$targetFunctionName = $this->GenerateLookupFunctionName($Method);

		//Do we have a matching function for this lookup method?
		if (method_exists($this,$targetFunctionName))
		{
			$returnValue = $this->$targetFunctionName ($Parameters, self::LOOKUP_TYPE_FIELDS, $PageSize, $PageNumber);
		}

		return $returnValue;
	}

	public function LookupCount($Class, $Method, $Parameters)
	{
		$targetFunctionName = $this->GenerateLookupFunctionName($Method);

		//Do we have a matching function for this lookup method?
		if (method_exists($this,$targetFunctionName))
		{
			$ds = $this->$targetFunctionName ($Parameters, self::LOOKUP_TYPE_COUNT);

			if (count($ds) > 0)
			{
				$dr = $ds[0];

				$returnValue = $dr['LookupCount'];
			}
		}

		return $returnValue;

	}

	protected function GenerateLookupFunctionName($Method)
	{
		$returnValue = "Lookup_{$Method}";

		return $returnValue;
	}

	protected function Lookup_All($Parameters, $LookupType, $PageSize = null, $PageNumber = null)
	{

		$query = new Query();

		$selectClause = $this->GenerateLookupSelectClause($LookupType, $PageSize, $PageNumber);
		$fromClause = $this->GenerateBaseFromClause();
		$whereClause = $this->GenerateBaseWhereClause();
		$limitClause = $this->GenerateLookupLimitClause($PageSize, $PageNumber);

		$query->SQL = $selectClause . $fromClause . $whereClause . $limitClause;

		$query->Execute();

		return $query->Results;

	}

	protected function GenerateLookupSelectClause($LookupType, $PageSize, $PageNumber)
	{

		$className = get_class($this);

		//What are we selecting?  Fields or count?
		if ($LookupType == self::LOOKUP_TYPE_FIELDS)
		{
			$returnValue =  $this->GenerateBaseSelectClause();
		}
		else
		{
			$returnValue = "SELECT Count(*) LookupCount ";
		}

		return $returnValue;
	}

	protected function GenerateLookupLimitClause($PageSize, $PageNumber)
	{

		if (is_set($PageSize) && is_set($PageNumber))
		{
			$offset = $PageSize * ($PageNumber - 1);

			$returnValue =  "LIMIT {$offset}, {$PageSize}";
		}

		return $returnValue;
	}

	/*
	Static Query Functions
	*/
	static public function GenerateBaseSelectClause()
	{
		return null;
	}

	static public function GenerateBaseFromClause()
	{
		return null;
	}

	static public function GenerateBaseWhereClause()
	{

		$session = Application::Session();

		if (array_key_exists("IsAccountLimitOverride", $session) && $session['IsAccountLimitOverride'] == true)
		{
			$returnValue = "	WHERE	a.AccountID = a.AccountID ";
		}
		else
		{
			$returnValue = "	WHERE	a.AccountID = " . Application::License()->AccountID . " ";
		}

		return $returnValue;
	}

	/*
	Search Query Functions
	*/
	static public function SearchMultipleEntity($SearchTerm, $MaxResults)
	{
		return null;
	}

	static public function SearchSingleEntity($SearchTerm, $MaxResults)
	{

		return self::SearchMultipleEntity($SearchTerm);
	}

}
?>