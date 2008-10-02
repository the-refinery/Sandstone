<?php
/*
ActiveMerchantAccount Class File

@package Sandstone
@subpackage Merchant
 */

NameSpace::Using("Sandstone.ADOdb");

class ActiveMerchantAccount extends MerchantAccount
{
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

		$this->AddProperty("TransactionFee","decimal","TransactionFee",false,false,false,false,false,null);
		$this->AddProperty("DiscountPercent","decimal","DiscountPercent",false,false,false,false,false,null);
		$this->AddProperty("Parameters","array",null,true,false,false,false,true,"LoadParameters");

		parent::SetupProperties();
	}

    public function LoadByID($ID)
    {

		//Since this is a relationship type of object, we'll do a base ShippingMethod load when requested by ID.
		//That way we can Load a ActiveShippingMethod, then set all the properties for the account and it's relationship
		//To this account.

        $conn = GetConnection();

		$selectClause = MerchantAccount::GenerateBaseSelectClause();

		$fromClause = MerchantAccount::GenerateBaseFromClause();

		$whereClause = "WHERE a.{$this->_primaryIDproperty->DBfieldName} = {$ID} ";

        $query = $selectClause . $fromClause . $whereClause;

        $ds = $conn->Execute($query);

        if ($ds && $ds->RecordCount() > 0)
        {
            $dr = $ds->FetchRow();
            $returnValue = $this->Load($dr);
        }
        else
        {
            $returnValue = false;
        }

        return $returnValue;

    }

    public function LoadParameters()
	{

		$conn = GetConnection();

		$query = "	SELECT	ParameterName,
							ParameterValue,
							IsEncrypted
					FROM	core_MerchantAccountParameters
					WHERE	AccountID = {$this->AccountID}
					AND		MerchantAccountID = {$this->_merchantAccountID}";

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow())
			{
				$tempKey = $dr['ParameterName'];
				$tempValue = $dr['ParameterValue'];
				$isEncrypted = Connection::GetBooleanField($dr['IsEncrypted']);

				if ($isEncrypted)
				{
					$tempValue = DIencrypt::Decrypt($tempValue);
				}

				$this->_parameters[$tempKey] = $tempValue;
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
		//Clear any existing record.
		$this->Delete();

		$conn = GetConnection();

		$query = "	INSERT INTO core_ActiveMerchantAccount
							(
								AccountID,
								MerchantAccountID,
								TransactionFee,
								DiscountPercent
							)
							VALUES
							(
								{$this->AccountID},
								{$this->_merchantAccountID},
								{$conn->SetNullNumericField($this->_transactionFee)},
								{$conn->SetNullNumericField($this->_discountPercent)}
							)";

		$conn->Execute($query);

		return true;
	}

	protected function SaveUpdateRecord()
	{
		//We always delete and re-add since this is really a relationship entity
		return $this->SaveNewRecord();
	}

	public function Delete()
	{
		$conn = GetConnection();

		$query = "	DELETE
					FROM	core_ActiveMerchantAccount
					WHERE	AccountID = {$this->AccountID} ";

		$conn->Execute($query);

		return true;
	}

	public function ProcessAuthorization($CreditCard, $Amount)
	{

		if ($CreditCard instanceof CreditCard && $CreditCard->IsLoaded && $Amount > 0)
		{
			$processor = $this->SetupProcessor($CreditCard);

			if (is_set($processor))
			{
				$returnValue = $processor->ProcessAuthorization($Amount);

				$this->HandleProcessedTransaction($returnValue, "Authorization");
			}
		}

		return $returnValue;

	}

	public function ProcessCharge($CreditCard, $Amount, $AuthTransaction = null)
	{

		if ($CreditCard instanceof CreditCard && $CreditCard->IsLoaded && $Amount > 0)
		{
			$processor = $this->SetupProcessor($CreditCard);

			if (is_set($processor))
			{
				$returnValue = $processor->ProcessCharge($Amount, $AuthTransaction);

				$this->HandleProcessedTransaction($returnValue, "Charge");
			}
		}

		return $returnValue;
	}

	public function ProcessCredit($CreditCard, $Amount, $ChargeTransaction = null)
	{
		if ($CreditCard instanceof CreditCard && $CreditCard->IsLoaded && $Amount > 0)
		{
			$processor = $this->SetupProcessor($CreditCard);

			if (is_set($processor))
			{
				$returnValue = $processor->ProcessCredit($Amount, $ChargeTransaction);

				$this->HandleProcessedTransaction($returnValue, "Credit");

			}
		}

		return $returnValue;

	}

	protected function SetupProcessor($CreditCard)
	{

    	//Creates an object of the class specified from the database.
		$returnValue = new $this->_processorClassName ($this->Parameters);

		$success = $CreditCard->SetupMerchantProcessor($returnValue);

		if ($success = false)
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	protected function HandleProcessedTransaction($Transaction, $Action)
	{

		if (is_set($Transaction))
		{
			if ($returnValue->IsSuccessful)
			{
				Action::Log("CreditCardProcessSuccessful", "Credit Card {$Action} Transaction Processing Successful", $CreditCard->CreditCardID);
			}
			else
			{
				Action::Log("CreditCardProcessFailed", "Credit Card {$Action} Transaction Processing Failed", $CreditCard->CreditCardID);
			}
		}
		else
		{
			Action::Log("CreditCardProcessFailed", "Credit Card {$Action} Transaction Processing Failed", $CreditCard->CreditCardID);
		}

	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{

		$returnValue = MerchantAccount::GenerateBaseSelectClause();

		$returnValue .= ",	b.TransactionFee,
							b.DiscountPercent ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{

		$returnValue = MerchantAccount::GenerateBaseFromClause();

		$returnValue .= "INNER JOIN core_ActiveMerchantAccount b on b.MerchantAccountID = a.MerchantAccountID ";

		return $returnValue;
	}

	static public function ClearActiveMerchantAccount()
	{

		$conn = GetConnection();

		$accountID = Application::License()->AccountID;

		$query = "	DELETE
					FROM	core_ActiveMerchantAccount
					WHERE	AccountID = {$accountID} ";

		$conn->Execute($query);

	}

}
?>