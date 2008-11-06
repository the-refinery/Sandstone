<?php
/*
Benchmark Class File

@package Sandstone
@subpackage Debug

This class measures the elapsed time of events.
*/
class Benchmark extends Debug
{
	/*
	A microtime() for the start of the process
	
	@var float microtime
	*/
	protected $_startTime;
	
	/*
	A microtime() for the end of the process
	
	@var float microtime
	*/
	protected $_endTime;
	
	/*
	Calculate the elapsed time of the benchmark
	
	@return float or false if process hasn't completed
	*/
	public function getElapsedTime()
	{
		if (is_set($this->_startTime) && is_set($this->_endTime))
		{
			$ReturnValue = $this->_endTime - $this->_startTime;
		}
		else 
		{
			$ReturnValue = false;
		}
		
		return $ReturnValue;
	}
	
	/*
	Start the logging process
	*/
	public function Start()
	{
		$this->_startTime = microtime();
	}
	
	/*
	Stop the logging process
	*/
	public function Stop()
	{
		$this->_endTime = microtime();
	}
}

?>