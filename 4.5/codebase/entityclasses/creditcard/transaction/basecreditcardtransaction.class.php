<?php
/*
 BaseCreditCardTransaction Class File
 
 @package Sandstone
 @subpackage CreditCard

 */

Namespace::Using("Sandstone.Merchant.CIM");

class BaseCreditCardTransaction extends EntityBase
{

	const AUTHORIZATION_TRANSACTION_TYPE = 1;
	const CAPTURE_TRANSACTION_TYPE = 2;
	const CREDIT_TRANSACTION_TYPE = 3;

    protected function SetupProperties()
    {		
        $this->AddProperty("TransactionID","integer","TransactionID",PROPERTY_PRIMARY_ID);
        $this->AddProperty("MerchantAccount","MerchantAccount","MerchantAccountID",PROPERTY_REQUIRED+PROPERTY_LOADED_REQUIRED);
        $this->AddProperty("CreditCardTransactionTypeID","integer","CreditCardTransactionTypeID",PROPERTY_REQUIRED);
        $this->AddProperty("Timestamp","date","Timestamp",PROPERTY_READ_ONLY);
        $this->AddProperty("Amount","decimal","Amount",PROPERTY_REQUIRED);
        $this->AddProperty("MerchantTransactionID","string","MerchantTransactionID",PROPERTY_READ_WRITE);
        $this->AddProperty("IsSuccessful","boolean","IsSuccessful",PROPERTY_REQUIRED);
        $this->AddProperty("TransactionFee","decimal","TransactionFee",PROPERTY_READ_WRITE);
        $this->AddProperty("DiscountPercent","decimal","DiscountPercent",PROPERTY_READ_WRITE);
        $this->AddProperty("RelatedTransaction","BaseCreditCardTransaction","RelatedTransactionID",PROPERTY_LOADED_REQUIRED);
        $this->AddProperty("CIMcustomerProfileID","integer","CIMcustomerProfileID",PROPERTY_READ_WRITE);
        $this->AddProperty("CIMpaymentProfileID","integer","CIMpaymentProfileID",PROPERTY_READ_WRITE);
        $this->AddProperty("PartC","integer","PartC",PROPERTY_READ_WRITE);
				$this->AddProperty("TransactionMessages","array",null,PROPERTY_READ_ONLY,"LoadTransactionMessages");
        
        parent::SetupProperties();
    }

	public function setRelatedTransaction($Value)
	{
		if ($Value instanceof BaseCreditCardTransaction && $Value->IsLoaded)
		{
			$this->_relatedTransaction = $Value;
			$this->_partC = $Value->PartC;

			if (is_set($this->_amount) == false)
			{
				$this->_amount = $Value->Amount;
			}
		}
		else
		{
			$this->_relatedTransaction = null;
		}
	}

	public function LoadTransactionMessages()
	{
		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$this->_timestamp = new Date();

		$query->SQL = "	INSERT INTO core_CreditCardTransactionMaster
						(
							AccountID,
							MerchantAccountID,
							CreditCardTransactionTypeID,
							Timestamp,
							Amount,
							MerchantTransactionID,
							IsSuccessful,
							TransactionFee,
							DiscountPercent,
							RelatedTransactionID,
							CIMcustomerProfileID,
							CIMpaymentProfileID,
							PartC
						)
						VALUES
						(
							{$this->AccountID},
							{$this->_merchantAccount->MerchantAccountID},
							{$this->_creditCardTransactionTypeID},
							{$query->SetDateField($this->_timestamp)},
							{$this->_amount},
							{$query->SetNullTextField($this->_merchantTransactionID)},
							{$query->SetBooleanField($this->_isSuccessful)},
							{$query->SetNullNumericField($this->_transactionFee)},
							{$query->SetNullNumericField($this->_discountPercent)},
							{$query->SetNullNumericField($this->_relatedTransaction->TransactionID)},
							{$query->SetNullNumericField($this->_cIMcustomerProfileID)},
							{$query->SetNullNumericField($this->_cIMpaymentProfileID)},
							{$query->SetNullNumericField($this->_partC)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		//We only save messages on new transactions.
		$this->SaveMessages();

		return true;
	}

	protected function SaveMessages()
	{
		if (count($this->_messages) > 0)
		{
			$query = new Query();

			foreach ($this->_transactionMessages as $tempMessage)
			{
				$query->SQL = "	INSERT INTO core_CreditCardTransactionMessage
												(
													TransactionID,
													MessageText
												)
												VALUES
												(
													{$this->_transactionID},
													{$query->SetTextField($tempMessage)}
												) ";

				$query->Execute();
			}
		}
	}


	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_CreditCardTransactionMaster SET
							MerchantAccountID = {$this->_merchantAccount->MerchantAccountID},
							CreditCardTransactionTypeID = {$this->_creditCardTransactionTypeID},
							Amount = {$this->_amount},
							MerchantTransactionID = {$query->SetNullTextField($this->_merchantTransactionID)},
							IsSuccessful = {$query->SetBooleanField($this->_isSuccessful)},
							TransactionFee = {$query->SetNullNumericField($this->_transactionFee)},
							DiscountPercent = {$query->SetNullNumericField($this->_discountPercent)},
							RelatedTransactionID = {$query->SetNullNumericField($this->_relatedTransaction->TransactionID)},
							CIMcustomerProfileID = {$query->SetNullNumericField($this->_cIMcustomerProfileID)},
							CIMpaymentProfileID = {$query->SetNullNumericField($this->_cIMpaymentProfileID)},
							PartC = {$query->SetNullNumericField($this->_partC)}
						WHERE TransactionID = {$this->_transactionID}";

		$query->Execute();

		return true;
	}

	public function AddMessage($Message)
	{
		if (strlen($Message) > 0)
		{
			$this->_transactionMessages[] = $Message;
		}
		
	}

	/*
	Static Query Functions
	*/
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT 	TransactionID,
									MerchantAccountID,
									CreditCardTransactionTypeID,
									Timestamp,
									Amount,
									MerchantTransactionID,
									IsSuccessful,
									TransactionFee,
									DiscountPercent,
									RelatedTransactionID,
									CIMcustomerProfileID,
									CIMpaymentProfileID,
									PartC ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_CreditCardTransactionMaster a ";

		return $returnValue;
	}

}
?>
