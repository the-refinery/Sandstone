<?php
/*
Date Class

@package Sandstone
@subpackage Date
*/

class Date extends Module
{
	protected $_dateStamp;

	public function __construct($dateStamp = null)
	{
		if (! is_set($dateStamp))
		{
			$this->setDateStamp(time());
		}
		else
		{
			$this->setDateStamp($dateStamp);
		}
	}

	public function __toString()
	{
		$returnValue = $this->MySQLtimestamp . " ({$this->_dateStamp})";

		return $returnValue;
	}

	public function __call($Name, $Parameters)
	{
		
		$testName = strtolower($Name);
		
		if (substr($testName, 0, 3) == "add")
		{
			$returnValue = $this->AddTime($Name, $Parameters);
		}
		elseif(substr($testName, 0, 8) == "subtract")
		{
			$returnValue = $this->SubtractTime($Name, $Parameters);
		}
		else
		{
			parent::__call($Name, $Parameters);
		}
		
		return $returnValue;
	}

	public function getDateStamp()
	{
		return $this->_dateStamp;
	}

	public function setDateStamp($value)
	{
		if (is_int($value))
		{
			$this->_dateStamp = $value;
		}
		else
		{
			$this->_dateStamp = strtotime($value);
		}
	}

	public function getUnixTimestamp()
	{
		return $this->_dateStamp;
	}

	public function getMySQLtimestamp()
	{
		
		$returnValue = $this->FormatDate("Y-m-d H:i:s");

		return $returnValue;
	}

	public function getDay()
	{
		return $this->FormatDate('d');
	}

	public function setDay($Day)
	{
		$this->DateStamp = "{$this->Month}/{$Day}/{$this->Year} {$this->Hour}:{$this->Minute}:{$this->Second}";
	}

	public function getDayOfWeek()
	{
		return $this->FormatDate('l');
	}

	public function getDayOfYear()
	{
		return $this->FormatDate('z');
	}

	public function getWeekOfYear()
	{
		return $this->FormatDate('W');
	}

	public function getMonth()
	{
		return $this->FormatDate('m');
	}

	public function setMonth($Month)
	{
		$this->DateStamp = "{$Month}/{$this->Day}/{$this->Year} {$this->Hour}:{$this->Minute}:{$this->Second}";
	}

	public function getDaysInMonth()
	{
		return $this->FormatDate('t');
	}

	public function getIsLeapYear()
	{
		return ($this->FormatDate('L')) ? true : false;
	}

	public function getYear()
	{
		return $this->FormatDate('Y');
	}

