<?php
/*
MerchantAccount Class File

@package Sandstone
@subpackage Merchant
 */

NameSpace::Using("Sandstone.ADOdb");
NameSpace::Using("Sandstone.CreditCard");

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

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_MerchantAccountMaster
							(
								Name,
								ProcessorClassName,
								IsAvailable
							)
							VALUES
							(
								{$conn->SetTextField($this->_name)},
								{$conn->SetTextField($this->_processorClassName)},
								{$conn->SetBooleanField($this->_isAvailable)}
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

		$query = "	UPDATE core_MerchantAccountMaster SET
								Name = {$conn->SetTextField($this->_name)},
								ProcessorClassName = {$conn->SetTextField($this->_processorClassName)},
								IsAvailable = {$conn->SetBooleanField($this->_isAvailable)}
							WHERE MerchantAccountID = {$this->_merchantAccountID}";

		$conn->Execute($query);

		return true;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.MerchantAccountID,
										a.Name,
										a.ProcessorClassName,
										a.IsAvailable ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_MerchantAccountMaster a ";

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		return null;

	}

}
?>