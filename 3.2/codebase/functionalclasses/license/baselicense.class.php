<?php
/*
BaseLicense Class File

@package Sandstone
@subpackage License
 */

NameSpace::Using("Sandstone.ADOdb");
NameSpace::Using("Sandstone.Merchant");

class BaseLicense extends EntityBase
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

		$this->AddProperty("AccountID","integer","AccountID",true,false,true,false,false,null);
		$this->AddProperty("Name","string","Name",false,true,false,false,false,null);
		$this->AddProperty("ActiveMerchantAccount","ActiveMerchantAccount",null,false,false,false,true,true,"LoadActiveMerchantAccount");

		parent::SetupProperties();
	}

	//To override the EntityBase one
	public function getAccountID()
	{
		return $this->_accountID;
	}

	public function getIsValid()
	{
		return $this->IsLoaded;
	}

	public function setActiveMerchantAccount($Value)
	{
		if ($Value instanceof ActiveMerchantAccount && $Value->IsLoaded && $Value->IsAvailable)
		{
			$this->_activeMerchantAccount = $Value;

			$this->_activeMerchantAccount->Save();
		}
		else
		{
			$this->_activeMerchantAccount = null;

			ActiveMerchantAccount::ClearActiveMerchantAccount();
		}
	}

	public function LoadActiveMerchantAccount()
	{
		$returnValue = false;

		$this->_activeMerchantAccount = null;

		$conn = GetConnection();

		$selectClause = ActiveMerchantAccount::GenerateBaseSelectClause();
		$fromClause = ActiveMerchantAccount::GenerateBaseFromClause();

		$whereClause = "WHERE	AccountID = {$this->_accountID} ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds)
		{
			if ($ds->RecordCount() > 0)
			{
				$dr = $ds->FetchRow();

				$this->_activeMerchantAccount = new ActiveMerchantAccount($dr);
			}

			$returnValue = true;
		}

		return $returnValue;

	}

	public function LookupByName($AccountName)
	{

		$returnValue = false;

		$conn = GetConnection();

		$searchString = strtolower($AccountName);

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$whereClause = "WHERE	LOWER(a.Name) = {$conn->SetTextField($searchString)} ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();

			$returnValue = $this->Load($dr);
		}

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_AccountMaster
							(
								Name
							)
							VALUES
							(
								{$conn->SetTextField($this->_name)}
							)";

		$conn->Execute($query);

		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";

		$dr = $conn->GetRow($query);

		$this->_primaryIDproperty->Value = $dr['newID'];

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$conn = GetConnection();

		$query = "	UPDATE core_AccountMaster SET
								Name = {$conn->SetTextField($this->_name)}
							WHERE AccountID = {$this->_accountID}";

		$conn->Execute($query);

		return true;
	}

	static public function FormatValidAccountName($AccountName)
	{

		$returnValue = strtolower($AccountName);

		$returnValue = StringFunc::RemovePunctuation($returnValue);

		if (strlen($returnValue) > 75)
		{
			$returnValue = substr($returnValue, 0, 75);
		}

		return $returnValue;
	}

	static public function ValidateUniqueAccountName($NewAccountName)
	{

		$conn = GetConnection();

		$selectClause = "	SELECT 	COUNT(AccountID) NumberAccounts ";
		$fromClause = self::GenerateBaseFromClause();

		$whereClause .= "WHERE Name LIKE '{$NewAccountName}' ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);
		$dr = $ds->FetchRow();

		if ($dr['NumberAccounts'] == 0)
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.AccountID,
										a.Name ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_AccountMaster a ";

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		return null;
	}

}
?>