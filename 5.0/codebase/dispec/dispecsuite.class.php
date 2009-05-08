<?php

class DISpecSuite
{
	public function Expects($ExpectedValue)
	{
		return new TestsAssertion($ExpectedValue);
	}

	public function Run()
	{
		$returnValue = array();

		foreach ($this->FindSpecs() as $tempSpec)
		{
			$returnValue[$tempSpec] = $this->$tempSpec();
		}

		return $returnValue;
	}

	public function FindSpecs()
	{
		$reflector = new ReflectionClass($this);
		$tempMethods = $reflector->getMethods();
		$returnValue = array();

		foreach ($tempMethods as $tempMethod)
		{
			if ($this->DetermineIfMethodIsASpec($tempMethod->name))
			{
				$returnValue[] = $tempMethod->name;
			}
		}
		
		return $returnValue;
	}

	protected function DetermineIfMethodIsASpec($MethodName)
	{
		return strpos($MethodName, 'It') === 0;
	}
}
