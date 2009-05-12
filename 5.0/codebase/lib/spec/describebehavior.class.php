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
		$callStack = debug_backtrace();
		$specName = $callStack[1]['function'];
		
		return new AssertCondition($ExpectedValue, $specName, $this);
	}

	public function Pending()
	{
		$callStack = debug_backtrace();
		$specName = $callStack[1]['function'];
		
		return new AssertCondition(null, $specName, $this);
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
