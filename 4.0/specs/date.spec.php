<?php

class DateSpec extends SpecBase
{
	protected $_date;

	public function BeforeEach()
	{
		$this->_date = new Date(); 
	}
	
	public function ItShouldReturnAUnixTimestamp()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->DateStamp)->ShouldBeEqualTo(422596800);
	}
	
	public function ItShouldReturnAMysqlTimestamp()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->MySQLTimeStamp)->ShouldBeEqualTo('1983-05-24 00:00:00');
	}
	
	public function ItShouldGetTheDay()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->Day)->ShouldBeEqualTo('24');
	}
	
	public function ItShouldSetTheDay()
	{
		$this->_date->DateStamp = "5/24/1983";
		$this->_date->Day = 20;
		
		Check($this->_date->Day)->ShouldBeEqualTo('20');
	}
	
	public function ItShouldGetTheDayOfTheWeek()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->DayOfWeek)->ShouldBeEqualTo('Tuesday');
	}
	
	public function ItShouldGetTheDayOfTheYear()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->DayOfYear)->ShouldBeEqualTo('143');		
	}
	
	public function ItShouldGetTheWeekOfTheYear()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->WeekOfYear)->ShouldBeEqualTo('21');
	}
	
	public function ItShouldGetTheMonth()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->Month)->ShouldBeEqualTo('05');
	}
	
	public function ItShouldSetTheMonth()
	{
		$this->_date->DateStamp = "5/24/1983 3:45";
		$this->_date->Month = 7;
		Check($this->_date->Month)->ShouldBeEqualTo('07');
	}
	
	public function ItShouldGetTheDaysInAMonth()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->DaysInMonth)->ShouldBeEqualTo('31');
	}
	
	public function ItShouldGetIsLeapYear()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->IsLeapYear)->ShouldBeEqualTo(false);
	}
	
	public function ItShouldGetTheYear()
	{
		$this->_date->DateStamp = "5/24/1983";

		Check($this->_date->Year)->ShouldBeEqualTo('1983');
	}
	
	public function ItShouldSetTheYear()
	{
		$this->_date->DateStamp = "5/24/1983";
		$this->_date->Year = "1990";
		Check($this->_date->Year)->ShouldBeEqualTo('1990');		
	}
	
	public function ItShouldGetTheTime()
	{
		$this->_date->DateStamp = "5/24/1983 3:45";
		
		Check($this->_date->Time)->ShouldBeEqualTo('03:45:00');
	}
	
	public function ItShouldGetIsDst()
	{
		$this->_date->DateStamp = "5/24/1983";
		
		Check($this->_date->IsDST)->ShouldBeEqualTo(true);		
	}
	
	public function ItShouldGetTheHour()
	{
		$this->_date->DateStamp = "5/24/1983 3:45";
		
		Check($this->_date->Hour)->ShouldBeEqualTo('03');
	}
	
	public function ItShouldGetTheMinute()
	{
		$this->_date->DateStamp = "5/24/1983 3:45";
		
		Check($this->_date->Minute)->ShouldBeEqualTo('45');		
	}
	
	public function ItShouldGetTheSecond()
	{
		$this->_date->DateStamp = "5/24/1983 3:45:15";
		
		Check($this->_date->Second)->ShouldBeEqualTo('15');
	}
}

?>
