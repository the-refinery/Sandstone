<?php
/*
Tag Class File

@package Sandstone
@subpackage Tag
 */

class Tag extends EntityBase
{

	public function __construct($ID = null)
	{

		$this->_isTagsDisabled = true;
		$this->_isMessagesDisabled = true;

		parent::__construct($ID);

	}

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

		$this->AddProperty("TagID","integer","TagID",true,false,true,false,false,null);
		$this->AddProperty("Text","string","TagText",false,true,false,false,false,null);
		$this->AddProperty("User","User","UserID",false,false,false,true,false,null);
		$this->AddProperty("AddTimestamp","date","AddTimestamp",false,false,false,false,false,null);

		parent::SetupProperties();
	}

	public function setText($Value)
	{
		$this->_text = Tag::FormatTextForTag($Value);

		$this->LookupIDfromText();
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_TagMaster
						(
							AccountID,
							TagText
						)
						VALUES
						(
							{$this->AccountID},
							{$query->SetTextField($this->_text)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_TagMaster SET
								TagText = {$query->SetTextField($this->_text)}
							WHERE TagID = {$this->_tagID}";

		$query->Execute();

		return true;
	}

	protected function LookupIDfromText()
	{

		if (strlen($this->_text) > 0)
		{
			$query = new Query();

			$query->SQL = "	SELECT	TagID
							FROM 	core_TagMaster
							WHERE	TagText = {$query->SetTextField($this->_text)}";

			$query->Execute();

			if ($query->SelectedRows > 0)
			{
				$this->_tagID = $query->SingleRowResult['TagID'];
				$this->_isLoaded = true;
			}
			else
			{
            	$this->_isLoaded = false;
				$this->_tagID = null;
			}
		}
		else
		{
			$this->_isLoaded = false;
			$this->_tagID = null;
		}

	}


	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.TagID,
										a.TagText ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_TagMaster a ";

		return $returnValue;
	}

	/*
	Search Query Functions
	 */
	static public function SearchMultipleEntity($SearchTerm)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$whereClause .= "WHERE 	LOWER (TagText) LIKE {$likeClause} ";

		$returnValue = self::PerformSearch($whereClause);

		return $returnValue;
	}

	static public function SearchSingleEntity($SearchTerm)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$whereClause .= "WHERE 	LOWER (TagText) LIKE {$likeClause} ";

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

		$returnValue = new ObjectSet($query, "Tag", "TagID");

		return $returnValue;
	}

	static public function FormatTextForTag($Text)
	{

		$returnValue = preg_replace('|[^a-z0-9_.\-@#$%*!&]|i', '', strtolower($Text));

		return $returnValue;
	}

}
?>