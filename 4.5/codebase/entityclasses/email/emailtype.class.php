<?php
/*
EmailType Class File

@package Sandstone
@subpackage Email
 */

class EmailType extends EntityBase
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

		$this->AddProperty("EmailTypeID","integer","EmailTypeID",true,false,true,false,false,null);
		$this->AddProperty("Description","string","Description",false,true,false,false,false,null);

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_EmailTypeMaster
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

		$query->SQL = "	UPDATE core_EmailTypeMaster SET
								Description = {$query->SetTextField($this->_description)}
							WHERE EmailTypeID = {$this->_emailTypeID}";

		$query->Execute();

		return true;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.EmailTypeID,
										a.Description ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_EmailTypeMaster a ";

		return $returnValue;
	}

    static public function GenerateBaseWhereClause()
	{
		return null;
	}

}
?>