<?php

Namespace::Using("Sandstone.Utilities.String");

class SpecAssertionBase extends Module
{
	protected $_testName;
	protected $_actualValue;
	protected $_testResult;
	protected $_message;
	protected $_file;
	protected $_line;
	
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
	
	public function getFile()
	{
		return $this->_file;
	}
	
	public function getLine()
	{
		return $this->_line;
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
		
		$this->_file = $callStack[1]['file'];
		$this->_line = $callStack[1]['line'];
		
		// Determine wether this is a ShouldBe or ShouldNotBe
		if (stristr($Method,'shouldnot'))
		{
			$assertion = substr($Method, 9);
			$this->_testResult = ($this->Assert($assertion, $parameter) == false);
		}
		elseif (stristr($Method,'should'))
		{
			$assertion = substr($Method, 6);
			$this->_testResult = $this->Assert($assertion, $parameter);
		}
		
		// If failed, create a message
		if ($this->_testResult == false)
		{
			$methodDescription = StringFunc::CamelCaseToSentance($Method);
			
			if (is_set($this->_message) == false)
			{
				$this->_message = "(" . gettype($this->_actualValue) . ") {$this->_actualValue} {$methodDescription} (" . gettype($parameter) . ") {$parameter}";
			}
		}
		
		$testSpec->AddTestResult($this);
	}
	
	public function Assert($Method, $Parameter)
	{
		if (method_exists($this, $Method))
		{
			$returnValue = $this->$Method($Parameter);
		}
		else
		{
			Throw New UnknownAssertException("Unknown Assert: $Method");
		}
		
		return $returnValue;
	}
	
	/*** ASSERTS ***/
	
	public function BeTrue()
	{
		return $this->_actualValue === true;
	}

	public function BeNull()
	{
		return is_null($this->_actualValue);
	}
	
	public function BeEqualTo($ExpectedValue)
	{
		return $this->_actualValue === $ExpectedValue;
	}
	
	public function BeSimilarTo($ExpectedValue)
	{
		return $this->_actualValue == $ExpectedValue;		
	}
	
	public function BeGreaterThan($CompareValue)
	{
		return $this->_actualValue > $CompareValue;
	}
	
	public function BeGreaterThanOrEqualTo($CompareValue)
	{
		return $this->_actualValue >= $CompareValue;
	}
	
	public function BeLessThan($CompareValue)
	{
		return $this->_actualValue < $CompareValue;
	}
	
	public function BeLessThanOrEqualTo($CompareValue)
	{
		return $this->_actualValue <= $CompareValue;
	}
	
	public function HaveKey($ExpectedKey)
	{
		return array_key_exists($ExpectedKey, $this->_actualValue);
	}
	
	public function Contain($ExpectedValue)
	{
		return in_array($ExpectedValue, $this->_actualValue, true);
	}
	
	public function BeEmpty()
	{
		return count($this->_actualValue) === 0;
	}
		
	public function MatchRegex($Pattern)
	{
		$test = preg_match($Pattern, $this->_actualValue);
		
		return $test >= 1;
	}

	public function BeInstanceOf($ObjectType)
	{
		return $this->_actualValue instanceof $ObjectType;
	}

	public function BeOfType($Type)
	{
		return gettype($this->_actualValue) === $Type;
	}
	
	public function BeAnArray($Type)
	{
		return is_array($this->_actualValue);
	}
	
	public function BeLoaded()
	{
		if ($this->_actualValue instanceof Module)
		{
			$returnValue = $this->_actualValue->IsLoaded;
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;
	}
}

?>
