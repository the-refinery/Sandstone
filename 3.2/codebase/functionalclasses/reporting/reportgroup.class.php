<?php
/*
Report Group Class File

@package Sandstone
@subpackage Reporting
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class ReportGroup extends Module
{

    protected $_report;

    protected $_field;
    protected $_value;
    protected $_data;
    protected $_totals;

    public function __construct($Report, $Field = null, $Value = null)
    {
        $this->_report = $Report;
        $this->_field = $Field;
        $this->_value = $Value;

        $this->_data = new DIarray();
        $this->_totals = new DIarray();

        //If field is null, this is the top level group.  Totals are the
        //actual report totals, otherwise we work from the sub totaled fields
        if (is_set($this->_field))
        {
            foreach ($this->_report->SubTotalFields as $tempID)
            {
                $this->_totals[$tempID] = 0;
            }
        }
        else
        {
            foreach ($this->_report->TotalFields as $tempID)
            {
                $this->_totals[$tempID] = 0;
            }
        }
    }

    /*
    Field property

    @return ReportField
     */
    public function getField()
    {
        return $this->_field;
    }

    /*
    Value property

    @return variant
     */
    public function getValue()
    {
        return $this->_value;
    }

    /*
    Data property

    @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /*
    Totals property

    @return array
     */
    public function getTotals()
    {
        return $this->_totals;
    }

	public function getRecordCount()
	{
		return count($this->_data);
	}

    public function AddData($dr)
    {

        //Do I have any totals to handle?
        if (count($this->_totals) > 0)
        {
            foreach ($this->_totals as $tempID=>$tempValue)
            {
                $fieldAlias = $this->_report->Fields[$tempID]->FieldAlias;

                $this->_totals[$tempID] += $dr[$fieldAlias];
            }
        }

        $dataRecord = new DIarray();

        //Later we will make this build the lower level groups, etc.
		foreach ($dr as $tempAlias=>$tempValue)
		{
			if (array_key_exists(strtolower($tempAlias), $this->_report->FieldsByAlias))
			{
				$fieldID = $this->_report->FieldsByAlias[strtolower($tempAlias)]->FieldID;

				$dataRecord[$fieldID] = $tempValue;
			}
		}

        $this->_data[] = $dataRecord;
    }

}
?>