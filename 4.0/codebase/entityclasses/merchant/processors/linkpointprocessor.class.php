<?php
/*
LinkPoint Processor Class

@package Sandstone
@subpackage Merchant
*/

SandstoneNamespace::Using("Sandstone.Address");
SandstoneNamespace::Using("Sandstone.CreditCard");
SandstoneNamespace::Using("Sandstone.Utilities.XML");

class LinkPointProcessor extends ProcessorBase
{

	protected $_apiURL;
	protected $_pemFileSpec;

    public function __construct($Parameters)
    {
        parent::__construct($Parameters);

        //If the test mode flag exists (and is true), we'll use DI's test account
        //against the test API URL
        if ($this->_parameters["testmode"] == 1)
        {
            $this->_apiURL = "https://staging.linkpt.net:1129/LSGSXML";
        }
        else
        {
            $this->_apiURL = "https://secure.linkpt.net:1129/LSGSXML";
        }

        $this->_pemFileSpec = Application::Registry()->MerchantAssetsPath . $this->_parameters['StoreNumber'] . '.pem';

    }

    public function ProcessAuthorization($Amount)
    {
		$this->_amount = $Amount;

		//Build our call parameters
		$requestArray = $this->BuildRequestArray('PREAUTH');

		//Send the process request
		$xmlResult = $this->SendRequest($requestArray);

		//Did we get results back?
		if (is_set($xmlResult))
		{
			//We did, process them into a transaction
			$returnValue = $this->ProcessResult($xmlResult, CreditCardTransaction::AUTHORIZATION_TRANSACTION_TYPE, $AuthTransaction);
		}
		else
		{
			//Some system error, return null
			$returnValue = null;
		}

		return $returnValue;

    }

	public function ProcessCharge($Amount, $AuthTransaction = null)
	{

		$this->_amount = $Amount;

		//Build our call parameters
		if ($AuthTransaction instanceof CreditCardTransaction && $AuthTransaction->IsLoaded)
		{
			$requestArray = $this->BuildRequestArray('POSTAUTH');

			$requestArray = $this->AddOrderID($requestArray, $AuthTransaction);
		}
		else
		{
			$requestArray = $this->BuildRequestArray('SALE');
		}

		//Send the process request
		$xmlResult = $this->SendRequest($requestArray);

		//Did we get results back?
		if (is_set($xmlResult))
		{
			//We did, process them into a transaction
			$returnValue = $this->ProcessResult($xmlResult, CreditCardTransaction::CHARGE_TRANSACTION_TYPE, $AuthTransaction);
		}
		else
		{
			//Some system error, return null
			$returnValue = null;
		}

		return $returnValue;

	}

    public function ProcessCredit($Amount, $ChargeTransaction = null)
    {
		$this->_amount = $Amount;

		//Build our call parameters
		$requestArray = $this->BuildRequestArray('CREDIT');
		$requestArray = $this->AddOrderID($requestArray, $ChargeTransaction);

		//Send the process request
		$xmlResult = $this->SendRequest($requestArray);

		//Did we get results back?
		if (is_set($xmlResult))
		{
			//We did, process them into a transaction
			$returnValue = $this->ProcessResult($xmlResult, CreditCardTransaction::CREDIT_TRANSACTION_TYPE, $AuthTransaction);
		}
		else
		{
			//Some system error, return null
			$returnValue = null;
		}

		return $returnValue;

    }

	protected function BuildRequestArray($OrderType)
	{
		$returnValue['orderoptions'] = $this->BuildOrderOptions($OrderType);
		$returnValue['merchantinfo'] = $this->BuildMerchantInfo();
		$returnValue['creditcard'] = $this->BuildCreditCard();
		$returnValue['billing'] = $this->BuildBilling();
		$returnValue['payment'] = $this->BuildPayment();

		return $returnValue;
	}

	protected function BuildOrderOptions($OrderType)
	{
		$returnValue['result'] = 'LIVE';
		$returnValue['ordertype'] = strtoupper($OrderType);

		return $returnValue;
	}

	protected function BuildMerchantInfo()
	{
		$returnValue['configfile'] = $this->_parameters['StoreNumber'];

		return $returnValue;
	}

	protected function BuildCreditCard()
	{

		$returnValue['cardnumber'] = $this->_number;
		$returnValue['cardexpmonth'] = $this->_expirationDate->FormatDate('m');
		$returnValue['cardexpyear'] = substr($this->_expirationDate->Year, 2,2);
		$returnValue['cvmvalue'] = $this->_cvv;
		$returnValue['cvmindicator'] = 'provided';

		return $returnValue;

	}

	protected function BuildBilling()
	{
		$returnValue['name'] = $this->_firstName . ' ' . $this->_lastName;
		$returnValue['address1'] = $this->_billingAddress->Street;
		$returnValue['city'] = $this->_billingAddress->City;
		$returnValue['state'] = $this->_billingAddress->ProvinceCode;
		$returnValue['zip'] = $this->_billingAddress->PostalCode;

		return $returnValue;
	}

	protected function BuildPayment()
	{
		$returnValue['chargetotal'] = number_format($this->_amount, 2);

		return $returnValue;
	}

	protected function AddOrderID($RequestArray, $Transaction)
	{

		if ($Transaction instanceof CreditCardTransaction && $Transaction->IsLoaded)
		{
			$RequestArray['transactiondetails']['oid'] = $Transaction->MerchantTransactionID;
		}

		return $RequestArray;
	}

	protected function SendRequest($RequestArray)
	{

		$requestXML = DIxml::ArrayToXML($RequestArray, "order");

		# use PHP built-in curl functions
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_apiURL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXML);
		curl_setopt($ch, CURLOPT_SSLCERT, $this->_pemFileSpec);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		#send the string to LSGS
		$returnValue = curl_exec($ch);

		if (strlen($returnValue) < 2)    # no response
		{
			$returnValue = null;
		}
		else
		{
			$returnValue = "<results>" . $returnValue . "</results>";
		}

		return $returnValue;

	}

	protected function ProcessResult($XMLresult, $TransactionTypeID, $RelatedTransaction = null)
	{

		$resultsArray = DIxml::XMLtoArray($XMLresult);

		$returnValue = new CreditCardTransaction();

		$returnValue->MerchantAccount = Application::License()->ActiveMerchantAccount;
		$returnValue->CreditCardTransactionTypeID = $TransactionTypeID;
		$returnValue->RelatedTransaction = $RelatedTransaction;

        $returnValue->CreditCard = new CreditCard($this->_creditCardID);
        $returnValue->Timestamp = new Date();
		$returnValue->Amount = $this->_amount;
		$returnValue->MerchantTransactionID = $resultsArray['r_ordernum'];

		//Was this successful?
		if ($resultsArray['r_approved'] == "APPROVED")
		{
			$returnValue->IsSuccessful = true;
		}
		else
		{
			$returnValue->IsSuccessful = false;

			if (strlen($resultsArray['r_approved']) > 0)
			{
				$returnValue->AddMessage('Approval: ' . $resultsArray['r_approved']);
			}

			if (strlen($resultsArray['r_code']) > 0)
			{
				$returnValue->AddMessage('Code: ' . $resultsArray['r_code']);
			}

			if (strlen($resultsArray['r_error']) > 0)
			{
				$returnValue->AddMessage('Error: ' . $resultsArray['r_error']);
			}
		}

		if (strlen($resultsArray['r_code']) > 0)
		{
			$returnValue->AddMessage('Code: ' . $resultsArray['r_code']);
		}

		$returnValue->Save();

		return $returnValue;

	}
}