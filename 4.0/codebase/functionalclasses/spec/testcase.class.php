<?php

class TestCase extends Module
{
	protected $_testName;
	protected $_testResult;
	
	public function getTestName()
	{
		return $this->_testName;
	}
	
	public function getFriendlyTestName()
	{
		preg_match_all('/[A-Z][^A-Z]*/', $this->_testName, $results);

		return implode(' ', $results[0]);
	}
	
	public function setTestName($Value)
	{
		$this->_testName = $Value;
	}
	
	public function getTestResult()
	{
		return $this->_testResult;
	}

	public function setTestResult($Value)
	{
		$this->_testResult = $Value;
	}

	public function getIsPassed()
	{
		return $this->_testResult === true;
	}
}


?>