<?php

class DescribeBehavior
{
	public $Name;

	public function __construct()
	{
		$this->Name = get_class($this);
	}

	public function Expects($ExpectedValue)
	{
		return new AssertCondition($ExpectedValue, $this);
	}

	public function Pending()
	{
		$returnValue = new AssertCondition(null, $this);
		$returnValue->TestResult = RunSpecs::PENDING;

		return $returnValue;
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
