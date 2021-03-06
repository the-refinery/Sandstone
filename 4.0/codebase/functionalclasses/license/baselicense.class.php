<?php
/*
BaseLicense Class File

@package Sandstone
@subpackage License
 */

SandstoneNamespace::Using("Sandstone.Merchant");
SandstoneNamespace::Using("Sandstone.Utilities.String");

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

		$this->AddProperty("AccountID","integer","AccountID",PROPERTY_PRIMARY_ID);
		$this->AddProperty("Name","string","Name",PROPERTY_REQUIRED);
		$this->AddProperty("IsCancelled","boolean","IsCancelled",PROPERTY_READ_WRITE);

		$this->AddProperty("APIkey","string",null,PROPERTY_READ_ONLY,"LoadAPIkey");

		parent::SetupProperties();
	}

	//To override the EntityBase one
	public function getAccountID()
	{
		return $this->_accountID;
	}

  public function setName($Value)
  {
    $this->_name = License::FormatValidAccountName($Value);
  }

	public function getIsValid()
	{
		return $this->IsLoaded;
	}

	public function getActiveMerchantAccount()
	{
		$returnValue = new MerchantAccount();
		$returnValue->LoadActive();

		if ($returnValue->IsLoaded == false)
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function LoadAPIkey()
	{
		$query = new Query();

		$query->SQL = "SELECT APIkey
			FROM core_AccountAPIkey
			WHERE AccountID = {$this->_accountID} ";

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$this->_apiKey = $query->SingleRowResult['APIkey'];
		}

	}

	public function LookupByName($AccountName)
	{
		$returnValue = false;

		$query = new Query();

		$searchString = strtolower($AccountName);

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();

		$whereClause = "WHERE	LOWER(a.Name) = {$query->SetTextField($searchString)} ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_AccountMaster
			(
				Name
			)
			VALUES
			(
	{$query->SetTextField($this->_name)}
)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_AccountMaster SET
			Name = {$query->SetTextField($this->_name)}
			WHERE AccountID = {$this->_accountID}";

		$query->Execute();

		return true;
	}

	public function GenerateAPIkey()
	{
		$now = new Date();

		$keyData = "{$this->_name}-{$now->Datestamp}";

		$apiKey = md5($keyData);

		$this->_apiKey = $apiKey;

		$this->SaveAPIkey();
	}

	protected function SaveAPIkey()
	{
		$query = new Query();

		$query->SQL = "DELETE 
			FROM core_AccountAPIkey
			WHERE AccountID = {$this->_accountID} ";

		$query->Execute();

		$query->SQL = "INSERT INTO core_AccountAPIkey
			(
				AccountID,
				APIkey
			)
			VALUES
			(
	{$this->_accountID},
	{$query->SetTextField($this->_apiKey)}
) ";

		$query->Execute();

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

  public function UniqueAccountName($Control)
  {
    $newName = License::FormatValidAccountName($Control->Value);
    $defaultName = License::FormatValidAccountName($Control->DefaultValue);

    if($newName != $defaultName)
    {
      if (License::ValidateUniqueAccountName($newName) == false)
      {
        if (is_set($Control->LabelText))
        {
          $name = $Control->LabelText;
        }
        else
        {
          $name = $Control->Name;
        }

        $returnValue = $name . " has already been taken, please try another name!";
      }
    }

    return $returnValue;
  }

	static public function ValidateUniqueAccountName($NewAccountName)
	{

		$query = new Query();

		$selectClause = "	SELECT 	COUNT(AccountID) NumberAccounts ";
		$fromClause = self::GenerateBaseFromClause();

		$whereClause .= "WHERE Name LIKE {$query->SetTextField($NewAccountName)} ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		if ($query->SingleRowResult['NumberAccounts'] == 0)
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
