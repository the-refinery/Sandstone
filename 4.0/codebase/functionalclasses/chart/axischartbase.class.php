<?php

class AxisChartBase extends ChartBase
{

	protected $_isVerticleGridDrawn;
	protected $_isHorizontalGridDrawn;

	protected $_xAxis;
	protected $_yAxis;

	protected $_seriesLabels;
	protected $_seriesColors;

	public function __construct()
	{
		parent::__construct();

		$this->_xAxis = Array();
		$this->_yAxis = Array();

		$this->_seriesLabels = Array();
		$this->_seriesColors = Array();

	}

	/*
	IsVerticleGridDrawn property

	@return boolean
	@param boolean $Value
	 */
	public function getIsVerticleGridDrawn()
	{
		return $this->_isVerticleGridDrawn;
	}

	public function setIsVerticleGridDrawn($Value)
	{
		$this->_isVerticleGridDrawn = $Value;
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

	public function AddDataSeries($Series, $Label=null, $Color=null)
	{

		$returnValue = parent::AddDataSeries($Series);

		if ($returnValue == true)
		{
			if (is_set($Label))
			{
				$this->_seriesLabels[] = urlencode($Label);
			}

			if (is_set($Color))
			{
				$this->_seriesColors[] = $Color;
			}
		}

		return $returnValue;

	}

    protected function SetupURLqueryParameters()
	{
		parent::SetupURLqueryParameters();

		//Series Labels & Colors
		if (count($this->_seriesLabels) > 0)
		{
			$this->_urlQueryParameters[] = "chdl=" . implode("|", $this->_seriesLabels);
		}

		if (count($this->_seriesColors) > 0)
		{
			$this->_urlQueryParameters[] = "chco=" . implode(",", $this->_seriesColors);
		}

		//Build the Axis DAta
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

		//Grid
		$this->_urlQueryParameters[] = $this->ComputeVerticleGridParameter();

	}

	protected function ComputeVerticleGridParameter()
	{
		if ($this->_isVerticleGridDrawn)
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


		$returnValue = "chg={$xGridStep},{$yGridStep}";

		return $returnValue;
	}

}
?>