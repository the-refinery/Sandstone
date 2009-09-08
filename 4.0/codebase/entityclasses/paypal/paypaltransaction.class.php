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
		$this->AddProperty("IsCancelled","boolean","IsCancelled",PROPERTY_REQUIRED);
		$this->AddProperty("PayerID","string","PayerID",PROPERTY_READ_WRITE);
		$this->AddProperty("PayerStatus","string","PayerStatus",PROPERTY_READ_WRITE);
		$this->AddProperty("PayPalTransactionNumber","string","PayPalTransactionNumber",PROPERTY_READ_WRITE);
		$this->AddProperty("CorrelationID","string","CorrelationID",PROPERTY_READ_WRITE);
		$this->AddProperty("FeeAmount","decimal","FeeAmount",PROPERTY_READ_WRITE);
		$this->AddProperty("PaymentStatus","string","PaymentStatus",PROPERTY_READ_WRITE);
		$this->AddProperty("PendingReason","string","PendingReason",PROPERTY_READ_WRITE);
		$this->AddProperty("ReasonCode","string","ReasonCode",PROPERTY_READ_WRITE);

		parent::SetupProperties();
	}

	public function LoadByToken($Token)
	{
		$query = new Query();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		
		$whereClause = self::GenerateBaseWhereClause();
		$whereClause .= "AND Token = {$query->SetTextField($Token)} ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;
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
				CorrelationID,
				FeeAmount,
				PaymentStatus,
				PendingReason,
				ReasonCode,
				IsCancelled,
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
	{$query->SetNullTextField($this->_correlationID)},
	{$query->SetNullNumericField($this->_FeeAmount)},
	{$query->SetNullTextField($this->_paymentStatus)},
	{$query->SetNullTextField($this->_pendingReason)},
	{$query->SetNullTextField($this->_reasonCode)},
	{$query->SetBooleanField($this->_isCancelled)},
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
				IsCancelled = {$query->SetBooleanField($this->_isCancelled)},
				PayerID = {$query->SetNullTextField($this->_payerID)},
				PayerStatus = {$query->SetNullTextField($this->_payerStatus)},
				PayPalTransactionNumber = {$query->SetNullTextField($this->_payPalTransactionNumber)},
				CorrelationID = {$query->SetNullTextField($this->_correlationID)},
				FeeAmount = {$query->SetNullNumericField($this->_feeAmount)},
				PaymentStatus = {$query->SetNullTextField($this->_PaymentStatus)},
				PendingReason = {$query->SetNullTextField($this->_pendingReason)},
				ReasonCode = {$query->SetNullTextField($this->_reasonCode)}
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
			a.IsCancelled,
			a.PayerID,
			a.PayerStatus,
			a.CorrelationID,
			a.FeeAmount,
			a.PaymentStatus,
			a.PendingReason,
			a.ReasonCode,
		a.PayPalTransactionNumber	";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_PayPalTransactionMaster a ";

		return $returnValue;
	}
}
