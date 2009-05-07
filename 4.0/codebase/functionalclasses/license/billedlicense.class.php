<?php
/*
BilledLicense Class File

@package Sandstone
@subpackage License
 */

Namespace::Using("Sandstone.Address");
Namespace::Using("Sandstone.Email");
Namespace::Using("Sandstone.Merchant.CIM");

class BilledLicense extends BaseLicense
{

	protected $_addressID;
	
	protected function SetupProperties()
	{
		$this->AddProperty("CompanyName","string","CompanyName",PROPERTY_REQUIRED);
		$this->AddProperty("SignupDate","date","SignupDate",PROPERTY_READ_ONLY);
		$this->AddProperty("Address","Address",null,PROPERTY_READ_WRITE);
		$this->AddProperty("CIMcustomerProfile","CIMcustomerProfile","CIMcustomerProfileID",PROPERTY_READ_ONLY);
		$this->AddProperty("IsPastDue","boolean","IsPastDue",PROPERTY_READ_WRITE);
		$this->AddProperty("IsDisabled","boolean","IsDisabled",PROPERTY_READ_WRITE);

		$this->AddProperty("AdminUsers","array",null,PROPERTY_READ_ONLY,"LoadAdminUsers");
		$this->AddProperty("PrimaryAdminUser","user",null,PROPERTY_READ_ONLY,"LoadAdminUsers");

		parent::SetupProperties();
	}

	public function getAddress()
	{
		if (is_set($this->_address))
		{
			$returnValue = $this->_address;
		}
		else
		{
			if (is_set($this->_addressID))
			{
				$this->_address = new Address($this->_addressID);
				$returnValue = $this->_address;
			}
		}

		return $returnValue;
	}

	public function setAddress($Value)
	{
		if ($Value instanceof Address && $Value->IsLoaded)
		{
			$this->_addressID = $Value->AddressID;
			$this->_address = $Value;
		}
		else
		{
			$this->_addressID = null;
			$this->_address = null;
		}
	}

	public function Load($dr)
	{

		//Add the University account detail to the dr
		$dr = $this->LoadAccountDetails($dr);

		$returnValue = parent::Load($dr);

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

	public function Save()
	{
		$returnValue = parent::Save();

		if ($returnValue == true)
		{
			$returnValue = $this->SaveAccountDetails();
		}

		return $returnValue;
	}

	public function SetupCreditCard($CreditCard)
	{
		$returnValue = false;

		if ($CreditCard instanceof CreditCard && $CreditCard->IsLoaded && $CreditCard->IsValid)
		{
			if (is_set($this->_cimCustomerProfile) == false)
			{
				$newCustomerProfile = new CIMcustomerProfile();
				$newCustomerProfile->CustomerID = $this->_accountID;
				$newCustomerProfile->Description = $this->_companyName;
				$newCustomerProfile->Save();

				$this->_cimCustomerProfile = $newCustomerProfile;
			}

			$this->_cimCustomerProfile->SetupCreditCard($CreditCard);

			$returnValue = $this->Save();
			
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

}
?>
