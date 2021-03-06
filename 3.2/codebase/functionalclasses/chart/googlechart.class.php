<?php

SandstoneNamespace::Using("Sandstone.Utilities.Color");

class GoogleChart extends Module
{
	const BASEURL = 'http://chart.apis.google.com/chart?';

	protected $_type;
	protected $_title;

	protected $_data = array();

	protected $_width;
	protected $_height;
	protected $_labelsXY = false;

	protected function getTitle()
	{
		return $this->_title;
	}

	protected function setTitle( $Value )
	{
		$this->_title = $Value;
	}

	protected function getData()
	{
		return $this->_data;
	}

	protected function getDataValues()
	{

		$returnValue = $this->SimpleEncode($this->_data);

		return $returnValue;
	}

	protected function getWidth()
	{
		return $this->_width;
	}

	protected function setWidth($Value)
	{
		$this->_width = $Value;
	}

	protected function getHeight()
	{
		return $this->_height;
	}

	protected function setHeight($Value)
	{
		$this->_height = $Value;
	}

	protected function getLabelsXY()
	{
		if ( $this->_labelsXY == true )
		{
			$returnValue = "x,y";
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	protected function setLabelsXY( $Value )
	{
		$this->_labelsXY = $Value;
	}

	public function BuildChartURL()
	{

		$query = $this->SetupParameters();
		$parameters = DIarray::ImplodeAssoc("=","&", $query);

		$returnValue = self::BASEURL . $parameters;

		return $returnValue;
	}

	protected function SetupParameters()
	{
		$returnValue = array(
							'chtt'	=> $this->_title,							// Title
							'chd'	=> 's:' . $this->DataValues,				// Data
							'chs'	=> $this->_width . 'x' . $this->_height		// Size
						);

			$parameters = $this->BuildParameters();

			foreach ($parameters as $key => $value)
			{
				$returnValue[$key] = $value;
			}

		return $returnValue;
	}

	protected function BuildParameters()
	{
		// Override in other classes to add appropriate parameters
		return array();
	}

	public function AddData($data)
	{
		$this->_data[] = $data;
	}

	protected function SimpleEncode($Data)
	{
	        $table = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

			// Calculate min and max
			foreach ($Data as $series)
			{
				if (is_array($series))
				{
					foreach ($series as $value)
					{
						if (is_set($min) == false || $value < ($min + 1))
						{
							$min = $value - 1;
						}

						if (is_set($max) == false || $value > ($max - 1))
						{
							$max = $value + 1;
						}
					}
				}
				else
				{
					if (is_set($min) == false || $series < ($min + 1))
					{
						$min = $series - 1;
					}

					if (is_set($max) == false || $series > ($max - 1))
					{
						$max = $series + 1;
					}
				}
			}

	        $delta = $max - $min;

	        $size = strlen($table) - 1;

	        foreach ($Data as $series)
			{
				if (is_array($series))
				{
					if (strlen($returnValue) > 0)
					{
						$returnValue .= ",";
					}

					foreach ($series as $value)
					{
						$translationLocation = $size * ($value - $min) / $delta;
						$translationLocation = round($translationLocation);

						$returnValue .= $table[$translationLocation];
					}
				}
				else
				{
					$translationLocation = $size * ($series - $min) / $delta;
					$translationLocation = round($translationLocation);

					$returnValue .= $table[$translationLocation];
				}
	        }

	        return $returnValue;
	}

	protected function FormatColor($Color)
	{
		$returnValue = str_replace("#", '', $Color);
		$returnValue = strtolower($returnValue);

		return $returnValue;
	}

	public function OutputChart()
	{
		$url = $this->BuildChartURL();

		$fileContents = file_get_contents($url);

        Header("Content-Type: image/png");
		echo $fileContents;

	}

}

?>