<?php

class SparkLineChart extends ChartBase
{

	public function AddDataSeries($Data, $Labels=null, $Legend=null, $Color=null)
	{
		$returnValue = false;

		//Only 1 data series allowed
		if (count($this->_dataSeries) == 0)
		{
			$returnValue = parent::AddDataSeries($Data, $Labels, $Legend, $Color);
		}

		return $returnValue;
	}

   	protected function SetupURLqueryParameters()
	{
		parent::SetupURLqueryParameters();

		$this->_urlQueryParameters[] = "cht=ls";

		if (is_set($this->_dataSeries[0]->Color))
		{
			$this->_urlQueryParameters[] = "chco={$this->_dataSeries[0]->Color}";
		}

		$this->_urlQueryParameters[] = "chls=2";

		if (strlen($this->_dataSeries[0]->Color) == 6)
		{
			$fillColor = $this->_dataSeries[0]->Color . "66";
		}
		else
		{
			$fillColor = $this->_dataSeries[0]->Color;
		}

		$this->_urlQueryParameters[] = "chm=B,{$fillColor},0,0,0";
	}
}

?>