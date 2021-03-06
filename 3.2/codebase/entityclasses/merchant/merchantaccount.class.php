<?php
/*
MerchantAccount Class File

@package Sandstone
@subpackage Merchant
 */

SandstoneNamespace::Using("Sandstone.Action");
SandstoneNamespace::Using("Sandstone.ADOdb");
SandstoneNamespace::Using("Sandstone.CreditCard");

class MerchantAccount extends EntityBase
{

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

		$this->AddProperty("MerchantAccountID","integer","MerchantAccountID",true,false,true,false,false,null);
		$this->AddProperty("Name","string","Name",false,true,false,false,false,null);
		$this->AddProperty("ProcessorClassName","string","ProcessorClassName",false,true,false,false,false,null);
		$this->AddProperty("IsAvailable","boolean","IsAvailable",false,true,false,false,false,null);
		$this->AddProperty("TransactionFee","decimal","TransactionFee",false,false,false,false,false,null);
		$this->AddProperty("DiscountPercent","decimal","DiscountPercent",false,false,false,false,false,null);
		$this->AddProperty("IsActive","boolean",null,true,false,false,false,false,null);
		$this->AddProperty("Parameters","array",null,true,false,false,false,true,"LoadParameters");

		parent::SetupProperties();
	}

	public function Load($dr)
	{
		$returnValue = parent::Load($dr);

		if (is_set($dr['AccountID']))
		{
			$this->_isActive = true;
		}
		else
		{
			$this->_isActive = false;
		}
	}

	public function LoadActive()
	{

		$returnValue = false;

		$conn = GetConnection();

		$selectClause = MerchantAccount::GenerateBaseSelectClause();

		$fromClause = MerchantAccount::GenerateBaseFromClause();
		$fromClause = str_replace("LEFT JOIN", "INNER JOIN", $fromClause);

		$query = $selectClause . $fromClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();

			$returnValue = $this->Load($dr);
		}

		return $returnValue;

	}

    public function LoadParameters()
	{

		$this->_parameters->Clear();

		//Preload the parameter names
		switch ($this->_merchantAccountID)
		{
			case 2:
				$this->PreloadLinkpointParameters();
				break;

			case 3:
				$this->PreloadAuthorizeNetParameters();
				break;
		}

		$conn = GetConnection();

		$query = "	SELECT	ParameterName,
							ParameterValue
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

	protected function PreloadLinkpointParameters()
	{
		$this->_parameters['StoreNumber'] = null;
	}

	protected function PreloadAuthorizeNetParameters()
	{
		$this->_parameters['x_login'] = null;
		$this->_parameters['x_tran_key'] = null;
	}

	protected function SaveNewRecord()
	{
		//There is no "new" record to save.  Since this is really a relationship
		//class
		return false;
	}

	protected function SaveUpdateRecord()
	{

		$returnValue = false;

		//Only save an update if this is the active record
		if ($this->_isActive)
		{

			$conn = GetConnection();

			//Update the Transaction Fee & Discount Percent
			$query = "	UPDATE 	core_ActiveMerchantAccount SET
							TransactionFee = {$conn->SetNullNumericField($this->_transactionFee)},
							DiscountPercent = {$conn->SetNullNumericField($this->_discountPercent)}
						WHERE 	MerchantAccountID = {$this->_merchantAccountID}
						AND		AccountID = {$this->AccountID}";

			$conn->Execute($query);

			//Make sure our parameters are Loaded
			$this->LoadParameters();

			//Clear any existing parameters from the database.
			$this->ClearParameters();

			//Loop the parameters and save anything that has a value
			foreach ($this->_parameters as $key=>$value)
			{
				if (is_set($value))
				{
					$query = "	INSERT INTO core_MerchantAccountParameters
								(
									AccountID,
									MerchantAccountID,
									ParameterName,
									ParameterValue
								)
								VALUES
								(
									{$this->AccountID},
									{$this->_merchantAccountID},
									{$conn->SetTextField($key)},
									{$conn->SetTextField($value)}
								)";

					$conn->Execute($query);
				}
			}

			$returnValue = true;
		}

		return $returnValue;
	}

	protected function ClearParameters()
	{
		$conn = GetConnection();

		$query = "	DELETE
                    FROM	core_MerchantAccountParameters
					WHERE	AccountID = {$this->AccountID}
					AND		MerchantAccountID = {$this->_merchantAccountID}";

		$conn->Execute($query);

	}

	public function Activate()
	{

		$returnValue = false;

		if ($this->_isActive == false)
		{
			$conn = GetConnection();

			//Delete any existing active merchant account
			$this->ClearActiveMerchantAccount();

			//Insert a new record
			$query = "	INSERT INTO core_ActiveMerchantAccount
						(
							AccountID,
							MerchantAccountID
						)
						VALUES
						(
							{$this->AccountID},
							{$this->_merchantAccountID}
						)";

			$conn->Execute($query);

			$this->_isActive = true;

			//Save the rest of the data including any parameters.
			$returnValue = $this->SaveUpdateRecord();
		}

		return $returnValue;
	}

	protected function ClearActiveMerchantAccount()
	{
		$conn = GetConnection();

		//Delete any existing active merchant account
		$query = "	DELETE
					FROM	core_ActiveMerchantAccount
					WHERE	AccountID = {$this->AccountID}";

		$conn->Execute($query);
	}

	public function Deactivate()
	{
		$returnValue = false;

		if ($this->_isActive)
		{
			//Clear any parameters
			$this->ClearParameters();

			//Remove the active account
			$this->ClearActiveMerchantAccount();

			$returnValue = true;
		}

		return $returnValue;
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
		$returnValue = "	SELECT	a.MerchantAccountID,
										a.Name,
										a.ProcessorClassName,
										a.IsAvailable,
										b.TransactionFee,
										b.DiscountPercent,
										b.AccountID ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{

		$accountID = Application::License()->AccountID;

		$returnValue = "	FROM	core_MerchantAccountMaster a
									LEFT JOIN core_ActiveMerchantAccount b ON
										b.MerchantAccountID = a.MerchantAccountID
										AND b.AccountID = {$accountID} ";

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		return null;

	}

}
?>