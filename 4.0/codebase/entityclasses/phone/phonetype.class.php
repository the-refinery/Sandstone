<?php
/*
PhoneType Class File

@package Sandstone
@subpackage Phone
 */

class PhoneType extends EntityBase
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

		$this->AddProperty("PhoneTypeID","integer","PhoneTypeID",true,false,true,false,false,null);
		$this->AddProperty("Description","string","Description",false,true,false,false,false,null);

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_PhoneTypeMaster
						(
							Description
						)
						VALUES
						(
							{$query->SetTextField($this->_description)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_PhoneTypeMaster SET
							Description = {$query->SetTextField($this->_description)}
						WHERE PhoneTypeID = {$this->_phoneTypeID}";

		$conn->Execute($query);

		return true;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.PhoneTypeID,
										a.Description ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_PhoneTypeMaster a ";

		return $returnValue;
	}

    static public function GenerateBaseWhereClause()
	{
		return null;
	}

}
?>