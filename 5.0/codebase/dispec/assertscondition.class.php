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
}
