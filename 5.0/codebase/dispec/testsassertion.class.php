<?php

class TestsAssertion
{
	public $ExpectedValue;

	public function __construct($ExpectedValue)
	{
		$this->ExpectedValue = $ExpectedValue;
	}

	public function ToBeEqualTo($ActualValue)
	{
		return $this->ExpectedValue === $ActualValue;
	}

	public function ToBeInstanceOf($ActualValue)
	{
		return $this->ExpectedValue instanceof $ActualValue;
	}
}
