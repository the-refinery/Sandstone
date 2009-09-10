<?php
/*
County Class File

@package Sandstone
@subpackage Address
 */

class County extends CodeTableEntityBase
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

        $this->AddProperty("CountyID","integer","CountyID",true,false,true,false,false,null);
        $this->AddProperty("CountyName","string","CountyName",false,true,false,false,false,null);
        $this->AddProperty("CountyFIPS","string","CountyFIPS",false,false,false,false,false,null);
        $this->AddProperty("Province","Province","ProvinceID",false,true,false,true,false,null);

        parent::SetupProperties();
    }

    protected function SaveNewRecord()
    {
        $query = new Query();

        $query->SQL = "	INSERT INTO location_CountyMaster
                        (
                            CountyName,
                            CountyFIPS,
                            ProvinceID
                        )
                        VALUES
                        (
                            {$query->SetTextField($this->_countyName)},
                            {$query->SetNullTextField($this->_countyFIPS)},
                            {$this->_province}
                        )";

        $query->Execute();

		$this->GetNewPrimaryID();

        return true;
    }

    protected function SaveUpdateRecord()
    {
        $query = new Query();

        $query->SQL = "	UPDATE location_CountyMaster SET
	                        CountyName = {$query->SetTextField($this->_countyName)},
	                        CountyFIPS = {$query->SetNullTextField($this->_countyFIPS)},
	                        ProvinceID = {$this->_province}
                        WHERE CountyID = {$this->_countyID}";

        $query->Execute();

        return true;
    }

    /*
    Static Query Functions
     */
    static public function GenerateBaseSelectClause()
    {
        $returnValue = "    SELECT    a.CountyID,
                                        a.CountyName,
                                        a.CountyFIPS,
                                        a.ProvinceID ";

        return $returnValue;
    }

    static public function GenerateBaseFromClause()
    {
        $returnValue = "    FROM    location_CountyMaster a ";

        return $returnValue;
    }

    static public function GenerateBaseWhereClause()
    {
        return null;

    }

}
?>