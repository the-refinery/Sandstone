<?php
/*
MessageComment Class File

@package Sandstone
@subpackage Message
*/

class MessageComment extends EntityBase
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

		$this->AddProperty("CommentID","integer","CommentID",true,false,true,false,false,null);
		$this->AddProperty("Message","Message","MessageID",false,true,false,true,false,null);
		$this->AddProperty("User","User","UserID",false,true,false,true,false,null);
		$this->AddProperty("Timestamp","date","Timestamp",true,false,false,false,false,null);
		$this->AddProperty("Content","string","Content",false,true,false,false,false,null);

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$this->_timestamp = new Date();

		$query->SQL = "	INSERT INTO core_MessageCommentMaster
							(
								AccountID,
								MessageID,
								UserID,
								Timestamp,
								Content
							)
							VALUES
							(
								{$this->AccountID},
								{$this->_message->MessageID},
								{$this->_user->UserID},
								{$query->SetNullDateField($this->_timestamp)},
								{$query->SetTextField($this->_content)}
							)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_MessageCommentMaster SET
								UserID = {$this->_user->UserID},
								Content = {$query->SetTextField($this->_content)}
							WHERE CommentID = {$this->_commentID}";

		$query->Execute();

		return true;
	}

	public function Delete()
	{
		$query = new Query();

		$query->SQL = "	DELETE
					    FROM    core_MessageCommentMaster
					    WHERE CommentID = {$this->_commentID} ";

		$query->Execute();

		$this->_commentID = null;

		return true;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.CommentID,
										a.MessageID,
										a.UserID,
										a.Timestamp,
										a.Content ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_MessageCommentMaster a ";

		return $returnValue;
	}

	/*
	Search Query Functions
	 */
	static public function SearchMultipleEntity($SearchTerm, $MaxResults)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$searchClause .= "LOWER(Content) {$likeClause} ";

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "AND ({$searchClause}) ";

		$returnValue = self::PerformSearch($whereClause, $MaxResults);

		return $returnValue;
	}

	static public function SearchSingleEntity($SearchTerm, $MaxResults)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$searchClause .= "LOWER(Content) {$likeClause} ";

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "AND ({$searchClause}) ";

		$returnValue = self::PerformSearch($whereClause, $MaxResults);

		return $returnValue;
	}

	static protected function PerformSearch($WhereClause, $MaxResults)
	{
		$query = new Query();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$limitClause = " LIMIT {$MaxResults} ";

		$query->SQL = $selectClause . $fromClause . $WhereClause . $limitClause;

		$query->Execute();

		$returnValue = new ObjectSet($query->Results, "MessageComment", "CommentID");

		return $returnValue;
	}

}
?>