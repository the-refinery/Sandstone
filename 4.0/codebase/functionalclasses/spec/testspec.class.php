<?php

// Tests are any PUBLIC functions which are only present in the inherited classes definition
// Helpers methods are any PROTECTED functions not on the TestSpec class, and are ignored by this class

class TestSpec extends Component
{
	protected $_tests = array();
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
	
	/*** INTERNALS ***/
	
	public function Run()
	{
		$reflector = new ReflectionClass(get_class($this));
		$this->DetermineTests($reflector->getMethods());
		
		$this->Before();
		foreach ($this->_tests as $test => $value)
		{
			$this->BeforeEach();
			
			$this->_currentTest = $test;
			$this->$test();
			
			$this->AfterEach();
		}
		$this->After();		
	}
	
	protected function DetermineTests($ReflectorMethods)
	{
		// Get a list of methods on the TestSpec base, so we can determine
		// which methods the user entered as tests.
		$baseReflector = new ReflectionClass('TestSpec');
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
				$this->_tests[$tempMethod->name] = null;
			}
		}
	}
			
	/*** ASSERTS ***/
	
	public function RecordTestResult($TestResult)
	{
		$this->_tests[$this->_currentTest] = $TestResult;
	}
	
	public function AssertTrue($Boolean)
	{
		if ($Boolean)
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("Expected true, but was not.");
		}
	}

	public function AssertFalse($Boolean)
	{
		if ($Boolean == false)
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("Expected false, but was not.");
		}
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
	
	public function AssertEqual($ActualValue, $ExpectedValue)
	{
		if ($ActualValue == $ExpectedValue)
		{
			$this->RecordTestResult(true);
		}
		else
		{
			$this->RecordTestResult("{$ActualValue} was expected to be {$ExpectedValue}");
		}
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