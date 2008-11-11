<?php
/*
Search Class File

@package Sandstone
@subpackage Search
*/

Namespace::Using("Sandstone.Database");

class Search extends Module
{

	const DEFAULT_MAX_RESULTS = 30;

	protected $_availableTypes;

	protected $_types;
	protected $_searchTerm;

	protected $_results;
    protected $_tags;

	protected $_maxResults;

	public function __construct()
	{
		$this->_types = new DIarray();
        $this->_tags = new DIarray();
        $this->_results = new DIarray();

		$this->LoadAvailableTypes();

		//Set the default Max Results
		$this->_maxResults = self::DEFAULT_MAX_RESULTS;
	}

	/*
	Types property

	@return diarray
	*/
	public function getTypes()
	{
		return $this->_types;
	}

	/*
	SearchTerm property

	@return string
	@param string $Value
	*/
	public function getSearchTerm()
	{
		return $this->_searchTerm;
	}

	public function setSearchTerm($Value)
	{
		$this->_searchTerm = $Value;
	}

	/*
	Results property

	@return diarray
	*/
	public function getResults()
	{
		return $this->_results;
	}

    /*
    Tags property

    @return DIarray
    */
    public function getTags()
    {
        return $this->_tags;
    }

	/*
	MaxResults property

	@return integer
	@param integer $Value
	 */
	public function getMaxResults()
	{
		return $this->_maxResults;
	}

	public function setMaxResults($Value)
	{
		if (is_set($Value) && is_numeric($Value) && $Value > 0)
		{
			$this->_maxResults = $Value;
		}
		else
		{
			$this->_maxResults = self::DEFAULT_MAX_RESULTS;
		}
	}

	public function LoadAvailableTypes()
	{
		$returnValue = false;

		$this->_availableTypes = new DIarray();

		$query = new Query();

		$selectClause = SearchEntity::GenerateBaseSelectClause();
		$fromClause = SearchEntity::GenerateBaseFromClause();

		$whereClause = "WHERE	IsActive = 1 ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$query->LoadEntityArray($this->_availableTypes, "SearchEntity", "ClassName");
			$returnValue = true;
		}

