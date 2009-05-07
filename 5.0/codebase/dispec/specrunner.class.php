<?php

class SpecRunner
{
	public $SpecSuites = array();

	public function AddSpecSuite($SpecSuiteName)
	{
		$tempSpecSuite = new $SpecSuiteName();

		$this->SpecSuites[] = $tempSpecSuite;
	}

	public function Run()
	{
		foreach ($this->SpecSuites as $tempSpecSuite)
		{
			$results = $tempSpecSuite->Run();

			$this->OutputSpecSuiteName(get_class($tempSpecSuite));

			foreach($results as $specName => $specResult)
			{
				$this->OutputSpecResult($specName, $specResult);
			}	
		}
	}

	protected function TranslateResultToEnglish($Result)
	{
		if ($Result === true)
		{
			$returnValue = "PASS";
		}
		elseif ($Result === false)
		{
			$returnValue = "FAIL";
		}
		else
		{
			$returnValue = "PENDING";
		}

		return $returnValue;
	}
	
	protected function OutputSpecSuiteName($SpecSuiteName)
	{

	}

	protected function OutputSpecResult($SpecName, $SpecResult)
	{

	}
}
