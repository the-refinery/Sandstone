<?php

class LineChart extends GoogleChart
{
	protected $_legend = array();
	protected $_showLegend = true;
	protected $_showGrid = true;

	protected $_colors = array();

	protected $_bottomAxisLabels = array();

	protected $_axisSize = 50; // Space labels a minimum of 50px apart

	protected $_leftAxisLabels = array();

	protected function BuildParameters()
	{
		$returnValue = array(
								'cht' => 'lc',
								'chdl' => $this->Legend,
								'chco' => $this->Colors,
								'chxt' => $this->Axes,
								'chxl' => $this->AxesLabels,
								'chg' => $this->Grid
							);

		return $returnValue;
	}

	public function AddData($data, $legend = "", $color = null)
	{
		$this->_data[] = $data;
		$this->_legend[] = urlencode($legend);

		if (is_null($color))
		{
			$color = ColorFunc::GenerateRandomColor(50,180);
		}

		if (count(array_keys($data)) > 0)
		{
			$this->_bottomAxisLabels = array_keys($data);
		}

		$this->_colors[] = $this->FormatColor($color);
	}

	public function getShowLegend()
	{
		return $this->_showLegend;
	}

	public function getLegend()
	{
		if ($this->_showLegend)
		{
			$returnValue = implode("|", $this->_legend);
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	public function getGrid()
	{
		if ($this->_showGrid == true)
		{
			// Vertical lines are a percentage
			if (count($this->_bottomAxisLabels) == count($this->_data[0]))
			{
				// If there are the same number of labels as data points, subtract one to make them align
				$x = 100 / (count($this->_bottomAxisLabels) - 1);
			}
			else
			{
				// If there are the fewer labels than data points, subtract two to make them align
				$x = 100 / (count($this->_bottomAxisLabels) - 2);
			}

			$numberOfLabels = ($this->Height / $this->_axisSize);
			$y = 100 / ($numberOfLabels - 1);

			$returnValue = "{$x},{$y}";
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	public function setShowGrid($Value)
	{
		$this->_showGrid = $Value;
	}

	public function getColors()
	{
		$returnValue = implode(",", $this->_colors);

		return $returnValue;
	}

	public function getAxes()
	{
		if (count($this->_bottomAxisLabels) > 0)
		{
			$axes[] = "x";
		}

		$axes[] = "y";

		if (is_array($axes))
		{
			$returnValue = implode(',', $axes);
		}

		return $returnValue;
	}

	public function getAxesLabels()
	{
		$returnValue = "";

		// Bottom Axis
		$returnValue .= $this->getBottomAxisLabels();

		// Left Axis
		$returnValue .= $this->getLeftAxisLabels();

		return $returnValue;
	}

	public function getBottomAxisWidth()
	{
		return $this->_bottomAxisWidth;
	}

	public function setBottomAxisWidth($Value)
	{
		$this->_bottomAxisWidth = $Value;
	}

	public function getBottomAxisLabels()
	{
		if (count($this->_bottomAxisLabels) > 0)
		{
			// Don't show all labels if they are too closely spaced.
			$labelCount = count($this->_bottomAxisLabels);

			while (($this->Width / $labelCount) <= $this->_axisSize)
			{
				$this->_bottomAxisLabels = $this->RemoveAlternatingArrayElements($this->_bottomAxisLabels);
				$labelCount = count($this->_bottomAxisLabels);
			}

			$returnValue = "0:|" . implode("|", $this->_bottomAxisLabels);
		}

		return $returnValue;
	}

	public function getLeftAxisLabels()
	{
		// Merge all data series together to get min/max values
		$mergedData = array();
		foreach ($this->_data as $dataSeries)
		{
			$mergedData = array_merge($mergedData, array_values($dataSeries));
		}
		$minValue = round(min($mergedData));
		$maxValue = round(max($mergedData));
		$dataDifference = $maxValue - $minValue;

		// Determine how many labels we can fit
		$numberOfLabels = $this->Height / $this->_axisSize;
		$labelSize = $dataDifference / ($numberOfLabels - 1);

		// Determine what the value of each label should be
		$labels[] = $minValue;
		for ($i = 1; $i <= ($numberOfLabels - 2); $i++)
		{
			$labels[] = round($minValue + ($labelSize * $i));
		}
		$labels[] = $maxValue;

		// Format for Google
		$returnValue = "1:|" . implode("|", $labels);

		return $returnValue;
	}

	protected function RemoveAlternatingArrayElements($Array)
	{
		for ($i = 0; $i <= count($Array); $i+=2)
		{
			$returnValue[] = $Array[$i];
		}

		return $returnValue;
	}
}

?>