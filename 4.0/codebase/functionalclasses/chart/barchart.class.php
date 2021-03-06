<?php

class BarChart extends AxisChartBase
{
	const STACKED_TYPE = 0;
	const GROUPED_TYPE = 1;

	const VERTICAL_BAR = 0;
	const HORIZONTAL_BAR = 1;

	const DEFAULT_BAR_WIDTH = 23;
	const DEFAULT_BAR_SPACING = 4;
	const DEFAULT_GROUP_SPACING = 8;

	protected $_type;
	protected $_direction;

	protected $_dataAxis;
	protected $_scaleAxis;

	protected $_barWidth;
	protected $_barSpacing;
	protected $_groupSpacing;

	public function __construct()
	{
		parent::__construct();

		$this->_dataAxis = Array();
		$this->_scaleAxis = Array();

		$this->_type = BarChart::STACKED_TYPE;
		$this->_direction = BarChart::VERTICAL_BAR;
		
		//these are the Google defaults
		$this->_barWidth = self::DEFAULT_BAR_WIDTH;
		$this->_barSpacing = self::DEFAULT_BAR_SPACING;
		$this->_groupSpacing = self::DEFAULT_GROUP_SPACING;
	}

	/*
	Type property

	@return integer
	@param integer $Value
	 */
	public function getType()
	{
		return $this->_type;
	}

	public function setType($Value)
	{
		$this->_type = $Value;
	}

	/*
	Direction property

	@return integer
	@param integer $Value
	 */
	public function getDirection()
	{
		return $this->_direction;
	}

	public function setDirection($Value)
	{
		$this->_direction = $Value;
	}

	public function getBarWidth()
	{
		return $this->_barWidth;
	}
	
	public function setBarWidth($Value)
	{
		if ($Value > 0)
		{
			$this->_barWidth = $Value;
		}
		else
		{
			$this->_barWidth = self::DEFAULT_BAR_WIDTH;
		}
	}

	public function getBarSpacing()
	{
		return $this->_barSpacing;
	}
	
	public function setBarSpacing($Value)
	{
		if ($Value > 0)
		{
			$this->_barSpacing = $Value;
		}
		else
		{
			$this->_barSpacing = self::DEFAULT_BAR_SPACING;
		}
	}

	public function getGroupSpacing()
	{
		return $this->_groupSpacing;
	}
	
	public function setGroupSpacing($Value)
	{
		if ($Value > 0)
		{
			$this->_groupSpacing = $Value;
		}
		else
		{
			$this->_groupSpacing = self::DEFAULT_GROUP_SPACING;
		}
	}

	public function AddDataAxis($Labels, $Color=null, $FontSize=null, $Alignment=ChartAxis::CENTER_LABEL_ALIGN)
	{
		$returnValue = false;

		if (is_array($Labels) || $Labels instanceof DIarray)
		{
			$newAxis = new ChartAxis($Labels, $Color, $FontSize, $Alignment);

			$this->_dataAxis[] = $newAxis;

			$returnValue = true;
		}

		return $returnValue;
	}

	public function AddScaleAxis($Labels, $Color=null, $FontSize=null, $Alignment=ChartAxis::CENTER_LABEL_ALIGN)
	{
		$returnValue = false;

		if (is_array($Labels) || $Labels instanceof DIarray)
		{
			$newAxis = new ChartAxis($Labels, $Color, $FontSize, $Alignment);

			$this->_scaleAxis[] = $newAxis;

			$returnValue = true;
		}

		return $returnValue;
	}

   	protected function SetupURLqueryParameters()
	{

		//Orient the data & scale axis based on the bar direction.
		$this->OrientAxis();

		parent::SetupURLqueryParameters();

		$this->_urlQueryParameters[] = "cht=" . $this->BuildChartType();
		$this->_urlQueryParameters[] = $this->BuildSizeAndSpacing();
	}

	protected function OrientAxis()
	{
		if ($this->_direction == BarChart::VERTICAL_BAR)
		{
			//Data is X
			$this->_xAxis = $this->_dataAxis;

			//Scale is Y
			$this->_yAxis = $this->_scaleAxis;
		}
		else
		{
			//Data is Y
			$this->_yAxis = $this->_dataAxis;

			//Scale is X
			$this->_xAxis = $this->_scaleAxis;
		}
	}

	protected function BuildChartType()
	{
		$returnValue = "b";

		if ($this->_direction == BarChart::VERTICAL_BAR)
		{
			$returnValue .= "v";
		}
		else
		{
			$returnValue .= "h";
		}

		if ($this->_type == BarChart::STACKED_TYPE)
		{
			$returnValue .= "s";
		}
		else
		{
			$returnValue .= "g";
		}
		
		return $returnValue;
	}
	
	protected function BuildSizeAndSpacing()
	{
		
		$returnValue = "chbh={$this->_barWidth},{$this->_barSpacing},{$this->_groupSpacing}";
		
		return $returnValue;
	}

}
?>