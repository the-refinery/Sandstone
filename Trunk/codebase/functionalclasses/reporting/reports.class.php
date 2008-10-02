<?php
/*
Reports Class File

@package Sandstone
@subpackage Reporting
 */

NameSpace::Using("Sandstone.ADOdb");

class Reports extends Module
{

	protected $_reports;
	protected $_reportsByType;

	public function __construct()
	{
		$this->_reports = new DIarray();
		$this->_reportsByType = new DIarray();

		$this->Load();
	}

	/*
	Reports property

	@return array
	 */
	public function getReports()
	{
		return $this->_reports;
	}

	/*
	ReportsByType property

	@return array
	 */
	public function getReportsByType()
	{
		return $this->_reportsByType;
	}

	public function Load()
	{

		$this->_isLoaded = false;

		$this->_reports->Clear();
		$this->_reportsByType->Clear();

		$conn = GetConnection();

		$selectClause = Report::GenerateBaseSelectClause();

		$selectClause = Report::GenerateBaseSelectClause();
		$fromClause = Report::GenerateBaseFromClause();
		$whereClause = Report::GenerateBaseWhereClause();

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow())
			{
				$tempReport = new Report($dr);

				$this->_reports[$tempReport->ReportID] = $tempReport;

				$tempEntityType = strtolower($tempReport->AssociatedEntityType);

				if (is_set($this->_reportsByType[$tempEntityType]) == false)
				{
					$this->_reportsByType[$tempEntityType] = new DIarray();
				}

				$this->_reportsByType[$tempEntityType][$tempReport->ReportID] = $tempReport;
			}

			$this->_isLoaded = true;
		}

		return $this->_isLoaded;
	}


}
?>