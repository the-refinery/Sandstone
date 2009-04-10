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
	*/
	public function getSearchTerm()
	{
		return $this->_searchTerm;
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

	public function getIsMultiEntitySearch()
	{
		if (count($this->_types) > 1)
        {
			$returnValue = true;
        }
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;
	}

	public function AddType($ClassName)
	{
		
		$returnValue = false;
		
		$classNameKey = strtolower($ClassName);

		if (array_key_exists($classNameKey, $this->_types) == false)
		{
			if (class_exists($ClassName))
			{
				$tempClass = new $ClassName ();
				
				if ($tempClass instanceof EntityBase && $tempClass->IsSearchable)
				{
					$this->_types[$classNameKey] = $tempClass;
					
					$this->ClearResults();
					
					$returnValue = true;
				}
			}			
		}
		else
		{
			$returnValue = true;
		}
		
		return $returnValue;
	}

	protected function ClearResults()
	{
		$this->_tags->Clear();
		$this->_results->Clear();		
	}

	public function RemoveType($ClassName)
	{
		unset($this->_types[strtolower($ClassName)]);

		$this->ClearResults();

	}

    public function Search($SearchTerm)
    {
		$this->ClearResults();

        $this->_searchTerm = $SearchTerm;

		//Setup our grouped results array
		$groupedResults = new DIarray();

		//Perform the search on each type
		$this->PerformTypesSearch($SearchTerm, $groupedResults);

		//Perform a tag search, and add it's results to our GroupedResults array
		$this->PerformTagSearch($SearchTerm, $groupedResults);

		//Do we have results?
		if (count($groupedResults) > 0)
		{
			$this->BuildFinalResults($groupedResults);

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

        return $returnValue;
    }

	protected function PerformTypesSearch($SearchTerm, $GroupedResults)
	{
		
		$query = new Query();
		
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

	    foreach ($this->_types as $tempKey=>$tempType)
	    {
			$typeResults = $tempType->Search($query, $SearchTerm, $likeClause, $this->_maxResults, $this->IsMultiEntitySearch);
	    	
	        if ($typeResults->Count > 0)
	        {
	            $GroupedResults[$tempKey] = $typeResults->ItemsByKey;
	        }
	    }

	}


	protected function PerformTagSearch($SearchTerm, $GroupedResults)
	{
		$typeNamesCSV = $this->GenerateTypeNamesCSV();

		$returnValue = $this->LoadMatchingTags($SearchTerm, $typeNamesCSV);

		if ($returnValue == true)
		{
			$returnValue = $this->LoadTagResults($GroupedResults, $typeNamesCSV, $SearchTerm);
		}

		return $returnValue;

	}

	protected function GenerateTypeNamesCSV()
	{
		
		$typeNames = Array();
		
		foreach($this->_types as $tempType)
		{
			$typeNames[] = "'" . get_class($tempType) . "'";
		}
		
		$returnValue = implode(", ", $typeNames);
		
		return $returnValue;
	}

	protected function LoadMatchingTags($SearchTerm, $TypeNamesCSV)
	{

		$returnValue = false;

		$query = new Query();

		$tagText  = Tag::FormatTextForTag($SearchTerm);
		$tagText = mysql_real_escape_string($tagText);

		$accountID = Application::License()->AccountID;

		$query->SQL = "	SELECT	a.TagID,
								a.TagText
						FROM	core_TagMaster a
								INNER JOIN core_EntityTag b ON
									b.TagID = a.TagID
									AND  b.AssociatedEntityType IN ({$TypeNamesCSV})
						WHERE	a.TagText LIKE '%{$tagText}%' 
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

	protected function LoadTagResults($GroupedResults, $TypeNamesCSV, $SearchTerm)
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

		$returnValue = $this->LoadResultsFromTypeIDdataset($GroupedResults, $query, $SearchTerm);

		return $returnValue;
	}

	protected function LoadResultsFromTypeIDdataset($GroupedResults, $Query, $SearchTerm)
	{

		if ($Query->SelectedRows > 0)
		{
			foreach ($Query->Results as $dr)
			{
				//Build the matching object
				$tempEntityType = $dr['AssociatedEntityType'];
				$tempEntityID = $dr['AssociatedEntityID'];

				$tempEntity = new $tempEntityType ($tempEntityID);
				
				$tempEntity->CalculateSearchWeight($SearchTerm, $this->IsMultiEntitySearch);

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