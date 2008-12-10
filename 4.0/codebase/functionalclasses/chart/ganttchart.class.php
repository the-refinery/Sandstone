<?php

class GanttChart extends AxisChartBase
{

	protected $_completeColor;
	protected $_onTimeColor;
	protected $_lateColor;
	protected $_futureColor;
	protected $_otherColor;

	protected $_otherDescription;

	protected $_tasks;
	protected $_totalUnits;

	protected $_targetUnits;
	protected $_targetLabel;
	protected $_targetColor;

	protected $_startDate;
	protected $_todayMarkerColor;

	public function __construct()
	{
		parent::__construct();

		$this->_completeColor = "000000";
		$this->_onTimeColor = "00ff00";
		$this->_lateColor = "ff0000";
		$this->_futureColor = "0000ff";
		$this->_otherColor = "ff6600";

		$this->_otherDescription = "Other";

		$this->_targetLabel = "Target";
		$this->_targetColor = "000000";

		$this->_todayMarkerColor = "99ccff";
	}

	/*
	CompleteColor property

	@return string
	@param string $Value
	 */
	public function getCompleteColor()
	{
		return $this->_completeColor;
	}

	public function setCompleteColor($Value)
	{
		$this->_completeColor = $Value;
	}

	/*
	OnTimeColor property

	@return string
	@param string $Value
	 */
	public function getOnTimeColor()
	{
		return $this->_onTimeColor;
	}

	public function setOnTimeColor($Value)
	{
		$this->_onTimeColor = $Value;
	}

	/*
	LateColor property

	@return string
	@param string $Value
	 */
	public function getLateColor()
	{
		return $this->_lateColor;
	}

	public function setLateColor($Value)
	{
		$this->_lateColor = $Value;
	}

	/*
	FutureColor property

	@return string
	@param string $Value
	 */
	public function getFutureColor()
	{
		return $this->_futureColor;
	}

	public function setFutureColor($Value)
	{
		$this->_futureColor = $Value;
	}

	/*
	OtherColor property

	@return string
	@param string $Value
	 */
	public function getOtherColor()
	{
		return $this->_otherColor;
	}

	public function setOtherColor($Value)
	{
		$this->_otherColor = $Value;
	}

	/*
	OtherDescription property

	@return string
	@param string $Value
	 */
	public function getOtherDescription()
	{
		return $this->_otherDescription;
	}

	public function setOtherDescription($Value)
	{
		$this->_otherDescription = $Value;
	}

	/*
	Tasks property

	@return array
	 */
	public function getTasks()
	{
		return $this->_tasks;
	}

	/*
	TargetUnits property

	@return integer
	@param integer $Value
	 */
	public function getTargetUnits()
	{
		return $this->_targetUnits;
	}

	public function setTargetUnits($Value)
	{
		$this->_targetUnits = $Value;
	}

	/*
	TargetLabel property

	@return string
	@param string $Value
	 */
	public function getTargetLabel()
	{
		return $this->_targetLabel;
	}

	public function setTargetLabel($Value)
	{
		$this->_targetLabel = $Value;
	}

	/*
	TargetColor property

	@return string
	@param string $Value
	 */
	public function getTargetColor()
	{
		return $this->_targetColor;
	}

	public function setTargetColor($Value)
	{
		$this->_targetColor = $Value;
	}

	/*
	StartDate property

	@return date
	@param date $Value
	 */
	public function getStartDate()
	{
		return $this->_startDate;
	}

	public function setStartDate($Value)
	{
		$this->_startDate = $Value;
	}

	/*
	TodayMarkerColor property

	@return string
	@param string $Value
	 */
	public function getTodayMarkerColor()
	{
		return $this->_todayMarkerColor;
	}

	public function setTodayMarkerColor($Value)
	{
		$this->_todayMarkerColor = $Value;
	}

	public function AddTask($Name, $Units, $Status)
	{
		$newTask = new GanttTask();
		$newTask->Name = $Name;
		$newTask->Units = $Units;
		$newTask->Status = $Status;

		$this->_tasks[] = $newTask;

		$this->_totalUnits += $Units;

	}

   	protected function SetupURLqueryParameters()
	{

		$this->BuildDataSeries();
		$this->BuildXaxis();
		$this->BuildYaxis();

		$this->IsVerticleGridDrawn = true;

		parent::SetupURLqueryParameters();

		$this->_urlQueryParameters[] = "cht=bhs";

		$this->BuildTodayMarker();
	}

