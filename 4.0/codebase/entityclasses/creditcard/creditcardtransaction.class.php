<?php
/*
CreditCardTransaction Class File

@package Sandstone
@subpackage CreditCard
 */

NameSpace::Using("Sandstone.Date");

class CreditCardTransaction extends EntityBase
{

	const AUTHORIZATION_TRANSACTION_TYPE = 1;
	const CHARGE_TRANSACTION_TYPE = 2;
	const CREDIT_TRANSACTION_TYPE = 3;

    public function __construct($ID = null)
    {

        $this->_isTagsDisabled = true;
        $this->_isMessagesDisabled = true;

        parent::__construct($ID);
    }

	protected function SetupProperties()
	{

		//AddProperty Parameters:
		// 1) Name
		// 2) DataType
		// 3) DBfieldName
		// 4) IsReadOnly
		// 5) IsRequired
		// 6) IsPrimaryID
		// 7) IsLoadedRequired
		// 8) IsLoadOnDemand
		// 9) LoadOnDemandFunctionName

		$this->AddProperty("TransactionID","integer","TransactionID",true,false,true,false,false,null);
		$this->AddProperty("MerchantAccount","MerchantAccount","MerchantAccountID",false,true,false,true,false,null);
		$this->AddProperty("CreditCard","CreditCard","CreditCardID",false,true,false,true,false,null);
		$this->AddProperty("CreditCardTransactionTypeID","integer","CreditCardTransactionTypeID",false,true,false,false,false,null);
		$this->AddProperty("Timestamp","date","Timestamp",false,true,false,false,false,null);
		$this->AddProperty("Amount","decimal","Amount",false,true,false,false,false,null);
		$this->AddProperty("MerchantTransactionID","string","MerchantTransactionID",false,false,false,false,false,null);
		$this->AddProperty("IsSuccessful","boolean","IsSuccessful",false,true,false,false,false,null);
		$this->AddProperty("TransactionFee","decimal","TransactionFee",false,false,false,false,false,null);
		$this->AddProperty("DiscountPercent","decimal","DiscountPercent",false,false,false,false,false,null);
		$this->AddProperty("RelatedTransaction","CreditCardTransaction","RelatedTransactionID",false,false,false,true,false,null);
		$this->AddProperty("TransactionMessages","array",null,true,false,false,false,true,"LoadTransactionMessages");

		parent::SetupProperties();
	}

	public function setCreditCardTransactionTypeID($Value)
	{
		switch($Value)
		{
			case self::AUTHORIZATION_TRANSACTION_TYPE:
			case self::CHARGE_TRANSACTION_TYPE:
			case self::CREDIT_TRANSACTION_TYPE:
				$this->_creditCardTransactionTypeID = $Value;
				break;

			default:
				$this->_creditCardTransactionTypeID = null;
				break;
		}

	}

	public function setMerchantAccount($Value)
	{
		if ($Value instanceof MerchantAccount && $Value->IsLoaded)
		{
			$this->_merchantAccount = $Value;
			$this->_transactionFee = $Value->TransactionFee;
			$this->_discountPercent = $Value->DiscountPercent;
		}
		else
		{
			$this->_merchantAccount = null;
			$this->_transactionFee = null;
			$this->_discountPercent = null;
		}

	}

	public function LoadTransactionMessages()
	{

		$query = new Query();

		$this->_transactionMessages->Clear();

		$query->SQL = "	SELECT 	MessageText
						FROM	core_CreditCardTransactionMessage
						WHERE	TransactionID = {$this->_transactionID}
						ORDER BY MessageID";

		$query->Execute();

		foreach ($query->Results as $dr)
		{
			$this->_transactionMessages[] = $dr['MessageText'];
		}

		$returnValue = true;

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		if (is_set($this->_relatedTransaction))
		{
			$relatedTransactionID = $this->_relatedTransaction->TransactionID;
		}


		$query->SQL = "	INSERT INTO core_CreditCardTransactionMaster
						(
							MerchantAccountID,
							CreditCardID,
							CreditCardTransactionTypeID,
							Timestamp,
							Amount,
							MerchantTransactionID,
							IsSuccessful,
							TransactionFee,
							DiscountPercent,
							RelatedTransactionID
						)
						VALUES
						(
							{$this->_merchantAccount->MerchantAccountID},
							{$this->_creditCard->CreditCardID},
							{$this->_creditCardTransactionTypeID},
							{$query->SetDateField($this->_timestamp)},
							{$this->_amount},
							{$query->SetNullTextField($this->_merchantTransactionID)},
							{$query->SetBooleanField($this->_isSuccessful)},
							{$query->SetNullNumericField($this->_transactionFee)},
							{$query->SetNullNumericField($this->_discountPercent)},
							{$query->SetNullNumericField($relatedTransactionID)}
						)";

		$query->Execute();

		//Get the new ID
		$query->SQL = "SELECT LAST_INSERT_ID() newID ";

		$query->Execute();

		$this->_primaryIDproperty->Value = $query->SingleRowResult['newID'];

		//Now save our messages (if any)
		$this->SaveMessages();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		//We don't save updates - so fail if it already exists.
		return false;
	}

	protected function SaveMessages()
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
								'{$tempMessage}'
							)";

			$query->Execute();
		}

	}

	public function AddMessage($NewMessage)
	{
		$this->_transactionMessages[] = $NewMessage;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.TransactionID,
										a.MerchantAccountID,
										a.CreditCardID,
										a.CreditCardTransactionTypeID,
										a.Timestamp,
										a.Amount,
										a.MerchantTransactionID,
										a.IsSuccessful,
										a.TransactionFee,
										a.DiscountPercent,
										a.RelatedTransactionID ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_CreditCardTransactionMaster a ";

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		return null;

	}

}
?>