<?php
/*
BilledLicense Class File

@package Sandstone
@subpackage License
 */

SandstoneNamespace::Using("Sandstone.Address");
SandstoneNamespace::Using("Sandstone.Email");
SandstoneNamespace::Using("Sandstone.Merchant.CIM");

class BilledLicense extends BaseLicense
{

	protected $_addressID;
	protected $_cimProfileID;
	
	protected function SetupProperties()
	{
		$this->AddProperty("CompanyName","string","CompanyName",PROPERTY_REQUIRED);
		$this->AddProperty("SignupDate","date","SignupDate",PROPERTY_READ_ONLY);
		$this->AddProperty("AnniversaryDay","integer","AnniversaryDay",PROPERTY_READ_ONLY);
		$this->AddProperty("Address","Address",null,PROPERTY_READ_WRITE);
		$this->AddProperty("CIMcustomerProfile","CIMcustomerProfile",null,PROPERTY_READ_ONLY, "LoadCIMprofile");
		$this->AddProperty("PastDueTimestamp","date","PastDueTimestamp",PROPERTY_READ_ONLY);
		$this->AddProperty("DisabledTimestamp","date","DisabledTimestamp",PROPERTY_READ_ONLY);


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

	public function getIsPastDue()
	{
		if (is_set($this->_pastDueTimestamp))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function getIsDisabled()
	{
		if (is_set($this->_disabledTimestamp))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function getNextBillingDate()
	{	
		if (is_set($this->_anniversaryDay))
		{
			$returnValue = new Date();

			if ($returnValue->Day >= $this->_anniversaryDay)
			{
				$returnValue = $returnValue->AddMonth(1);
			}

			$returnValue->Day = $this->_anniversaryDay;
		}

		return $returnValue;
	}

	public function Load($dr)
	{

		//Add the University account detail to the dr
		$dr = $this->LoadAccountDetails($dr);

		$returnValue = parent::Load($dr);

		return $returnValue;

	}

	public function LoadCIMprofile()
	{
		if (is_set($this->_cimProfileID))
		{
			$this->_cimCustomerProfile = new CIMcustomerProfile($this->_cimProfileID);
		}
	}


	protected function GenerateBaseDetailSelectClause()
	{

		$returnValue = "	SELECT	a.CompanyName,
															a.SignupDate,
															a.AnniversaryDay,
															a.AddressID,
															a.PastDueTimestamp,
															a.DisabledTimestamp,
															a.IsCancelled,
															a.CIMcustomerProfileID ";

		return $returnValue;
	}

	protected function GenerateBaseDetailWhereClause()
	{
		$returnValue = "WHERE	a.AccountID = {$this->_accountID} ";

		return $returnValue;
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
				$newCustomerProfile->Description = $this->_name;
				$newCustomerProfile->Save();

				$this->_cimCustomerProfile = $newCustomerProfile;
			}

			$returnValue = $this->_cimCustomerProfile->SetupCreditCard($CreditCard);

			if ($returnValue == true)
			{
				$returnValue = $this->Save();
			}
		}

		return $returnValue;
	}

	public function MarkPastDue($NumberOfDays = 0)
	{
		$now = new Date();
		$now->Time = "00:00:00";
		
		$this->_pastDueTimestamp = $now->AddDays($NumberOfDays);

		$this->Save();
			
	}

	public function ClearPastDue()
	{
		$this->_pastDueTimestamp = null;

		$this->Save();
	}

	public function MarkDisabled()
	{
		$now = new Date();
		$now->Time = "00:00:00";
		
		$this->_disabledTimestamp = $now;

		$this->Save();
			
	}

	public function MarkEnabled()
	{
		$this->_disabledTimestamp = null;

		$this->Save();
	}

}
?>
