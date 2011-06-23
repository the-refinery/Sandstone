<?php
/**
 * User Class
 * 
 * @package Sandstone
 * @subpackage User
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * @version 1.0
 * 
 * @copyright 2006 Designing Interactive
 * 
 * @todo
 * 
 */

SandstoneNamespace::Using("Sandstone.Action");
SandstoneNamespace::Using("Sandstone.Address");
SandstoneNamespace::Using("Sandstone.ADOdb");
SandstoneNamespace::Using("Sandstone.Dataset");
SandstoneNamespace::Using("Sandstone.Date");
SandstoneNamespace::Using("Sandstone.Email");
SandstoneNamespace::Using("Sandstone.Phone");

class User extends Module
{
	protected $_userID;
	protected $_firstName;
	protected $_lastName;
	protected $_gender;
	protected $_username;
	protected $_password;
	protected $_joinDate;
	protected $_isBulkMailAllowed;
	protected $_isDisabled;
	protected $_addresses;
	protected $_phones;
	protected $_emails;
	protected $_roles = array();
	protected $_referrals;
	protected $_token;
	
	/**
	 * Class Constructor
	 * 
	 * Optionally accepts either an integer ID or
	 * datarow from which to load the object
	 *
	 * @param unknown_type $ID
	 */
	public function __construct($ID = null)
	{

		$this->_addresses = new Addresses('core', 'User', 0);
		$this->_phones = new Phones('core', 'User', 0);
		$this->_emails = new Emails('core', 'User', 0);
		$this->_isBulkMailAllowed = true;
		
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

	}
	
	/**
	 * UserID property
	 *
	 * @return integer
	 */
	public function getUserID()
	{
		return $this->_userID;
	}
	
	/**
	 * FirstName Property
	 *
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getFirstName()
	{
		return $this->_firstName;
	}
	
	public function setFirstName($Value)
	{
		$this->_firstName = substr(trim($Value), 0, DB_FIRST_NAME_MAX_LEN);
	}

	/**
	 * LastName Property
	 *
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getLastName()
	{
		return $this->_lastName;
	}
	
	public function setLastName($Value)
	{
		$this->_lastName = substr(trim($Value), 0, DB_LAST_NAME_MAX_LEN);
	}
	
	/**
	 * Gender Property
	 * 
	 * Allowable values: "m", "f", null
	 *
	 * @return string
	 * 
	 * @param string $Value
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
	
	/**
	 * UserName Property
	 *
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getUsername()
	{
		return $this->_username;
	}
	
	public function setUserName($Value)
	{
		$this->_username = substr(trim($Value), 0, DB_USER_NAME_MAX_LEN);
	}
	
	/**
	 * Password Property
	 *
	 * @return string
	 * 
	 * @param string $Value
	 */	
	public function getPassword()
	{
		return $this->_password;
	}
	
	public function setPassword($Value)
	{
		
		$registry = Application::Registry();

		$Value = $registry->PasswordPrefix . substr(trim($Value), 0, DB_PASSWORD_MAX_LEN);
		
		$this->_password = sha1($Value);
	}
	
	/**
	 * Addresses property
	 *
	 * @return Addresses
	 */
	public function getAddresses()
	{
		if (is_set($this->_addresses) == false)
		{
			$this->_addresses = new Addresses('core', 'User', $this->_userID);
		}
		
		return $this->_addresses;
	}
	
	/**
	 * Emails property
	 * 
	 * @return 
	 */
	public function getEmails()
	{
		
		if (is_set($this->_emails) == false)
		{
			$this->_emails = new Emails('core', 'User', $this->_userID);
		}
		
		return $this->_emails;
	}
	
	/**
	 * Phones property
	 * 
	 * @return 
	 */
	public function getPhones()
	{
		if (is_set($this->_phones) == false)
		{
			$this->_phones = new Phones('core', 'User', $this->_userID);
		}
		
		return $this->_phones;
	}
	
	/**
	 * Roles property
	 * 
	 * @return array
	 */
	public function getRoles()
	{
		return $this->_roles;
	}
	
	/**
	 * JoinDate property
	 * 
	 * @return date
	 */
	public function getJoinDate()
	{
		return $this->_joinDate;
	}
	
	/**
	 * IsBulkMailAllowed property
	 * 
	 * @return boolean
	 * 
	 * @param boolean $Value
	 */
	public function getIsBulkMailAllowed()
	{
		return $this->_isBulkMailAllowed;
	}
	
	public function setIsBulkMailAllowed($Value)
	{
		$this->_isBulkMailAllowed = $Value;
	}
	
