<?php
/*
CIM Payment Profile Class File

@package Sandstone
@subpackage Merchant
 */

Namespace::Using("Sandstone.Address");

class CIMpaymentProfile extends CIMbase
{
	protected $_customerProfileID;
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
		$this->_customerProfileID = $Data['customerProfileId'];
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
			$responseArray['paymentProfile']['customerProfileId'] = $CustomerProfileID;
			$returnValue = $this->Load($responseArray['paymentProfile']);
		}

		return $returnValue;
	}

	public function ProcessAuthorization($Amount)
	{
		if ($Amount > 0)
		{
			$data = $this->SetupTransactionData('profileTransAuthOnly', $Amount);

			$returnValue = $this->ProcessTransaction($data, 1, $Amount);
		}

		return $returnValue;
	}

	public function ProcessAuthorizationAndCapture($Amount)
	{
		if ($Amount > 0)
		{
			$data = $this->SetupTransactionData('profileTransAuthCapture', $Amount);

			$returnValue = $this->ProcessTransaction($data, 2, $Amount);
		}

		return $returnValue;
	}

	public function ProcessPriorAuthorizationCapture($AuthorizationTransaction, $Amount = null)
	{

		if ($AuthorizationTransaction instanceof AuthorizationTransaction && $AuthorizationTransaction->IsLoaded) 
		{
			if (is_set($Amount) == false)
			{
				$Amount = $AuthorizationTransaction->Amount;
			}

			$data = $this->SetupTransactionData('profileTransPriorAuthCapture', $Amount, Array('transId'=>$AuthorizationTransaction->MerchantTransactionID));

			$returnValue = $this->ProcessTransaction($data, 2, $Amount);

		}

		return $returnValue;
	}

	public function ProcessCredit($CaptureTransaction, $Amount = null)
	{
		if ($CaptureTransaction instanceof CaptureTransaction && $CaptureTransaction->IsLoaded) 
		{
			if (is_set($Amount) == false)
			{
				$Amount = $CaptureTransaction->Amount;
			}

			$data = $this->SetupTransactionData('profileTransRefund', $Amount, Array('transId'=>$CaptureTransaction->MerchantTransactionID));

			$returnValue = $this->ProcessTransaction($data, 3, $Amount);
		}

		return $returnValue;

	}

	protected function SetupTransactionData($TransactionType, $Amount, $OtherData = Array())
	{
			$transaction['amount'] = $Amount;
			$transaction['customerProfileId'] = $this->_customerProfileID;
			$transaction['customerPaymentProfileId'] = $this->_paymentProfileID;
			$transaction = array_merge($transaction, $OtherData);

			$returnValue['transaction'][$TransactionType] = $transaction;

			return $returnValue;
	}

	protected function ProcessTransaction($Data, $TransactionTypeID, $Amount)
	{
			$responseArray = $this->SendRequest("createCustomerProfileTransactionRequest", $Data, true);

			$success = $this->EvaluateRequestSuccess($responseArray);

			if ($success == true)
			{
				$processor = new AuthorizeNetProcessor($this->_processorParameters);

				$returnValue = $processor->ProcessResult($responseArray['directResponse'], $TransactionTypeID, $Amount);

				$this->RelateTransaction($returnValue);
			}

			return $returnValue;
	}

	protected function RelateTransaction($Transaction)
	{
		$Transaction->CIMcustomerProfileID = $this->_customerProfileID;
		$Transaction->CIMpaymentProfileID = $this->_paymentProfileID;
		$Transaction->Save();
	}
}
