<?php

Namespace::Using("Sandstone.Utilities.Color");

class ChartBase extends Module
{
	const BASEURL = 'http://chart.apis.google.com/chart?';

	const EXTENDED_DATA_ENCODING = 0;
	const SIMPLE_DATA_ENCODING = 1;

	protected $_dataSeries;
	protected $_title;
	protected $_width;
	protected $_height;
	protected $_scaleMinimumValue;
	protected $_scaleMaximumValue;

	protected $_topMargin;
	protected $_bottomMargin;
	protected $_leftMargin;
	protected $_rightMargin;

	protected $_dataCount;
	protected $_minimumDataValue;
	protected $_maximumDataValue;

	protected $_extendedCodeTable;
	protected $_simpleCodeTable;

	protected $_urlQueryParameters = array();

	public function __construct()
	{
		$this->_dataSeries = new DIarray();

		//Setup the defaults
		$this->_scaleMinimumValue = 0;
		
		$this->_topMargin = 0;
		$this->_bottomMargin = 0;
		$this->_leftMargin = 0;
		$this->_rightMargin = 0;
	
		$this->SetupCodeTables();
	}

	/*
	DataSeries property

	@return DIarray
	 */
	public function getDataSeries()
	{
		return $this->_dataSeries;
	}

	/*
	Title property

	@return string
	@param string $Value
	 */
	public function getTitle()
	{
		return $this->_title;
	}

	public function setTitle($Value)
	{
		$this->_title = $Value;
	}

	/*
	Width property

	@return integer
	@param integer $Value
	 */
	public function getWidth()
	{
		return $this->_width;
	}

	public function setWidth($Value)
	{
		$this->_width = $Value;
	}

	/*
	Height property

	@return integer
	@param integer $Value
	 */
	public function getHeight()
	{
		return $this->_height;
	}

	public function setHeight($Value)
	{
		$this->_height = $Value;
	}

	/*
	ScaleMinimumValue property

	@return decimal
	@param decimal $Value
	 */
	public function getScaleMinimumValue()
	{
		return $this->_scaleMinimumValue;
	}

	public function setScaleMinimumValue($Value)
	{
		$this->_scaleMinimumValue = $Value;
	}

	public function getTopMargin()
	{
		return $this->_topMargin;
	}
	
	public function setTopMargin($Value)
	{
		if (is_numeric($Value) && $Value > 0)
		{
			$this->_topMargin = $Value;
		}
		else
		{
			$this->_topMargin = null;
		}
	}

	public function getBottomMargin()
	{
		return $this->_bottomMargin;
	}
	
	public function setBottomMargin($Value)
	{
		if (is_numeric($Value) && $Value > 0)
		{
			$this->_bottomMargin = $Value;
		}
		else
		{
			$this->_bottomMargin = null;
		}
	}

	public function getRightMargin()
	{
		return $this->_rightMargin;
	}
	
	public function setRightMargin($Value)
	{
		if (is_numeric($Value) && $Value > 0)
		{
			$this->_rightMargin = $Value;
		}
		else
		{
			$this->_rightMargin = null;
		}
	}

	public function getLeftMargin()
	{
		return $this->_leftMargin;
	}
	
	public function setLeftMargin($Value)
	{
		if (is_numeric($Value) && $Value > 0)
		{
			$this->_leftMargin = $Value;
		}
		else
		{
			$this->_leftMargin = null;
		}
	}

	/*
	ScaleMaximumValue property

	@return decimal
	@param decimal $Value
	 */
	public function getScaleMaximumValue()
	{
		return $this->_scaleMaximumValue;
	}

	public function setScaleMaximumValue($Value)
	{
		$this->_scaleMaximumValue = $Value;
	}

	/*
	ExtendedCodeTable property

	@return DIarray
	 */
	public function getExtendedCodeTable()
	{
		return $this->_extendedCodeTable;
	}