	/**
	 * IsDisabled property
	 * 
	 * @return boolean
	 * 
	 * @param boolean $Value
	 */
	public function getIsDisabled()
	{
		return $this->_isDisabled;
	}
	
	public function setIsDisabled($Value)
	{
		if ($this->_isDisabled != $Value)
		{
			if ($Value == true)
			{
				Action::Log("UserDisabled", "User Account for {$this->_firstName} {$this->_lastName} was disabled.", $this->_userID);
			}
			else
			{
				Action::Log("UserEnabled", "User Account for {$this->_firstName} {$this->_lastName} was enabled.", $this->_userID);
			}
		}

		$this->_isDisabled = $Value;
	}
	
	/**
	 * Referrals property
	 * 
	 * @return 
	 */
	public function getReferrals()
	{
		if (is_set($this->_referrals) == false)
		{
			$this->LoadReferrals();
		}
		
		return $this->_referrals;
	}
	
	/**
	 * Token property
	 * 
	 * @return 
	 */
	public function getToken()
	{		
		if (is_set($this->_token) == false)
		{
			$this->LoadToken();
		}
		
		return $this->_token;
	}

	public function Load($dr)
	{
		$this->_userID = $dr['UserID'];
		$this->_firstName = $dr['FirstName'];
		$this->_lastName = $dr['LastName'];
		$this->_gender = $dr['Gender'];
		$this->_username = $dr['Username'];
		$this->_password = $dr['Password'];
		$this->_joinDate = new Date($dr['JoinDate']);
		$this->_isBulkMailAllowed = Connection::GetBooleanField($dr['IsBulkMailAllowed']);
		$this->_isDisabled = Connection::GetBooleanField($dr['IsDisabled']);
	
		$this->_addresses = null;
		$this->_phones = null;
		$this->_emails = null;
		
		$returnValue = $this->LoadRoles();
					
		$this->_isLoaded = $returnValue;
		
		return $returnValue;
	}
	
