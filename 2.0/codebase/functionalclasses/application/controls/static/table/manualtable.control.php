<?php
/**
 * Manual Table Control Class File
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

class ManualTableControl extends TableBaseControl
{


	/**
	 * DataRows property
	 *
	 * @return array
	 *
	 * @param array $Value
	 */
	public function getDataRows()
	{
		return $this->_dataRows;
	}

	public function setDataRows($Value)
	{
		$this->_dataRows = $Value;
	}

	public function AddDataRow($NewDataRow)
	{
		if (is_array($NewDataRow))
		{
			$this->_dataRows[] = $NewDataRow;
		}
	}

}
?>