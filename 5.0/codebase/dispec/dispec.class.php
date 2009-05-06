<?php

class DISpec
{
	public function Expects($ExpectedValue)
	{
		return new TestsAssertion($ExpectedValue);
	}

	public function IsPending()
	{
		echo "PENDING!";
	}

	public function Run()
	{
		$reflector = new ReflectionClass($this);
		$tempMethods = $reflector->getMethods();

		foreach ($tempMethods as $tempMethod)
		{
			$this->RunTest($tempMethod->name);
		}
	}

	protected function RunTest($MethodName)
	{
		if ($this->DetermineIfMethodIsASpec($MethodName))
		{
			echo $MethodName . ": ";
			$this->$MethodName();
		}
	}

	protected function DetermineIfMethodIsASpec($MethodName)
	{
		return strpos($MethodName, 'It') === 0;
	}
}
