<?php
/*
County Class File

@package Sandstone
@subpackage Address
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

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
        $conn = GetConnection();

        $query = "    INSERT INTO location_CountyMaster
                            (
                                CountyName,
                                CountyFIPS,
                                ProvinceID
                            )
                            VALUES
                            (
                                {$conn->SetTextField($this->_countyName)},
                                {$conn->SetNullTextField($this->_countyFIPS)},
                                {$this->_province}
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

        $query = "    UPDATE location_CountyMaster SET
                                CountyName = {$conn->SetTextField($this->_countyName)},
                                CountyFIPS = {$conn->SetNullTextField($this->_countyFIPS)},
                                ProvinceID = {$this->_province}
                            WHERE CountyID = {$this->_countyID}";

        $conn->Execute($query);

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