<?php

class ConsoleSpecRunner extends SpecRunner
{
	protected function OutputBeginRun()
	{
		echo OutputToBash::BlankLine();
	}

	protected function OutputEndRun()
	{
		$this->OutputPendingTests();
		$this->OutputFailingTests();	
		$this->OutputSpecResultCounts();
	}

	protected function OutputFailingTests()
	{
		if (count($this->Failing) > 0)
		{
			foreach ($this->Failing as $failedResult)
			{
				echo OutputToBash::BlankLine();
				$englishDescription = $this->CreateEnglishSpecDescription($failedResult->Spec->Name, $failedResult->Name);
				echo OutputToBash::ColoredText("Red", $englishDescription . " Expected: ");
				echo OutputToBash::Text($failedResult->ExplainValue($failedResult->ActualValue));
				echo OutputToBash::ColoredText("Red", " Actual: ");
				echo OutputToBash::Text($failedResult->ExplainValue($failedResult->ExpectedValue));
			}
			echo OutputToBash::NewLine();
		}
	}

	protected function OutputPendingTests()
	{
		if (count($this->Pending) > 0)
		{
			echo OutputToBash::NewLine();
			foreach ($this->Pending as $pendingResult)
			{
				$englishDescription = $this->CreateEnglishSpecDescription($pendingResult->Spec->Name, $pendingResult->Name);
				echo OutputToBash::ColoredText("Yellow",$englishDescription);
				echo OutputToBash::NewLine();
			}
			echo OutputToBash::NewLine();
		}
	}

	protected function OutputSpecResultCounts()
	{
		$PassCount = count($this->Passing);
		$FailCount = count($this->Failing);
		$PendingCount = count($this->Pending);

		echo OutputToBash::NewLine();
		echo OutputToBash::Text("[[ {$PassCount} PASSED, {$FailCount} FAILED, {$PendingCount} PENDING ]]");
		echo OutputToBash::NewLine();
	}

	protected function OutputSpecResult($SpecSuiteName, $SpecName, $SpecResult)
	{
		if ($SpecResult->TestResult === true)
		{
			echo OutputToBash::Text(".");
		}
		elseif ($SpecResult->TestResult === false)
		{
			echo OutputToBash::ColoredText("Red","F");
		}
		else
		{
			echo OutputToBash::ColoredText("Yellow","S");
		}
	}
}
