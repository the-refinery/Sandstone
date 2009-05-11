<?php

class AssertsCondition
{
	public $Name;
	public $Spec;

	public $ExpectedValue;
	public $ActualValue;
	public $TestResult;

	public function __construct($ExpectedValue = null, $SpecName, $Spec)
	{
		$this->ExpectedValue = $ExpectedValue;
		$this->Name = $SpecName;
		$this->Spec = $Spec;
	}

	public function BeEqualTo()
	{
		return $this->ExpectedValue == $this->ActualValue;
	}

	public function BeTrue()
	{
		return $this->ExpectedValue === true;
	}

	public function Exist()
	{
		return isset($this->ExpectedValue);
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
