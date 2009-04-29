<?php
/*
MerchantAccountProcessor Class

@package Sandstone
@subpackage Merchant
*/

class ProcessorBase extends Module
{
	protected $_parameters;

	public function __construct($Parameters)
	{
		$this->_parameters = $Parameters;
	}

	public function ProcessAuthorization($CreditCard, $Amount)
	{
		return null;
	}

	public function ProcessAuthorizationAndCapture($CreditCard, $Amount)
	{
		return null;
	}

	public function ProcessPriorAuthorizationCapture($AuthorizationTransaction, $Amount = null)
	{
		return null;
	}

	public function ProcessCredit($CaptureTransaction,  $Amount = null)
	{
		return null;
	}

}
