<?php
/*
 PayPalTransaction Class File

 @package Sandstone
 @subpackage PayPal

 */

class PayPalTransaction extends EntityBase
{

	protected function SetupProperties()
	{		
		$this->AddProperty("TransactionID","integer","TransactionID",PROPERTY_PRIMARY_ID);
		$this->AddProperty("Token","string","Token",PROPERTY_REQUIRED);
		$this->AddProperty("CreateTimestamp","date","CreateTimestamp",PROPERTY_READ_ONLY);
		$this->AddProperty("GetDetailsTimestamp","date","GetDetailsTimestamp",PROPERTY_READ_WRITE);
		$this->AddProperty("ProcessTimestamp","date","ProcessTimestamp",PROPERTY_READ_WRITE);
		$this->AddProperty("Amount","decimal","Amount",PROPERTY_REQUIRED);
		$this->AddProperty("IsSuccessful","boolean","IsSuccessful",PROPERTY_REQUIRED);
		$this->AddProperty("PayerID","string","PayerID",PROPERTY_READ_WRITE);
		$this->AddProperty("PayerStatus","string","PayerStatus",PROPERTY_READ_WRITE);
		$this->AddProperty("PayPalTransactionNumber","string","PayPalTransactionNumber",PROPERTY_READ_WRITE);

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$this->_createTimestamp = new Date();

		$query->SQL = "	INSERT INTO core_PayPalTransactionMaster
			(
				AccountID,
				Token,
				CreateTimestamp,
				GetDetailsTimestamp,
				ProcessTimestamp,
				Amount,
				IsSuccessful,
				PayerID,
				PayerStatus,
				PayPalTransactionNumber
			)
			VALUES
			(
	{$this->AccountID},
	{$query->SetTextField($this->_token)},
	{$query->SetDateField($this->_createTimestamp)},
	{$query->SetNullDateField($this->_getDetailsTimestamp)},
	{$query->SetNullDateField($this->_processTimestamp)},
	{$this->_amount},
	{$query->SetBooleanField($this->_isSuccessful)},
	{$query->SetNullTextField($this->_payerID)},
	{$query->SetNullTextField($this->_payerStatus)},
	{$query->SetNullTextField($this->_payPalTransactionNumber)}
)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_PayPalTransactionMaster SET
			Token = {$query->SetTextField($this->_token)},
				GetDetailsTimestamp = {$query->SetNullDateField($this->_getDetailsTimestamp)},
				ProcessTimestamp = {$query->SetNullDateField($this->_processTimestamp)},
				Amount = {$this->_amount},
				IsSuccessful = {$query->SetBooleanField($this->_isSuccessful)},
				PayerID = {$query->SetNullTextField($this->_payerID)},
				PayerStatus = {$query->SetNullTextField($this->_payerStatus)},
				PayPalTransactionNumber = {$query->SetNullTextField($this->_payPalTransactionNumber)}
				WHERE TransactionID = {$this->_transactionID}";

		$query->Execute();

		return true;
	}



	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT 	a.TransactionID,
			a.Token,
			a.CreateTimestamp,
			a.GetDetailsTimestamp,
			a.ProcessTimestamp,
			a.Amount,
			a.IsSuccessful,
			a.PayerID,
			a.PayerStatus,
		a.PayPalTransactionNumber	";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_PayPalTransactionMaster a ";

		return $returnValue;
	}
}