<?php
/*
User Class File

@package Sandstone
@subpackage User
 */

NameSpace::Using("Sandstone.Action");
NameSpace::Using("Sandstone.Address");
NameSpace::Using("Sandstone.ADOdb");
NameSpace::Using("Sandstone.Date");
NameSpace::Using("Sandstone.Email");
NameSpace::Using("Sandstone.Phone");

class User extends EntityBase
{

	protected $_passwordSalt;

	public function __construct($ID = null)
	{

		//We handle the $ID paramer differently, so don't pass
		//it to our parent
		parent::__construct();

		if (is_set($ID))
		{
			if (is_array($ID))
			{
				$this->Load($ID);
			}
			elseif (is_numeric($ID))
			{
				$this->LoadByID($ID);
			}
			else
			{
				$this->LoadByToken($ID);
			}
		}
		else
		{
			$this->GeneratePasswordSalt();
		}
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

		$this->AddProperty("UserID","integer","UserID",true,false,true,false,false,null);
		$this->AddProperty("FirstName","string","FirstName",false,true,false,false,false,null);
		$this->AddProperty("LastName","string","LastName",false,true,false,false,false,null);
		$this->AddProperty("Gender","string","Gender",false,false,false,false,false,null);
		$this->AddProperty("UserName","string","UserName",false,false,false,false,false,null);
		$this->AddProperty("Password","string","Password",false,false,false,false,false,null);
		$this->AddProperty("IsBulkMailAllowed","boolean","IsBulkMailAllowed",false,true,false,false,false,null);
		$this->AddProperty("IsDisabled","boolean","IsDisabled",false,true,false,false,false,null);
		$this->AddProperty("Token","string",null,false,false,false,false,true,"LoadToken");
		$this->AddProperty("Roles","array",null,false,false,false,false,true,"LoadRoles");

		$this->AddCollective("Emails", "Emails");
		$this->AddCollective("Phones", "Phones");

		parent::SetupProperties();
	}

	/*
	Gender Property

	Allowable values: "m", "f", null

	@return string
	@param string $Value
	*/
	public function getGender()
	{
		switch (strtolower($this->_gender))
		{
			case "m":
				$returnValue = "Male";
				break;

			case "f":
				$returnValue = "Female";
				break;

			default:
				$returnValue = "Not Specified";
				break;
		}

		return $returnValue;
	}

	public function setGender($Value)
	{
		$Value = strtoupper($Value);

		switch ($Value)
		{
			case "M":
			case "MALE":
				$this->_gender = "M";
				break;

			case "F":
			case "FEMALE":
				$this->_gender = "F";
				break;

			default:
				$this->_gender = null;
		}

	}

	public function setPassword($Value)
	{
		$this->_password = $this->EncryptPassword($Value);
	}

    public function getSearchResultsText()
	{
		return $this->FirstLastName;
	}

	public function getFirstLastName()
	{
		$returnValue = "{$this->_firstName} {$this->_lastName}";

		return $returnValue;
	}

	public function getLastFirstNameNoSpace()
	{
		$returnValue = "{$this->_lastName},{$this->_firstName}";

		return $returnValue;
	}

	public function getLastFirstNameSpace()
	{
		$returnValue = "{$this->_lastName}, {$this->_firstName}";

		return $returnValue;
	}

	protected function EncryptPassword($RawPassword)
	{
		$returnValue = md5(md5($RawPassword) . $this->_passwordSalt);

		return $returnValue;
	}

	public function Load($dr)
	{

		$returnValue = parent::Load($dr);

		if (is_set($dr['PasswordSalt']))
		{
			$this->_passwordSalt = $dr['PasswordSalt'];
		}

		return $returnValue;
	}

	public function LoadByID($ID)
	{
		$conn = GetConnection();

		$selectClause = User::GenerateBaseSelectClause();
		$fromClause = User::GenerateBaseFromClause();

		//User ID 1 is special, ALL accounts can load it.
		if ($ID == 1)
		{
			$whereClause .= "WHERE UserID = {$ID} ";
		}
		else
		{
			$whereClause = User::GenerateBaseWhereClause();
			$whereClause .= "AND UserID = {$ID} ";
		}

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

        return $returnValue;

	}

	public function LoadByToken($Token)
	{
		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$fromClause .= "INNER JOIN core_UserToken b ON  b.UserID = a.UserID ";

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause = "AND b.Token = '{$Token}' ";

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

		return $returnValue;

	}

