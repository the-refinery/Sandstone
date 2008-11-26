<?php

class BarChart extends GoogleChart
{
	protected $_colors = array();

	protected function BuildParameters()
	{
		$returnValue = array(
								'cht' => 'bhs',
							);

		return $returnValue;
	}

	public function AddData($data, $legend = "")
	{
		$this->_data[] = $data;

		if (is_null($color))
		{
			$color = ColorFunc::GenerateRandomColor(50,180);
		}

		$this->_colors[] = $this->FormatColor($color);
	}
}

?>