<?php
/*
CIM Base Class File

@package Sandstone
@subpackage Merchant
 */

class CIMbase extends Module
{
	protected $_authenticationParameters;
	protected $_apiURL;

	protected $_processorParameters;

	public function __construct()
	{
		$this->SetupAuthorizeNetParameters();
	}

	protected function SetupAuthorizeNetParameters()
	{
		$query = new Query();

		$query->SQL = "	SELECT	ParameterName,
														ParameterValue
										FROM	core_MerchantAccountParameters
										WHERE	AccountID = 1
										AND		MerchantAccountID = 3";

		$query->Execute();

		foreach ($query->Results as $dr)
		{
			$tempKey = $dr['ParameterName'];
			$tempValue = $dr['ParameterValue'];

			$this->_processorParameters[$tempKey] = $tempValue;
		}

		if ($this->_processorParameters['testmode'] == 1)
		{
			$this->_authenticationParameters['name'] = "6zz6m5N4Et";
			$this->_authenticationParameters['transactionKey'] = "9V9wUv6Yd92t27t5";
			$this->_apiURL = "https://apitest.authorize.net/xml/v1/request.api";
		}
		else
		{
			$this->_authenticationParameters['name'] = $this->_processorParameters['x_login'];
			$this->_authenticationParameters['transactionkey'] = $this->_processorParameters['x_tran_key'];
			$this->_apiURL = "https://api.authorize.net/xml/v1/request.api";
		}
	}

	protected function SendRequest($RequestName, $Data, $IsDebug = false)
	{
		//Add the auth stuff
		$requestData['merchantAuthentication'] = $this->_authenticationParameters;
		$requestData = array_merge($requestData, $Data);

		$xml = DIxml::ArrayToXML($requestData, $RequestName, null, true, true, true, "1.0", "utf-8", Array("xmlns"=>"AnetApi/xml/v1/schema/AnetApiSchema.xsd"));

		if ($IsDebug)
		{
			echo "<h1>{$RequestName}</h1>";
			echo "<textarea cols=100 rows=10>{$xml}</textarea>";
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_apiURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);

		if ($IsDebug)
		{
			echo "<textarea cols=100 rows=10>{$response}</textarea>";
		}

		$returnValue = DIxml::XMLtoArray($response);

		if ($IsDebug)
		{
			di_var_dump($returnValue);
		}


		return $returnValue;
	}

	protected function EvaluateRequestSuccess($ResponseArray)
	{
		if (strtolower($ResponseArray['messages']['resultCode']) == "ok")
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

}
