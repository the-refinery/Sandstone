<?php

class TimePickerControl extends BaseControl
{
	protected $_minuteIncrement;

	public function __construct()
	{
		parent::__construct();

		$this->_isTopLevelControl = true;
		$this->_isRawValuePosted = false;
	}

	public function getMinuteIncrement()
	{
		return $this->_minuteIncrement;
	}

	public function setMinuteIncrement($Value)
	{
		if ($Value > 0 && $Value < 60)
		{
			$this->_minuteIncrement = $Value;
			$this->LoadMinuteDropDown();
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
			$this->SetupDefaultFromDate($Value);
		}
		else
		{
			$this->SetupDefaultFromString($Value);
		}
	}

	protected function SetupDefaultFromDate($Value)
	{
		$this->Hour->SelectElement(intval($Value->Hour));
		$this->Minute->SelectElement(intval($Value->Minute));
		$this->AMPM->SelectElement($Value->FormatDate("A"));
	}

	protected function SetupDefaultFromString($Value)
	{
		$parts = explode(":", $Value);
		$minuteAMPM = explode(" ", $parts[1]);

		$this->Hour->SelectElement(intval($parts[0]));
		$this->Minute->SelectElement(intval($minuteAMPM[0]));
		$this->AMPM->SelectElement(strtoupper($minuteAMPM[1]));
	}

	protected function ParseEventParameters()
	{
		$hour = $this->Hour->Value;
		$minute = $this->Minute->Elements[$this->Minute->Value]->Label;
		$ampm = $this->AMPM->Value;

		$this->_value = "{$hour}:{$minute} {$ampm}";
	}

	protected function SetupControls()
	{

		parent::SetupControls();

		$this->Hour = new DropdownControl();
		$this->LoadHourDropdown();

		$this->Minute = new DropdownControl();
		$this->LoadMinuteDropdown();

		$this->AMPM = new DropdownControl();
		$this->AMPM->AddElement("AM","AM");
		$this->AMPM->AddElement("PM","PM");
	}

	protected function LoadHourDropdown()
	{
		for ($i=1; $i<=12; $i++)
		{
			$this->Hour->AddElement($i,$i);
		}
	}

	protected function LoadMinuteDropdown()
	{
		$this->Minute->ClearElements();

		if (is_set($this->_minuteIncrement) == false)
		{
			$this->_minuteIncrement = 1;
		}

		$i = 0;

		while ($i <= 59)
		{
			if ($i < 10)
			{
				$label = "0{$i}";
			}
			else
			{
				$label = $i;
			}

			$this->Minute->AddElement($i,$label);

			$i = $i + $this->_minuteIncrement;
		}
	}
}
