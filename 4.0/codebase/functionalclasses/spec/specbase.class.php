<?php

// Tests are any PUBLIC functions which are only present in the inherited classes definition
// Helpers methods are any PROTECTED functions not on the TestSpec class, and are ignored by this class

class SpecBase extends Module
{
	protected $_tests = array();
	protected $_testResults = array();
	
	protected $_currentTest;
	
	protected $_passedTests = array();
	protected $_failedTests = array();
	
	public function Before() {}
	public function BeforeEach() {}
	public function After() {}
	public function AfterEach() {}
	
	public function getTestResults()
	{
		return $this->_testResults;
	}
	
	public function getPassedTests()
	{
		return $this->_passedTests;
	}
	
	public function getFailedTests()
	{
		return $this->_failedTests;
	}
	
	public function AddTestResult($Value)
	{
		if ($Value->TestResult == true)
		{
			$this->_passedTests[] = $Value;
		}
		else
		{
			$this->_failedTests[] = $Value;
		}
		
		$this->_testResults[] = $Value;
	}
		
	/*** INTERNALS ***/
	
	public function Run()
	{
		$reflector = new ReflectionClass(get_class($this));
		$this->DetermineTests($reflector->getMethods());
		
		$this->Before();
		foreach ($this->_tests as $test)
		{
			$this->BeforeEach();
			$this->$test();
			$this->AfterEach();
		}
		$this->After();		
	}
		
	protected function DetermineTests($ReflectorMethods)
	{
		// Get a list of methods on the TestSpec base, so we can determine
		// which methods the user entered as tests.
		$baseReflector = new ReflectionClass('SpecBase');
		$tempMethods = $baseReflector->getMethods();
		
		foreach ($tempMethods as $tempMethod)
		{
			$baseMethods[] = $tempMethod->name;
		}
		
		// Loop over the tests methods, and grab those which aren't in the base array
		foreach ($ReflectorMethods as $tempMethod)
		{
			if ($tempMethod->isPublic() && in_array($tempMethod->name, $baseMethods) == false)
			{
				$this->_tests[] = $tempMethod->name;
			}
		}		
	}

}

function Check($ActualValue)
{
	$testResults = new SpecAssertion($ActualValue);
	
	return $testResults;
}

?>