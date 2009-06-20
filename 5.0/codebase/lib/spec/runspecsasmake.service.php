<?php

class RunSpecsAsMake extends RunSpecs
{
	protected function OutputSpecResult($SpecSuiteName, $SpecName, $SpecResult)
	{
		if ($SpecResult->TestResult === false)
		{
			echo $SpecResult->Filename 
				. ":" . $SpecResult->LineNumber 
				. ":" . $this->CreateEnglishSpecDescription($SpecResult->Spec->Name, $SpecResult->Name)
				. " > Expected: " . $SpecResult->ExplainValue($SpecResult->ActualValue)
				. " Actual: " . $SpecResult->ExplainValue($SpecResult->ExpectedValue);

			die(); // in Vim, we only want the first failing
		}
	}
}

