<?php

class TestsAssertion
{
	public $ExpectedValue;

	public function __construct($ExpectedValue)
	{
		$this->ExpectedValue = $ExpectedValue;
	}

	public function BeEqualTo($ActualValue)
	{
		return $this->ExpectedValue == $ActualValue;
	}

	public function BeTrue()
	{
		return $this->ExpectedValue === true;
	}
	
	public function __call($Method, $Args)
	{
		$parameter = $Args[0];

		if (stripos($Method, 'tonot') === 0)
		{
			$assertion = substr($Method, 5);
			$testCondition = $this->$assertion($parameter);

			$returnValue = $testCondition == false;
		}
		elseif (stripos($Method, 'to') === 0)
		{
			$assertion = substr($Method, 2);
			$testCondition = $this->$assertion($parameter);

			$returnValue = $testCondition == true;
		}
		
		return $returnValue;
	}
}
