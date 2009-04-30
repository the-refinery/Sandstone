<?php
/*
CIM Payment Profile Class File

@package Sandstone
@subpackage Merchant
 */

Namespace::Using("Sandstone.Address");

class CIMpaymentProfile extends CIMbase
{
	protected $_paymentProfileID;
	protected $_creditCardNumber;
	protected $_expirationDate;
	protected $_billToName;
	protected $_billToAddress;

	public function __construct($Data = null, $ID = null)
	{
		parent::__construct();

		if (is_set($Data))
		{
			if (is_array($Data))
			{
				$this->Load($Data);
			}
			elseif (is_set($ID))
			{
				$this->LoadByID($Data, $ID);
			}
		}

	}

	/*
	PaymentProfileID property
	
	@return integer
	*/
	public function getPaymentProfileID()
	{
		return $this->_paymentProfileID;
	}


	/*
	CreditCardNumber property
	
	@return string
	*/
	public function getCreditCardNumber()
	{
		return $this->_creditCardNumber;
	}


	/*
	ExpirationDate property
	
	@return Date
	*/
	public function getExpirationDate()
	{
		return $this->_expirationDate;
	}


	/*
	BillToName property
	
	@return string
	*/
	public function getBillToName()
	{
		return $this->_billToName;
	}


	/*
	BillToAddress property
	
	@return Address
	*/
	public function getBillToAddress()
	{
		return $this->_billToAddress;
	}

	public function Load($Data)
	{
		$this->_paymentProfileID = $Data['customerPaymentProfileId'];
		$this->_billToName = $Data['billTo']['firstName'] . " " . $Data['billTo']['lastName']; 

		$this->_billToAddress = new Address();
		$this->_billToAddress->Street = $Data['billTo']['address'];
		$this->_billToAddress->City = $Data['billTo']['city'];
		$this->_billToAddress->ProvinceCode = $Data['billTo']['state'];
		$this->_billToAddress->PostalCode = $Data['billTo']['zip'];
		$this->_billToAddress->CountryCode = $Data['billTo']['country'];

		$this->_creditCardNumber = $Data['payment']['creditCard']['cardNumber'];

		$this->_isLoaded = true;
		
		return true;
	}

	public function LoadByID($CustomerProfileID, $PaymentProfileID)
	{
		$returnValue = false;
	
		$data['customerProfileId'] = $CustomerProfileID;
		$data['customerPaymentProfileId'] = $PaymentProfileID;

		$responseArray = $this->SendRequest("getCustomerPaymentProfileRequest", $data);

		$success = $this->EvaluateRequestSuccess($responseArray);


		if ($success == true)
		{
			$returnValue = $this->Load($responseArray['paymentProfile']);
		}

		return $returnValue;
	}


}
