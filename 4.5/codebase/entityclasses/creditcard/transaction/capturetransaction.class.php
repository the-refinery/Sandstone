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
			$returnValue = $customerProfile->PaymentProfile->ProcessCredit($this, $Amount);
		}

		return $returnValue;
	}

	protected function ProcessMerchantAccountTransaction($Amount)
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
