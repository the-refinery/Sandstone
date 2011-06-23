<?php
/**
 * PayPal Processor Class
 * 
 * @package Sandstone
 * @subpackage Merchant
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

SandstoneNamespace::Using("Sandstone.Address");
SandstoneNamespace::Using("Sandstone.CreditCard");

class PayPalProcessor extends MerchantAccountProcessor
{
	
	public function ProcessSale($Amount)
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
	
	protected function SetupCurl($QueryParameters)
	{
		
		//Build a cURL object
		$returnValue = curl_init();
		
		//setting the curl parameters.
		curl_setopt($returnValue, CURLOPT_URL, $this->_parameters['APIendpoint'] );
		curl_setopt($returnValue, CURLOPT_VERBOSE, 1);
	
		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($returnValue, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($returnValue, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($returnValue, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($returnValue, CURLOPT_POST, 1);

		curl_setopt($returnValue, CURLOPT_POSTFIELDS,$QueryParameters);
		
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