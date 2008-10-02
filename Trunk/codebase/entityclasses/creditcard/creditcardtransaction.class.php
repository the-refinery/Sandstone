<?php
/*
CreditCardTransaction Class File

@package Sandstone
@subpackage CreditCard
 */

NameSpace::Using("Sandstone.ADOdb");
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
		if ($Value instanceof ActiveMerchantAccount && $Value->IsLoaded)
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

		$conn = GetConnection();

		$this->_transactionMessages->Clear();

		$query = "	SELECT 	MessageText
					FROM	core_CreditCardTransactionMessage
					WHERE	TransactionID = {$this->_transactionID}
					ORDER BY MessageID";

		$ds = $conn->Execute($query);

		if ($ds)
		{
			if ($ds->RecordCount() > 0)
			{
				while ($dr = $ds->FetchRow())
				{
					$this->_transactionMessages[] = $dr['MessageText'];
				}
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		if (is_set($this->_relatedTransaction))
		{
			$relatedTransactionID = $this->_relatedTransaction->TransactionID;
		}


		$query = "	INSERT INTO core_CreditCardTransactionMaster
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
								{$conn->SetDateField($this->_timestamp)},
								{$this->_amount},
								{$conn->SetNullTextField($this->_merchantTransactionID)},
								{$conn->SetBooleanField($this->_isSuccessful)},
								{$conn->SetNullNumericField($this->_transactionFee)},
								{$conn->SetNullNumericField($this->_discountPercent)},
								{$conn->SetNullNumericField($relatedTransactionID)}
							)";

		$conn->Execute($query);

		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";

		$dr = $conn->GetRow($query);

		$this->_primaryIDproperty->Value = $dr['newID'];

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

		$conn = GetConnection();

		foreach ($this->_transactionMessages as $tempMessage)
		{

			$query = "	INSERT INTO core_CreditCardTransactionMessage
						(
							TransactionID,
							MessageText
						)
						VALUES
						(
							{$this->_transactionID},
							'{$tempMessage}'
						)";

			$conn->Execute($query);
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