<?php

class AxisChartBase extends ChartBase
{

	protected $_isVerticalGridDrawn;
	protected $_isHorizontalGridDrawn;
	
	protected $_isLegendDrawn;

	protected $_xAxis;
	protected $_yAxis;

	protected $_rangeMarkers;
	
	public function __construct()
	{
		parent::__construct();

		$this->_xAxis = Array();
		$this->_yAxis = Array();
		
		$this->_rangeMarkers = Array();

		$this->_isLegendDrawn = true;
	}

	/*
	IsVerticalGridDrawn property

	@return boolean
	@param boolean $Value
	 */
	public function getIsVerticalGridDrawn()
	{
		return $this->_isVerticalGridDrawn;
	}

	public function setIsVerticalGridDrawn($Value)
	{
		$this->_isVerticalGridDrawn = $Value;
	}

	/*
	IsHorizontalGridDrawn property

	@return boolean
	@param boolean $Value
	 */
	public function getIsHorizontalGridDrawn()
	{
		return $this->_isHorizontalGridDrawn;
	}

	public function setIsHorizontalGridDrawn($Value)
	{
		$this->_isHorizontalGridDrawn = $Value;
	}
	
	/*
	IsLegendDrawn property

	@return boolean
	@param boolean $Value
	 */
	public function getIsLegendDrawn()
	{
		return $this->_isLegendDrawn;
	}

	public function setIsLegendDrawn($Value)
	{
		$this->_isLegendDrawn = $Value;
	}

	public function AddXaxis($Labels, $Color=null, $FontSize=null, $Alignment=ChartAxis::CENTER_LABEL_ALIGN)
	{
		$returnValue = false;

		if (is_array($Labels) || $Labels instanceof DIarray)
		{
			$newAxis = new ChartAxis($Labels, $Color, $FontSize, $Alignment);

			$this->_xAxis[] = $newAxis;

			$returnValue = true;
		}

		return $returnValue;
	}

	public function AddYaxis($Labels, $Color=null, $FontSize=null, $Alignment=ChartAxis::CENTER_LABEL_ALIGN)
	{
		$returnValue = false;

		if (is_array($Labels) || $Labels instanceof DIarray)
		{
			$newAxis = new ChartAxis($Labels, $Color, $FontSize, $Alignment);

			$this->_yAxis[] = $newAxis;

			$returnValue = true;
		}

		return $returnValue;
	}

	public function AddVerticalRangeMarker($Start, $End, $Color = "ff0000")
	{
		$this->AddRangeMarker(AxisRangeMarker::VERTICAL_DIRECTION, $Start, $End, $Color);
	}
	
	public function AddHorizontalRangeMarker($Start, $End, $Color = "ff0000")
	{
		$this->AddRangeMarker(AxisRangeMarker::HORIZONTAL_DIRECTION, $Start, $End, $Color);
	}

	protected function AddRangeMarker($Direction, $Start, $End, $Color)
	{
		$newRangeMarker = new AxisRangeMarker($Start, $End, $Direction, $Color);
		
		$this->_rangeMarkers[] = $newRangeMarker;
	}

    protected function SetupURLqueryParameters()
	{
		parent::SetupURLqueryParameters();

		$this->SetupSeriesLegendURLqueryParameter();
		$this->SetupSeriesColorURLqueryParameter();
		$this->SetupAxisURLqueryParameters();
		$this->SetupGridURLqueryParameter();
		$this->SetupRangeMarkerURLqueryParameter();

	}

	protected function SetupSeriesColorURLqueryParameter()
	{

		foreach ($this->_dataSeries as $tempDataSeries)
		{
			$colors[] = $tempDataSeries->Color;
		}

		$this->_urlQueryParameters[] = "chco=" . implode(",", $colors);

	}

	protected function SetupSeriesLegendURLqueryParameter()
	{
		if ($this->_isLegendDrawn)
		{
			foreach ($this->_dataSeries as $tempDataSeries)
			{
				$legends[] = urlencode($tempDataSeries->Legend);
			}

			$this->_urlQueryParameters[] = "chdl=" . implode("|", $legends);
		}
	}

	protected function SetupAxisURLqueryParameters()
	{
		//Build the Axis Data
		foreach($this->_xAxis as $tempAxis)
		{
			$enabledAxis[] = "x";
			$axisLabels[] = $tempAxis->FormattedLabels;
			$axisStyle[] = $tempAxis->FormattedStyle;
		}

		foreach($this->_yAxis as $tempAxis)
		{
			$enabledAxis[] = "y";
			$axisLabels[] = $tempAxis->FormattedLabels;
			$axisStyle[] = $tempAxis->FormattedStyle;
		}

		//Axis Drawn
		if (count($enabledAxis) > 0)
		{
			$this->_urlQueryParameters[] = "chxt=" . implode(",", $enabledAxis);
		}

		//Labels
		if (count($axisLabels) > 0)
		{
			$labelContent = Array();

			foreach($axisLabels as $tempIndex=>$tempLabels)
			{
				$labelContent[] = "{$tempIndex}:|{$tempLabels}";
			}

			$this->_urlQueryParameters[] = "chxl=" . implode("|", $labelContent);
		}

		//Style
		if (count($axisStyle) > 0)
		{
			$styleContent = Array();

			foreach($axisStyle as $tempIndex=>$tempStyle)
			{
				if (is_set($tempStyle))
				{
					$styleContent[] = "{$tempIndex},{$tempStyle}";
				}
			}

			$this->_urlQueryParameters[] = "chxs=" . implode("|", $styleContent);
		}

	}

	protected function SetupGridURLqueryParameter()
	{
		if ($this->_isVerticalGridDrawn)
		{
			if (is_set($this->_xAxis[0]))
			{
				//Grid size is based off the label count of the first X axis
				$labelCount = count($this->_xAxis[0]->Labels) - 1;

				$xGridStep = round(100 / $labelCount, 1);
			}
			else
			{
				//Default to 10%
				$xGridStep = 10;
			}
		}
		else
		{
			$xGridStep = 0;
		}


		if ($this->_isHorizontalGridDrawn)
		{
			if (is_set($this->_yAxis[0]))
			{
				//Grid size is based off the label count of the first Y axis
				$labelCount = count($this->_yAxis[0]->Labels) - 1;

				$yGridStep = round(100 / $labelCount, 1);
			}
			else
			{
				//Default to 10%
				$yGridStep = 10;
			}
		}
		else
		{
			$yGridStep = 0;
		}


		$this->_urlQueryParameters[] = "chg={$xGridStep},{$yGridStep}";

	}

	protected function SetupRangeMarkerURLqueryParameter()
	{

		foreach ($this->_rangeMarkers as $tempRangeMarker)
		{
			$markers[] = $tempRangeMarker->GenerateQueryParameterData();
		}

		if (count($markers) > 0)
		{
			$this->_urlQueryParameters[] = "chm=" . implode("|", $markers);	
		}
	}

}
?>
