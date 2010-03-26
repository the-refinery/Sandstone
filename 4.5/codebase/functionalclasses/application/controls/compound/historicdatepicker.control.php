<?php

class HistoricDatePickerControl extends BaseControl
{
	protected $_startYear;
	protected $_endYear;

	protected $_defaultValue;

	public function __construct()
	{
		parent::__construct();

		$this->_isTopLevelControl = true;
		$this->_isRawValuePosted = false;
	}

	public function getStartYear()
	{
		return $this->_startYear;
	}

	public function setStartYear($Value)
	{
		if ($Value > 0)
		{
			$this->_startYear = $Value;
		}
	}

	public function getEndYear()
	{
		return $this->_endYear;
	}

	public function setEndYear($Value)
	{
		if ($Value > 0)
		{
			$this->_endYear = $Value;
		}
	}


	public function getDefaultValue()
	{
		return $this->_defaultValue;
	}

	public function setDefaultValue($Value)
	{
		if ($Value instanceof Date)
		{
			$this->_defaultValue = $Value;
			
		}
	}

	protected function ParseEventParameters()
	{
		$month = $this->Month->Value;
		$day = $this->Day->Value;
		$year = $this->Year->Value;

		$this->_value = new Date("{$month}/{$day}/{$year}");
	}

	protected function SetupControls()
	{

		parent::SetupControls();

		$this->Month = new DropdownControl();

		$this->Day = new DropdownControl();

		$this->Year = new DropdownControl();

	}

	public function Render()
	{
		$this->LoadMonthDropdown();
		$this->LoadDayDropdown();
		$this->LoadYearDropdown();

		if (is_set($this->_defaultValue))
		{
			$this->Month->SelectElement(intval($this->_defaultValue->FormatDate("m")));
			$this->Day->SelectElement($this->_defaultValue->Day);
			$this->Year->SelectElement($this->_defaultValue->Year);
		}

		$returnValue = parent::Render();

		return $returnValue;
	}

	protected function LoadMonthDropdown()
	{
		$this->Month->AddElement(0," ");

		for ($i=1; $i<=12; $i++)
		{
			$this->Month->AddElement($i,$i);
		}
	}

	protected function LoadDayDropdown()
	{
		$this->Day->AddElement(0," ");

		for ($i=1; $i<=31; $i++)
		{
			$this->Day->AddElement($i,$i);
		}
	}

	protected function LoadYearDropdown()
	{
		$this->Year->AddElement(0," ");

		if (is_set($this->_startYear) == false)
		{
			$this->_startYear = 1971;
		}

		if (is_set($this->_endYear) == false || $this->_endYear < $this->_startYear)
		{
			$now = new Date();
			$this->_endYear = $now->Year;
		}

		for ($i=$this->_endYear;$i>=$this->_startYear;$i--)
		{
			$this->Year->AddElement($i,$i);
		}
	}

}

