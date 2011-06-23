<?php
/*
Country Class File

@package Sandstone
@subpackage Address
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class Country extends CodeTableEntityBase
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

		$this->AddProperty("CountryID","integer","CountryID",true,false,true,false,false,null);
		$this->AddProperty("Name","string","Name",true,true,false,false,false,null);
		$this->AddProperty("CountryCode","string","ISO",true,true,false,false,false,null);

		parent::SetupProperties();
	}


    public function LoadByCode($CountryCode)
    {

        $CountryCode = strtoupper($CountryCode);

        $selectClause = self::GenerateBaseSelectClause();
        $fromClause = self::GenerateBaseFromClause();

        $whereClause = "WHERE   ISO = '{$CountryCode}' ";

        $query = $selectClause . $fromClause . $whereClause;

        $ds = $this->Conn->Execute($query);

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


	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.CountryID,
										a.Name,
										a.ISO ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	location_CountryMaster a ";

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		return null;

	}

}
?>