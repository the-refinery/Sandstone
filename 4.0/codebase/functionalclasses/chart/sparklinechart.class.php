<?php

class SparkLineChart extends AxisChartBase
{
   	protected function SetupURLqueryParameters()
	{
		parent::SetupURLqueryParameters();

		$this->_urlQueryParameters[] = "cht=ls";
	}
}

?>