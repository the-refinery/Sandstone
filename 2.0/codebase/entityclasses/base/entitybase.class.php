<?php
/**
 * Entity Base Class File
 * @package Sandstone
 * @subpackage EntityBase
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

NameSpace::Using("Sandstone.Tag");
NameSpace::Using("Sandstone.Message");

class EntityBase extends Module
{

	protected $_properties;
	protected $_primaryIDproperty;
	protected $_isPropertiesSetup;

	protected $_isOutput;

	protected $_isTagsDisabled;
	protected $_isMessagesDisabled;

    public function __construct($ID = null)
    {

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
                $this->LoadByID($ID, $conn);
            }
        }

        //Most entities will support tags, but certian ones may need them
        //disabled.
        if ($this->_isTagsDisabled != true)
        {
	        //Setup the tags
	        $this->_tags = new Tags($this);
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

	public function __get($Name)
	{
		$getter='get'.$Name;

		if(method_exists($this,$getter))
		{
			$returnValue = $this->$getter();
		}
		elseif (array_key_exists(strtolower($Name), $this->_properties))
		{
			$targetProperty = $this->_properties[strtolower($Name)];
			$returnValue = $targetProperty->PropertyValue;
		}
		elseif(substr($Name, 0, 1) == "_" && $this->IsInternalCall())
		{
			//Only allow this for internal calls

			//Determine the associated property name
			$propertyName = strtolower(substr($Name, 1, strlen($Name) - 1));

			//Does it exist?
			if (array_key_exists($propertyName, $this->_properties))
			{
				//Return it's value
				$targetProperty = $this->_properties[$propertyName];
				$returnValue = $targetProperty->Value;
			}
			else
			{
				throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
			}
		}
		else
		{
			throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
		}

		return $returnValue;
	}

	public function __set($Name, $Value)
	{
		$setter='set'.$Name;

		if(method_exists($this,$setter))
		{
			$this->$setter($Value);
		}
		else if(method_exists($this,'get'.$Name))
		{
			throw new InvalidPropertyException("Property $Name is read only!", get_class($this), $Name);
		}
		elseif (array_key_exists(strtolower($Name), $this->_properties))
		{
			$targetProperty = $this->_properties[strtolower($Name)];

			if ($targetProperty->IsReadOnly == false)
			{
				$targetProperty->PropertyValue = $Value;
			}
			else
			{
				throw new InvalidPropertyException("Property $Name is read only!", get_class($this), $Name);
			}
		}
		elseif(substr($Name, 0, 1) == "_" && $this->IsInternalCall())
		{
			//Only allow this for internal calls

			//Determine the associated property name
			$propertyName = strtolower(substr($Name, 1, strlen($Name) - 1));

			//Does it exist?
			if (array_key_exists($propertyName, $this->_properties))
			{
				//Set it's value
				$targetProperty = $this->_properties[$propertyName];
				$targetProperty->Value = $Value;
			}
			else
			{
				throw new InvalidPropertyException("No Writeable Property: $Name", get_class($this), $Name);
			}
		}
		else
		{
			throw new InvalidPropertyException("No Writeable Property: $Name", get_class($this), $Name);
		}
	}

	public function __toString()
	{

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

			$returnValue .= "<div id=\"{$anchorID}_summary\" style=\"border: 0; background-color: #ddd; padding: 6px;\">";

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

			$returnValue .= "<div id=\"{$anchorID}_detail\" style=\"border: 0; background-color: #ddd; padding: 6px; display:none;\">";

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

	/**
	 * PrimaryIDproperty property
	 *
	 * @return Property
	 */
	final public function getPrimaryIDproperty()
	{
		return $this->_primaryIDproperty;
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

		$this->AddProperty("Tags", null, null, true, false, false, false, false, null);
		$this->AddProperty("Messages", null, null, true, false, false, false, false, null);
		$this->AddProperty("SearchRank", integer, null, false, false, false, false, false, null);
		$this->AddProperty("TopSearchRank", integer, null, false, false, false, false, false, null);

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
        $conn = GetConnection();

        //Build the Select and from clause
        //We have to do it this way so we call the correct static functions
		$currentClassName = get_class($this);

		$cmd = "	\$selectClause = {$currentClassName}::GenerateBaseSelectClause();
					\$fromClause = {$currentClassName}::GenerateBaseFromClause();";

		eval($cmd);
        $whereClause = "WHERE {$this->_primaryIDproperty->DBfieldName} = {$ID} ";

        $query = $selectClause . $fromClause . $whereClause;

        $ds = $conn->Execute($query);

        if ($ds && $ds->RecordCount() > 0)
        {
            $dr = $ds->FetchRow();
            $returnValue = $this->Load($dr);
        }
        else
        {
            $returnValue = false;
        }

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
				//No primary property, just do an insert
				$returnValue = $this->SaveNewRecord();
			}

			if ($this->_isTagsDisabled != true)
			{
				//Save any tags
				$this->_tags->Save();
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

	final protected function AddProperty($Name, $DataType, $DBfieldName = null, $IsReadOnly = false, $IsRequired = false, $IsPrimaryID = false, $IsLoadedRequired = false, $IsLoadOnDemand = false, $LoadOnDemandFunctionName = null)
	{
		$newProperty = new Property($this, $Name, $DataType, $DBfieldName, $IsReadOnly, $IsRequired, $IsPrimaryID, $IsLoadedRequired, $IsLoadOnDemand, $LoadOnDemandFunctionName);

		$this->_properties[strtolower($Name)] = $newProperty;

		if ($IsPrimaryID)
		{
			$this->_primaryIDproperty = $newProperty;
		}
	}

	public function AddTag($TagText)
	{
		if ($this->_isTagsDisabled != true)
		{
			$returnValue = $this->_tags->AddTag($TagText);
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function RemoveTag($TagText)
	{
		if ($this->_isTagsDisabled != true)
		{
			$this->_tags->RemoveTag($TagText);
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

	/**
	 *
	 * Static Query Functions
	 *
	 */
	static public function GenerateBaseSelectClause()
	{
		return null;
	}

	static public function GenerateBaseFromClause()
	{
		return null;
	}


	/**
	 *
	 * Search Query Functions
	 *
	 */
	static public function SearchMultipleEntity($SearchTerm)
	{
		return null;
	}

	static public function SearchSingleEntity($SearchTerm)
	{

		return self::SearchMultipleEntity($SearchTerm);
	}

}
?>