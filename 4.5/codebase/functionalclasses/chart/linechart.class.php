<?php

class LineChart extends AxisChartBase
{
   	protected function SetupURLqueryParameters()
	{
		parent::SetupURLqueryParameters();

		$this->_urlQueryParameters[] = "cht=lc";
	}
}

?>