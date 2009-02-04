<?php

Namespace::Using("Sandstone.Utilities.String");

class SpecAssertion extends Module
{
	protected $_testName;
	protected $_actualValue;
	protected $_testResult;
	protected $_message;
	
	public function __construct($ActualValue)
	{
		$this->_actualValue = $ActualValue;
	}
	
	public function getFriendlyTestName()
	{
		return StringFunc::CamelCaseToSentance($this->_testName);
	}
	
	public function getTestResult()
	{
		return $this->_testResult;
	}
	
	public function getMessage()
	{
		return $this->_message;
	}

	public function __call($Method, $Arguments)
	{
		$callStack = debug_backtrace();
		//The call context we are interested in will be index 2 in the array.
		// 0 = this function
		// 1 = internal function call to this test
		// 2 = context in question
		$this->_testName = $callStack[2]['function'];
		$testSpec = $callStack[2]['object'];
		$parameter = $Arguments[0];
		
		// Determine wether this is a ShouldBe or ShouldNotBe
		if (stristr($Method,'shouldbe'))
		{
			$assertion = substr($Method, 8);
			$this->_testResult = $this->$assertion($parameter);			
		}
		elseif (stristr($Method,'shouldnotbe'))
		{
			$assertion = substr($Method, 11);
			$this->_testResult = ($this->$assertion($parameter) == false);
		}
		
		// If failed, create a message
		if ($this->_testResult == false)
		{
			$methodDescription = StringFunc::CamelCaseToSentance($Method);
			
			if (is_set($this->_message) == false)
			{
				$this->_message = "{$this->_actualValue} {$methodDescription} {$parameter}";
			}
		}
		
		$testSpec->AddTestResult($this);
	}
	
	/*** ASSERTS ***/
	
	public function True()
	{
		return $this->_actualValue === true;
	}
	
	public function EqualTo($ExpectedValue)
	{
		return $this->_actualValue === $ExpectedValue;
	}
	
}

?>