		return $returnValue;
	}

	public function AddType($ClassName)
	{
		$classNameKey = strtolower($ClassName);

		//Validate that the supplied type exists
		if (array_key_exists($classNameKey, $this->_availableTypes))
		{
			$this->_types[$classNameKey] = $this->_availableTypes[$classNameKey];

            //Any change to the search criteria clears previous results
            $this->_tags->Clear();
            $this->_results->Clear();

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function RemoveType($ClassName)
	{
		unset($this->_types[strtolower($ClassName)]);

        //Any change to the search criteria clears previous results
        $this->_tags->Clear();
        $this->_results->Clear();

	}

    public function Search($SearchTerm)
    {
        //Clear any previous results
        $this->_tags->Clear();
        $this->_results->Clear();

        $this->_searchTerm = $SearchTerm;

		$returnValue = $this->SetupTypesArray();

        if ($returnValue == true)
        {
			//Build a CSV of all active class names
			//This is used in Tag and Message Searches to limit the results
			//to only the specific types selected
			$typeNamesCSV = $this->BuildTypesCSV();

            //Setup our grouped results array
            $groupedResults = new DIarray();

			//Perform the search on each type
			$this->PerformTypesSearch($SearchTerm, $groupedResults);

			//Perform a tag search, and add it's results to our GroupedResults array
			$this->PerformTagSearch($SearchTerm, $groupedResults, $typeNamesCSV);

			//Perform a message search, and add it's results to our GroupedResults array
			//$this->PerformMessageSearch($SearchTerm, $groupedResults, $typeNamesCSV);

            //Do we have results?
            if (count($groupedResults) > 0)
            {
				//Calculate each results rank
				$this->CalculateResultsRank($SearchTerm, $groupedResults);

				//Now build the final sorted results array
				$this->BuildFinalResults($groupedResults);

				$returnValue = true;
			}
            else
            {
                $returnValue = false;
            }
        }

        return $returnValue;
    }

	protected function SetupTypesArray()
	{

        //Has the user supplied specific types to search?
        if (count($this->_types) == 0)
        {
            //No - default to all available, did we any available?
            if (count($this->_availableTypes) > 0)
            {
                $this->_types = $this->_availableTypes;
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

	protected function BuildTypesCSV()
	{

		foreach ($this->_types as $tempType)
		{
			$tempClassNames[] = "'{$tempType->ClassName}'";
		}
		$returnValue = implode(",", $tempClassNames);

		return $returnValue;
	}

	protected function PerformTypesSearch($SearchTerm, $GroupedResults)
	{

        //Are we searching across multiple types or a single type?
        if (count($this->_types) == 1)
        {
            //Single Entity Search
            $isSingleEntitySearch = true;
        }
        else
        {
            //Multiple Entity Search
            $isSingleEntitySearch = false;
        }

	    //Loop through each of the types and get it's
	    //dataset of all matches
	    foreach ($this->_types as $tempType)
	    {
	        $typeResults  = $this->PerformIndividualTypeSearch($tempType, $SearchTerm, $isSingleEntitySearch);

	        if (is_set($typeResults))
	        {
	            $GroupedResults[strtolower($tempType->ClassName)] = $typeResults;
	        }
	    }

		return true;
	}

    protected function PerformIndividualTypeSearch($CurrentType, $SearchTerm, $IsSingleEntitySearch)
    {

		//Make sure we have the necessary namespace in use
		Namespace::Using($CurrentType->RequiredNamespace);

		//Since we have a dynamic class name, we have to build the
		//search statement then eval it.
		if ($IsSingleEntitySearch)
		{
			$functionName = "SearchSingleEntity";
		}
		else
		{
			$functionName = "SearchMultipleEntity";
		}

		$cmd = "\$objectSet= {$CurrentType->ClassName}::{$functionName}(\"{$SearchTerm}\", {$this->_maxResults});";

		//Perform the search
		eval($cmd);

		$returnValue = $objectSet->ItemsByKey;

    	return $returnValue;
    }

	protected function PerformTagSearch($SearchTerm, $GroupedResults, $TypeNamesCSV)
	{

		$success = $this->LoadMatchingTags($SearchTerm, $TypeNamesCSV);

		if ($success)
		{
			$returnValue = $this->LoadTagResults($GroupedResults, $TypeNamesCSV);
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	protected function LoadMatchingTags($SearchTerm, $TypeNamesCSV)
	{

		$returnValue = false;

		$query = new Query();

		$tagText  = Tag::FormatTextForTag($SearchTerm);

		$tagText = mysql_real_escape_string($tagText);

		$likeClause = "LIKE '%{$tagText}%' ";

		$accountID = Application::License()->AccountID;

		$query->SQL = "	SELECT	a.TagID,
								a.TagText
						FROM	core_TagMaster a
								INNER JOIN core_EntityTag b ON
									b.TagID = a.TagID
									AND  b.AssociatedEntityType IN ({$TypeNamesCSV})
						WHERE	a.TagText {$likeClause}
						AND		a.AccountID = {$accountID}
						ORDER BY TagText ";

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$query->LoadEntityArray($this->_tags, "Tag", "TagID");

			$returnValue = true;
		}

		return $returnValue;
	}

	protected function LoadTagResults($GroupedResults, $TypeNamesCSV)
	{
		$query = new Query();

		$tagIDs = implode(",", $this->_tags->Keys());

		$query->SQL = "	SELECT	DISTINCT AssociatedEntityID,
								AssociatedEntityType
						FROM	core_EntityTag
						WHERE	TagID IN ({$tagIDs})
						AND		AssociatedEntityType IN ({$TypeNamesCSV})
						ORDER BY AddTimestamp DESC ";

		$query->Execute();

		$returnValue = $this->LoadResultsFromTypeIDdataset($GroupedResults, $query);

		return $returnValue;
	}

	protected function LoadResultsFromTypeIDdataset($GroupedResults, $Query)
	{

		if ($Query->SelectedRows > 0)
		{
			foreach ($Query->Results as $dr)
			{
				//Build the matching object
				$tempEntityType = $dr['AssociatedEntityType'];
				$tempEntityID = $dr['AssociatedEntityID'];

				$tempEntity = new $tempEntityType ($tempEntityID);

				//Make sure an array for this EntityType Exists
				if (array_key_exists(strtolower($tempEntityType), $GroupedResults) == false)
				{
					$GroupedResults[strtolower($tempEntityType)] = new DIarray();
				}

				//Add the object to the array for that entity type
				$GroupedResults[strtolower($tempEntityType)][$tempEntityID] = $tempEntity;
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}


		return $returnValue;

	}

	protected function PerformMessageSearch($SearchTerm, $GroupedResults, $TypeNamesCSV)
	{


		$query = new Query();

		$SearchTerm = mysql_real_escape_string($SearchTerm);

		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$query->SQL = "	SELECT	DISTINCT a.AssociatedEntityType,
								a.AssociatedEntityID
						FROM	core_MessageMaster a
								LEFT JOIN core_MessageCommentMaster b ON b.MessageID = a.MessageID
						WHERE	LOWER(a.Subject) {$likeClause}
						OR		LOWER(a.Content) {$likeClause}
						OR		LOWER(b.Content) {$likeClause} ";

		$query->Execute();

		$returnValue = $this->LoadResultsFromTypeIDdataset($GroupedResults, $query);

		return $returnValue;

	}

	protected function CalculateResultsRank($SearchTerm, $GroupedResults)
	{
		foreach ($GroupedResults as $tempTypeName=>$tempObjects)
		{
			$currentSearchEntity = $this->_types[$tempTypeName];

			foreach ($tempObjects as $tempResultObject)
			{
				$currentSearchEntity->CalculateResultRank($SearchTerm, $tempResultObject);
			}
		}
	}

	protected function BuildFinalResults($GroupedResults)
	{

		$preLimitResults = new DIarray();

		//Move all the grouped results into the main array
		foreach ($GroupedResults as $tempResults)
		{
			foreach($tempResults as $tempObject)
			{
				$preLimitResults[] = $tempObject;
			}
		}

		//Sort the results, with the highest rank on top
		$preLimitResults = DIarray::SortByObjectProperty($preLimitResults, SearchRank, true, false);

		//Set the top search rank for each result (to calculate relevance)
		$topSearchRank = $preLimitResults[0]->SearchRank;

		$i = 0;

		while ($i < count($preLimitResults) && $i < $this->_maxResults)
		{
			$tempResult = $preLimitResults[$i];
			$tempResult->TopSearchRank = $topSearchRank;

			$this->_results[] = $tempResult;

			$i++;
		}
	}

}
?>