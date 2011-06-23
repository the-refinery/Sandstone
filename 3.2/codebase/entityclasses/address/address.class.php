<?php
/*
Address Class File

@package Sandstone
@subpackage Address
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class Address extends EntityBase
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

        $this->AddProperty("AddressID","integer","AddressID",true,false,true,false,false,null);
        $this->AddProperty("Street","string","Street",false,true,false,false,false,null);
        $this->AddProperty("City","string","City",false,true,false,false,false,null);
        $this->AddProperty("ProvinceCode","string","ProvinceCode",false,true,false,false,false,null);
        $this->AddProperty("PostalCode","string","PostalCode",false,true,false,false,false,null);
        $this->AddProperty("CountryCode","string","CountryCode",false,true,false,false,false,null);

        parent::SetupProperties();
    }

	public function setProvinceCode($Value)
	{
		$this->_provinceCode = strtoupper($Value);
	}

	public function setPostalCode($Value)
	{
		$this->_postalCode = strtoupper($Value);
	}

	public function setCountryCode($Value)
	{
		$this->_countryCode = strtoupper($Value);
	}

    protected function SaveNewRecord()
    {
        $conn = GetConnection();

        $query = "    INSERT INTO core_AddressMaster
                            (
                                AccountID,
                                Street,
                                City,
                                ProvinceCode,
                                PostalCode,
                                CountryCode
                            )
                            VALUES
                            (
                                {$this->AccountID},
                                {$conn->SetTextField($this->_street)},
                                {$conn->SetTextField($this->_city)},
                                {$conn->SetTextField($this->_provinceCode)},
                                {$conn->SetTextField($this->_postalCode)},
                                {$conn->SetTextField($this->_countryCode)}
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
        //We only ever save new records - never overwrite an existing address
        $returnValue = $this->SaveNewRecord();

        return $returnValue;
    }

	public function IsSameAddress($TargetAddress)
	{
		$returnValue = true;

		if (is_set($TargetAddress) && $TargetAddress instanceof Address)
		{
			if ($TargetAddress->IsLoaded)
			{
				if ($TargetAddress->AddressID <> $this->_addressID)
				{
					$returnValue = false;
				}
			}
			else
			{
				if (strtolower($TargetAddress->Street) != strtolower($this->_street))
				{
					$returnValue = false;
				}

				if (strtolower($TargetAddress->City) != strtolower($this->_city))
				{
					$returnValue = false;
				}

				if ($TargetAddress->ProvinceCode != $this->_provinceCode)
				{
					$returnValue = false;
				}

				if ($TargetAddress->PostalCode != $this->_postalCode)
				{
					$returnValue = false;
				}

				if ($TargetAddress->CountryCode != $this->_countryCode)
				{
					$returnValue = false;
				}

			}
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
        $returnValue = "    SELECT    a.AddressID,
                                        a.Street,
                                        a.City,
                                        a.ProvinceCode,
                                        a.PostalCode,
                                        a.CountryCode ";

        return $returnValue;
    }

    static public function GenerateBaseFromClause()
    {
        $returnValue = "    FROM    core_AddressMaster a ";

        return $returnValue;
    }

    /*
    Search Query Functions
     */
    static public function SearchMultipleEntity($SearchTerm)
    {
        $likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

        $whereClause .= "WHERE     LOWER (Street) LIKE {$likeClause} ";
        $whereClause .= "OR         LOWER (City) LIKE {$likeClause} ";
        $whereClause .= "OR         LOWER (ProvinceCode) LIKE {$likeClause} ";
        $whereClause .= "OR         LOWER (PostalCode) LIKE {$likeClause} ";
        $whereClause .= "OR         LOWER (CountryCode) LIKE {$likeClause} ";

        $returnValue = self::PerformSearch($whereClause);

        return $returnValue;
    }

    static public function SearchSingleEntity($SearchTerm)
    {
        $likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

        $whereClause .= "WHERE     LOWER (Street) LIKE {$likeClause} ";
        $whereClause .= "OR         LOWER (City) LIKE {$likeClause} ";
        $whereClause .= "OR         LOWER (ProvinceCode) LIKE {$likeClause} ";
        $whereClause .= "OR         LOWER (PostalCode) LIKE {$likeClause} ";
        $whereClause .= "OR         LOWER (CountryCode) LIKE {$likeClause} ";

        $returnValue = self::PerformSearch($whereClause);

        return $returnValue;
    }

    static protected function PerformSearch($WhereClause)
    {
        $conn = GetConnection();

        $selectClause = self::GenerateBaseSelectClause();
        $fromClause = self::GenerateBaseFromClause();

        $query = $selectClause . $fromClause . $whereClause;

        $ds = $conn->Execute($query);

        $returnValue = new Dataset($ds, "Address", "AddressID");

        return $returnValue;
    }

}
?>