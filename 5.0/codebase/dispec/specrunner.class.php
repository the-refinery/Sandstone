<?php

class SpecRunner
{
	public $SpecSuites = array();

	protected $_alerts = array();

	protected $_passCount = 0;
	protected $_failCount = 0;
	protected $_pendingCount = 0;

	public function AddSpecSuite($SpecSuiteName)
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

	protected function RunSuite($Suite)
	{
		$suiteResults = $Suite->Run();

		foreach($suiteResults as $specName => $specResult)
		{
			$specSuiteName = get_class($Suite);

			$this->OutputSpecResult($specSuiteName, $specName, $specResult);
			$this->AnalyzeResult($specSuiteName, $specName, $specResult);
		}	
	}

	protected function AnalyzeResult($SpecSuiteName, $SpecName, $SpecResult)
	{
		if ($SpecResult === true)
		{
			$this->_passCount++;
		}
		elseif ($SpecResult === false)
		{
			$this->_failCount++;
			$this->_alerts[] = "{$SpecSuiteName}: {$SpecName}()";
		}
		else
		{
			$this->_pendingCount++;
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
