<?php
/*
PayPal Express Checkout Processor Class

@package Sandstone
@subpackage Merchant
*/

NameSpace::Using("Sandstone.Address");
NameSpace::Using("Sandstone.CreditCard");

class PayPalExpressCheckoutProcessor extends Module
{

	protected $_parameters;

	public function __construct()
	{
		$this->_parameters = Application::License()->ActiveMerchantAccount->Parameters;
	}

	public function getIsTestMode()
	{
		$returnValue = false;

		if ($this->_parameters['testmode'] == 1)
		{
			$returnValue = true;
		}

		return $returnValue;

	}

	public function SetupTransaction($Amount, $Description, $ReturnURL, $CancelURL)
	{
		$returnValue = false;


		$postParameters = $this->BuildBaseAPIparameters($postParameters);
		
		$postParameters["METHOD"] = "SetExpressCheckout";
		$postParameters["PAYMENTACTION"] = "Sale";
		$postParameters["CurrencyCode"] = "USD";

		$postParameters["AMT"] = $Amount;
		$postParameters["Desc"] = urlencode($Description);

		$postParameters["RETURNURL"] = urlencode($ReturnURL);
		$postParameters["CANCELURL"] = urlencode($CancelURL);

		$apiResult = $this->SendAPIrequest($postParameters);

		if ($apiResult['ACK'] == "Success")
		{
			$transaction = new PayPalTransaction();
			$transaction->Token = $apiResult['TOKEN'];
			$transaction->Amount = $Amount;
			$transaction->Save();

			$this->RedirectUserToPayPal($apiResult['TOKEN']);
		}
		
		return $returnValue;

	}

	protected function RedirectUserToPayPal($Token)
	{
		if ($this->IsTestMode)
		{
			$target = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
		}
		else
		{
			$target = 'https://www.paypal.com/cgi-bin/webscr?';
		}
		
		$parameters = "cmd=_express-checkout&useraction=commit&token={$Token}";

		$url = $target . $parameters;

		header("Location: {$url}");

	}

	public function CancelTransaction($Token)
	{
		$returnValue = new PayPalTransaction();
		$returnValue->LoadByToken($Token);

		if ($returnValue->IsLoaded)
		{
			$returnValue->IsCancelled = true;
			$returnValue->Save();
		}

		return $returnValue;
	}

	public function CompleteTransaction($Token)
	{
		$returnValue = new PayPalTransaction();
		$returnValue->LoadByToken($Token);

		if ($returnValue->IsLoaded)
		{

			$apiResult = $this->GetCheckoutDetails($Token);

			$returnValue->GetDetailsTimestamp = new Date();
			$returnValue->CorrelationID = $apiResult['CORRELATIONID'];
			$returnValue->PayerID = $apiResult['PAYERID'];
			$returnValue->PayerStatus = $apiResult['PAYERSTATUS'];
			$returnValue->Save();

			if ($apiResult['ACK'] == "Success")
			{
				$returnValue = $this->DoCheckout($returnValue);
			}
		}

		return $returnValue;
	}

	protected function GetCheckoutDetails($Token)
	{
		$postParameters = $this->BuildBaseAPIparameters();

		$postParameters["METHOD"] = "GetExpressCheckoutDetails";
		$postParameters["TOKEN"]= $Token;

		$returnValue = $this->SendAPIrequest($postParameters);

		return $returnValue;
	}

