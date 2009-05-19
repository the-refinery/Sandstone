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
		$this->BeforeAll();

		foreach ($this->FindSpecs() as $tempSpec)
		{
			$this->BeforeEach();
			$returnValue[$tempSpec] = $this->$tempSpec();
			$this->AfterEach();
		}

		$this->AfterAll();
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

	public function BeforeAll() {}
	public function BeforeEach() {}
	public function AfterEach() {}
	public function AfterAll() {}
}
