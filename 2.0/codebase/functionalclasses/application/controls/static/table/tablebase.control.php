<?php
/**
 * Table Base Control Class File
 * @package Sandstone
 * @subpackage Application
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

class TableBaseControl extends BaseControl
{

	protected $_columnHeaders;
	protected $_dataRows;

	protected $_noRecordsText;

   	public function __construct()
	{

		parent::__construct();

		//Setup the default style classes
		$this->_bodyStyle->AddClass('table_body');

		$this->Label->BodyStyle->AddClass('table_label');
		$this->Message->BodyStyle->AddClass('table_message');

		$this->_columnHeaders = Array();
		$this->_dataRows = Array();

		$this->_noRecordsText = "No Records";
	}

	/**
	 * ColumnHeaders property
	 *
	 * @return Array
	 *
	 * @param Array $Value
	 */
	public function getColumnHeaders()
	{
		return $this->_columnHeaders;
	}

	public function setColumnHeaders($Value)
	{
		if (is_array($Value))
		{
			$this->_columnHeaders = $Value;
		}
		else
		{
			$this->_columnHeaders = Array();
		}

	}

	/**
	 * NoRecordsText property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getNoRecordsText()
	{
		return $this->_noRecordsText;
	}

	public function setNoRecordsText($Value)
	{
		$this->_noRecordsText = $Value;
	}

	/**
	 * InnerHTML property
	 *
	 * @return string
	 */
	public function getInnerHTML()
	{

		//Setup the headings and data arrays we will use
		$this->SetupHeadingsAndData();

		if (count($this->_dataRows) == 0 && count($this->_columnHeaders) == 0)
		{
			//We simply output the message div

			//Render a label
			$this->Label->TargetControlName = $this->Message->Name;
			$returnValue = $this->Label->__toString();

			$this->Message->InnerHTML = DIescape($this->_noRecordsText);
			$returnValue .= $this->Message->__toString();
		}
		else
		{
			//We output a table

     		$dataRows = $this->RenderDataRows();
			$headers = $this->RenderHeaderRow();

			//Render a label
			$this->Label->TargetControlName = $this->Name;
			$returnValue = $this->Label->__toString();

			$id = "id=\"{$this->Name}\"";

			$returnValue .= "<table {$id} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style}>";

			$returnValue .= $headers;
			$returnValue .= $dataRows;

			$returnValue .= "</table>";

		}

		return $returnValue;

	}

	protected function RenderLabel()
	{

		$this->Label->TargetControlName = $this->Name;

		$returnValue = $this->Label->__toString();

		return $returnValue;

	}

	protected function RenderDataRows()
	{

		$maxColumnCount = 0;

		if (count($this->_dataRows) > 0)
		{
			foreach($this->_dataRows as $tempID=>$tempDataRow)
			{
				$id = "id=\"{$this->Name}_{$tempID}\"";

				$returnValue .= "<tr {$id}>";

				$tempColumnCount = 0;

				foreach($tempDataRow as $tempDataItem)
				{
      				if ($tempDataItem == "")
					{
						$tempDataItem = "&nbsp;";
					}

					$returnValue .= "<td>{$tempDataItem}</td>";

					$tempColumnCount++;
				}

				$returnValue .= "</tr>";

				//Do we have a new max count?
				if ($tempColumnCount > $maxColumnCount)
				{
					$maxColumnCount = $tempColumnCount;
				}
			}

			//Now that we are though all the records, make sure we have enough column headers
			$this->FixColumnHeaders($maxColumnCount);
		}
		else
		{
			$noRecordsClass = "class=\"table_norecords\"";

			$columns = count($this->_columnHeaders);

			if ($columns > 0)
			{
				$colSpan = "colspan=\"{$columns}\"";
			}

			$messageText = DIescape($this->_noRecordsText);

			$returnValue .= "<tr><td {$colSpan} {$noRecordsClass}>{$messageText}</td></tr>";
		}

		return $returnValue;

	}

	protected function FixColumnHeaders($MaxColumnCount)
	{
		if (count($this->_columnHeaders) > 0)
		{
			while (count($this->_columnHeaders) < $MaxColumnCount)
			{
				$this->_columnHeaders[] = "&nbsp;";
			}
		}
	}

	protected function RenderHeaderRow()
	{

		if (count($this->_columnHeaders) > 0)
		{
			$id = "id=\"{$this->Name}_Header\"";
			$returnValue = "<tr {$id}>";

			foreach($this->_columnHeaders as $tempColumnName)
			{
				if ($tempColumnName == "")
				{
					$tempColumnName = "&nbsp;";
				}

				$returnValue .= "<th>{$tempColumnName}</th>";
			}

			$returnValue .= "</tr>";
		}

		return $returnValue;
	}

	public function AddColumnHeader($NewHeader)
	{
		$this->_columnHeaders[] = $NewHeader;
	}

	protected function SetupHeadingsAndData()
	{

	}

}
?>