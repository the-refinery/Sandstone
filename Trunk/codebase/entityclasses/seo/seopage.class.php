<?php
/*
SEOpage Class File

@package Sandstone
@subpackage SEO
*/

NameSpace::Using("Sandstone.ADOdb");

class SEOpage extends EntityBase
{

    public function __construct($ID = null)
    {

        $this->_isTagsDisabled = true;
        $this->_isMessagesDisabled = true;

        parent::__construct($ID);

    }

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

		$this->AddProperty("SEOpageID","integer","SEOpageID",true,false,true,false,false,null);
		$this->AddProperty("Name","string","Name",false,true,false,false,false,null);
		$this->AddProperty("AssociatedEntityType","string","AssociatedEntityType",false,true,false,false,false,null);
		$this->AddProperty("AssociatedEntityID","int","AssociatedEntityID",false,true,false,false,false,null);
		$this->AddProperty("RoutingRuleName","string","RoutingRuleName",false,true,false,false,false,null);
		$this->AddProperty("Keywords","array",null,true,false,false,false,true,"LoadKeywords");

		parent::SetupProperties();
	}

	public function setName($Value)
	{
		if (is_set($Value))
		{
			$Value = $this->EncodeString($Value);

			//Make sure the name we are being passed is unique
			$success = $this->VerifyUniqueSEOname($Value);

			if ($success)
			{
				$this->_name = $Value;
			}
		}
	}

	protected function EncodeString($Title)
	{

		$returnValue = $Title;

		$returnValue = str_replace(" ","-", $returnValue);
		$returnValue = str_replace(".","", $returnValue);
		$returnValue = str_replace(",","", $returnValue);
		$returnValue = str_replace("\"","", $returnValue);
		$returnValue = str_replace("&","And", $returnValue);
		$returnValue = str_replace("\\","", $returnValue);
		$returnValue = str_replace("=","-", $returnValue);
		$returnValue = str_replace("@","-", $returnValue);
		$returnValue = str_replace("&","And", $returnValue);
		$returnValue = str_replace("?","", $returnValue);

		$returnValue = urlencode($returnValue);

		$returnValue = str_replace("+","-", $returnValue);

		return $returnValue;
	}

	public function LoadKeywords()
	{



		return $returnValue;
	}

	public function LoadByName($Name)
	{
		$conn = GetConnection();

		$Name = strtolower($Name);

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$whereClause = "WHERE 	AccountID = {$this->AccountID}
						AND		LOWER(Name) = {$conn->SetTextField($Name)} ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

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

	public function LoadByTypeAndID($AssociatedEntityType, $AssociatedEntityID)
	{

		$returnValue = false;

		if (is_set($AssociatedEntityType) && is_set($AssociatedEntityID))
		{
			$conn = GetConnection();

			$Name = strtolower($Name);

			$selectClause = self::GenerateBaseSelectClause();
			$fromClause = self::GenerateBaseFromClause();
			$whereClause = "WHERE	AccountID = {$this->AccountID}
							AND		LOWER(AssociatedEntityType) = {$conn->SetTextField($AssociatedEntityType)}
							AND		AssociatedEntityID = {$AssociatedEntityID} ";

			$query = $selectClause . $fromClause . $whereClause;

			$ds = $conn->Execute($query);

			if ($ds && $ds->RecordCount() > 0)
			{
				$dr = $ds->FetchRow();

				$returnValue = $this->Load($dr);
			}
		}

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_SEOpageMaster
							(
								Name,
								AccountID,
								AssociatedEntityType,
								AssociatedEntityID,
								RoutingRuleName
							)
							VALUES
							(
								{$conn->SetTextField($this->_name)},
								{$this->AccountID},
								{$conn->SetTextField($this->_associatedEntityType)},
								{$this->_associatedEntityID},
								{$conn->SetTextField($this->_routingRuleName)}
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

		$query = "	UPDATE core_SEOpageMaster SET
								Name = {$conn->SetTextField($this->_name)},
								AssociatedEntityType = {$conn->SetTextField($this->_associatedEntityType)},
								AssociatedEntityID = {$this->_associatedEntityID},
								RoutingRuleName = {$conn->SetTextField($this->_routingRuleName)}
							WHERE SEOpageID = {$this->_sEOpageID}";

		$conn->Execute($query);

		return true;
	}

	public function VerifyUniqueSEOname($TestSEOname)
	{

		$conn = GetConnection();

		$query = "	SELECT 	SEOpageID,
							Name
					FROM 	core_SEOpageMaster
					WHERE 	Name LIKE '{$TestSEOname}'";

		if (is_set($this->_seoPageID))
		{
			$query .= "	AND	SEOpageID <> {$this->_seoPageID}";
		}

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() == 0)
		{
			$returnValue = true;
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
		$returnValue = "	SELECT	a.SEOpageID,
										a.Name,
										a.AssociatedEntityType,
										a.AssociatedEntityID,
										a.RoutingRuleName ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_SEOpageMaster a ";

		return $returnValue;
	}

}
?>