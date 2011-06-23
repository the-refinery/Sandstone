<?php
/*
SystemMessage Class File

@package Sandstone
@subpackage SystemMessgae
*/

SandstoneNamespace::Using("Sandstone.ADOdb");

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

		$conn = GetConnection();

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

		$query = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();

			$returnValue = $this->Load($dr);
		}

		return $returnValue;

	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_SystemMessageMaster
							(
								Title,
								Content,
								StartDate,
								EndDate,
								IsAdminOnly
							)
							VALUES
							(
								{$conn->SetNullTextField($this->_title)},
								{$conn->SetTextField($this->_content)},
								{$conn->SetNullDateField($this->_startDate)},
								{$conn->SetNullDateField($this->_endDate)},
								{$conn->SetBooleanField($this->_isAdminOnly)}
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

		$query = "	UPDATE core_SystemMessageMaster SET
								Title = {$conn->SetNullTextField($this->_title)},
								Content = {$conn->SetTextField($this->_content)},
								StartDate = {$conn->SetNullDateField($this->_startDate)},
								EndDate = {$conn->SetNullDateField($this->_endDate)},
								IsAdminOnly = {$conn->SetBooleanField($this->_isAdminOnly)}
							WHERE MessageID = {$this->_messageID}";

		$conn->Execute($query);

		return true;
	}

	public function MarkRead()
	{
		if ($this->IsLoaded)
		{
			$user = Application::CurrentUser();

			$conn = GetConnection();

			//Delete first, just to make sure we don't get an error
			$query = "	DELETE
						FROM 	core_SystemMessageReadLog
						WHERE	MessageID = {$this->_messageID}
						AND		UserID = {$user->UserID} ";

			$conn->Execute($query);

			$query = "	INSERT INTO core_SystemMessageReadLog
						(
							MessageID,
							UserID
						)
						VALUES
						(
							{$this->_messageID},
							{$user->UserID}
						)";

			$conn->Execute($query);
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