<?php
/*
Email Class File

@package Sandstone
@subpackage Email
 */

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
			$query = new Query();

			$Email = strtolower($Email);

			$selectClause = Email::GenerateBaseSelectClause();

			$fromClause = Email::GenerateBaseFromClause();

			$whereClause = Email::GenerateBaseWhereClause();
			$whereClause .= "AND	LOWER(Address) = {$query->SetTextField($Email)}";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			$returnValue = $query->LoadEntity($this);
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

		$query->SQL = "	INSERT INTO core_EmailMaster
						(
							AccountID,
							Address
						)
						VALUES
						(
							{$this->AccountID},
							{$query->SetTextField($this->_address)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_EmailMaster SET
							Address = {$query->SetTextField($this->_address)}
						WHERE EmailID = {$this->_emailID}";

		$query->Execute();

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
		$query = new Query();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = new ObjectSet($query, "Email", "EmailID");

		return $returnValue;
	}
}
?>