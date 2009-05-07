<?php

class ConsoleSpecRunner extends SpecRunner
{
	protected function OutputSpecSuiteName($SpecSuiteName)
	{
		echo "\n" . $SpecSuiteName . "\n" . "========================================\n";
	}

	protected function OutputSpecResult($SpecName, $SpecResult)
	{
		$englishResult = $this->TranslateResultToEnglish($SpecResult);

		echo "{$englishResult} : {$SpecName}\n"; 
	}
}
