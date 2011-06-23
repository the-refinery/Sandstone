<?php
/**
 * SearchEntity Class File
 * @package Sandstone
 * @subpackage Search
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class SearchEntity extends EntityBase
{

	public function __construct($ID = null)
	{
		$this->_isTagsDisabled = true;
		$this->_isMessagesDisabled = true;

		parent::__construct($ID);
	}

	protected function SetupProperties()
	{
		$this->AddProperty("TopLevelNamespace","string","TopLevelNamespace",true,false,false,false,false,null);
		$this->AddProperty("ClassName","string","ClassName",true,false,false,false,false,null);
		$this->AddProperty("RequiredNamespace","string","RequiredNamespace",true,false,false,false,false,null);
		$this->AddProperty("EntityWeight","integer","EntityWeight",true,false,false,false,false,null);
		$this->AddProperty("TagMatchWeight","integer","TagMatchWeight",true,false,false,false,false,null);
		$this->AddProperty("TagWildcardWeight","integer","TagWildcardWeight",true,false,false,false,false,null);
		$this->AddProperty("MessageWildcardWeight","integer","MessageWildcardWeight",true,false,false,false,false,null);
		$this->AddProperty("IsActive","boolean","IsActive",true,false,false,false,false,null);
		$this->AddProperty("SearchableProperties","array",null,true,false,false,true,true,"LoadSearchableProperties");

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		return false;
	}

	protected function SaveUpdateRecord()
	{
		return false;
	}

	public function LoadSearchableProperties()
	{
		$this->_searchableProperties->Clear();

		$conn = GetConnection();

		$selectClause = SearchProperty::GenerateBaseSelectClause();
		$fromClause = SearchProperty::GenerateBaseFromClause();

		$whereClause = "WHERE	TopLevelNamespace = {$conn->SetTextField($this->_topLevelNamespace)}
						AND		ClassName = {$conn->SetTextField($this->_className)} ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow())
			{
				$tempSearchProperty = new SearchProperty($dr);

				$this->_searchableProperties[strtolower($tempSearchProperty->PropertyName)] = $tempSearchProperty;
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	public function CalculateResultRank($SearchTerm, $ResultObject)
	{

		$rank = 0;

		//All comparisons are case insensitive.
		$SearchTerm = strtolower($SearchTerm);

		//Check Properties
		$rank += $this->CalculatePropertiesRank($SearchTerm, $ResultObject);

		//Check Tags
		$rank += $this->CalculateTagRank($SearchTerm, $ResultObject);

		//Check Messages (if any)
		$rank += $this->CalculateMessageRank($SearchTerm, $ResultObject);

		//Apply the entity weight
		$ResultObject->SearchRank = $rank * $this->_entityWeight;
	}

	protected function CalculatePropertiesRank($SearchTerm, $ResultObject)
	{

		$returnValue = 0;

		//Loop the searchable properties and check for match & wildcard hits.
		foreach ($this->SearchableProperties as $tempProperty)
		{
			$propertyName = $tempProperty->PropertyName;
			$propertyValue = strtolower($ResultObject->$propertyName);

			//Is this a match?
			if ($propertyValue == $SearchTerm)
			{
				//Dead Hit!
				$returnValue += $tempProperty->MatchWeight;
			}
			else
			{
				//Is this a wildcard match?
				$matchCount = substr_count($propertyValue, $SearchTerm);

				if ($matchCount > 0)
				{
					$returnValue += $matchCount * $tempProperty->WildcardWeight;
				}
			}

		}

		return $returnValue;
	}

	protected function CalculateTagRank($SearchTerm, $ResultObject)
	{

		$returnValue = 0;

		//Loop through any tags checking for match or wildcard hits.
    	foreach($ResultObject->Tags->Tags as $tempTag)
		{
			$tagText = strtolower($tempTag->Text);

			if ($tagText == $SearchTerm)
			{
				//Tag Match
				$returnValue += $this->_tagMatchWeight;
			}
			else
			{
				//Tag wildcard?
				if (substr_count($tagText, $SearchTerm) > 0)
				{
					$returnValue += $this->_tagWildcardWeight;
				}
			}
		}

		return $returnValue;

	}

	protected function CalculateMessageRank($SearchTerm, $ResultObject)
	{

		if (is_set($ResultObject->Messages))
		{
			$returnValue = $ResultObject->Messages->CountSearchTermOccurrances($SearchTerm);
		}
		else
		{
			$returnValue = 0;
		}

		$returnValue = $returnValue * $this->_messageWildcardWeight;

		return $returnValue;
	}

	/**
	 *
	 * Static Query Functions
	 *
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.TopLevelNamespace,
										a.ClassName,
										a.RequiredNamespace,
										a.EntityWeight,
										a.TagMatchWeight,
										a.TagWildcardWeight,
										a.MessageWildcardWeight,
										a.IsActive ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_SearchEntityMaster a ";

		return $returnValue;
	}

}
?>