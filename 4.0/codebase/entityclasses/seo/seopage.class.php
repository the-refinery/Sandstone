<?php
/*
SEOpage Class File

@package Sandstone
@subpackage SEO
*/

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
		$returnValue = str_replace("/","", $returnValue);

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

		$query = new Query();

		$Name = strtolower($Name);

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$whereClause = "WHERE 	AccountID = {$this->AccountID}
						AND		LOWER(Name) = {$query->SetTextField($Name)} ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;
	}

	public function LoadByTypeAndID($AssociatedEntityType, $AssociatedEntityID)
	{

		$returnValue = false;

		if (is_set($AssociatedEntityType) && is_set($AssociatedEntityID))
		{
			$query = new Query();

			$Name = strtolower($Name);

			$selectClause = self::GenerateBaseSelectClause();
			$fromClause = self::GenerateBaseFromClause();
			$whereClause = "WHERE	AccountID = {$this->AccountID}
							AND		LOWER(AssociatedEntityType) = {$query->SetTextField($AssociatedEntityType)}
							AND		AssociatedEntityID = {$AssociatedEntityID} ";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			$returnValue = $query->LoadEntity($this);
		}

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_SEOpageMaster
						(
							Name,
							AccountID,
							AssociatedEntityType,
							AssociatedEntityID,
							RoutingRuleName
						)
						VALUES
						(
							{$query->SetTextField($this->_name)},
							{$this->AccountID},
							{$query->SetTextField($this->_associatedEntityType)},
							{$this->_associatedEntityID},
							{$query->SetTextField($this->_routingRuleName)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_SEOpageMaster SET
							Name = {$query->SetTextField($this->_name)},
							AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)},
							AssociatedEntityID = {$this->_associatedEntityID},
							RoutingRuleName = {$query->SetTextField($this->_routingRuleName)}
						WHERE SEOpageID = {$this->_sEOpageID}";

		$query->Execute();

		return true;
	}

	public function VerifyUniqueSEOname($TestSEOname)
	{

		$query = new Query();

		$query->SQL = "	SELECT 	SEOpageID,
								Name
						FROM 	core_SEOpageMaster
						WHERE 	Name LIKE {$query->SetTextField($TestSEOname)}";

		if (is_set($this->_seoPageID))
		{
			$query->SQL .= "	AND	SEOpageID <> {$this->_seoPageID}";
		}

		$query->Execute();

		if ($query->SelectedRows == 0)
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