	/*
	SimpleCodeTable property

	@return DIarray
	 */
	public function getSimpleCodeTable()
	{
		return $this->_simpleCodeTable;
	}

	protected function SetupCodeTables()
	{
		//SimpleCodeTable
		$simpleCodeCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$this->_simpleCodeTable = new DIarray(str_split($simpleCodeCharacters));

		//ExtendedCodeTable
		$extendedCodeCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';
		$extendedCharacters = str_split($extendedCodeCharacters);

		$this->_extendedCodeTable = new DIarray();

		$i = 0;
		foreach($extendedCharacters as $firstCharacter)
		{

			foreach($extendedCharacters as $secondCharacter)
			{
				$this->_extendedCodeTable[$i] = $firstCharacter . $secondCharacter;
				$i++;
			}
		}
	}

	public function AddDataSeries($Data, $Labels=null, $Legend=null, $Color=null)
	{
		$returnValue = false;

		if (is_array($Data) || $Data instanceof DIarray)
		{
			$newSeries = new ChartDataSeries();
			$newSeries->AddData($Data, $Labels);
			$newSeries->Legend = $Legend;
			$newSeries->Color = $Color;

			$newSeries->Chart = $this;
			$this->_dataSeries[] = $newSeries;

			$this->_dataCount += $newSeries->DataCount;

			if (is_set($this->_minimumDataValue) == false || $newSeries->MinimumDataValue < $this->_minimumDataValue)
			{
				$this->_minimumDataValue = $newSeries->MinimumDataValue;
			}

			if (is_set($this->_maximumDataValue) == false || $newSeries->MaximumDataValue > $this->_maximumDataValue)
			{
				$this->_maximumDataValue = $newSeries->MaximumDataValue;
			}

			$returnValue = true;
		}

		return $returnValue;
	}

	protected function EncodeData()
	{
		$returnValue = "chd=";

		if ($this->_dataCount > 50)
		{
			$mode = ChartBase::SIMPLE_DATA_ENCODING;
			$returnValue .= "s:";
		}
		else
		{
			$mode = ChartBase::EXTENDED_DATA_ENCODING;
			$returnValue .= "e:";
		}

		if (is_set($this->_scaleMinimumValue))
		{
			$encodeMinValue = $this->_scaleMinimumValue;
		}
		else
		{
			$encodeMinValue = $this->_minimumDataValue;
		}

		if (is_set($this->_scaleMaximumValue))
		{
			$encodeMaxValue = $this->_scaleMaximumValue;
		}
		else
		{
			$encodeMaxValue = $this->_maximumDataValue;
		}

		foreach ($this->_dataSeries as $tempSeries)
		{
			$encodedData[] = $tempSeries->EncodeData($encodeMinValue, $encodeMaxValue, $mode);
		}

		$returnValue .= implode(",", $encodedData);

		return $returnValue;
	}

	protected function SetupURLqueryParameters()
	{
    if (strlen($this->_title) > 0)
    {
      $this->_urlQueryParameters[] = "chtt=" . urlencode($this->_title);
    }

		$this->_urlQueryParameters[] = "chs={$this->_width}x{$this->_height}";
		$this->_urlQueryParameters[] = "chma={$this->_leftMargin},{$this->_rightMargin},{$this->_topMargin},{$this->_bottomMargin}";

		$this->_urlQueryParameters[] = $this->EncodeData();
	}

  public function AddURLqueryParameter($key, $value)
  {
    $this->_urlQueryParameters[] = "{$key}={$value}";
  }

	public function BuildURL()
	{
		$this->SetupURLqueryParameters();

		$returnValue = ChartBase::BASEURL . implode("&", $this->_urlQueryParameters);

		return $returnValue;
	}

	public function OutputChart()
	{
		$url = $this->BuildURL();

		$fileContents = file_get_contents($url);

        Header("Content-Type: image/png");
		echo $fileContents;
	}

}
?>