	protected function DoCheckout($PayPalTransaction)
	{
		$returnValue = $PayPalTransaction;

		$postParameters = $this->BuildBaseAPIparameters();

		$postParameters["METHOD"] = "DoExpressCheckoutPayment";
		$postParameters["TOKEN"]= $PayPalTransaction->Token;
		$postParameters["PAYERID"] = $PayPalTransaction->PayerID;

		$postParameters["PAYMENTACTION"] = "Sale";
		$postParameters["AMT"] = StringFunc::FormatNumber($PayPalTransaction->Amount, 2);

		$apiResult = $this->SendAPIrequest($postParameters);

		if ($apiResult['ACK'] == "Success")
		{
			$returnValue->PayPalTransactionNumber = $apiResult['TRANSACTIONID'];
			$returnValue->FeeAmount = $apiResult['FEEAMT'];
			$returnValue->PaymentStatus = $apiResult['PAYMENTSTATUS'];
			$returnValue->PendingReason = $apiResult['PENDINGREASON'];
			$returnValue->ReasonCode = $apiResult['REASONCODE'];
			$returnValue->ProcessTimestamp = new Date();

			if ($apiResult['PAYMENTSTATUS'] == "Completed")
			{
				$returnValue->IsSuccessful = true;
			}

			$returnValue->Save();
		}

		return $returnValue;
	}

	protected function BuildBaseAPIparameters()
	{

		if ($this->IsTestMode)
		{
			$returnValue['USER'] = "admin_1252359450_biz_api1.university-hq.com";
			$returnValue['PWD'] = "1252359461";
			$returnValue['SIGNATURE'] = "AFcWxV21C7fd0v3bYYYRCpSSRl31AiCHmR16bUaqBkJZWnwwG2bwuA0c";
		}
		else
		{
			$returnValue['USER'] = urlencode($this->_parameters['APIusername']);
			$returnValue['PWD'] = urlencode($this->_parameters['APIpassword']);
			$returnValue['SIGNATURE'] = urlencode($this->_parameters['APIsignature']);

		}

		$returnValue['VERSION'] = '51.0';

		return $returnValue;
	}