	public function LoadByID($ID)
	{
		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$whereClause = "WHERE 	UserID = {$ID} ";

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
		
		$whereClause = "WHERE b.Token = '{$Token}' ";
		
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
		
	public function LoadRoles()
	{
		$conn = GetConnection();
		
		$query = "	SELECT 	a.RoleID,  
							b.Description  
					FROM 	core_UserRole a 
							INNER JOIN core_RoleMaster b ON a.RoleID = b.RoleID 
					WHERE 	a.UserID = {$this->_userID}";

		$ds = $conn->Execute($query);
		
		if ($ds)
		{
			if ($ds->RecordCount() > 0)
			{
				
				//Set the return value to failure, then set it to true as soon as we are able to 
				//successfully load one.
				$returnValue = false;			
				
				while ($dr = $ds->FetchRow()) 
				{
					
					$tempRole = new Role($dr);
					
					if ($tempRole->IsLoaded)
					{
					    $this->_roles[$tempRole->RoleID] = $tempRole;
					    
					    $returnValue = true;					
					}
					
				}
				
			}
			else
			{
				//Return True if there weren't any records,
				//since it's ok for a user to not have any role.
				$returnValue = true;
			}			
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;
		
	}

	public function LoadReferrals()
	{
		$conn = GetConnection();
		
		$query = "	SELECT 	b.UserID, 
							b.FirstName,
							b.LastName,
							b.Gender,
							b.Username,
							b.Password,
							b.JoinDate,
							b.IsBulkMailAllowed,
							b.IsDisabled
					FROM 	addon_UserReferral a 
							INNER JOIN core_UserMaster b ON a.ChildUserID = b.UserID 
					WHERE 	a.ParentUserID = {$this->_userID}";
		
		$ds = $conn->Execute($query);
		
		if ($ds)
		{
			if ($ds->RecordCount() > 0)
			{
				
				//Set the return value to failure, then set it to true as soon as we are able to 
				//successfully load one.
				$returnValue = false;			
				
				while ($dr = $ds->FetchRow()) 
				{
			
					$tempUser = new User($dr);
					
					if ($tempUser->IsLoaded)
					{
					    $this->_referrals[$tempUser->UserID] = $tempUser;
					    
					    $returnValue = true;					
					}
					
				}
				
			}
			else
			{
				//Return True if there weren't any records,
				//since it's ok for a user to not have any referrals.
				$returnValue = true;
			}			
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
	
	public function Save()
	{
		$conn = GetConnection();
		
		if (is_set($this->_userID) OR $this->_userID > 0)
		{
			$this->SaveUpdateRecord($conn);
		}
		else
		{
			$this->SaveNewRecord($conn);
		}
		
		if (is_set($this->_addresses))
		{
			$this->_addresses->Save();
		}
		
		if (is_set($this->_phones))
		{
			$this->_phones->Save();
		}
		
		if (is_set($this->_emails))
		{
			$this->_emails->Save();
		}
		
		if (is_set($this->_token))
		{
			$this->SaveToken($conn);
		}
		
		$this->SaveRoles($conn);
		
		$this->_isLoaded = true;
		
	}

	protected function SaveNewRecord($conn)
	{
		
		$query = "	INSERT INTO core_UserMaster
					(
						 FirstName,
						 LastName,
						 Gender,
						 Username,
						 Password,
						 JoinDate,
						 IsBulkMailAllowed,
						 IsDisabled
					)
					VALUES
					(
						{$conn->SetTextField($this->_firstName)},
						{$conn->SetTextField($this->_lastName)},
						{$conn->SetNullTextField($this->_gender)},
						{$conn->SetNullTextField($this->_username)},
						{$conn->SetNullTextField($this->_password)},
						CURDATE(),
						{$conn->SetBooleanField($this->_isBulkMailAllowed)},
						{$conn->SetBooleanField($this->_isDisabled)}
					)";
				
		$conn->Execute($query);
		
		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";
		
		$dr = $conn->GetRow($query);
		
		$this->_userID = $dr['newID'];
		
		$this->_addresses->AssociatedEntityID = $dr['newID'];
		$this->_phones->AssociatedEntityID = $dr['newID'];
		$this->_emails->AssociatedEntityID = $dr['newID'];

		Action::Log("UserCreated", "User Account for {$this->_firstName} {$this->_lastName} was created.", $this->_userID);
	}
	
	protected function SaveUpdateRecord($conn)
	{
		
		$query = "	UPDATE core_UserMaster SET
						FirstName = {$conn->SetTextField($this->_firstName)},
						LastName = {$conn->SetTextField($this->_lastName)},
						Gender = {$conn->SetNullTextField($this->_gender)},
						Username = {$conn->SetNullTextField($this->_username)},
						Password = {$conn->SetNullTextField($this->_password)},
						IsBulkMailAllowed = {$conn->SetBooleanField($this->_isBulkMailAllowed)},
						IsDisabled = {$conn->SetBooleanField($this->_isDisabled)}
					WHERE UserID = {$this->_userID}";
		
		$conn->Execute($query);
		
	}

	protected function SaveRoles($conn)
	{
		
		//First, clear all database entries
		$query = "DELETE FROM core_UserRole
					WHERE UserID = {$this->_userID}";
		
		$conn->Execute($query);
		
		//Now loop through each of the roles and add a record for each
		foreach ($this->_roles as $tempRole)
		{
			$query = "	INSERT INTO core_UserRole
						(
							UserID,
							RoleID
						)
						VALUES
						(
							{$this->_userID},
							{$tempRole->RoleID}
						)";
			
			$conn->Execute($query);
		}

	}

	protected function SaveToken($conn)
	{
		
		//First, clear all database entries
		$query = "DELETE FROM core_UserToken
					WHERE UserID = {$this->_userID}";
		
		$conn->Execute($query);
		
		$query = "	INSERT INTO core_UserToken
					(
						UserID,
						Token
					)
					VALUES
					(
						{$this->_userID},
						{$conn->SetTextField($this->_token)}
					)";
		
		$conn->Execute($query);		
	}

	public function AddRole($NewRole)
	{
		if ($NewRole instanceof Role)
		{
			$this->_roles[$NewRole->RoleID] = $NewRole;
			Action::Log("UserRoleChanged", "User {$this->_firstName} {$this->_lastName} was added to the {$NewRole->Description} role.", $this->_userID);
		}
	}
	
	public function RemoveRole($OldRole)
	{
		if ($OldRole instanceof Role)
		{
			unset($this->_roles[$OldRole->RoleID]);
			Action::Log("UserRoleChanged", "User {$this->_firstName} {$this->_lastName} was removed from the {$NewRole->Description} role.", $this->_userID);
		}
		
	}

	public function IsInRole($CheckRole)
	{
		if ($CheckRole instanceof Role)
		{
			if (is_set($this->_roles[$CheckRole->RoleID]))
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

	public function Login()
	{
		//First, attempt a local login
		$returnValue = $this->AttemptLocalLogin();

		if ($returnValue == false)
		{
			//We didn't get a successful login.  Attempt to validate this
			//username & password to the DI administrative database
			$returnValue = $this->AttemptAdminLogin();
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

	public function Logout()
	{

		//Don't Log Admin logouts
		if ($this->_userID > 1)
		{
			Action::Log("UserLogout", "User {$this->_firstName} {$this->_lastName} logged out.", $this->_userID);
		}

		Application::ClearSessionVariable('DItoken');
		Application::ClearCookie('DItoken');
				
		return true;
	}
	
	protected function AttemptLocalLogin()
	{
		$conn = GetConnection();
		
		$query = "	SELECT 	UserID, 
							FirstName, 
							LastName, 
							Gender, 
							Username, 
							Password, 
							JoinDate,
							IsBulkMailAllowed,
							IsDisabled
				 	FROM 	core_UserMaster 
				 	WHERE 	Username LIKE '{$this->_username}' 
				 	AND 	Password = '{$this->_password}'
				 	AND		IsDisabled = 0";

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();
			$returnValue = $this->Load($dr);
			
			if ($returnValue == true)
			{
				//We have a valid user, create a new token for them
				$this->_token = md5($_SERVER['HTTP_USER_AGENT'] . date('dmys'));

				$this->SaveToken($conn);
			}
		}
		else 
		{
			$returnValue = null;
		}
		
		return $returnValue;
		
	}
	
	protected function AttemptAdminLogin()
	{
		$conn = GetConnection(License::$LicenseDBconfig);
		
		$query = "	SELECT 	UserID
				 	FROM 	core_UserMaster
				 	WHERE 	Username LIKE '{$this->_username}' 
				 	AND 	Password = '{$this->_password}'
				 	AND		IsDisabled = 0";
						
		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
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
					
					// Get a local connection, not a master connection
					$conn2 = GetConnection();
					$this->SaveToken($conn2);
				}
			}
		}
		else 
		{
			$returnValue = false;
		}
		
		return $returnValue;
		
	}
	
	public function ValidateUniqueUsername($Control)
	{
		$conn = GetConnection();
		
		$query = "	SELECT 	COUNT(UserID) NumberUsers
					FROM 	core_UserMaster
					WHERE 	Username LIKE '{$Control->Value}'";
		
		$ds = $conn->Execute($query);
		$dr = $ds->FetchRow();
		
		if ($dr['NumberUsers'] > 0) 
		{
			$returnValue = "This username has already been used";
		}
		
		return $returnValue;
	}

	/**
	 * 
	 * Static Query Functions
	 * 
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT 	a.UserID,
									a.FirstName,
									a.LastName,
									a.Gender,
									a.Username,
									a.Password,
									a.JoinDate,
									a.IsBulkMailAllowed,
									a.IsDisabled ";

		return $returnValue;

	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_UserMaster a ";

		return $returnValue;
	}

	static public function Search($SearchString)
	{
		
		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
			
		$whereClause = "WHERE 	a.UserID <> 1
								AND 
								(
								CONCAT(a.FirstName, ' ', a.LastName) LIKE '%{$SearchString}%'
					 			OR		CONCAT(a.LastName, ', ', a.FirstName) LIKE '%{$SearchString}%'
							 	OR 		CONCAT(a.LastName, ',', a.FirstName) LIKE '%{$SearchString}%'
							 	OR		a.Username LIKE '%{$SearchString}%'
								)";
		
		$orderByClause = "ORDER BY a.LastName, a.FirstName, a.Username";
		
		$query = $selectClause . $fromClause . $whereClause . $orderByClause;

		$ds = $conn->Execute($query);

		$returnValue = new Dataset($ds, "User", "UserID");
		
		return $returnValue;
	}
	
	static public function AutoComplete($SearchString, $Parameters = Array())
	{

		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
			
		$whereClause = "WHERE 	a.UserID <> 1
								AND 
								(
								CONCAT(a.FirstName, ' ', a.LastName) LIKE '%{$SearchString}%'
					 			OR		CONCAT(a.LastName, ', ', a.FirstName) LIKE '%{$SearchString}%'
							 	OR 		CONCAT(a.LastName, ',', a.FirstName) LIKE '%{$SearchString}%'
							 	OR		a.Username LIKE '%{$SearchString}%'
								) ";
		
		$orderByClause = "ORDER BY a.LastName, a.FirstName, a.Username ";
		$limitClause = "LIMIT 10";
		
		$query = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$ds = $conn->Execute($query);

		$returnValue = new Dataset($ds, "User", "UserID");
		
		return $returnValue;

	}
	
	static public function LookupAll()
	{
		
		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
			
		$whereClause = "WHERE 	a.UserID <> 1 ";
		
		$orderByClause = "ORDER BY a.LastName, a.FirstName, a.Username";
		
		$query = $selectClause . $fromClause . $whereClause . $orderByClause;
		
		$ds = $conn->Execute($query);
		
		$returnValue = new Dataset($ds, "User", "UserID");
		
		return $returnValue;
	}
}

?>