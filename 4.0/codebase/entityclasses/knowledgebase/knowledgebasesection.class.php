<?php
/*
KnowledgebaseSection Class File

@package Sandstone
@subpackage knowledgebase
 */

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

		$query = new Query();

		$selectClause = KnowledgebaseArticle::GenerateBaseSelectClause();
		$fromClause = KnowledgebaseArticle::GenerateBaseFromClause();
		$whereClause = "WHERE 	a.SectionID = {$this->_sectionID}
						AND		a.IsPublished = 1 ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$query->LoadEntityArray($this->_articles, "KnowledgebaseArticle", "ArticleID", $this, "LoadArticlesCallback");

		return true;
	}

	public function LoadArticlesCallback($Article)
	{

		$Article->Section = $this;

		return $Article;
	}

	public function LoadByName($Name)
	{
		$Name = strtolower($Name);

		$query = new Query();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$whereClause = "WHERE LOWER(a.Name) = {$query->SetTextField($Name)}";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_KnowledgebaseSectionMaster
						(
							Name,
							Description
						)
						VALUES
						(
							{$query->SetTextField($this->_name)},
							{$query->SetNullTextField($this->_description)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_KnowledgebaseSectionMaster SET
							Name = {$query->SetTextField($this->_name)},
							Description = {$query->SetNullTextField($this->_description)}
						WHERE SectionID = {$this->_sectionID}";

		$query->Execute();

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
		$query = new Query();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = new ObjectSet($query, "KnowledgebaseSection", "SectionID");

		return $returnValue;
	}

}
?>