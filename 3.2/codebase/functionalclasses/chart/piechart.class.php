<?php

class PieChart extends GoogleChart
{
	protected $_is3D = false;
	protected $_labels = array();
	protected $_isUsingLabels = false;
	
	protected function BuildParameters()
	{
		$returnValue = array(
								'cht' => ($this->_is3D) ? 'p3' : 'p',
								'chl' => $this->Labels
							);
		
		return $returnValue;
	}
	
	public function AddData($data, $label = null)
	{
		// If using labels, ALL data must have a label
		if ($label)
		{
			$this->_isUsingLabels = true;
			$this->_labels[] = $label;
		}
		
		$this->_data[] = $data;
	}
	
	public function getLabels()
	{
		if ($this->_isUsingLabels)
		{
			$returnValue = implode("|", $this->_labels);
		}
		else
		{
			$returnValue = null;
		}
		
		return $returnValue; 
	}
	
	protected function getIs3D()
	{
		return $this->_is3D;
	}
	
	protected function setIs3D($Value)
	{
		$this->_is3D = true;
	}
}

?>