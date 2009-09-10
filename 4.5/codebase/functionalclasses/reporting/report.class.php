<?php
/*
Report Class File

@package Sandstone
@subpackage Reporting
 */

class Report extends EntityBase
{

    protected $_groupLevels;
    protected $_totalFields;
    protected $_subtotalFields;

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

        $this->AddProperty("ReportID","integer","ReportID",true,false,true,false,false,null);
        $this->AddProperty("AccountID","int","AccountID",false,false,false,false,false,null);
        $this->AddProperty("Name","string","Name",false,true,false,false,false,null);
        $this->AddProperty("Description","string","Description",false,false,false,false,false,null);
        $this->AddProperty("AssociatedEntityType","string","AssociatedEntityType",false,false,false,false,false,null);
        $this->AddProperty("TemplateName","string","TemplateName",false,false,false,false,false,null);
        $this->AddProperty("IsActive","boolean","IsActive",false,true,false,false,false,null);
        $this->AddProperty("Fields","array",null,true,false,false,false,true,"LoadFields");
        $this->AddProperty("ReturnedFields","array",null,true,false,false,false,true,"LoadFields");
        $this->AddProperty("FieldsByAlias","array",null,true,false,false,false,true,"LoadFields");
        $this->AddProperty("Query","string",null,true,false,false,false,false,null);

