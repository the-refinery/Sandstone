<?php
/*
Tag Class File

@package Sandstone
@subpackage Tag
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

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
		$conn = GetConnection();

		$query = "	INSERT INTO core_TagMaster
							(
								AccountID,
								TagText
							)
							VALUES
							(
								{$this->AccountID},
								{$conn->SetTextField($this->_text)}
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

		$query = "	UPDATE core_TagMaster SET
								TagText = {$conn->SetTextField($this->_text)}
							WHERE TagID = {$this->_tagID}";

		$conn->Execute($query);

		return true;
	}

	protected function LookupIDfromText()
	{

		if (strlen($this->_text) > 0)
		{
			$conn = GetConnection();

			$query = "	SELECT	TagID
						FROM 	core_TagMaster
						WHERE	TagText = {$conn->SetTextField($this->_text)}";

			$ds = $conn->Execute($query);

			if ($ds && $ds->RecordCount() > 0)
			{
				$dr = $ds->FetchRow();

				$this->_tagID = $dr['TagID'];
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
		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		$returnValue = new ObjectSet($ds, "Tag", "TagID");

		return $returnValue;
	}

	static public function FormatTextForTag($Text)
	{

		$returnValue = preg_replace('|[^a-z0-9_.\-@#$%*!&]|i', '', strtolower($Text));

		return $returnValue;
	}

}
?>