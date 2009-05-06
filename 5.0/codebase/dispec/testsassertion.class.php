<?php

class TestsAssertion
{
	protected $_expectedValue;

	public function __construct($ExpectedValue)
	{
		$this->_expectedValue = $ExpectedValue;
	}

	protected function RespondToAssertion($Result)
	{
		if ($Result)
		{
			echo "PASSED!\n";
		}
		else
		{
			echo "FAILED!\n";
		}
	}

	public function ToBeEqualTo($ActualValue)
	{
		$this->RespondToAssertion($this->_expectedValue == $ActualValue);
	}
}
