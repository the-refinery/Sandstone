<?php
/*
Phone Class File

@package Sandstone
@subpackage Phone
 */

NameSpace::Using("Sandstone.ADOdb");
NameSpace::Using("Sandstone.Utilities.String");

class Phone extends EntityBase
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

		$this->AddProperty("PhoneID","integer","PhoneID",true,false,true,false,false,null);
		$this->AddProperty("CountryCode","string","CountryCode",false,false,false,false,false,null);
		$this->AddProperty("AreaCode","string","AreaCode",false,false,false,false,false,null);
		$this->AddProperty("LocalNumber","string","LocalNumber",false,true,false,false,false,null);
		$this->AddProperty("PhoneType", "PhoneType", "PhoneTypeID",false,false,false,true,false,null);

		parent::SetupProperties();
	}

	/*
	Number property

	@return int
	@param int $Value
	*/
	public function getNumber()
	{
		return "+" . $this->_countryCode . " (" . $this->_areaCode . ") " .
			substr($this->_localNumber,0,3) . "-" . substr($this->_localNumber,3,4);
	}

	public function setNumber($Value)
	{
		$Value = StringFunc::MakeDecimal($Value);

		if (strlen($Value) == 10)
		{
			$Value = "1" . $Value;
		}

		$this->_localNumber = substr($Value,-7,7);
		$this->_areaCode = substr($Value,-10,3);

		if(substr($Value,1,strlen($Value) - 10) != "")
		{
			$this->_countryCode = substr($Value,0,strlen($Value) - 10);
		}
		else
		{
			$this->_countryCode = 1;
		}
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_PhoneMaster
							(
								AccountID,
								CountryCode,
								AreaCode,
								LocalNumber
							)
							VALUES
							(
								{$this->AccountID},
								{$conn->SetNullTextField($this->_countryCode)},
								{$conn->SetNullTextField($this->_areaCode)},
								{$conn->SetTextField($this->_localNumber)}
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

		$query = "	UPDATE core_PhoneMaster SET
								CountryCode = {$conn->SetNullTextField($this->_countryCode)},
								AreaCode = {$conn->SetNullTextField($this->_areaCode)},
								LocalNumber = {$conn->SetNullTextField($this->_localNumber)}
							WHERE PhoneID = {$this->_phoneID}";

		$conn->Execute($query);

		return true;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.PhoneID,
										a.CountryCode,
										a.AreaCode,
										a.LocalNumber ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_PhoneMaster a ";

		return $returnValue;
	}

	/*
	Search Query Functions
	 */
	static public function SearchMultipleEntity($SearchTerm)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$whereClause .= "WHERE 	LOWER (CountryCode) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (AreaCode) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (LocalNumber) LIKE {$likeClause} ";

		$returnValue = self::PerformSearch($whereClause);

		return $returnValue;
	}

	static public function SearchSingleEntity($SearchTerm)
	{
		$likeClause = "LIKE '%" . strtolower($SearchTerm) . "%' ";

		$whereClause .= "WHERE 	LOWER (CountryCode) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (AreaCode) LIKE {$likeClause} ";
		$whereClause .= "OR 		LOWER (LocalNumber) LIKE {$likeClause} ";

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

		$returnValue = new ObjectSet($ds, "Phone", "PhoneID");

		return $returnValue;
	}

    // TODO: This is legacy and no longer works, also see new setNumber() for extra functionality
	public function IsValid($Control)
	{
		// No Value is ok, let the IsRequired Validator handle that
		if ($Control->Value != "")
		{
			$tempValue = ereg_replace("[^0-9]", '', $Control->Value);

			if (strlen($Control->Value) != 10)
			{
				$returnValue = $Control->LocalName . " is not a valid phone number!";
			}
		}

		return $returnValue;
	}


}
?>