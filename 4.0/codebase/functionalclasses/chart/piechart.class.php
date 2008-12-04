<?php

class PieChart extends ChartBase
{
	protected $_is3D;
	protected $_colors;

	public function __construct()
	{
		parent::__construct();

		$this->_colors = Array();
	}

	protected function getIs3D()
	{
		return $this->_is3D;
	}

	protected function setIs3D($Value)
	{
		$this->_is3D = $Value;
	}

	public function AddColor($Color)
	{
		if (is_array($Color) || $Color instanceof DIarray)
		{
			$this->_colors = Array();

			foreach ($Color as $tempColor)
			{
				$this->_colors[] = $tempColor;
			}
		}
		else
		{
			$this->_colors[] = $Color;
		}
	}

	protected function SetupURLqueryParameters()
	{

		parent::SetupURLqueryParameters();

		if ($this->_is3D)
		{
			$this->_urlQueryParameters[] = "cht=p3";
		}
		else
		{
			$this->_urlQueryParameters[] = "cht=p";
		}

		if (count($this->_dataSeries[0]->Labels) > 0)
		{
			$this->_urlQueryParameters[] = "chl=" . $this->_dataSeries[0]->FormattedLabels;
		}

		if (count($this->_colors)  > 0)
		{
			$this->_urlQueryParameters[] = "chco=" . implode("|", $this->_colors);
		}
	}

}

?>