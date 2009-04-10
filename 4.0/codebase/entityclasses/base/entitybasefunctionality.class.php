<?php
/*
Entity Base Functionality Class File

@package Sandstone
@subpackage EntityBase
*/

class EntityBaseFunctionality extends Module
{
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

	final protected function AddSearchProperty($PropertyName, $IsMultiEntity = false, $MatchWeight = 6, $WildcardWeight = 3, $DBfield = null)
	{
		$key = strtolower($PropertyName);
		
		if (array_key_exists($key, $this->_properties))
		{
			$property = $this->_properties[$key];
		}
		else
		{
			$property = new Property($this, $PropertyName, "string", $DBfield, PROPERTY_READ_ONLY);
			$this->_properties[$key] = $property;
		}
		
		$property->IsMultiEntity = $IsMultiEntity;
		$property->SearchMatchWeight = $MatchWeight;
		$property->SearchWildcardWeight = $WildcardWeight;
		$property->SearchDBfield = $DBfield;
		
		$this->_searchProperties[$key] = $property;
	}

	final protected function AddCollective($Name, $Type)
	{

		$newCollective = new $Type($Name, $this);

		$this->_collectives[strtolower($Name)] = $newCollective;

		$newCollective->Register($this->_collectiveProperties, $this->_collectiveMethods);
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


}
?>