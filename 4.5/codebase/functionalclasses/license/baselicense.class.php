<?php
/*
BaseLicense Class File

@package Sandstone
@subpackage License
 */

NameSpace::Using("Sandstone.Merchant");
Namespace::Using("Sandstone.Utilities.String");

class BaseLicense extends EntityBase
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

		$this->AddProperty("AccountID","integer","AccountID",true,false,true,false,false,null);
		$this->AddProperty("Name","string","Name",false,true,false,false,false,null);
		$this->AddProperty("IsCancelled","boolean","IsCancelled",false,false,false,false,false,null);

		$this->AddProperty("AdminUsers","array",null,PROPERTY_READ_ONLY,"LoadAdminUsers");
		$this->AddProperty("PrimaryAdminUser","user",null,PROPERTY_READ_ONLY,"LoadAdminUsers");

		parent::SetupProperties();
	}

	//To override the EntityBase one
	public function getAccountID()
	{
		return $this->_accountID;
	}

	public function getIsValid()
	{
		return $this->IsLoaded;
	}

	public function getActiveMerchantAccount()
	{
		$returnValue = new MerchantAccount();
		$returnValue->LoadActive();

		if ($returnValue->IsLoaded == false)
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function LoadAdminUsers()
	{
		$returnValue = false;

		$this->_adminUsers->Clear();
		$this->_primaryAdminUser = null;

		if ($this->IsLoaded)
		{
			$query = new Query();

			$selectClause = User::GenerateBaseSelectClause();
			$fromClause = User::GenerateBaseFromClause();
			$fromClause .= "INNER JOIN core_UserRole b ON
				b.UserID = a.UserID
				AND	b.RoleID =2 ";
			$whereClause = "WHERE a.AccountID = {$this->_accountID} ";
			$orderByClause = "ORDER BY a.UserID ";

			$query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause;

			$query->Execute();

			$query->LoadEntityArray($this->_adminUsers, "User", "UserID", $this, "LoadAdminUsersCallback");

			$returnValue = true;

		}

		return $returnValue;

	}

	public function LoadAdminUsersCallback($User)
	{
		if (is_set($this->_primaryAdminUser) == false)
		{
			$this->_primaryAdminUser = $User;
		}

	}

	public function LookupByName($AccountName)
	{
		$returnValue = false;

		$query = new Query();

		$searchString = strtolower($AccountName);

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$whereClause = "WHERE	LOWER(a.Name) = {$query->SetTextField($searchString)} ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_AccountMaster
						(
							Name
						)
						VALUES
						(
							{$query->SetTextField($this->_name)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_AccountMaster SET
							Name = {$query->SetTextField($this->_name)}
						WHERE AccountID = {$this->_accountID}";

		$query->Execute();

		return true;
	}

	static public function FormatValidAccountName($AccountName)
	{

		$returnValue = strtolower($AccountName);

		$returnValue = StringFunc::RemovePunctuation($returnValue);

		if (strlen($returnValue) > 75)
		{
			$returnValue = substr($returnValue, 0, 75);
		}

		return $returnValue;
	}

	public function UniqueAccountName($Control)
	{
		if (License::ValidateUniqueAccountName($Control->Value) == false)
		{
			if (is_set($Control->LabelText))
			{
				$name = $Control->LabelText;
			}
			else
			{
				$name = $Control->Name;
			}

			$returnValue = $name . " has already been taken, please try another name!";
		}

		return $returnValue;
	}

	public function CreateAdminUser($UserName, $FirstName, $LastName, $Password, $Email)
	{
		$returnValue = new User();

		$returnValue->UserName = $UserName;
		$returnValue->FirstName = $FirstName;
		$returnValue->LastName = $LastName;
		$returnValue->Password = $Password;

		$returnValue->Save();

		//Put in the admin role
		$returnValue->AddRole(new Role(2));

		//Create the email
		$email = new Email();
		$email->Address = $Email;
		$email->EmailType = new EmailType(2);
		$email->IsPrimary = true;
		$email->Save();

		$returnValue->AddEmail($email);

		return $returnValue;
	}

	static public function ValidateUniqueAccountName($NewAccountName)
	{
		$query = new Query();

		$selectClause = "	SELECT 	COUNT(AccountID) NumberAccounts ";
		$fromClause = self::GenerateBaseFromClause();

		$whereClause .= "WHERE Name LIKE {$query->SetTextField($NewAccountName)} ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		if ($query->SingleRowResult['NumberAccounts'] == 0)
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.AccountID,
										a.Name ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_AccountMaster a ";

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		return null;
	}

}
?>