	public function setYear($Year)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$Year} {$this->Hour}:{$this->Minute}:{$this->Second}";
	}

	public function getTime()
	{
		return $this->FormatDate('h:i:s');
	}

	public function getIsDST()
	{
		return ($this->FormatDate('I')) ? true : false;
	}

	public function getHour()
	{
		return $this->FormatDate('h');
	}

	public function setHour($Hour)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$this->Year} {$Hour}:{$this->Minute}:{$this->Second}";
	}

	public function getMinute()
	{
		return $this->FormatDate('i');
	}

	public function setMinute($Minute)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$this->Year} {$this->Hour}:{$Minute}:{$this->Second}";
	}

	public function getSecond()
	{
		return $this->FormatDate('s');
	}

	public function setSecond($Second)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$this->Year} {$this->Hour}:{$this->Minute}:{$Second}";
	}

	public function setTime($Time)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$this->Year} {$Time}";
	}

	public function getFriendlyDate()
	{
		return $this->BuildFriendlyDate();
	}

	/*
	Test whether this date is in the past or future
	*/
	public function getIsInPast()
	{
		return $this->_dateStamp < time();
	}

	public function getIsInFuture()
	{
		return $this->_dateStamp > time();
	}

	public function getAge()
	{
		$ageInSeconds = time() - $this->_dateStamp;
		$secondsInAYear = 31556926;

		$age = floor($ageInSeconds / $secondsInAYear);

		return $age;
	}

	public function getMonthName()
	{
		return $this->FormatDate('F');
	}

	public function FormatDate($dateFormat = null)
	{
		if ($this->_dateStamp == false)
		{
			$returnValue = null;
		}
		else
		{
		    // Use the specified date format, if given, otherwise try to use the one specified in the Registry
		    if (is_set($dateFormat))
		    {
		        // Use the one specified
		    }
		    elseif (is_set(Application::Registry()->DateFormat))
		    {
		        $dateFormat = Application::Registry()->DateFormat;
		    }
		    else
		    {
		        $dateFormat = "m/d/Y";
		    }

			$returnValue = date($dateFormat, $this->_dateStamp);

		}

		return $returnValue;
	}

	public function DateDiff($CompareDate = null, $Split = 'd')
	{
		if ( $CompareDate == null || ! $CompareDate instanceof Date )
		{
			$CompareDate = new Date($CompareDate);
		}

	   	$difference = abs($this->_dateStamp - $CompareDate->DateStamp);

	   	$totalDays = intval($difference/(24*60*60));
		$totalSecs = $difference-($totalDays*24*60*60);

		$returnValue['h'] = $h = intval($totalSecs/(60*60));
		$returnValue['m'] = $m = intval(($totalSecs-($h*60*60))/60);
		$returnValue['s'] = $totalSecs-($h*60*60)-($m*60);

		// set up array as necessary
		switch($Split)
		{
			case 'yw': # split years-weeks-days
				$returnValue['y'] = $y = intval($totalDays/365);
				$returnValue['w'] = $w = intval(($totalDays-($y*365))/7);
				$returnValue['d'] = $totalDays-($y*365)-($w*7);
				break;

			case 'y': # split years-days
				$returnValue['y'] = $y = intval($totalDays/365);
				$returnValue['d'] = $totalDays-($y*365);
				break;

			case 'w': # split weeks-days
				$returnValue['w'] = $w = intval($totalDays/7);
				$returnValue['d'] = $totalDays-($w*7);
				break;

			case 'd': # don't split -- total days
				$returnValue['d'] = $totalDays;
				break;

			default:
				$returnValue = null;
				break;
		}

		// Determine future or past (same datestamps is always +)
		if ( $this->_dateStamp < $CompareDate->DateStamp )
		{
			$returnValue['modifier'] = '-';
		}
		else
		{
			$returnValue['modifier'] = '+';
		}

		return $returnValue;
	}

	protected function BuildFriendlyDate()
	{

		$diff = $this->DateDiff();

		if ($diff['y'] == 0)
		{
			if ($diff['w'] == 0)
			{
				if ($diff['d'] == 0)
				{
					if ($diff['h'] == 0)
					{
						if ($diff['m'] == 0)
						{
							//These are both less than a minute.
							//If seconds = 0 force to a past tense
							if ($diff['s'] == 0)
							{
								$diff['modifier'] = "-";
							}

							$value = 0;
							$increment = "";
						}
						else
						{
							$value = $diff['m'];
							$increment = "minute";
						}
					}
					else
					{
						$value = $diff['h'];
						$increment = "hour";
					}
				}
				else
				{
					$value = $diff['d'];
					$increment = "day";
				}
			}
			else
			{
				$value = $diff['w'];
				$increment = "week";
			}
		}
		else
		{
			$value = $diff['y'];
			$increment = "year";
		}

		//Adjust the increment if we have a value of more than 1
		if ($value > 1)
		{
			$increment .= "s";
		}

		//Now build the output string for a future or past date.
		if ($diff['modifier'] == "+")
		{
			//Future Date
			if ($value == 0)
			{
				$returnValue = "In less than 1 minute";
			}
			else
			{
				$returnValue = "In {$value} {$increment}";
			}
		}
		else
		{
			//Past Date
			if ($value == 0)
			{
				$returnValue = "Less than 1 minute ago";
			}
			else
			{
				$returnValue = "{$value} {$increment} ago";
			}

		}

		return $returnValue;

	}


	protected function AddTime($Name, $Parameters)
	{	
		$uom = strtolower(substr($Name, 3));
		
		if (substr($uom, -1, 1) != "s")
		{
			$uom .= "s";
			$value  = 1;
		}
		else
		{
			if (count($Parameters) == 1)
			{
				$value = $Parameters[0];
			}
		}
		
		if (is_set($value))
		{
			$returnValue = $this->DateMath("+", $value, $uom);	
		}
		
		return $returnValue;
	}

	protected function SubtractTime($Name, $Parameters)
	{
		$uom = strtolower(substr($Name, 8));
		
		if (substr($uom, -1, 1) != "s")
		{
			$uom .= "s";
			$value  = 1;
		}
		else
		{
			if (count($Parameters) == 1)
			{
				$value = $Parameters[0];
			}
		}
		
		if (is_set($value))
		{
			$returnValue = $this->DateMath("-", $value, $uom);	
		}
		
		return $returnValue;
		
	}

	protected function DateMath($Modifier, $Value, $UOM)
	{
		$newDateStamp = strtotime("{$Modifier}{$Value} {$UOM}", $this->_dateStamp);
		
		$returnValue = new Date($newDateStamp);

		return $returnValue;		
	}

	static public function MonthName($MonthNumber)
	{
		$tempDate = new Date("{$MonthNumber}/1/2007");

		return $tempDate->FormatDate('F');
	}

}

?>