        parent::SetupProperties();
    }

    /*
    GroupLevels property

    @return array
     */
    public function getGroupLevels()
    {
        return $this->_groupLevels;
    }

    /*
    TotalFields property

    @return array
     */
    public function getTotalFields()
    {
        return $this->_totalFields;
    }

    /*
    SubtotalFields property

    @return array
     */
    public function getSubtotalFields()
    {
        return $this->_subtotalFields;
    }

	public function getQueryParameters()
	{
		foreach ($this->Fields as $tempField)
		{
			$fieldParms = $tempField->QueryParameters;

			if (is_set($fieldParms))
			{
				$parms[] = $fieldParms;
			}
		}

		if (count($parms) > 0)
		{
			$returnValue = implode("&", $parms);
		}

		return $returnValue;
	}

	public function LoadByTemplateName($Name)
	{

		$Name = strtolower($Name);

		$query = new Query();

		$selectClause = Report::GenerateBaseSelectClause();
		$fromClause = Report::GenerateBaseFromClause();

		$whereClause = Report::GenerateBaseWhereClause();
		$whereClause .= "AND LOWER(a.TemplateName) = {$query->SetTextField($Name)} ";

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;
	}

    protected function SaveNewRecord()
    {
        $query = new Query();

        $query->SQL = "	INSERT INTO core_ReportMaster
	                    (
	                        AccountID,
	                        Name,
	                        Description,
	                        AssociatedEntityType,
	                        TemplateName,
	                        IsActive
	                    )
	                    VALUES
	                    (
	                        {$query->SetNullNumericField($this->_accountID)},
	                        {$query->SetTextField($this->_name)},
	                        {$query->SetNullTextField($this->_description)},
	                        {$query->SetNullTextField($this->_associatedEntityType)},
	                        {$query->SetTextField($this->_templateName)},
	                        {$query->SetBooleanField($this->_isActive)}
	                    )";

        $query->Execute();

		$this->GetNewPrimaryID();

        return true;
    }

    protected function SaveUpdateRecord()
    {
        $query = new Query();

        $query->SQL = "	UPDATE core_ReportMaster SET
	                        AccountID = {$query->SetNullNumericField($this->_accountID)},
	                        Name = {$query->SetTextField($this->_name)},
	                        Description = {$query->SetNullTextField($this->_description)},
	                        AssociatedEntityType = {$query->SetNullTextField($this->_associatedEntityType)},
	                        TemplateName = {$query->SetTextField($this->_templateName)},
	                        IsActive = {$query->SetBooleanField($this->_isActive)}
                        WHERE ReportID = {$this->_reportID}";

        $query->Execute();

        return true;
    }

	public function SetupFromEventParameters($EventParameters)
	{
		foreach ($this->Fields as $tempField)
		{
			$tempField->SetupFromEventParameters($EventParameters);
		}
	}

    public function LoadFields()
    {

        $this->_fields->Clear();
        $this->_fieldsByAlias->Clear();
        $this->_returnedFields->Clear();

        $returnValue = false;

        if ($this->IsLoaded)
        {
            $query = new Query();

            $selectClause = ReportField::GenerateBaseSelectClause();
            $fromClause = ReportField::GenerateBaseFromClause();
            $whereClause = "WHERE a.ReportID = {$this->_reportID} ";

            $query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			if ($query->SelectedRows > 0)
			{
            	$query->LoadEntityArray($this->_fields, "ReportField", "FieldID", $this, "LoadFieldsCallback");

                $returnValue = true;
            }
        }

        return $returnValue;

    }

	public function LoadFieldsCallback($Field)
	{
		$Field->Report = $this;

		$this->_fieldsByAlias[strtolower($Field->FieldAlias)] = $Field;

        if ($Field->IsReturned)
        {
            $this->_returnedFields[$Field->FieldID] = $Field;
        }


		return $Field;
	}

    public function Generate()
    {

    	$this->BuildTotalsArray();
		$returnValue = new ReportGroup($this);

        //Make sure our fields are loaded
        if (count($this->_fields) == 0)
        {
            $this->LoadFields();
        }

        $sqlRender = new Renderable();
        $template = $sqlRender->Template;

        $template->RequestFileType = "sql";
        $template->FileName = $this->_templateName;
        $template->AccountID = $this->AccountID;

        $template->WhereClause = $this->BuildWhereClause();
        $template->HavingClause = $this->BuildHavingClause();
        $template->OrderByClause = $this->BuildOrderByClause();

        $this->_query = $sqlRender->Render();

        if (strlen($this->_query) > 0)
        {
            $query = new Query();

            $query->SQL = $this->_query;

            $query->Execute();

            if ($query->SelectedRows > 0)
            {
                foreach ($query->Results as $dr)
                {
                    $returnValue->AddData($dr);
                }
            }
        }

        return $returnValue;
    }

    protected function BuildWhereClause()
    {

        foreach ($this->_fields as $tempField)
        {
            if ($tempField->IsFilterable)
            {
                if ($tempField->IsFilterable && $tempField->IsHavingFilter == false)
                {
                    if (is_set($tempField->Condition))
                    {
                        $conditions[] = $tempField->Condition;
                    }
                }
            }
        }

        if (count($conditions) > 0)
        {
            $returnValue = implode("\nAND \t", $conditions);
        }

        if (strlen($returnValue) > 0)
        {
            $returnValue = "AND \t" . $returnValue;
        }

        return $returnValue;
    }

    protected function BuildHavingClause()
    {
        foreach ($this->_fields as $tempField)
        {
            if ($tempField->IsFilterable && $tempField->IsHavingFilter)
            {
                if (is_set($tempField->Condition))
                {
                    $conditions[] = $tempField->Condition;
                }
            }
        }

        if (count($conditions) > 0)
        {
            $returnValue = implode("\nAND \t", $conditions);
        }

        if (strlen($returnValue) > 0)
        {
            $returnValue = "HAVING \t" . $returnValue;
        }

        return $returnValue;

    }

    protected function BuildOrderByClause()
    {

        $sortOrder = Array();
        $sortLevels = Array();
        $this->_groupLevels = Array();

        foreach ($this->_fields as $tempField)
        {
            if (is_set($tempField->SortLevel))
            {
                $sortLevels[$tempField->SortLevel] = $tempField->FieldID;
            }

            if (is_set($tempField->GroupLevel))
            {
                $this->_groupLevels[$tempField->GroupLevel] = $tempField->FieldID;
            }
        }


        //Sort the arrays
        ksort($sortLevels);
        ksort($this->_groupLevels);

        //Grouping First
        foreach ($this->_groupLevels as $tempGroup)
        {
            $sortOrder[] = $tempGroup;
        }

        //Then sorting (if they aren't already in the list)
        foreach($sortLevels as $tempSort)
        {
            if (array_search($tempSort, $sortOrder) === false)
            {
                $sortOrder[] = $tempSort;
            }
        }

        //If there is any sort orders, build the clause
        if (count($sortOrder) > 0)
        {

            foreach ($sortOrder as $tempID)
            {
                $fieldSortClause = $this->_fields[$tempID]->FilterClause;

                if ($this->_fields[$tempID]->IsSortDescending)
                {
                    $fieldSortClause .= " DESC";
                }

                $sortOrderParts[] = $fieldSortClause;
            }

            $returnValue = "ORDER BY " . implode(", ", $sortOrderParts);
        }

        return $returnValue;
    }

    protected function BuildTotalsArray()
    {
        $this->_totalFields = Array();
        $this->_subtotalFields = Array();

        foreach ($this->_fields as $tempField)
        {
            if ($tempField->IsTotaled)
            {
                $this->_totalFields[] = $tempField->FieldID;

                if ($tempField->IsSubTotaled)
                {
                    $this->_subtotalFields[] = $tempField->FieldID;
                }
            }
        }
    }

    /*
    Static Query Functions
     */
    static public function GenerateBaseSelectClause()
    {
        $returnValue = "    SELECT    a.ReportID,
                                        a.AccountID,
                                        a.Name,
                                        a.Description,
                                        a.AssociatedEntityType,
                                        a.TemplateName,
                                        a.IsActive ";

        return $returnValue;
    }

    static public function GenerateBaseFromClause()
    {
        $returnValue = "    FROM    core_ReportMaster a ";

        return $returnValue;
    }

    static public function GenerateBaseWhereClause()
    {

        $accountID = Application::License()->AccountID;

        $returnValue = "WHERE (a.AccountID IS NULL OR a.AccountID = {$accountID}) ";

        return $returnValue;
    }

}
?>