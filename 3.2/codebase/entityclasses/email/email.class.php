<?php
/*
Email Class File

@package Sandstone
@subpackage Email
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class Email extends EntityBase
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

		$this->AddProperty("EmailID","integer","EmailID",true,false,true,false,false,null);
		$this->AddProperty("Address","string","Address",false,true,false,false,false,null);
		$this->AddProperty("EmailType","EmailType","EmailTypeID",false,false,false,true,false,null);
		$this->AddProperty("IsPrimary","boolean","IsPrimary",false,false,false,false,false,null);

		parent::SetupProperties();
	}

	public function LoadByAddress($Email)
	{
		if ($this->CheckValidEmail($Email))
		{

			$conn = GetConnection();

			$Email = strtolower($Email);

			$selectClause = Email::GenerateBaseSelectClause();

			$fromClause = Email::GenerateBaseFromClause();

			$whereClause = Email::GenerateBaseWhereClause();
			$whereClause .= "AND	LOWER(Address) = {$conn->SetTextField($Email)}";

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

		$query = "	INSERT INTO core_EmailMaster
							(
								AccountID,
								Address
							)
							VALUES
							(
								{$this->AccountID},
								{$conn->SetTextField($this->_address)}
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

		$query = "	UPDATE core_EmailMaster SET
								Address = {$conn->SetTextField($this->_address)}
							WHERE EmailID = {$this->_emailID}";

		$conn->Execute($query);

		return true;
	}

	public function GenerateGravatarURL($Dimension = 40, $Rating = "PG")
	{
		$returnValue = "http://www.gravatar.com/avatar.php?gravatar_id="
		 			. md5($this->_address)
		 			. "&size={$Dimension}"
					. "&rating={$Rating}";

		return $returnValue;
	}

	public function ValidateEmailFormat($Control)
	{
		if ($this->CheckValidEmail($Control->Value) == false)
		{
			if (is_set($Control->LabelText))
			{
				$name = $Control->LabelText;
			}
			else
			{
				$name = $Control->Name;
			}

			$returnValue = $name . " is not a valid email address!";

		}

		return $returnValue;
	}

	public function CheckValidEmail($Email)
	{
		if( preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $Email))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function Export()
	{
		$this->_exportEntities[] = $this->CreateXMLentity("address", $this->_address);
		$this->_exportEntities[] = $this->CreateXMLentity("type", $this->_emailType->Description);
		$this->_exportEntities[] = $this->CreateXMLentity("isprimary", $this->_isPrimary, true);

		return parent::Export();
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.EmailID,
										a.Address ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_EmailMaster a ";

		return $returnValue;
	}

	/*
	Search Query Functions
	 */
	static public function SearchMultipleEntity($SearchTerm)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$whereClause .= "WHERE 	LOWER (Address) LIKE {$likeClause} ";

		$returnValue = self::PerformSearch($whereClause);

		return $returnValue;
	}

	static public function SearchSingleEntity($SearchTerm)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$whereClause .= "WHERE 	LOWER (Address) LIKE {$likeClause} ";

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

		$returnValue = new ObjectSet($ds, "Email", "EmailID");

		return $returnValue;
	}
}
?>