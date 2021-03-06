<?php
/*
Reports Class File

@package Sandstone
@subpackage Reporting
 */

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

		$query = new Query();

		$selectClause = Report::GenerateBaseSelectClause();

		$selectClause = Report::GenerateBaseSelectClause();
		$fromClause = Report::GenerateBaseFromClause();
		$whereClause = Report::GenerateBaseWhereClause();

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			foreach ($query->Results as $dr)
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