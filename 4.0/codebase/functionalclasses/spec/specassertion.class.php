<?php

class SpecAssertion extends Module
{
	protected $_testName;
	protected $_actualValue;
	protected $_testResult;
	protected $_message;
	
	public function __construct($TestName)
	{
		$this->_testName = $TestName;
	}

	public function getTestName()
	{
		return $this->_testName;
	}
	
	public function getFriendlyTestName()
	{
		preg_match_all('/[A-Z][^A-Z]*/', $this->_testName, $results);

		return implode(' ', $results[0]);
	}

	public function getTestResult()
	{
		return $this->_testResult;
	}
	
	public function getActualValue()
	{
		return $this->_actualValue;
	}
	
	public function setActualValue($Value)
	{
		$this->_actualValue = $Value;
	}
	
	public function getMessage()
	{
		return $this->_message;
	}
	/*** ASSERTS ***/
	
	public function TestCondition($Result, $Message)
	{
		if ($Result === true)
		{
			$this->_testResult = true;
			$this->_message = '';
		}
		else
		{
			$this->_testResult = false;
			$this->_message = $Message;
		}
	}
	
	public function AssertTrue()
	{
		$this->TestCondition($this->_actualValue, "Expected true, but was not.");
	}

	public function AssertFalse()
	{
		$this->TestCondition($this->_actualValue == false, "Expected false, but was not.");		
	}

	public function AssertNull($Variable)
	{
		if (is_null($Variable))
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("Expected null, but was not.");
		}
	}

	public function AssertNotNull($Variable)
	{
		if (is_null($Variable) == false)
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("Was null, but should not have been.");
		}
	}
	
	public function AssertEqual($ExpectedValue)
	{
		$this->TestCondition($this->_actualValue == $ExpectedValue, "{$this->_actualValue} was expected to be {$ExpectedValue}");		
	}

	public function AssertNotEqual($ActualValue, $ExpectedValue)
	{
		if ($ActualValue != $ExpectedValue)
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("{$ExpectedValue} and {$ActualValue} should not be equal");
		}
	}
	
	public function AssertContains($Needle, $Haystack)
	{
		if (in_array($Needle,$Haystack))
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("{$Needle} was not found in the array");
		}
	}

	public function AssertDoesNotContain($Needle, $Haystack)
	{
		if (in_array($Needle,$Haystack) == false)
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("{$Needle} exists in the array, but should not.");
		}
	}
	
	public function AssertRegularExpression($Subject, $Pattern)
	{
		if (preg_match($Pattern, $Subject))
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("{$Subject} did not regex match {$Pattern}.");
		}
	}

	public function AssertType($Subject, $Type)
	{
		if (gettype($Subject) == strtolower($Type))
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("{$Subject} was expected to be a {$Type}, but was a " . gettype($Subject) . ".");
		}
	}

	public function AssertNotType($Subject, $Type)
	{
		if (gettype($Subject) != strtolower($Type))
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("{$Subject} was a {$Type}, but should not have been.");
		}
	}
}

?>