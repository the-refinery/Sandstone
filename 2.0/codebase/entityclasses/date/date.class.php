<?php
/**
 * Date Class
 * 
 * @package Sandstone
 * @subpackage Date
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

class Date extends Module
{
	protected $_dateStamp;
	
	// Constants needed to select Date Format
	const DAY_LEADING_ZERO = 1;
	const DAY_NO_LEADING_ZERO = 2;
	
	const DAY_OF_WEEK_SHORT_TEXT = 1;
	const DAY_OF_WEEK_LONG_TEXT = 2;
	const DAY_OF_WEEK_NUMBER = 3;
	
	const MONTH_LONG_TEXT = 1;
	const MONTH_LEADING_ZERO = 2;
	const MONTH_SHORT_TEXT = 3;
	const MONTH_NO_LEADING_ZERO = 4;
	
	const YEAR_FOUR_DIGITS = 1;
	const YEAR_TWO_DIGITS = 2;
	
	const TIME_12_HOUR_NO_LEADING_ZERO = 1;
	const TIME_12_HOUR_LEADING_ZERO = 2;
	const TIME_24_HOUR_NO_LEADING_ZERO = 3;
	const TIME_24_HOUR_LEADING_ZERO = 4;

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
	
	/**
	 * Date property
	 * 
	 * @return date
	 * 
	 * @param date $Value
	 */
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
	
	/**
	 * UnixTimestamp property
	 * 
	 * @return date
	 */
	public function getUnixTimestamp()
	{
		return $this->_dateStamp;
	}
	
	/**
	 * MySQLtimestamp property
	 * 
	 * @return 
	 */
	public function getMySQLtimestamp()
	{
		$returnValue = $this->getYear(self::YEAR_FOUR_DIGITS);
		$returnValue .= "-" . $this->getMonth(self::MONTH_LEADING_ZERO);
		$returnValue .= "-" . $this->getDay(self::DAY_LEADING_ZERO);
		$returnValue .= " " . $this->gettime(self::TIME_24_HOUR_LEADING_ZERO);
		$returnValue .= ":" . $this->getMinutes();
		$returnValue .= ":" . $this->getSeconds();
		
		return $returnValue;
	}
	
	/**
	 * Day property
	 * 
	 * @return date
	 */
	public function getDay($format = self::DAY_LEADING_ZERO)
	{
		switch ($format) 
		{
			case self::DAY_LEADING_ZERO:
				return $this->FormatDate('d');
				break;
			
			case self::DAY_NO_LEADING_ZERO:
				return $this->FormatDate('j');
				break;
		}
	}
	
	public function setDay($Day)
	{
		$this->DateStamp = "{$this->Month}/{$Day}/{$this->Year} {$this->Hours}:{$this->Minutes}:{$this->Seconds}";
	}
	
	/**
	 * DayOfWeek property
	 * 
	 * @return date
	 */
	public function getDayOfWeek($format = self::DAY_OF_WEEK_LONG_TEXT)
	{
		switch ($format) 
		{
			case self::DAY_OF_WEEK_SHORT_TEXT:
				return $this->FormatDate('D');
				break;
			
			case self::DAY_OF_WEEK_LONG_TEXT:
				return $this->FormatDate('l');
				break;
				
			case self::DAY_OF_WEEK_NUMBER:
				return $this->FormatDate('N');
				break;
		}
	}
	
	/**
	 * DayOfYear property
	 * 
	 * @return date
	 */
	public function getDayOfYear()
	{
		return $this->FormatDate('z');
	}
	
	/**
	 * WeekOfYear property
	 * 
	 * @return date
	 */
	public function getWeekOfYear()
	{
		return $this->FormatDate('W');
	}
	
	/**
	 * Month property
	 * 
	 * @return date
	 */
	public function getMonth($format = self::MONTH_LEADING_ZERO)
	{
		switch ($format) 
		{
			case self::MONTH_LONG_TEXT:
				return $this->FormatDate('F');
				break;
			
			case self::MONTH_LEADING_ZERO:
				return $this->FormatDate('m');
				break;
				
			case self::MONTH_SHORT_TEXT:
				return $this->FormatDate('M');
				break;
				
			case MONTH_NO_LEADING_ZERO:
				return $this->FormatDate('n');
				break;
		}
	}
	
	public function setMonth($Month)
	{
		$this->DateStamp = "{$Month}/{$this->Day}/{$this->Year} {$this->Hours}:{$this->Minutes}:{$this->Seconds}";
	}
	
	/**
	 * DaysInMonth property
	 * 
	 * @return date
	 */
	public function getDaysInMonth()
	{
		return $this->FormatDate('t');
	}
	
	/**
	 * IsLeapYear property
	 * 
	 * @return boolean
	 */
	public function getIsLeapYear()
	{
		return $this->FormatDate('L');
	}
	
	/**
	 * Year property
	 * 
	 * @return date
	 */
	public function getYear($format = self::YEAR_FOUR_DIGITS)
	{
		switch ($format) 
		{
			case self::YEAR_FOUR_DIGITS:
				return $this->FormatDate('Y');
				break;
			
			case self::YEAR_TWO_DIGITS:
				return $this->FormatDate('y');
				break;
		}
	}
	
	public function setYear($Year)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$Year} {$this->Hours}:{$this->Minutes}:{$this->Seconds}";
	}
	
	/**
	 * Time property
	 * 
	 * @return date
	 */
	public function getTime($format = self::TIME_12_HOUR_NO_LEADING_ZERO)
	{
		switch ($format) 
		{
			case self::TIME_12_HOUR_NO_LEADING_ZERO:
				return $this->FormatDate('g');
				break;
			
			case self::TIME_12_HOUR_LEADING_ZERO:
				return $this->FormatDate('h');
				break;
				
			case self::TIME_24_HOUR_NO_LEADING_ZERO:
				return $this->FormatDate('G');
				break;
				
			case self::TIME_24_HOUR_LEADING_ZERO:
				return $this->FormatDate('H');
				break;
		}
	}
	
	/**
	 * IsDST property
	 *
	 * shows if Daylight Savings Time
	 *
	 * @return boolean
	 */
	public function getIsDST()
	{
		return $this->FormatDate('I');
	}
	
	/**
	 * Minutes property
	 * 
	 * @return date
	 */
	public function getHours()
	{
		return $this->FormatDate('h');
	}
	
	public function setHours($Hours)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$this->Year} {$Hours}:{$this->Minutes}:{$this->Seconds}";
	}
	
	/**
	 * Minutes property
	 * 
	 * @return date
	 */
	public function getMinutes()
	{
		return $this->FormatDate('i');
	}
	
	public function setMinutes($Minutes)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$this->Year} {$this->Hours}:{$Minutes}:{$this->Seconds}";
	}
	
	/**
	 * Seconds property
	 *
	 * @return date
	 */
	public function getSeconds()
	{
		return $this->FormatDate('s');
	}
	
	public function setSeconds($Seconds)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$this->Year} {$this->Hours}:{$this->Minutes}:{$Seconds}";
	}
	
	public function setTime($Time)
	{
		$this->DateStamp = "{$this->Month}/{$this->Day}/{$this->Year} {$Time}";
	}

	/**
	 * FriendlyDate property
	 *
	 * @return string
	 */
	public function getFriendlyDate()
	{
		return $this->BuildFriendlyDate();
	}

	public function FormatDate($dateFormat)
	{
		if ($this->_dateStamp == false)
		{
			$returnValue = null;
		}
		else 
		{
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

	public function AddDays($NumberOfDays)
	{

		$newDateStamp = strtotime("+{$NumberOfDays} days", $this->_dateStamp);

		$returnValue = new Date($newDateStamp);

		return $returnValue;
	}

	public function SubtractDays($NumberOfDays)
	{
		$newDateStamp = strtotime("-{$NumberOfDays} days", $this->_dateStamp);

		$returnValue = new Date($newDateStamp);

		return $returnValue;
	}

	static public function MonthName($MonthNumber)
	{
		$tempDate = new Date("{$MonthNumber}/1/2007");
		
		return $tempDate->Month;
	}
}

?>