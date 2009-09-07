<?php
/*
Base User Class File

@package Sandstone
@subpackage User
 */

NameSpace::Using("Sandstone.Action");
NameSpace::Using("Sandstone.Address");
NameSpace::Using("Sandstone.Date");
NameSpace::Using("Sandstone.Email");
NameSpace::Using("Sandstone.Phone");

class BaseUser extends EntityBase
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
		$this->AddProperty("UserID","integer","UserID",PROPERTY_PRIMARY_ID);
		$this->AddProperty("FirstName","string","FirstName",PROPERTY_REQUIRED);
		$this->AddProperty("LastName","string","LastName",PROPERTY_REQUIRED);
		$this->AddProperty("Gender","string","Gender",PROPERTY_READ_WRITE);
		$this->AddProperty("UserName","string","UserName",PROPERTY_READ_WRITE);
		$this->AddProperty("Password","string","Password",PROPERTY_READ_WRITE);
		$this->AddProperty("IsBulkMailAllowed","boolean","IsBulkMailAllowed",PROPERTY_REQUIRED);
		$this->AddProperty("IsDisabled","boolean","IsDisabled",PROPERTY_REQUIRED);
		$this->AddProperty("Token","string",null,PROPERTY_READ_WRITE,"LoadToken");
		$this->AddProperty("Roles","array",null,PROPERTY_READ_ONLY,"LoadRoles");

		$this->AddCollective("Emails", "Emails", "User");
		$this->AddCollective("Phones", "Phones", "User");

		parent::SetupProperties();
	}

	public function SetupSearch()
	{
		parent::SetupSearch();

		$this->AddSearchProperty("UserName", false, 6, 6);
		$this->AddSearchProperty("FirstLastName", true, 6, 3, "CONCAT(a.FirstName, ' ', a.LastName)");
		$this->AddSearchProperty("LastFirstNameNoSpace", true, 6, 3, "CONCAT(a.LastName, ',', a.FirstName)");
		$this->AddSearchProperty("LastFirstNameSpace", true, 6, 3, "CONCAT(a.LastName, ', ', a.FirstName)");

		$this->_searchWhereClauseAddition = "AND a.UserID <> 1 ";
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
		$query = new Query();

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

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;

	}

	public function LoadByToken($Token)
	{
		$returnValue = false;

		if (is_set(Application::License()))
		{
			$query = new Query();

			$selectClause = self::GenerateBaseSelectClause();
			$fromClause = self::GenerateBaseFromClause();
			$fromClause .= "INNER JOIN core_UserToken b ON  b.UserID = a.UserID ";

			$whereClause = "WHERE (a.AccountID = {$this->AccountID} OR a.AccountID = 1)
				AND b.Token = '{$Token}' ";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			$returnValue = $query->LoadEntity($this);
		}

		return $returnValue;
	}

	public function LoadByUserName($UserName)
	{

		$query = new Query();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "	AND		Username LIKE '{$UserName}'
			AND		IsDisabled = 0 ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;
	}

	public function LoadToken()
	{
		$query = new Query();

		$query->SQL = "	SELECT 	Token
			FROM 	core_UserToken
			WHERE 	UserID = {$this->_userID}";

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$this->_token = $query->SingleRowResult["Token"];
		}

		return true;
	}

	public function LoadRoles()
	{
		$this->_roles->Clear();

		$query = new Query();

		$selectClause = Role::GenerateBaseSelectClause();

		$fromClause = Role::GenerateBaseFromClause();
		$fromClause .= "INNER JOIN core_UserRole b ON b.RoleID = a.RoleID ";

		$whereClause = "WHERE b.UserID = {$this->_userID} ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$query->LoadEntityArray($this->_roles, "Role", "RoleID");

		return true;
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

		$query = new Query();

		$query->SQL = "	INSERT INTO core_UserMaster
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
	{$query->SetTextField($this->_firstName)},
	{$query->SetTextField($this->_lastName)},
	{$query->SetNullTextField($this->_gender)},
	{$query->SetNullTextField($this->_userName)},
	{$query->SetNullTextField($this->_password)},
	{$query->SetTextField($this->_passwordSalt)},
	{$query->SetBooleanField($this->_isBulkMailAllowed)},
	{$query->SetBooleanField($this->_isDisabled)}
)";

		$query->Execute();

		$this->GetNewPrimaryID();

		Action::Log("UserCreated", "User {$this->_firstName} {$this->_lastName} (ID: {$this->_userID}) was created.", $this->_userID);

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_UserMaster SET
			FirstName = {$query->SetTextField($this->_firstName)},
				LastName = {$query->SetTextField($this->_lastName)},
				Gender = {$query->SetNullTextField($this->_gender)},
				UserName = {$query->SetNullTextField($this->_userName)},
				Password = {$query->SetNullTextField($this->_password)},
				IsBulkMailAllowed = {$query->SetBooleanField($this->_isBulkMailAllowed)},
				IsDisabled = {$query->SetBooleanField($this->_isDisabled)}
				WHERE UserID = {$this->_userID}";

		$query->Execute();

		return true;
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
		$query = new Query();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "	AND		Username LIKE '{$UserName}'
			AND		IsDisabled = 0 ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$dr = $query->SingleRowResult;

			$this->_passwordSalt = $query->SingleRowResult['PasswordSalt'];

			//We found a user, does the password match?
			if ($query->SingleRowResult['Password'] == $this->EncryptPassword($Password))
			{
				//Valid User!
				$returnValue = $query->LoadEntity($this);

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
		$query = new Query("DIteamDB");

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$whereClause .= "	WHERE 	Username LIKE '{$UserName}'
			AND		IsDisabled = 0 ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$this->_passwordSalt = $query->SingleRowResult['PasswordSalt'];

			//We found a user, does the password match?
			if ($query->SingleRowResult['Password'] == $this->EncryptPassword($Password))
			{
				//Valid User!
				Application::SetSessionVariable("AdminLoginFirstName", $query->SingleRowResult['FirstName']);
				Application::SetSessionVariable("AdminLoginLastName", $query->SingleRowResult['LastName']);

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

		$query = new Query();

		$query->SQL = "	INSERT INTO core_UserToken
			(
				UserID,
				Token,
				AccountID
			)
			VALUES
			(
				{$this->_userID},
				{$query->SetTextField($this->_token)},
				{$this->AccountID}
			)";

		$query->Execute();
	}

	protected function ClearToken()
	{

		$query = new Query();

		$query->SQL = "	DELETE
			FROM 	core_UserToken
			WHERE 	UserID = {$this->_userID}";

		$query->Execute();

	}

	public function UniqueUsernameValidator($Control)
	{
		if ($this->ValidateUniqueUsername($Control->Value) == false)
		{
			$returnValue = "This username has already been taken";
		}

		return $returnValue;
	}


	public function ValidateUniqueUsername($NewUserName)
	{
		$query = new Query();

		$selectClause = "	SELECT 	COUNT(UserID) NumberUsers ";
		$fromClause = self::GenerateBaseFromClause();

		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "AND Username LIKE '{$NewUserName}' ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		if ($query->SingleRowResult['NumberUsers'] == 0)
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
				$query = new Query();

				$query->SQL = "	INSERT INTO core_UserRole
					(
						UserID,
						RoleID
					)
					VALUES
					(
			{$this->_userID},
			{$NewRole->RoleID}
		)";

				$query->Execute();

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
				$query = new Query();

				$query->SQL = "	DELETE
					FROM	core_UserRole
					WHERE	UserID = {$this->_userID}
					AND		RoleID = {$OldRole->RoleID} ";

				$query->Execute();

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

	protected function GeneratePasswordSalt()
	{
		$this->_passwordSalt = User::GenerateSalt();
	}

	public function GenerateNewPassword($Syllables = 2)
	{

		//prefixes
		$prefix = array('aero', 'anti', 'auto', 'bi', 'bio',
			'cine', 'deca', 'demo', 'dyna', 'eco',
			'ergo', 'geo', 'gyno', 'hypo', 'kilo',
			'mega', 'tera', 'mini', 'nano', 'duo');

		//suffixes
		$suffix = array('dom', 'ity', 'ment', 'sion', 'ness',
			'ence', 'er', 'ist', 'tion', 'or'); 

		//vowel sounds 
		$vowels = array('a', 'o', 'e', 'i', 'y', 'u', 'ou', 'oo'); 

		//consonants 
		$consonants = array('w', 'r', 't', 'p', 's', 'd', 'f', 'g', 'h', 'j', 
			'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm', 'qu');

		$doubles = array('n', 'm', 't', 's');

		//Special characters
		$specialCharacters = array("+", "-", "_", "*", "&", "%", "$", "#", "@", "!", "?");

		$password = $this->RandomElement($prefix);

		$password_suffix = $this->RandomElement($suffix);

		for($i=0; $i<$Syllables; $i++)
		{
			// selecting random consonant
			$selectedConsonant = $this->RandomElement($consonants);

			if (in_array($selectedConsonant, $doubles)&&($i!=0)) { // maybe double it
				if (rand(0, 2) == 1) // 33% probability
					$selectedConsonant .= $selectedConsonant;
			}
			$returnValue .= $selectedConsonant;

			// selecting random vowel
			$returnValue .= $this->RandomElement($vowels);

			if ($i == $syllables - 1) // if suffix begin with vovel
				if (in_array($password_suffix[0], $vowels)) // add one more consonant 
					$returnValue .= $this->RandomElement($consonants);

		}

		// selecting random suffix
		$returnValue .= $password_suffix;

		//select a special character
		$returnValue .= $this->RandomElement($specialCharacters);

		//Add a numeric value
		$returnValue .=  rand(1, 999);

		return $returnValue;
	}

	protected function RandomElement($Array)
	{
		$index = rand(0, sizeof($Array)-1);

		return $Array[$index];
	}

	protected function Lookup_All($Parameters, $LookupType, $PageSize = null, $PageNumber = null)
	{

		$query = new Query();

		$selectClause = $this->GenerateLookupSelectClause($LookupType, $PageSize, $PageNumber);
		$fromClause = $this->GenerateBaseFromClause();

		$whereClause = $this->GenerateBaseWhereClause();
		$whereClause .= " AND a.UserID <> 1 ";

		$orderByClause = "ORDER BY a.LastName, a.FirstName, a.Username ";

		$limitClause = $this->GenerateLookupLimitClause($PageSize, $PageNumber);

		$query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$query->Execute();

		return $query->Results;

	}

	protected function Lookup_ByRole($Parameters, $LookupType, $PageSize = null, $PageNumber = null)
	{

		$query = new Query();

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


		$orderByClause = "ORDER BY a.LastName, a.FirstName, a.Username ";

		$limitClause = $this->GenerateLookupLimitClause($PageSize, $PageNumber);

		$query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$query->Execute();

		return $query->Results;

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

	static public function AutoComplete($SearchString, $Parameters = Array())
	{

		$query = new Query();

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

		$query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$query->Execute();

		$returnValue = new ObjectSet($query->Results, "User", "UserID");

		return $returnValue;
	}

	static public function GenerateSalt()
	{
		for ($i = 0; $i < 32; $i++)
		{
			$returnValue .= chr(rand(35, 126));
		}

		return $returnValue;
	}
}
?>
