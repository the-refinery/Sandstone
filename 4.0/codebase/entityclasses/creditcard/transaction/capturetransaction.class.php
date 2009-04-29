<?php
/*
 CaptureTransaction Class File
 
 @package Sandstone
 @subpackage CreditCard

 */

class CaptureTransaction extends BaseCreditCardTransaction
{
	public function ProcessCredit($Amount = null)
	{
		$merchantAccount = Application::License()->ActiveMerchantAccount;
		$returnValue = $merchantAccount->ProcessCredit($this, $Amount);

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		$returnValue = parent::GenerateBaseWhereClause();

		$returnValue .= "AND	a.CreditCardTransactionTypeID = 2 ";

		return $returnValue;
	}
}
?>
