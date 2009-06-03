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

class EntityBase extends EntityBaseFunctionality
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

	
	protected $_searchEntityWeight;
	protected $_searchTagMatchWeight;
	protected $_searchTagWildcardWeight;
	protected $_searchResultsAction;
	
	protected $_searchProperties;
	protected $_searchFromClauseAddition;
	protected $_searchWhereClauseAddition;

    public function __construct($ID = null)
    {

    	$this->_properties = new DIarray();
		$this->_collectives = new DIarray();
		$this->_collectiveProperties = new DIarray();
		$this->_collectiveMethods = new DIarray();
		$this->_searchProperties = new DIarray();

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

	protected function SetupProperties()
	{

		$this->AddProperty("Messages", null, null, PROPERTY_READ_ONLY);
		$this->AddProperty("SearchRank", integer, null, PROPERTY_READ_ONLY);
		$this->AddProperty("TopSearchRank", integer, null, PROPERTY_READ_WRITE);

        //Most entities will support tags, but certian ones may need them
        //disabled.
        if ($this->_isTagsDisabled != true)
        {
	        //Setup the tags
	        $this->AddCollective("Tags", "Tags");
        }

		$this->_isPropertiesSetup = true;
	}

	protected function SetupSearch()
	{
		$this->_searchEntityWeight = 5;
		$this->_searchTagMatchWeight = 6;
		$this->_searchTagWildcardWeight = 2;
		
		$this->_searchResultsAction = "view";
		
		$this->_searchProperties->Clear();
	}


	public function getIsSearchable()
	{
		$returnValue = false;
		
		if (count($this->_searchProperties) == 0)
		{
			$this->SetupSearch();
		}
	
		if (count($this->_searchProperties) > 0)
		{
			$returnValue = true;
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

	public function HasTag($TagText)
	{
		$returnValue = false;

		if ($this->_isTagsDisabled != true && is_string($TagText))
		{

			$searchText = Tag::FormatTextForTag($TagText);
	
			foreach ($this->Tags as $tempTag)
			{
				if ($tempTag->Text == $searchText);
				{
					$returnValue = true;
				}
			}
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
		$orderByClause = $this->GenerateBaseOrderByClause();
		$limitClause = $this->GenerateLookupLimitClause($PageSize, $PageNumber);

		$query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

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

	public function Search($Query, $SearchTerm, $LikeClause, $MaxResults, $IsMultiEntitySearch)
	{
		if ($this->IsSearchable)
		{
			$Query->SQL = $this->GenerateSearchSQL($LikeClause, $MaxResults, $IsMultiEntitySearch);
			
			$Query->Execute();
		
			$returnValue = new ObjectSet($Query->Results, get_class($this), $this->_primaryIDproperty->Name);

			if ($returnValue->Count > 0)
			{
				foreach ($returnValue->ItemsByKey as $tempResult)
				{
					$tempResult->CalculateSearchWeight($SearchTerm, $IsMulti);
				}				
			}
		}
		
		return $returnValue;
	}
	
	protected function GenerateSearchSQL($LikeClause, $MaxResults, $IsMultiEntitySearch)
	{
		$selectClause = $this->GenerateBaseSelectClause();
		$fromClause = $this->GenerateBaseFromClause() . $this->_searchFromClauseAddition;
		$whereClause = $this->GenerateBaseWhereClause() . $this->_searchWhereClauseAddition;
	
		foreach ($this->_searchProperties as $tempProperty)
		{
			if (($IsMultiEntitySearch == false) || ($IsMultiEntitySearch == true && $tempProperty->IsMultiEntity))
			{
				$searchParts[] = "LOWER({$tempProperty->DBfieldName})";		
			}				
		}
		
		$searchClause = implode(" {$LikeClause} OR ", $searchParts);
		
		if (strlen($whereClause) > 0)
		{
			$whereClause .= "AND ({$searchClause}) ";
		}
		else
		{
			$whereClause = "WHERE {$searchClause} ";
		}
		
		$limitClause = " LIMIT {$MaxResults} ";
	
		$returnValue = $selectClause . $fromClause . $whereClause . $limitClause;
		
		return $returnValue;
	}
	
	public function CalculateSearchWeight($SearchTerm, $IsMultiEntitySearch)
	{
		$this->_searchRank =  null;
		
		if ($this->IsSearchable)
		{
			$SearchTerm = strtolower($SearchTerm);
			
			$rank = $this->CalculatePropertiesSearchWeight($SearchTerm, $IsMultiEntitySearch);
			$rank += $this->CalculateTagsSearchWeight($SearchTerm);
			
			$this->_searchRank = $rank * $this->_searchEntityWeight;
		}
	}
	
	protected function CalculatePropertiesSearchWeight($SearchTerm, $IsMultiEntitySearch)
	{
		$returnValue = 0;
		
		foreach ($this->_searchProperties as $tempProperty)
		{
			$returnValue += $tempProperty->CalculateSearchWeight($SearchTerm, $IsMultiEntitySearch);
		}
		
		return $returnValue;
	}
	
	protected function CalculateTagsSearchWeight($SearchTerm)
	{
		
		$returnValue = 0;
		
		foreach($this->Tags as $tempTag)
		{
			$tagText = strtolower($tempTag->Text);

			if ($tagText == $SearchTerm)
			{
				//Tag Match
				$returnValue += $this->_searchTagMatchWeight;
			}
			else
			{
				//Tag wildcard?
				if (substr_count($tagText, $SearchTerm) > 0)
				{
					$returnValue += $this->_searchTagWildcardWeight;
				}
			}
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

	static public function GenerateBaseOrderByClause()
	{
		return null;
	}
}
?>
