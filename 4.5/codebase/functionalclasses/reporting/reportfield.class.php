<?php
/*
ReportField Class File

@package Sandstone
@subpackage Reporting
 */

class ReportField extends EntityBase
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

        $this->AddProperty("FieldID","integer","FieldID",true,false,true,false,false,null);
        $this->AddProperty("Report","Report","ReportID",false,true,false,true,false,null);
        $this->AddProperty("Name","string","Name",false,true,false,false,false,null);
        $this->AddProperty("Description","string","Description",false,false,false,false,false,null);
        $this->AddProperty("FieldAlias","string","FieldAlias",false,false,false,false,false,null);
        $this->AddProperty("DataType","string","DataType",false,true,false,false,false,null);
        $this->AddProperty("IsSortable","boolean","IsSortable",false,true,false,false,false,null);
        $this->AddProperty("IsTotalable","boolean","IsTotalable",false,true,false,false,false,null);
        $this->AddProperty("IsGroupable","boolean","IsGroupable",false,true,false,false,false,null);
        $this->AddProperty("IsFilterable","boolean","IsFilterable",false,true,false,false,false,null);
        $this->AddProperty("FilterClause","string","FilterClause",false,false,false,false,false,null);
        $this->AddProperty("IsHavingFilter","boolean","IsHavingFilter",false,true,false,false,false,null);
        $this->AddProperty("IsReturned","boolean","IsReturned",false,true,false,false,false,null);
        $this->AddProperty("IsIncluded","boolean",null,false,false,false,false,false,null);
        $this->AddProperty("SortLevel","integer",null,false,false,false,false,false,null);
        $this->AddProperty("IsSortDescending","boolean",null,false,false,false,false,false,null);
        $this->AddProperty("IsTotaled","boolean",null,false,false,false,false,false,null);
        $this->AddProperty("IsSubTotaled","boolean",null,false,false,false,false,false,null);
        $this->AddProperty("GroupLevel","integer",null,false,false,false,false,false,null);
        $this->AddProperty("MatchValue","variant",null,false,false,false,false,false,null);
        $this->AddProperty("NotValue","variant",null,false,false,false,false,false,null);
        $this->AddProperty("MinValue","variant",null,false,false,false,false,false,null);
        $this->AddProperty("MaxValue","variant",null,false,false,false,false,false,null);

        parent::SetupProperties();
    }

    public function getCondition()
    {

        if ($this->_isFilterable)
        {
            if (is_set($this->_matchValue))
            {
                $returnValue = $this->BuildMatchValueCondition();
            }
            else if (is_set($this->_notValue))
            {
            	$returnValue = $this->BuildNotValueCondition();
            }
            else
            {
                if (is_set($this->_minValue) && is_set($this->_maxValue))
                {
                    //Range
                    $returnValue = $this->BuildRangeValueCondition();
                }
                else if (is_set($this->_minValue))
                {
                    //Greater than
                    $returnValue = $this->BuildGreaterThanCondition();
                }
                else if (is_set($this->_maxValue))
                {
                    //Less than
                    $returnValue = $this->BuildLessThanCondition();
                }
            }
        }

        return $returnValue;
    }

	public function getQueryParameters()
	{
		$baseKey = "f{$this->_fieldID}";

		$parms = Array();

		//Filter Value
 		if ($this->_isFilterable)
        {
            if (is_set($this->_matchValue))
            {
                if ($this->_matchValue instanceof DIarray)
                {
                	foreach ($this->_matchValue as $tempValue)
                	{
                		$val = $this->FormatValueForQueryParameter($tempValue);
                		$parms[] = "{$baseKey}matv[]={$val}";
                	}
                }
                else
                {
                	$val = $this->FormatValueForQueryParameter($this->_matchValue);
                	$parms[] = "{$baseKey}matv={$val}";
                }
            }
            else if (is_set($this->_notValue))
            {
                if ($this->_notValue instanceof DIarray)
                {
                	foreach ($this->_notValue as $tempValue)
                	{
                		$val = $this->FormatValueForQueryParameter($tempValue);
                		$parms[] = "{$baseKey}notv[]={$val}";
                	}
                }
                else
                {
                	$val = $this->FormatValueForQueryParameter($this->_notValue);
                	$parms[] = "{$baseKey}notv={$val}";
                }
            }
            else
            {
                if (is_set($this->_minValue))
                {
					$val = $this->FormatValueForQueryParameter($this->_minValue);
					$parms[] = "{$baseKey}minv={$val}";
				}


                if (is_set($this->_maxValue))
                {
					$val = $this->FormatValueForQueryParameter($this->_maxValue);
					$parms[] = "{$baseKey}maxv={$val}";
                }
            }
        }

		//Sorting
		if ($this->_isSortable)
		{
			if (is_set($this->_sortLevel))
			{
				$parms[] = "{$baseKey}sl={$this->_sortLevel}";

				if ($this->_isSortDescending)
				{
					$parms[] = "{$baseKey}isd=1";
				}
			}
		}

		//Totaled
		if ($this->_isTotalable)
		{
			if ($this->_isTotaled)
			{
				$parms[] = "{$baseKey}it=1";
			}
		}

		if (count($parms) > 0)
		{
			$returnValue = implode("&", $parms);
		}

		return $returnValue;
	}

	protected function FormatValueForQueryParameter($Value)
	{
		if ($Value instanceof Date)
		{
			$returnValue = $Value->MySQLtimestamp;
		}
		else
		{
			$returnValue = $Value;
		}

		$returnValue = urlencode($returnValue);

		return $returnValue;
	}

	public function SetupFromEventParameters($EventParameters)
	{
		$baseKey = "f{$this->_fieldID}";

		//Filtering
        if ($this->_isFilterable)
        {
        	//Match Value
        	if (is_set($EventParameters["{$baseKey}matv"]))
        	{
        		if (is_array($EventParameters["{$baseKey}matv"]))
        		{
        			$this->_matchValue = new DIarray();

					foreach ($EventParameters["{$baseKey}matv"] as $tempValue)
					{
						$this->_matchValue[] = $this->ParseValueFromQueryParameters($tempValue);
					}
        		}
        		else
        		{
        			$this->_matchValue = $this->ParseValueFromQueryParameters($EventParameters["{$baseKey}matv"]);
        		}
        	}

        	//Not Value
        	if (is_set($EventParameters["{$baseKey}notv"]))
        	{
        		if (is_array($EventParameters["{$baseKey}notv"]))
        		{
        			$this->_notValue = new DIarray();

					foreach ($EventParameters["{$baseKey}notv"] as $tempValue)
					{
						$this->_notValue[] = $this->ParseValueFromQueryParameters($tempValue);
					}
        		}
        		else
        		{
        			$this->_notValue = $this->ParseValueFromQueryParameters($EventParameters["{$baseKey}notv"]);
        		}
        	}


        	//Min Value
        	if (is_set($EventParameters["{$baseKey}minv"]))
        	{
				$this->_minValue = $this->ParseValueFromQueryParameters($EventParameters["{$baseKey}minv"]);
			}

        	//Max Value
        	if (is_set($EventParameters["{$baseKey}maxv"]))
        	{
				$this->_maxValue= $this->ParseValueFromQueryParameters($EventParameters["{$baseKey}maxv"]);
			}
		}

		//Sorting
		if ($this->_isSortable)
		{
			if (is_set($EventParameters["{$baseKey}sl"]))
			{
				$this->_sortLevel = $EventParameters["{$baseKey}sl"];

				if (is_set($EventParameters["{$baseKey}isd"]))
				{
					$this->_isSortDescending = true;
				}
			}
		}

		//Totaled
		if ($this->_isTotalable)
		{
			if (is_set($EventParameters["{$baseKey}it"]))
			{
				$this->_isTotaled = true;
			}
		}

	}

	protected function ParseValueFromQueryParameters($QueryParameterValue)
	{

		if ($this->_dataType == "datetime" || $this->_dataType == "date")
		{
			$returnValue = new Date($QueryParameterValue);
		}
		else
		{
			$returnValue = $QueryParameterValue;
		}

		return $returnValue;
	}

    protected function BuildMatchValueCondition()
    {

        $returnValue = $this->_filterClause;

        if ($this->_matchValue instanceof DIarray)
        {
            foreach ($this->_matchValue as $tempValue)
            {
                $values[] = $this->FormatValue($tempValue);
            }

            $returnValue .= " IN (" . implode(",", $values) . ")";
        }
        else
        {
            $returnValue .= " = " . $this->FormatValue($this->_matchValue);
        }


        $returnValue .= " ";

        return $returnValue;
    }

    protected function BuildNotValueCondition()
    {

        $returnValue = $this->_filterClause;

        if ($this->_notValue instanceof DIarray)
        {
            foreach ($this->_notValue as $tempValue)
            {
                $values[] = $this->FormatValue($tempValue);
            }

            $returnValue .= " NOT IN (" . implode(",", $values) . ")";
        }
        else
        {
            $returnValue .= " <> " . $this->FormatValue($this->_notValue);
        }


        $returnValue .= " ";

        return $returnValue;
    }

    protected function BuildRangeValueCondition()
    {

        $returnValue = $this->_filterClause . " >= " . $this->FormatValue($this->_minValue);
        $returnValue .= " AND ";
        $returnValue .= $this->_filterClause . " <= " . $this->FormatValue($this->_maxValue);
        $returnValue .= " ";

        return $returnValue;

    }

    protected function BuildGreaterThanCondition()
    {

        $returnValue = $this->_filterClause . " > " . $this->FormatValue($this->_minValue);
        $returnValue .= " ";

        return $returnValue;

    }

    protected function BuildLessThanCondition()
    {

        $returnValue .= $this->_filterClause . " < " . $this->FormatValue($this->_maxValue);
        $returnValue .= " ";

        return $returnValue;

    }

    protected function FormatValue($Value)
    {
        switch (strtolower($this->_dataType))
        {

            case "integer":
            case "decimal":
                $returnValue = $Value;
                break;

            case "datetime":
                if ($Value instanceof Date)
                {
                    $query = new Query();
                    $returnValue = $query->SetDateField($Value);
                }
                else
                {
                    $returnValue = "'{$Value}'";
                }

                break;

            default:

                $returnValue = "'" . strtolower($Value) . "'";
                break;
        }

        return $returnValue;

    }

    protected function SaveNewRecord()
    {
        $query = new Query();

        $query->SQL = "	INSERT INTO core_ReportFieldMaster
                        (
                            ReportID,
                            Name,
                            Description,
                            FieldAlias,
                            DataType,
                            IsSortable,
                            IsTotalable,
                            IsGroupable,
                            IsFilterable,
                            FilterClause,
                            IsHavingFilter,
                            IsReturned
                        )
                        VALUES
                        (
                            {$this->_report},
                            {$query->SetTextField($this->_name)},
                            {$query->SetNullTextField($this->_description)},
                            {$query->SetNullTextField($this->_fieldAlias)},
                            {$query->SetTextField($this->_dataType)},
                            {$query->SetBooleanField($this->_isSortable)},
                            {$query->SetBooleanField($this->_isTotalable)},
                            {$query->SetBooleanField($this->_isGroupable)},
                            {$query->SetBooleanField($this->_isFilterable)},
                            {$query->SetNullTextField($this->_filterClause)},
                            {$query->SetBooleanField($this->_isHavingFilter)},
                            {$query->SetBooleanField($this->_isReturned)}
                        )";

        $query->Execute();

		$this->GetNewPrimaryID();

        return true;
    }

    protected function SaveUpdateRecord()
    {
        $query = new Query();

        $query->SQL = "    UPDATE core_ReportFieldMaster SET
                                ReportID = {$this->_report},
                                Name = {$query->SetTextField($this->_name)},
                                Description = {$query->SetNullTextField($this->_description)},
                                FieldAlias = {$query->SetNullTextField($this->_fieldAlias)},
                                DataType = {$query->SetTextField($this->_dataType)},
                                IsSortable = {$query->SetBooleanField($this->_isSortable)},
                                IsTotalable = {$query->SetBooleanField($this->_isTotalable)},
                                IsGroupable = {$query->SetBooleanField($this->_isGroupable)},
                                IsFilterable = {$query->SetBooleanField($this->_isFilterable)},
                                FilterClause = {$query->SetNullTextField($this->_filterClause)},
                                IsHavingFilter = {$query->SetBooleanField($this->_isHavingFilter)}
                                IsReturned = {$query->SetBooleanField($this->_isReturned)}
                            WHERE FieldID = {$this->_fieldID}";

        $query->Execute();

        return true;
    }

    /*
    Static Query Functions
     */
    static public function GenerateBaseSelectClause()
    {
        $returnValue = "    SELECT    a.FieldID,
                                        a.ReportID,
                                        a.Name,
                                        a.Description,
                                        a.FieldAlias,
                                        a.DataType,
                                        a.IsSortable,
                                        a.IsTotalable,
                                        a.IsGroupable,
                                        a.IsFilterable,
                                        a.FilterClause,
                                        a.IsHavingFilter,
                                        a.IsReturned ";

        return $returnValue;
    }

    static public function GenerateBaseFromClause()
    {
        $returnValue = "    FROM    core_ReportFieldMaster a ";

        return $returnValue;
    }

    static public function GenerateBaseWhereClause()
    {
        return null;

    }

}
?>