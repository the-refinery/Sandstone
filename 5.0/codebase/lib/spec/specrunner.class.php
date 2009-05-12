<?php

class SpecRunner
{
	public $SpecSuites = array();

	public $Passing = array();
	public $Failing = array();
	public $Pending = array();
	
	public function DescribeBehavior($SpecSuiteName)
	{
		$tempSpecSuite = new $SpecSuiteName();

		$this->SpecSuites[] = $tempSpecSuite;
	}

	public function Run()
	{
		$this->OutputBeginRun();

		foreach ($this->SpecSuites as $tempSpecSuite)
		{
			$this->RunSuite($tempSpecSuite);
		}

		$this->OutputEndRun();
	}

	public function CreateEnglishSpecDescription($DescribesBehavior, $SpecName)
	{
		$DescribesBehavior = substr($DescribesBehavior, 0, -4);
		$SpecName = substr($SpecName, 2);

		return FormatsString::CamelCaseToSentance($DescribesBehavior . $SpecName);
	}

	protected function RunSuite($Suite)
	{
		$suiteResults = $Suite->Run();

		foreach($suiteResults as $specName => $specResult)
		{
			$specSuiteName = get_class($Suite);

			$this->AnalyzeResult($specSuiteName, $specName, $specResult);
			$this->OutputSpecResult($specSuiteName, $specName, $specResult);
		}	
	}

	protected function AnalyzeResult($SpecSuiteName, $SpecName, $SpecResult)
	{
		if ($SpecResult->TestResult === true)
		{
			$this->Passing[] = $SpecResult;
		}
		elseif ($SpecResult->TestResult === false)
		{
			$this->Failing[] = $SpecResult;
		}
		else
		{
			$this->Pending[] = $SpecResult;
		}
	}

	protected function OutputSpecResult($SpecSuite, $SpecName, $SpecResult)
	{
	}

	protected function OutputBeginRun()
	{
	}

	protected function OutputEndRun()
	{
	}

	protected function OutputAlert($Alert)
	{
	}
}
