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
		if (is_set($this->_cimCustomerProfileID))
		{
			$returnValue = $this->ProcessCIMtransaction($Amount);
		}
		else
		{
			$returnValue = $this->ProcessMerchantAccountTransaction($Amount);
		}

		return $returnValue;
	}

	protected function ProcessCIMtransaction($Amount)
	{

		$customerProfile = new CIMcustomerProfile($this->_cimCustomerProfileID);

		if ($customerProfile->PaymentProfile->PaymentProfileID == $this->_cimPaymentProfileID)
		{
			$returnValue = $customerProfile->PaymentProfile->ProcessPriorAuthorizationCapture($this, $Amount);
		}

		return $returnValue;
	}

	protected function ProcessMerchantAccountTransaction($Amount)
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
