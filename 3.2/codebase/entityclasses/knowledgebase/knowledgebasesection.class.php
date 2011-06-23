<?php
/*
KnowledgebaseSection Class File

@package Sandstone
@subpackage knowledgebase
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class KnowledgebaseSection extends EntityBase
{
	protected function SetupProperties()
	{

		//AddProperty Parameters:
		// 1) Name
		// 2) DataType
		// 3) DBfieldName
		// 4) IsReadOnly
		// 5) IsRequired
		// 6) IsPrimaryID
		// 7) IsLoadedRequired
		// 8) IsLoadOnDemand
		// 9) LoadOnDemandFunctionName

		$this->AddProperty("SectionID","integer","SectionID",true,false,true,false,false,null);
		$this->AddProperty("Name","string","Name",false,true,false,false,false,null);
		$this->AddProperty("Description","string","Description",false,false,false,false,false,null);
		$this->AddProperty("Articles", "array", null, true, false, false, false, true, "LoadArticles");

		parent::SetupProperties();
	}

	public function LoadArticles()
	{

		$this->_articles->Clear();

		$conn = GetConnection();

		$selectClause = KnowledgebaseArticle::GenerateBaseSelectClause();
		$fromClause = KnowledgebaseArticle::GenerateBaseFromClause();
		$whereClause = "WHERE 	a.SectionID = {$this->_sectionID}
						AND		a.IsPublished = 1 ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds)
		{
			while ($dr = $ds->FetchRow())
			{
				$tempArticle = new KnowledgebaseArticle($dr);
				$tempArticle->Section = $this;

				$this->_articles[$tempArticle->ArticleID] = $tempArticle;
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function LoadByName($Name)
	{
		$Name = strtolower($Name);

		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$whereClause = "WHERE LOWER(a.Name) = '{$Name}' ";

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

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_KnowledgebaseSectionMaster
							(
								Name,
								Description
							)
							VALUES
							(
								{$conn->SetTextField($this->_name)},
								{$conn->SetNullTextField($this->_description)}
							)";

		$conn->Execute($query);

		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";

		$dr = $conn->GetRow($query);

		$this->_primaryIDproperty->Value = $dr['newID'];

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$conn = GetConnection();

		$query = "	UPDATE core_KnowledgebaseSectionMaster SET
								Name = {$conn->SetTextField($this->_name)},
								Description = {$conn->SetNullTextField($this->_description)}
							WHERE SectionID = {$this->_sectionID}";

		$conn->Execute($query);

		return true;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.SectionID,
										a.Name,
										a.Description ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_KnowledgebaseSectionMaster a ";

		return $returnValue;
	}

    static public function GenerateBaseWhereClause()
    {
        return null;
    }


	/*
	Search Query Functions
	 */
	static public function SearchMultipleEntity($SearchTerm)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$whereClause .= "WHERE 	LOWER (Name) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (Description) LIKE {$likeClause} ";

		$returnValue = self::PerformSearch($whereClause);

		return $returnValue;
	}

	static public function SearchSingleEntity($SearchTerm)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$whereClause .= "WHERE 	LOWER (Name) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (Description) LIKE {$likeClause} ";

		$returnValue = self::PerformSearch($whereClause);

		return $returnValue;
	}

	static protected function PerformSearch($WhereClause)
	{
		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		$returnValue = new ObjectSet($ds, "KnowledgebaseSection", "SectionID");

		return $returnValue;
	}

}
?>