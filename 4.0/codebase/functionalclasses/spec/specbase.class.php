<?php

// Tests are any PUBLIC functions which are only present in the inherited classes definition
// Helpers methods are any PROTECTED functions not on the TestSpec class, and are ignored by this class

class SpecBase extends Module
{
	protected $_tests = array();
	protected $_testResults = array();
	
	protected $_currentTest;
	
	// Run before any tests
	public function Before()
	{	
	}
	
	// Run before each test
	public function BeforeEach()
	{
	}
	
	// Run after all tests
	public function After()
	{
	}
	
	// Run after each test
	public function AfterEach()
	{
	}
	
	public function getTestResults()
	{
		return $this->_testResults;
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
			
			$this->_currentTest = new SpecAssertion($test);
			$this->$test();
			$this->_testResults[] = $this->_currentTest;
			
			$this->AfterEach();
		}
		$this->After();		
	}
	
	public function Should($ActualValue)
	{
		$this->_currentTest->ActualValue = $ActualValue;
		
		return $this->_currentTest;
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

?>