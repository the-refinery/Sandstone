<?php
/*
Province Class File

@package Sandstone
@subpackage Address
 */

NameSpace::Using("Sandstone.ADOdb");

class Province extends CodeTableEntityBase
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

        $this->AddProperty("ProvinceID","integer","ProvinceID",true,false,true,false,false,null);
        $this->AddProperty("ProvinceName","string","ProvinceName",false,true,false,false,false,null);
        $this->AddProperty("ProvinceCode","string","ProvinceCode",false,true,false,false,false,null);
        $this->AddProperty("StateFIPS","string","StateFIPS",false,false,false,false,false,null);
        $this->AddProperty("Country","Country","CountryID",false,false,false,true,false,null);

        parent::SetupProperties();
    }

    public function LoadByCode($CountryCode)
    {

        $CountryCode = strtoupper($CountryCode);

        $selectClause = self::GenerateBaseSelectClause();
        $fromClause = self::GenerateBaseFromClause();

        $whereClause = "WHERE   ProvinceCode = '{$CountryCode}' ";

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
        $returnValue = "    SELECT    a.ProvinceID,
                                        a.ProvinceName,
                                        a.ProvinceCode,
                                        a.StateFIPS,
                                        a.CountryID ";

        return $returnValue;
    }

    static public function GenerateBaseFromClause()
    {
        $returnValue = "    FROM    location_ProvinceMaster a ";

        return $returnValue;
    }

    static public function GenerateBaseWhereClause()
    {
        return null;

    }

}
?>