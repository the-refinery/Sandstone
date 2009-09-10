<?php

class ChartDataSeries extends Module
{

	protected $_chart;

	protected $_data;
	protected $_labels;
	protected $_legend;
	protected $_color;

	protected $_minimumDataValue;
	protected $_maximumDataValue;

	public function __construct()
	{
		$this->_data = new DIarray();
		$this->_labels = new DIarray();
	}

	/*
	Chart property

	@return ChartBase
	@param ChartBase $Value
	 */
	public function getChart()
	{
		return $this->_chart;
	}

	public function setChart($Value)
	{
		$this->_chart = $Value;
	}

	/*
	Data property

	@return DIarray
	 */
	public function getData()
	{
		return $this->_data;
	}

	/*
	Labels property

	@return DIarray
	 */
	public function getLabels()
	{
		return $this->_labels;
	}

	/*
	Legend property

	@return text
	@param text $Value
	 */
	public function getLegend()
	{
		return $this->_legend;
	}

	public function setLegend($Value)
	{
		$this->_legend = $Value;
	}

	/*
	Color property

	@return text
	@param text $Value
	 */
	public function getColor()
	{
		return $this->_color;
	}

	public function setColor($Value)
	{
		$this->_color = $Value;
	}

	/*
	MinimumDataValue property

	@return decimal
	 */
	public function getMinimumDataValue()
	{
		return $this->_minimumDataValue;
	}

	/*
	MaximumDataValue property

	@return decimal
	 */
	public function getMaximumDataValue()
	{
		return $this->_maximumDataValue;
	}

	public function getDataCount()
	{
		return count($this->_data);
	}

	public function getFormattedLabels()
	{
		$labels = Array();

		foreach ($this->_labels as $tempLabel)
		{
			$labels[] = urlencode($tempLabel);
		}

		$returnValue = implode("|", $labels);

		return $returnValue;
	}

	public function AddData($Data, $Label = null)
	{
		if (is_array($Data) || $Data instanceof DIarray)
		{
			$this->_data->Clear();
			$this->_labels->Clear();

			foreach($Data as $tempData)
			{
				$this->_data[] = $tempData;

				$this->DoMinMaxChecks($tempData);
			}

			if (is_array($Label) || $Label instanceof DIarray)
			{
				foreach($Label as $tempLabel)
				{
					$this->_labels[] = $tempLabel;
				}
			}
		}
		else
		{
			//Single Value
			if (is_numeric($Data))
			{
				$this->_data[] = $Data;

				$this->DoMinMaxChecks($Data);
			}

			if (is_set($Label))
			{
				$this->_labels[] = $Label;
			}
		}
	}

	protected function DoMinMaxChecks($Value)
	{
		if (is_set($this->_minimumDataValue) == false || $Value < $this->_minimumDataValue)
		{
			$this->_minimumDataValue = $Value;
		}

		if (is_set($this->_maximumDataValue) == false || $Value > $this->_maximumDataValue)
		{
			$this->_maximumDataValue = $Value;
		}

	}

	public function EncodeData($MinimumValue, $MaximumValue, $Method)
	{

		if ($Method == ChartBase::SIMPLE_DATA_ENCODING)
		{
			$returnValue = $this->SimpleEncodeData($MinimumValue, $MaximumValue);
		}
		else
		{
			$returnValue = $this->ExtendedEncodeData($MinimumValue, $MaximumValue);
		}

		return $returnValue;
	}

	protected function SimpleEncodeData($MinimumValue, $MaximumValue)
	{
		$delta = $MaximumValue - $MinimumValue;

		foreach ($this->_data as $tempValue)
		{
			$characterIndex = floor(($tempValue - $MinimumValue) * (61 / $delta));

			$returnValue .= $this->Chart->SimpleCodeTable[$characterIndex];
		}

		return $returnValue;
	}

    protected function ExtendedEncodeData($MinimumValue, $MaximumValue)
	{
		$delta = $MaximumValue - $MinimumValue;

		foreach ($this->_data as $tempValue)
		{
			$characterIndex = floor(($tempValue - $MinimumValue) * (4095 / $delta));

			$returnValue .= $this->Chart->ExtendedCodeTable[$characterIndex];

		}

		return $returnValue;

	}

}
?>