	public function LoadToken()
	{
		$conn = GetConnection();

		$query = "	SELECT 	Token
					FROM 	core_UserToken
					WHERE 	UserID = {$this->_userID}";

		$ds = $conn->Execute($query);

		if ($ds)
		{
			if ($ds->RecordCount() > 0)
			{
				$dr = $ds->FetchRow();
				$this->_token = $dr["Token"];

				$returnValue = true;
			}
			else
			{
				//Return True if there weren't any records,
				//since it's ok for a user to not have a token.
				$returnValue = true;
			}
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	public function LoadRoles()
	{
		$this->_roles->Clear();

		$conn = GetConnection();

		$selectClause = Role::GenerateBaseSelectClause();

		$fromClause = Role::GenerateBaseFromClause();
		$fromClause .= "INNER JOIN core_UserRole b ON b.RoleID = a.RoleID ";

		$whereClause = "WHERE b.UserID = {$this->_userID} ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow())
			{
				$tempRole = new Role($dr);

				$this->_roles[$tempRole->RoleID] = $tempRole;
			}
		}
		else
		{
			//It's ok if we don't find any roles
			$returnValue = true;
		}

		return $returnValue;
	}

	public function LoadAddresses()
	{

	}

	protected function LoadEmails()
	{

	}

	protected function LoadPhones()
	{

	}

	protected function SaveNewRecord($AccountID = null)
	{

		$conn = GetConnection();

		$query = "	INSERT INTO core_UserMaster
							(
								AccountID,
								FirstName,
								LastName,
								Gender,
								UserName,
								Password,
								PasswordSalt,
								IsBulkMailAllowed,
								IsDisabled
							)
							VALUES
							(
								{$this->AccountID},
								{$conn->SetTextField($this->_firstName)},
								{$conn->SetTextField($this->_lastName)},
								{$conn->SetNullTextField($this->_gender)},
								{$conn->SetNullTextField($this->_userName)},
								{$conn->SetNullTextField($this->_password)},
								{$conn->SetTextField($this->_passwordSalt)},
								{$conn->SetBooleanField($this->_isBulkMailAllowed)},
								{$conn->SetBooleanField($this->_isDisabled)}
							)";

		$conn->Execute($query);

		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";

		$dr = $conn->GetRow($query);

		$this->_primaryIDproperty->Value = $dr['newID'];

        Action::Log("UserCreated", "User {$this->_firstName} {$this->_lastName} (ID: {$this->_userID}) was created.", $this->_userID);

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$conn = GetConnection();

		$query = "	UPDATE core_UserMaster SET
								FirstName = {$conn->SetTextField($this->_firstName)},
								LastName = {$conn->SetTextField($this->_lastName)},
								Gender = {$conn->SetNullTextField($this->_gender)},
								UserName = {$conn->SetNullTextField($this->_userName)},
								Password = {$conn->SetNullTextField($this->_password)},
								IsBulkMailAllowed = {$conn->SetBooleanField($this->_isBulkMailAllowed)},
								IsDisabled = {$conn->SetBooleanField($this->_isDisabled)}
							WHERE UserID = {$this->_userID}";

		$conn->Execute($query);

		return true;
	}

	protected function GeneratePasswordSalt()
	{

		for ($i = 0; $i < 32; $i++)
     	{
          	$newSalt .= chr(rand(35, 126));
     	}

         $this->_passwordSalt = $newSalt;

	}

	public function Login($UserName, $Password)
	{
		//First, attempt a local login
		$returnValue = $this->AttemptLocalLogin($UserName, $Password);

		if ($returnValue == false)
		{
			//We didn't get a successful login.  Attempt to validate this
			//username & password to the DI administrative database
			$returnValue = $this->AttemptAdminLogin($UserName, $Password);
		}

		//If we found a user, set the token into the session and the cookie.
		if ($returnValue == true)
		{
			Application::SetSessionVariable('DItoken', $this->_token);
			Application::SetCookie('DItoken', $this->_token);

			//Don't Log Admin Logins
			if ($this->_userID > 1)
			{
				Action::Log("UserLogin", "User {$this->_firstName} {$this->_lastName} logged in.", $this->_userID);
			}
		}
		else
		{
			Action::Log("UserLoginFailure", "Attempted Login for username {$this->_username} failed.");
		}

		return $returnValue;

	}

	protected function AttemptLocalLogin($UserName, $Password)
	{
		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "	AND		Username LIKE '{$UserName}'
							AND		IsDisabled = 0 ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();

			$this->_passwordSalt = $dr['PasswordSalt'];

			//We found a user, does the password match?
			if ($dr['Password'] == $this->EncryptPassword($Password))
			{
				//Valid User!
				$returnValue = $this->Load($dr);

				//We have a valid user, create a new token for them
				$this->_token = md5($_SERVER['HTTP_USER_AGENT'] . date('dmys'));

				$this->SaveToken();
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

	protected function AttemptAdminLogin($UserName, $Password)
	{
		$conn = GetConnection("DIteamDB");

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$whereClause .= "	WHERE 	Username LIKE '{$UserName}'
							AND		IsDisabled = 0 ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();

			$this->_passwordSalt = $dr['PasswordSalt'];

			//We found a user, does the password match?
			if ($dr['Password'] == $this->EncryptPassword($Password))
			{
				//Valid User!
				//Load the object as ID #1 (the Barracuda Suite Admin login)
				$returnValue = $this->LoadByID(1);

				if ($returnValue == true)
				{
					//We have a valid admin login, load the token, if one exists
					$this->LoadToken();

					if (is_set($this->_token) == false)
					{
						//There wasn't a token, so create one
						$this->_token = md5($_SERVER['HTTP_USER_AGENT'] . date('dmys'));
						$this->SaveToken();
					}
				}
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

	public function Logout()
	{

		//Don't Log Admin logouts
		if ($this->_userID > 1)
		{
			Action::Log("UserLogout", "User {$this->_firstName} {$this->_lastName} logged out.", $this->_userID);
		}

		Application::ClearSessionVariable('DItoken');
		Application::ClearCookie('DItoken');

		if ($this->_userID > 1)
		{
			$this->ClearToken();
		}

		return true;
	}

	protected function SaveToken()
	{
		//First, clear all database entries
		$this->ClearToken();

        $conn = GetConnection();

		$query = "	INSERT INTO core_UserToken
					(
						UserID,
						Token,
						AccountID
					)
					VALUES
					(
						{$this->_userID},
						{$conn->SetTextField($this->_token)},
						{$this->AccountID}
					)";

		$conn->Execute($query);
	}

	protected function ClearToken()
	{

		$conn = GetConnection();

		$query = "	DELETE
					FROM 	core_UserToken
					WHERE 	UserID = {$this->_userID}";

		$conn->Execute($query);

	}

	public function ValidateUniqueUsername($NewUserName)
	{
		$conn = GetConnection();

		$selectClause = "	SELECT 	COUNT(UserID) NumberUsers ";
		$fromClause = self::GenerateBaseFromClause();

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "AND Username LIKE '{$NewUserName}' ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);
		$dr = $ds->FetchRow();

		if ($dr['NumberUsers'] == 0)
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function AddRole($NewRole)
	{
		if ($NewRole instanceof Role && $NewRole->IsLoaded)
		{
			if ($this->IsInRole($NewRole) == false)
			{
				$conn = GetConnection();

				$query = "	INSERT INTO core_UserRole
							(
								UserID,
								RoleID
							)
							VALUES
							(
								{$this->_userID},
								{$NewRole->RoleID}
							)";

				$conn->Execute($query);

				$this->LoadRoles();

                Action::Log("UserRoleChanged", "User {$this->_firstName} {$this->_lastName} was added to the {$NewRole->Description} role.", $this->_userID);
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function RemoveRole($OldRole)
	{
		if ($OldRole instanceof Role && $OldRole->IsLoaded)
		{

			if ($this->IsInRole($OldRole))
			{
				$conn = GetConnection();

				$query = "	DELETE
							FROM	core_UserRole
							WHERE	UserID = {$this->_userID}
							AND		RoleID = {$OldRole->RoleID} ";

				$conn->Execute($query);

				$this->LoadRoles();

				Action::Log("UserRoleChanged", "User {$this->_firstName} {$this->_lastName} was removed from the {$NewRole->Description} role.", $this->_userID);

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

	public function IsInRole($CheckRole)
	{
		if ($CheckRole instanceof Role && $CheckRole->IsLoaded)
		{
			if (is_set($this->Roles[$CheckRole->RoleID]))
			{
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

	protected function Lookup_All($Parameters, $LookupType, $PageSize = null, $PageNumber = null)
	{

		$conn = GetConnection();

		$selectClause = $this->GenerateLookupSelectClause($LookupType, $PageSize, $PageNumber);
		$fromClause = $this->GenerateBaseFromClause();

		$whereClause = $this->GenerateBaseWhereClause();
		$whereClause .= " AND a.UserID <> 1 ";

		$orderByClause = "ORDER BY a.LastName, a.FirstName, a.Username";

        $limitClause = $this->GenerateLookupLimitClause($PageSize, $PageNumber);

		$query = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$returnValue = $conn->Execute($query);

		return $returnValue;

	}

	protected function Lookup_ByRole($Parameters, $LookupType, $PageSize = null, $PageNumber = null)
	{

		$conn = GetConnection();


		$selectClause = $this->GenerateLookupSelectClause($LookupType, $PageSize, $PageNumber);
		$fromClause = $this->GenerateBaseFromClause();
		$fromClause .= "INNER JOIN core_UserRole b on b.UserID = a.UserID ";

		$whereClause = $this->GenerateBaseWhereClause();
		$whereClause .= " AND a.UserID <> 1 ";

		if (is_array($Parameters['roleid']))
		{
			$ids = implode(",", $Parameters['roleid']);

			$whereClause .= "AND b.RoleID IN ({$ids}) ";
		}
		else
		{
			$whereClause .= "AND b.RoleID = {$Parameters['roleid']} ";
		}


		$orderByClause = "ORDER BY a.LastName, a.FirstName, a.Username";

        $limitClause = $this->GenerateLookupLimitClause($PageSize, $PageNumber);

		$query = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$returnValue = $conn->Execute($query);

		return $returnValue;

	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.UserID,
										a.FirstName,
										a.LastName,
										a.Gender,
										a.UserName,
										a.Password,
										a.PasswordSalt,
										a.IsBulkMailAllowed,
										a.IsDisabled ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_UserMaster a ";

		return $returnValue;
	}

	/*
	Search Query Functions
	 */
	static public function SearchMultipleEntity($SearchTerm, $MaxResults)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$searchClause = "LOWER(CONCAT(a.FirstName, ' ', a.LastName)) {$likeClause} ";
		$searchClause .= "OR		LOWER(CONCAT(a.LastName, ', ', a.FirstName)) {$likeClause} ";
		$searchClause .= "OR 		LOWER(CONCAT(a.LastName, ',', a.FirstName)) {$likeClause} ";

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "AND a.UserID <> 1 AND ({$searchClause}) ";

		$returnValue = self::PerformSearch($whereClause, $MaxResults);

		return $returnValue;
	}

	static public function SearchSingleEntity($SearchTerm, $MaxResults)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$searchClause = "LOWER(a.UserName) {$likeClause} ";
		$searchClause .= "OR		LOWER(CONCAT(a.FirstName, ' ', a.LastName)) {$likeClause} ";
		$searchClause .= "OR		LOWER(CONCAT(a.LastName, ', ', a.FirstName)) {$likeClause} ";
		$searchClause .= "OR 		LOWER(CONCAT(a.LastName, ',', a.FirstName)) {$likeClause} ";

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "AND a.UserID <> 1 AND ({$searchClause}) ";

		$returnValue = self::PerformSearch($whereClause, $MaxResults);

		return $returnValue;
	}

	static protected function PerformSearch($WhereClause, $MaxResults)
	{
		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$limitClause = " LIMIT {$MaxResults} ";

		$query = $selectClause . $fromClause . $WhereClause . $limitClause;

		$ds = $conn->Execute($query);

		$returnValue = new ObjectSet($ds, "User", "UserID");

		return $returnValue;
	}

	static public function AutoComplete($SearchString, $Parameters = Array())
	{

		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$likeClause = "'" . strtolower($SearchString) . "%'";

		$searchClause = "LOWER(a.FirstName) LIKE {$likeClause} ";
		$searchClause .= "OR 		LOWER(a.LastName) LIKE {$likeClause} ";
		$searchClause .= "OR 		LOWER(a.UserName) LIKE {$likeClause} ";
		$searchClause .= "OR		LOWER(CONCAT(a.FirstName, ' ', a.LastName)) LIKE {$likeClause} ";
		$searchClause .= "OR		LOWER(CONCAT(a.LastName, ', ', a.FirstName)) LIKE {$likeClause} ";
		$searchClause .= "OR 		LOWER(CONCAT(a.LastName, ',', a.FirstName)) LIKE {$likeClause} ";

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "AND a.UserID <> 1 AND ({$searchClause})";

		$orderByClause = "ORDER BY a.LastName, a.FirstName, a.Username ";
		$limitClause = "LIMIT 10";

		$query = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$ds = $conn->Execute($query);

		$returnValue = new ObjectSet($ds, "User", "UserID");

		return $returnValue;
	}

}
?>