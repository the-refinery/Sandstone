<?php
/*
Role Class File

@package Sandstone
@subpackage User
 */

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
		$query = new Query();

		$query->SQL = "	INSERT INTO core_RoleMaster
						(
							Description
						)
						VALUES
						(
							{$query->SetNullTextField($this->_description)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_RoleMaster SET
							Description = {$query->SetTextField($this->_description)}
						WHERE RoleID = {$this->_roleID}";

		$query->Execute();

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