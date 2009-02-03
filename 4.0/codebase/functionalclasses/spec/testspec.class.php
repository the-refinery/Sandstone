<?php

// Tests are any PUBLIC functions which are only present in the inherited classes definition
// Helpers methods are any PROTECTED functions not on the TestSpec class, and are ignored by this class

class TestSpec extends Component
{
	protected $_tests = array();
	
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
				$this->_tests[] = $tempMethod->name;
			}
		}
		
		di_var_dump($this->_tests,true);
	}
			
	/*** ASSERTS ***/
	
	public function AssertEqual($Variable, $Value)
	{
		return $Variable == $Value;
	}
}

?>