	protected function BuildDataSeries()
	{

		$leadUnits = 0;

		foreach ($this->_tasks as $tempIndex=>$tempTask)
		{
			//Lead Time
			$leadTimeData[$tempIndex] = $leadUnits;

        	//Set zeros for each status
			$completeData[$tempIndex] = 0;
			$onTimeData[$tempIndex] = 0;
			$lateData[$tempIndex] = 0;
			$futureData[$tempIndex] = 0;
			$otherData[$tempIndex] = 0;

			//Now add the units for the specific status
			switch ($tempTask->Status)
			{
				case GanttTask::COMPLETE_STATUS:
					$completeData[$tempIndex] = $tempTask->Units;
					break;

				case GanttTask::ON_TIME_STATUS:
					$onTimeData[$tempIndex] = $tempTask->Units;
					break;

				case GanttTask::LATE_STATUS:
					$lateData[$tempIndex] = $tempTask->Units;
					break;

				case GanttTask::FUTURE_STATUS:
					$futureData[$tempIndex] = $tempTask->Units;
					break;

				case GanttTask::OTHER_STATUS:
					$otherData[$tempIndex] = $tempTask->Units;
					break;
			}

			$leadUnits += $tempTask->Units;
		}

		$this->AddDataSeries($leadTimeData, null, null, "ffffff00");
		$this->AddDataSeries($completeData, null, "Complete", $this->_completeColor);
		$this->AddDataSeries($onTimeData, null, "On Time", $this->_onTimeColor);
		$this->AddDataSeries($lateData, null, "Late", $this->_lateColor);
		$this->AddDataSeries($futureData, null, "Future", $this->_futureColor);
		$this->AddDataSeries($otherData, null, $this->_otherDescription, $this->_otherColor);

		if (is_set($this->_targetUnits) && $this->_targetUnits > $this->_totalUnits)
		{
			$this->ScaleMaximumValue = $this->_targetUnits;
		}
		else
		{
			$this->ScaleMaximumValue = $this->_totalUnits;
		}

	}

	protected function BuildXaxis()
	{
		$i = 0;

        if (is_set($this->_targetUnits) && $this->_targetUnits > $this->_totalUnits)
		{
			$totalLabels = $this->_targetUnits;
		}
		else
		{
			$totalLabels = $this->_totalUnits;
		}


		while ($i <= $totalLabels)
		{
			//If we have a start date, units are considered days
			//and we print the date rather than the increment.
			if (is_set($this->_startDate))
			{
            	$stepDate = $this->_startDate->AddDays($i);
            	$scalePoints[] = $stepDate->FormatDate("m/d");
			}
			else
			{
				$scalePoints[] = $i;
			}

			//Is this the target?
			if (is_set($this->_targetUnits))
			{
				if (round($this->_targetUnits, 0) == $i)
				{
					$targetLabels[] = $this->_targetLabel;
				}
				else
				{
					$targetLabels[] = " ";
				}
			}

			$i++;
		}

		$this->AddXaxis($scalePoints);
		$this->AddXaxis($targetLabels, $this->_targetColor, 12);

	}

	protected function BuildYaxis()
	{

		for ($i=count($this->_tasks)-1; $i >= 0; $i--)
		{
			$taskLabels[] = "{$this->_tasks[$i]->Name}";
		}

		$this->AddYaxis($taskLabels);
	}

	protected function BuildTodayMarker()
	{
		if (is_set($this->_startDate))
		{

	        if (is_set($this->_targetUnits) && $this->_targetUnits > $this->_totalUnits)
			{
				$totalIncrements = $this->_targetUnits;
			}
			else
			{
				$totalIncrements = $this->_totalUnits;
			}

			$singleDayIncrement = round(1 / $totalIncrements, 2);

			$diff = $this->_startDate->DateDiff();
			$daysSinceStart = $diff['d'];

			$todayMarkerStart = $singleDayIncrement * $daysSinceStart;
			$todayMarkerEnd = $todayMarkerStart + .01;

			if (strlen($this->_todayMarkerColor) == 6)
			{
				$color = $this->_todayMarkerColor . "44";
			}
			else
			{
				$color = $this->_todayMarkerColor;
			}

			$this->_urlQueryParameters[] = "chm=r,{$color},0,0,{$todayMarkerEnd}";
		}
	}
}
?>