<?php

class BarChart extends GoogleChart
{
	protected $_colors = array();
	
	protected function BuildParameters()
	{
		$returnValue = array(
								'cht' => 'bvs',
								'chco' => $this->Colors
							);

		return $returnValue;
	}

	protected function getDataValues()
	{
		$returnValue = $this->SimpleEncode($this->_data,0,16);

		return $returnValue;
	}

	public function AddData($data, $legend = "")
	{
		$this->_data[] = $data;
	}
		
	public function getColors()
	{
		if (is_array($this->_colors))
		{
			$returnValue = implode(",", $this->_colors);
		}
		else
		{
			$returnValue = $this->_colors;
		}
		return $returnValue;
	}
	
	public function setColors($Colors)
	{
		$this->_colors = $Colors;
	}
}

?>