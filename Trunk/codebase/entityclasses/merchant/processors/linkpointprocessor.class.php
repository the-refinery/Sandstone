<?php
/*
LinkPoint Processor Class

@package Sandstone
@subpackage Merchant
*/

NameSpace::Using("Sandstone.Address");
NameSpace::Using("Sandstone.CreditCard");
NameSpace::Using("Sandstone.Utilities.XML");

class LinkPointProcessor extends ProcessorBase
{

	public function ProcessCharge($Amount, $AuthTransaction = null)
	{

		$this->_amount = $Amount;

		//Build our call parameters in XML
		$xmlRequest = $this->BuildXMLrequest();

		//Send the process request
		$xmlResult = $this->SendProcessRequest($xmlRequest);

		//Did we get results back?
		if (is_set($xmlResult))
		{
			//We did, process them into a transaction
			$returnValue = $this->ProcessXMLresult($xmlResult);
		}
		else
		{
			//Some system error, return null
			$returnValue = null;
		}

		return $returnValue;

	}

	protected function BuildXMLrequest()
	{
		$order['orderoptions'] = $this->BuildOrderOptions();
		$order['merchantinfo'] = $this->BuildMerchantInfo();
		$order['creditcard'] = $this->BuildCreditCard();
		$order['billing'] = $this->BuildBilling();
		$order['payment'] = $this->BuildPayment();

		$returnValue = DIxml::ArrayToXML($order, "order");

		return $returnValue;
	}

	protected function BuildOrderOptions()
	{
		$returnValue['result'] = 'LIVE';
		$returnValue['ordertype'] = 'SALE';

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

	protected function SendProcessRequest($XMLrequest)
	{

		GLOBAL $IS_ADMIN_BILLING;

		$LICENSE = Application::License();

		if ($IS_ADMIN_BILLING)
		{
			$pemFilespec = "/home/bacdata/accounts/0000/assets/" . $this->_parameters['StoreNumber'] . '.pem';
		}
		else
		{
			$pemFilespec = $LICENSE->AssetsPath . $this->_parameters['StoreNumber'] . '.pem';
		}

		$hostString = "https://" . $this->_parameters['APIhost'] . ":" . $this->_parameters['APIport'] . "/LSGSXML";

		# use PHP built-in curl functions
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$hostString);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $XMLrequest);
		curl_setopt($ch, CURLOPT_SSLCERT, $pemFilespec);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		//curl_setopt ($ch, CURLOPT_VERBOSE, 1);	// optional - verbose debug output
													// not for production use

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

	protected function ProcessXMLresult($XMLresult)
	{

		$resultsArray = DIxml::XMLtoArray($XMLresult);

		$returnValue = new CreditCardTransaction();

		$returnValue->CreditCardID = $this->_creditCardID;
		$returnValue->Timestamp = new Date($resultsArray['r_time']);
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