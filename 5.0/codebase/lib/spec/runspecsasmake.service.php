<?php

class RunSpecsAsMake extends RunSpecs
{
	protected function OutputFailingTests()
	{
		if (count($this->Failing) > 0)
		{
			foreach ($this->Failing as $failedResult)
			{
			}
		}
	}

	protected function OutputSpecResult($SpecSuiteName, $SpecName, $SpecResult)
	{
		if ($SpecResult->TestResult === false)
		{
			echo $SpecResult->Filename . ":" . $SpecResult->LineNumber . ":" . $this->CreateEnglishSpecDescription($SpecResult->Spec->Name, $SpecResult->Name);
		}
	}
}

