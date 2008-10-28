<?php
/**
 * SQL Table Control Class File
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

class SQLtableControl extends TableBaseControl
{

	protected $_query;

	protected $_recordCount;


	/**
	 * Query property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getQuery()
	{
		return $this->_query;
	}

	public function setQuery($Value)
	{
		$this->_query = $Value;
	}

	/**
	 * RecordCount property
	 *
	 * @return int
	 */
	public function getRecordCount()
	{
		return $this->_recordCount;
	}

	protected function SetupHeadingsAndData()
	{
		if (strlen($this->_query) > 0)
		{
			$conn = GetConnection();

			$ds = $conn->Execute($this->_query);

			$this->_recordCount = $ds->RecordCount();

			if ($this->_recordCount > 0)
			{
				$data = $ds->GetArray();

				//Determine the field names
				foreach (array_keys($data[0]) as $key)
				{
					if (is_numeric($key) == false)
					{
						$fieldNames[] = $key;
					}
				}

				//if we don't have column headers already, use the field names
				if (count($this->_columnHeaders) == 0)
				{
					$this->_columnHeaders = $fieldNames;
				}

				//Now build our data rows
				foreach($data as $tempRecord)
				{
					$dataRow = Array();

					foreach($fieldNames as $columnName)
					{
						$dataRow[$columnName] = $tempRecord[$columnName];
					}

					$this->_dataRows[] = $dataRow;
				}
			}

		}

	}


}
?>
