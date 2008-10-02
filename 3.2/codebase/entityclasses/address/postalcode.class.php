<?php
/*
PostalCode Class File

@package Sandstone
@subpackage Address
 */

NameSpace::Using("Sandstone.ADOdb");

class PostalCode extends CodeTableEntityBase
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

        $this->AddProperty("PostalCode","string","PostalCode",false,false,false,false,false,null);
        $this->AddProperty("PostalCodeTypeCode","string","PostalCodeTypeCode",false,false,false,false,false,null);
        $this->AddProperty("CityName","string",null,true,false,false,false,false,null);
        $this->AddProperty("County","County",null,true,false,false,false,false,null);
        $this->AddProperty("Province","Province","ProvinceID",false,false,false,false,false,null);
        $this->AddProperty("TimeZone","string","TimeZone",false,false,false,false,false,null);
        $this->AddProperty("UTC","decimal","UTC",false,false,false,false,false,null);
        $this->AddProperty("IsDST","boolean","IsDST",false,false,false,false,false,null);
        $this->AddProperty("Latitude","decimal","Latitude",false,false,false,false,false,null);
        $this->AddProperty("Longitude","decimal","Longitude",false,false,false,false,false,null);
        $this->AddProperty("Cities","array",null,true,false,false,false,false,null);
        $this->AddProperty("IsInvalidFormat","boolean",null,true,false,false,false,false,null);

        parent::SetupProperties();
    }

    public function LoadByID($ID)
    {

    	$returnValue = false;
		$this->_cities->Clear();

		$postalCode = $this->ValidateCodeFormat($ID);

		if (is_set($postalCode))
		{
	        $selectClause = PostalCode::GenerateBaseSelectClause();

	        $group = substr($ID, 0, 2);
	        $fromClause = PostalCode::GenerateBaseFromClause();
	        $fromClause = str_replace("XX", $group, $fromClause);

			$whereClause = "WHERE UPPER(PostalCode) = '{$postalCode}' ";

	        $query = $selectClause . $fromClause . $whereClause;

	        $ds = $this->Conn->Execute($query);

	        if ($ds && $ds->RecordCount() > 0)
	        {

				while ($dr = $ds->FetchRow())
				{
					if ($this->IsLoaded == false)
					{
						$returnValue = $this->Load($dr);
					}

					$this->LoadCity($dr);
				}
	        }
		}

        return $returnValue;

    }

	protected function ValidateCodeFormat($PostalCode)
	{

		$this->_isInvalidFormat = false;

		$PostalCode = trim(strtoupper($PostalCode));

		if (is_numeric($PostalCode) && strlen($PostalCode) == 5)
		{
			//5 digit US
			$returnValue = $PostalCode;
		}
		else if (strlen($PostalCode) == 10 && strpos($PostalCode, "-") == 5)
		{
			//5+4 digit US
			$returnValue = substr($PostalCode, 0, 5);
		}
		else if (strlen($PostalCode) == 7 && strpos($PostalCode, " ") == 3)
		{
			//Canadian
			$returnValue = $PostalCode;
		}
		else
		{
			//Not a valid format
			$this->_isInvalidFormat = true;
		}

		if (is_set($returnValue))
		{
			//Make sure a lookup table for it exists
			$targetTableName = "location_PostalCodeMaster_" . substr($returnValue, 0, 2);

			$ds = $this->Conn->Execute("show tables");

			$isFound = false;

			while ($dr = $ds->FetchRow())
			{
				if (strtolower($dr[0]) == strtolower($targetTableName))
				{
					$isFound = true;
				}
			}

			if ($isFound == false)
			{
				//No match found
				$returnValue = null;
			}

		}

		return $returnValue;
	}


	protected function LoadCity($dr)
	{

		$newCity = new City();

		$newCity->CityName = $dr['CityName'];
		$newCity->CityTypeCode = $dr['CityTypeCode'];
		$newCity->County = new County($dr['CountyID']);
		$newCity->PostalCode = $this;

		$this->_cities[] = $newCity;

		if ($newCity->CityTypeCode == "D")
		{
			$this->_cityName = $newCity->CityName;
			$this->_county = $newCity->County;
		}

	}

    /*
    Static Query Functions
     */
    static public function GenerateBaseSelectClause()
    {
        $returnValue = "    SELECT    a.PostalCode,
                                        a.PostalCodeTypeCode,
                                        a.CityName,
                                        a.CityTypeCode,
                                        a.CountyID,
                                        a.ProvinceID,
                                        a.TimeZone,
                                        a.UTC,
                                        a.IsDST,
                                        a.Latitude,
                                        a.Longitude ";

        return $returnValue;
    }

    static public function GenerateBaseFromClause()
    {
        $returnValue = "    FROM    location_PostalCodeMaster_XX a ";

        return $returnValue;
    }

    static public function GenerateBaseWhereClause()
    {
        return null;

    }

}
?>