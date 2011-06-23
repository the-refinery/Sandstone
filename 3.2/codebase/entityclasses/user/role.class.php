<?php
/*
Role Class File

@package Sandstone
@subpackage User
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class Role extends EntityBase
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

		$this->AddProperty("RoleID","integer","RoleID",true,false,true,false,false,null);
		$this->AddProperty("Description","string","Description",false,true,false,false,false,null);

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_RoleMaster
							(
								Description
							)
							VALUES
							(
								{$conn->SetNullTextField($this->_description)}
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

		$query = "	UPDATE core_RoleMaster SET
								Description = {$conn->SetTextField($this->_description)}
							WHERE RoleID = {$this->_roleID}";

		$conn->Execute($query);

		return true;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.RoleID,
										a.Description ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_RoleMaster a ";

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		return null;
	}


}
?>