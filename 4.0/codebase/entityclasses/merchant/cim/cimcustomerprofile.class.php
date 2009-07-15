<?php
/*
CIM Customer Profile Class File

@package Sandstone
@subpackage Merchant
 */

NameSpace::Using("Sandstone.CreditCard");

class CIMcustomerProfile extends CIMbase
{
	protected $_customerProfileID;
	protected $_customerID;
	protected $_description;
	protected $_paymentProfile;

	public function __construct($ID = null)
	{
		parent::__construct();

		if (is_set($ID))
		{
			$this->Load($ID);
		}
	}

	/*
	CustomerProfileID property
	
	@return integer
	*/
	public function getCustomerProfileID()
	{
		return $this->_customerProfileID;
	}


	/*
	CustomerID property
	
	@return integer
	@param integer $Value
	*/
	public function getCustomerID()
	{
		return $this->_customerID;
	}

	public function setCustomerID($Value)
	{
		$this->_customerID = $Value;
	}

	/*
	Description property
	
	@return string
	@param string $Value
	*/
	public function getDescription()
	{
		return $this->_description;
	}

	public function setDescription($Value)
	{
		$this->_description = $Value;
	}

	/*
	PaymentProfile property
	
	@return CIMpaymentProfile
	*/
	public function getPaymentProfile()
	{
		return $this->_paymentProfile;
	}

	public function Load($ID)
	{
		$data['customerProfileId'] = $ID;

		$responseArray = $this->SendRequest("getCustomerProfileRequest", $data);

		$returnValue = $this->EvaluateRequestSuccess($responseArray);

		if ($returnValue == true)
		{
			$this->_customerProfileID = $ID;
			$this->_customerID = $responseArray['profile']['merchantCustomerId'];
			$this->_description = $responseArray['profile']['description'];

			if (array_key_exists("paymentProfiles", $responseArray['profile']))
			{
				$responseArray['profile']['paymentProfiles']['customerProfileId'] = $ID;
				$this->_paymentProfile = new CIMpaymentProfile($responseArray['profile']['paymentProfiles']);
			}

			$this->_isLoaded = true;
		}

	}

	public function Save()
	{
		if (is_set($this->_customerProfileID))
		{
			$returnValue = $this->SaveUpdate();
		}
		else
		{
			$returnValue = $this->SaveNew();
		}

		return $returnValue;
	}

	public function SaveNew()
	{
		$returnValue = false;

		if (is_set($this->_customerID))
		{
			$data = $this->BuildSaveData();

			$responseArray = $this->SendRequest("createCustomerProfileRequest", $data);

			$returnValue = $this->EvaluateRequestSuccess($responseArray);

			if ($returnValue == true)
			{
				$this->_customerProfileID = $responseArray['customerProfileId'];
				$this->_isLoaded = true;
			}
		}

		return $returnValue;
	}

	public function SaveUpdate()
	{
		$data = $this->BuildSaveData();
		$data['profile']['customerProfileId'] = $this->_customerProfileID;

		$responseArray = $this->SendRequest("updateCustomerProfileRequest", $data);

		$returnValue = $this->EvaluateRequestSuccess($responseArray);

		return $returnValue;
	}

	protected function BuildSaveData()
	{
		$returnValue['profile']['merchantCustomerId'] = $this->_customerID;
		$returnValue['profile']['description'] = $this->_description;

		return $returnValue;
	}

	public function SetupCreditCard($CreditCard)
	{
		$returnValue = false;

		if ($CreditCard instanceof CreditCard && $CreditCard->IsLoaded && $CreditCard->IsValid)
		{
			$this->DeletePaymentProfile();

			$data['customerProfileId'] = $this->_customerProfileID;
			$data['paymentProfile']['billTo'] = $this->BuildBillToData($CreditCard);
			$data['paymentProfile']['payment'] = $this->BuildPaymentData($CreditCard);
			$data['validationMode'] = "liveMode";

			$responseArray = $this->SendRequest("createCustomerPaymentProfileRequest", $data);

			$returnValue = $this->EvaluateRequestSuccess($responseArray);

			if ($returnValue == true)
			{
				$this->_paymentProfile = new CIMpaymentProfile($this->_customerProfileID, $responseArray['customerPaymentProfileId']);
			}

		}

		return $returnValue;
	}

	protected function BuildBillToData($CreditCard)
	{

		$returnValue['firstName'] = $CreditCard->FirstName;
		$returnValue['lastName'] = $CreditCard->LastName;
		$returnValue['address'] = $CreditCard->BillingAddress->Street;
		$returnValue['city'] = $CreditCard->BillingAddress->City;
		$returnValue['state'] = $CreditCard->BillingAddress->ProvinceCode;
		$returnValue['zip'] = $CreditCard->BillingAddress->PostalCode;
		$returnValue['country'] = $CreditCard->BillingAddress->CountryCode;

		return $returnValue;
	}

	protected function BuildPaymentData($CreditCard)
	{

		$returnValue['creditCard']['cardNumber'] = $CreditCard->Number;
		$returnValue['creditCard']['expirationDate'] = $CreditCard->ExpirationDate->FormatDate('Y-m');

		return $returnValue;
	}

	protected function DeletePaymentProfile()
	{
		if (is_set($this->_paymentProfile))
		{
			$data['customerProfileId'] = $this->_customerProfileID;
			$data['customerPaymentProfileId'] = $this->_paymentProfile->PaymentProfileID;

			$responseArray = $this->SendRequest("deleteCustomerPaymentProfileRequest", $data);
		}
	}

}
