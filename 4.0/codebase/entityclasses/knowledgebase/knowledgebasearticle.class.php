<?php
/*
KnowledgebaseArticle Class File

@package Sandstone
@subpackage Knowledgebase
 */

class KnowledgebaseArticle extends EntityBase
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

		$this->AddProperty("ArticleID","integer","ArticleID",true,false,true,false,false,null);
		$this->AddProperty("Section","KnowledgebaseSection","SectionID",false,true,false,true,false,null);
		$this->AddProperty("Title","string","Title",false,true,false,false,false,null);
		$this->AddProperty("ShortDescription","string","ShortDescription",false,false,false,false,false,null);
		$this->AddProperty("HTML","string","HTML",false,true,false,false,false,null);
		$this->AddProperty("SearchContent","string","SearchContent",true,false,false,false,false,null);
		$this->AddProperty("IsPublished","boolean","IsPublished",false,true,false,false,false,null);
		$this->AddProperty("SEOpage","SEOpage",null,true,false,false,false,true,"LoadSEOpage");

		parent::SetupProperties();
	}

	public function setHTML($Value)
	{
		$this->_html = $Value;

		//Build the search content
		$this->_searchContent = strip_tags($this->_html);
	}

	public function getSectionName()
	{
		return $this->_section->Name;
	}

	public function LoadSEOpage()
	{
		if ($this->IsLoaded)
		{
			$query = new Query();

			$selectClause = SEOpage::GenerateBaseSelectClause();
			$fromClause = SEOpage::GenerateBaseFromClause();
			$whereClause = "WHERE	AssociatedEntityType = 'KnowledgebaseArticle'
							AND		AssociatedEntityID = {$this->_articleID} ";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			if ($query->SelectedRows > 0)
			{
				$this->_seoPage = new SEOpage($query->SingleRowResult);

				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_KnowledgebaseArticleMaster
						(
							SectionID,
							Title,
							ShortDescription,
							HTML,
							SearchContent,
							IsPublished
						)
						VALUES
						(
							{$this->_section->SectionID},
							{$query->SetTextField($this->_title)},
							{$query->SetNullTextField($this->_shortDescription)},
							{$query->SetTextField($this->_html)},
							{$query->SetNullTextField($this->_searchContent)},
							{$query->SetBooleanField($this->_isPublished)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		//Now create an SEOpage for this article
		$this->_seoPage = new SEOpage();
		$this->_seoPage->Name = $this->_title;
		$this->_seoPage->AssociatedEntityType = "KnowledgebaseArticle";
		$this->_seoPage->AssociatedEntityID = $this->_articleID;
		$this->_seoPage->RoutingRuleName = "KnowledgebaseArticle";
		$this->_seoPage->Save();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_KnowledgebaseArticleMaster SET
								SectionID = {$this->_section->SectionID},
								Title = {$query->SetTextField($this->_title)},
								ShortDescription = {$query->SetNullTextField($this->_shortDescription)},
								HTML = {$query->SetTextField($this->_html)},
								SearchContent = {$query->SetNullTextField($this->_searchContent)},
								IsPublished = {$query->SetBooleanField($this->_isPublished)}
							WHERE ArticleID = {$this->_articleID}";

		$query->Execute();

		return true;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.ArticleID,
										a.SectionID,
										a.Title,
										a.ShortDescription,
										a.HTML,
										a.SearchContent,
										a.IsPublished ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_KnowledgebaseArticleMaster a ";

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

		$whereClause .= "WHERE 	LOWER (Title) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (ShortDescription) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (SearchContent) LIKE {$likeClause} ";

		$returnValue = self::PerformSearch($whereClause);

		return $returnValue;
	}

	static public function SearchSingleEntity($SearchTerm)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$whereClause .= "WHERE 	LOWER (Title) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (ShortDescription) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (SearchContent) LIKE {$likeClause} ";

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

		$returnValue = new ObjectSet($query->Results, "KnowledgebaseArticle", "ArticleID");

		return $returnValue;
	}

}
?>