<?php
/*
Date Picker Control Class File

@package Sandstone
@subpackage Application
*/

SandstoneNamespace::Using("Sandstone.Date");

class DatePickerControl extends BaseControl
{

	protected $_startYear;
	protected $_endYear;

	protected $_defaultValue;

    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_controlStyle->AddClass('datepicker_general');
		$this->_bodyStyle->AddClass('datepicker_body');

		// Default Start and End Years
		$currentDate = new Date();
		$this->_startYear = $currentDate->Year;
		$this->_endYear = $currentDate->Year + 10;

		//Since these won't ever change, we'll load up the
		//elements for month and day dropdowns.
    	$this->LoadMonthDropDown();
		$this->LoadDayDropDown();

		$this->_isRawValuePosted = false;

		$this->_template->FileName = "datepicker";
	}

	/*
	StartYear property

	@return int
	@param int $Value
	*/
	public function getStartYear()
	{
		return $this->_startYear;
	}

	public function setStartYear($Value)
	{
		$this->_startYear = $Value;

		$this->LoadYearDropdown();
	}

	/*
	EndYear property

	@return int
	@param int $Value
	*/
	public function getEndYear()
	{
		return $this->_endYear;
	}

	public function setEndYear($Value)
	{
		$this->_endYear = $Value;

		$this->LoadYearDropdown();
	}

	/*
	DefaultValue property

	@return date
	@param date $Value
	*/
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
		else
		{
			$this->_defaultValue = null;
		}

		//Setup the year dropdown
		$this->LoadYearDropdown();

		//Now set the current selected value for the controls
		if (is_set($this->_defaultValue))
		{
			$this->Month->Elements[$this->_defaultValue->FormatDate('n')]->IsDefaultSelected = true;
			$this->Day->Elements[$this->_defaultValue->FormatDate('j')]->IsDefaultSelected = true;
			$this->Year->Elements[$this->_defaultValue->FormatDate('Y')]->IsDefaultSelected = true;
		}
		else
		{
			$this->Month->Elements[0]->IsDefaultSelected = true;
			$this->Day->Elements[0]->IsDefaultSelected = true;
			$this->Year->Elements[0]->IsDefaultSelected = true;
		}

	}

	protected function ParseEventParameters()
	{

		if ($this->Month->Value > 0 && $this->Day->Value > 0 && $this->Year->Value > 0)
		{
			$this->_value = new Date("{$this->Month->Value}/{$this->Day->Value}/{$this->Year->Value}");
		}
		else
		{
			$this->_value = null;
		}

		//Setup the year dropdown
		$this->LoadYearDropdown();
	}

	protected function SetupControls()
	{
		parent::SetupControls();

		$this->Month = new DropDownControl();
		$this->Month->ControlStyle->AddClass("datepicker_monthitem");
		$this->Month->LabelText = "Month";

		$this->Day = new DropDownControl();
		$this->Day->ControlStyle->AddClass("datepicker_dayitem");
		$this->Day->LabelText = "Day";

   		$this->Year = new DropDownControl();
		$this->Year->ControlStyle->AddClass("datepicker_yearitem");
		$this->Year->LabelText = "Year";

	}

	protected function LoadMonthDropDown()
	{

		$this->Month->ClearElements();

		//Start with a blank one.
		$this->Month->AddElement(0, "");

   		for ($i = 1; $i <= 12; $i++)
		{
			$monthName = Date::MonthName($i);

			$this->Month->AddElement($i, $monthName);
		}
	}

	protected function LoadDayDropDown()
	{

		$this->Day->ClearElements();

        //Start with a blank one.
		$this->Day->AddElement(0, "");

	    for ($i = 1; $i <= 31; $i++)
		{
			$this->Day->AddElement($i, $i);
		}

	}

	protected function LoadYearDropdown()
	{

		//Make sure our start and end values are up to date.
		$this->SetupYearRange();

		//Clear any existing elements
		$this->Year->ClearElements();

        //Start with a blank one.
		$this->Year->AddElement(0, "");

		//And loop from start to end years
        for ($i = $this->_startYear; $i <= $this->_endYear; $i++)
		{
			$this->Year->AddElement($i, $i);
		}

	}

	protected function SetupYearRange()
	{
		if (is_set($this->_value))
		{
			$this->SetYearRangeFromDate($this->_value);
		}
		else if(is_set($this->_defaultValue))
		{
			$this->SetYearRangeFromDate($this->_defaultValue);
		}
	}

	protected function SetYearRangeFromDate($TargetDate)
	{
		if ($TargetDate->Year < $this->_startYear)
		{
			$this->_startYear = $TargetDate->Year;
		}

		if ($TargetDate->Year > $this->_endYear)
		{
			$this->_endYear = $TargetDate->Year;
		}
	}

}
?>