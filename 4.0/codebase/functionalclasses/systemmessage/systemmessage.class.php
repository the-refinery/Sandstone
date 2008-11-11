<?php
/*
SystemMessage Class File

@package Sandstone
@subpackage SystemMessgae
*/

class SystemMessage extends EntityBase
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

		$this->AddProperty("MessageID","integer","MessageID",true,false,true,false,false,null);
		$this->AddProperty("Title","string","Title",false,false,false,false,false,null);
		$this->AddProperty("Content","string","Content",false,true,false,false,false,null);
		$this->AddProperty("StartDate","date","StartDate",false,false,false,false,false,null);
		$this->AddProperty("EndDate","date","EndDate",false,false,false,false,false,null);
		$this->AddProperty("IsAdminOnly","boolean","IsAdminOnly",false,true,false,false,false,null);

		parent::SetupProperties();
	}

	public function LoadCurrentMessage()
	{

		$returnValue = false;

		$license = Application::License();
		$user = Application::CurrentUser();

		$query = new Query();

		$selectClause = SystemMessage::GenerateBaseSelectClause();
		$fromClause = SystemMessage::GenerateBaseFromClause();

		$whereClause = "WHERE	a.EndDate >= NOW() ";

		if ($license->hasProperty("SignupDate"))
		{
			$whereClause .= "AND a.StartDate >= {$conn->SetDateField($license->SignupDate)} ";
		}

		if ($user->IsInRole(new Role(1)) == false)
		{
			$whereClause .= "AND a.IsAdminOnly = 0 ";
		}

		$whereClause .= "AND a.MessageID NOT IN  (	SELECT 	MessageID
													FROM 	core_SystemMessageReadLog
													WHERE	UserID = {$user->UserID} )";

		$orderByClause = "ORDER BY a.MessageID DESC ";
		$limitClause = "LIMIT 1";

		$query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;

	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_SystemMessageMaster
						(
							Title,
							Content,
							StartDate,
							EndDate,
							IsAdminOnly
						)
						VALUES
						(
							{$query->SetNullTextField($this->_title)},
							{$query->SetTextField($this->_content)},
							{$query->SetNullDateField($this->_startDate)},
							{$query->SetNullDateField($this->_endDate)},
							{$query->SetBooleanField($this->_isAdminOnly)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_SystemMessageMaster SET
								Title = {$query->SetNullTextField($this->_title)},
								Content = {$query->SetTextField($this->_content)},
								StartDate = {$query->SetNullDateField($this->_startDate)},
								EndDate = {$query->SetNullDateField($this->_endDate)},
								IsAdminOnly = {$query->SetBooleanField($this->_isAdminOnly)}
							WHERE MessageID = {$this->_messageID}";

		$query->Execute();

		return true;
	}

	public function MarkRead()
	{
		if ($this->IsLoaded)
		{
			$user = Application::CurrentUser();

			$query = new Query();

			//Delete first, just to make sure we don't get an error
			$query->SQL = "	DELETE
							FROM 	core_SystemMessageReadLog
							WHERE	MessageID = {$this->_messageID}
							AND		UserID = {$user->UserID} ";

			$query->Execute();

			$query->SQL = "	INSERT INTO core_SystemMessageReadLog
							(
								MessageID,
								UserID
							)
							VALUES
							(
								{$this->_messageID},
								{$user->UserID}
							)";

			$query->Execute();

		}
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.MessageID,
										a.Title,
										a.Content,
										a.StartDate,
										a.EndDate,
										a.IsAdminOnly ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_SystemMessageMaster a ";

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		return null;

	}

}
?>