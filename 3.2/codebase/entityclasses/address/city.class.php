<?php
/*
City Class File

@package Sandstone
@subpackage Address
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class City extends CodeTableEntityBase
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

        $this->AddProperty("CityName","string",null,false,false,false,false,false,null);
        $this->AddProperty("CityTypeCode","string",null,false,false,false,false,false,null);
        $this->AddProperty("County","County",null,false,false,false,false,false,null);
        $this->AddProperty("PostalCode","PostalCode",null,false,false,false,false,false,null);

        parent::SetupProperties();
    }

}
?>