<?php
/*
 Authorization Transaction  Class File
 
 @package Sandstone
 @subpackage CreditCard

 */

class AuthorizationTransaction extends BaseCreditCardTransaction
{
	public function ProcessPriorAuthorizationCapture($Amount = null)
	{
		$merchantAccount = Application::License()->ActiveMerchantAccount;
		$returnValue = $merchantAccount->ProcessPriorAuthorizationCapture($this, $Amount);

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		$returnValue = parent::GenerateBaseWhereClause();

		$returnValue .= "AND	a.CreditCardTransactionTypeID = 1 ";

		return $returnValue;
	}
}
?>
