<?php

class AssertCondition
{
	public $Name;
	public $Spec;

	public $Filename;
	public $LineNumber;
		
	public $ExpectedValue;
	public $ActualValue;
	public $TestResult;

	public function __construct($ExpectedValue = null, $Spec)
	{
		$callStack = debug_backtrace();
		$this->Name = $callStack[2]['function'];
		$this->Filename = $callStack[1]['file'];
		$this->LineNumber = $callStack[1]['line'];

		$this->ExpectedValue = $ExpectedValue;
		$this->Spec = $Spec;
	}

	public function BeEqualTo()
	{
		return $this->ExpectedValue == $this->ActualValue;
	}

	public function BeNull()
	{
		$this->ActualValue = null;
		return is_null($this->ExpectedValue);
	}

	public function BeGreaterThan()
	{
		return $this->ExpectedValue > $this->ActualValue;
	}

	public function BeLessThan()
	{
		return $this->ExpectedValue < $this->ActualValue;
	}

	public function BeGreaterThanOrEqualTo()
	{
		return $this->ExpectedValue >= $this->ActualValue;
	}

	public function BeLessThanOrEqualTo()
	{
		return $this->ExpectedValue <= $this->ActualValue;
	}

	public function BeTrue()
	{
		$this->ActualValue = true;
		return $this->ExpectedValue === $this->ActualValue;
	}

	public function Exist()
	{
		$this->ActualValue = true;
		return isset($this->ExpectedValue);
	}

	public function BeEmpty()
	{
		$this->ActualValue = array();
		return count($this->ExpectedValue) == 0;
	}

	public function Contain()
	{
		return in_array($this->ActualValue, $this->ExpectedValue);
	}

	public function HaveKey()
	{
		return array_key_exists($this->ActualValue, $this->ExpectedValue);
	}

	public function BeInstanceOf()
	{
		return $this->ExpectedValue instanceof $this->ActualValue;
	}

	public function MatchRegex()
	{
		return preg_match($this->ActualValue, $this->ExpectedValue);
	}
	
	public function __call($Method, $Args)
	{
		$this->ActualValue = $Args[0];

		if (stripos($Method, 'tonot') === 0)
		{
			$assertion = substr($Method, 5);
			$testCondition = $this->$assertion();

			$this->TestResult = $testCondition == false;
		}
		elseif (stripos($Method, 'to') === 0)
		{
			$assertion = substr($Method, 2);
			$testCondition = $this->$assertion();

			$this->TestResult = $testCondition == true;
		}
		else
		{
			throw new Exception('Unknown Assertion');
		}

		return $this;
	}

	public function ExplainValue($Value = null)
	{
		if (is_null($Value))
		{
			$englishType = "null";
			$englishValue = "";
		}
		elseif (is_bool($Value))
		{
			$englishType = "boolean";	
			$englishValue = $this->ExplainBooleanValue($Value);
		}
		elseif (is_numeric($Value))
		{
			$englishType = "numeric";
			$englishValue = $Value;
		}
		elseif (is_string($Value))
		{
			$englishType = "string";
			$englishValue = "\"{$Value}\"";
		}
		elseif (is_array($Value))
		{
			$englishType = "array";
			$englishValue = $this->ExplainArrayValue($Value);
		}
		elseif (is_object($Value))
		{
			$englishType = "object";
			$englishValue = get_class($Value);
		}
		
		return "({$englishType}) {$englishValue}";
	}

	protected function ExplainBooleanValue($Value = false)
	{
		return ($Value === true) ? "true" : "false";
	}

	protected function ExplainArrayValue($Array)
	{
		foreach ($Array as $key => $value)
		{
			$returnValue .= "[{$key}] => {$value}, ";
		}

		$returnValue = substr($returnValue, 0, -2);
		return "({$returnValue})";
	}
}