	protected function SendAPIrequest($PostParameters)
	{

		$postParametersString = $this->BuildPostParametersString($PostParameters);

		$cURL = $this->SetupCurl($postParametersString);

		//execute the API call
		$apiResponseString = curl_exec($cURL);

		if (strlen($apiResponseString) > 0)
		{
			//Process the API results & build the transaction object
			$returnValue = $this->ParseAPIresponseString($apiResponseString);
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	protected function BuildPostParametersString($PostParameters)
	{
		foreach ($PostParameters as $name=>$value)
		{
			$nvps[] = "{$name}={$value}";
		}

		$returnValue = implode("&",$nvps);

		return $returnValue;
	}

	protected function SetupCurl($PostParameters)
	{

		//Build a cURL object
		$returnValue = curl_init();

		if ($this->IsTestMode)
		{
			curl_setopt($returnValue, CURLOPT_URL, "https://api-3t.sandbox.paypal.com/nvp" );
		}
		else
		{
			curl_setopt($returnValue, CURLOPT_URL, "https://api-3t.paypal.com/nvp" );
		}


		//setting the curl parameters.
		curl_setopt($returnValue, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($returnValue, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($returnValue, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($returnValue, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($returnValue, CURLOPT_POST, 1);

		curl_setopt($returnValue, CURLOPT_POSTFIELDS,$PostParameters);

		return $returnValue;
	}

	protected function ParseAPIresponseString($ResponseString)
	{
		$nvps = explode("&", $ResponseString);

		foreach($nvps as $tempNVP)
		{
			$parts = explode("=", $tempNVP);
	
			$returnValue[urldecode($parts[0])] = urldecode($parts[1]);

		}

		return $returnValue;
	}
















	public function ProcessCharge($Amount, $AuthTransaction = null)
	{
		$this->_amount = $Amount;

		//Build the Query Parms for the API call
		$queryParameters = $this->SetupQueryParameters();

		//Setup a cURL object to process the API call
		$cURL = $this->SetupCurl($queryParameters);

		//execute the API call
		$responseQueryString = curl_exec($cURL);

		if (strlen($responseQueryString) > 0)
		{
			//Process the API results & build the transaction object
			$returnValue = $this->ProcessResults($responseQueryString);
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	protected function SetupQueryParameters()
	{

		GLOBAL $CONFIG;

		$returnValue = "USER=" . urlencode($this->_parameters['APIusername']);
		$returnValue .= "&PWD=" . urlencode($this->_parameters['APIpassword']);
		$returnValue .= "&VERSION=" . urlencode($this->_parameters['APIversion']);
		$returnValue .= "&SIGNATURE=" . urlencode($this->_parameters['APIsignature']);
		$returnValue .= "&METHOD=DoDirectPayment";
		$returnValue .= "&PAYMENTACTION=Sale";
		$returnValue .= "&CREDITCARDTYPE=" . urlencode($this->_cardType);
		$returnValue .= "&ACCT=" . urlencode($this->_number);
		$returnValue .= "&EXPDATE=" . urlencode($this->_expirationDate->FormatDate('m') . $this->_expirationDate->Year);
		$returnValue .= "&CVV2=" . urlencode($this->_cvv);
		$returnValue .= "&FIRSTNAME=" . urlencode($this->_firstName);
		$returnValue .= "&LASTNAME=" . urlencode($this->_lastName);
		$returnValue .= "&AMT=" . urlencode(number_format($this->_amount, 2));

		return $returnValue;
	}


	protected function ProcessResults($ResponseQueryString)
	{

		//Break the response Query String into an array of Key & Values
		$resultsArray = $this->SetupResultsArray($ResponseQueryString);

		//Setup the transaction object
		$returnValue = new CreditCardTransaction();
		$returnValue->CreditCardID = $this->_creditCardID;
		$returnValue->Timestamp = new Date($resultsArray['TIMESTAMP']);
		$returnValue->Amount = $this->_amount;

		//Was the process successful?
		if (strpos(strtolower($resultsArray['ACK']), "success") === false)
		{
			//Transaction Fail
			$returnValue->IsSuccessful = false;

			$this->SetupTransactionMessages($resultsArray, $returnValue);
		}
		else
		{
			//Transaction Success
			$returnValue->IsSuccessful = true;
			$returnValue->MerchantTransactionID = $resultsArray['TRANSACTIONID'];
		}

		$returnValue->Save();

		return $returnValue;

	}

	protected function SetupResultsArray($ResponseQueryString)
	{

		$returnValue = Array();

		//First pull the string apart into individual Key & Value pairs
 		$keyValuePairs = explode("&", $ResponseQueryString);

 		//For each Key & Value pair string, separate the elements and add to
 		//the array.
		foreach ($keyValuePairs as $tempKeyValuePair)
		{
			$keyAndValue = explode("=", $tempKeyValuePair);

			$returnValue[strtoupper($keyAndValue[0])] = urldecode($keyAndValue[1]);
		}

		return $returnValue;
	}

	protected function SetupTransactionMessages($ResultsArray, &$Transaction)
	{
		$i = 0;
		$isFound = true;

		while($isFound)
		{
			$tempErrorCodeKey = "L_ERRORCODE" . $i;
			$tempShortMessageKey = "L_SHORTMESSAGE" . $i;
			$tempLongMessageKey = "L_LONGMESSAGE" . $i;
			$tempSeverityCodeKey = "L_SEVERITYCODE" . $i;

			if (key_exists($tempErrorCodeKey, $ResultsArray))
			{
				$isFound = true;

				$tempMessage = $ResultsArray[$tempErrorCodeKey] . ' ';
				$tempMessage .= '[' .$ResultsArray[$tempSeverityCodeKey] . ']: ';
				$tempMessage .= $ResultsArray[$tempShortMessageKey];
				if (strlen($ResultsArray[$tempLongMessageKey]) > 0)
				{
					$tempMessage .= ' (' . $ResultsArray[$tempLongMessageKey] . ')';
				}

				$Transaction->AddMessage($tempMessage);
			}
			else
			{
				$isFound = false;
			}

			$i += 1;
		}
	}

}
