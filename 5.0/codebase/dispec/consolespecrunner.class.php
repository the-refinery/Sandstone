<?php

class ConsoleSpecRunner extends SpecRunner
{
	protected function OutputBeginRun()
	{
		echo $this->BlankLine();
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
			echo $this->NewLine();
			foreach ($this->Failing as $failedResult)
			{
				echo $this->Red("{$failedResult->Name}") . $this->NewLine();
			}
			echo $this->NewLine();
		}
	}

	protected function OutputPendingTests()
	{
		if (count($this->Pending) > 0)
		{
			echo $this->NewLine();
			foreach ($this->Pending as $failedResult)
			{
				echo $this->Yellow("{$failedResult->Name}") . $this->NewLine();
			}
			echo $this->NewLine();
		}
	}

	protected function OutputSpecResultCounts()
	{
		$PassCount = count($this->Passing);
		$FailCount = count($this->Failing);
		$PendingCount = count($this->Pending);

		echo $this->NewLine();
		echo "[[ {$PassCount} PASSED, {$FailCount} FAILED, {$PendingCount} PENDING ]]\n";
		echo $this->NewLine();
	}

	protected function OutputSpecResult($SpecSuiteName, $SpecName, $SpecResult)
	{
		if ($SpecResult->TestResult === true)
		{
			echo ".";
		}
		elseif ($SpecResult->TestResult === false)
		{
			echo $this->Red("F");
		}
		else
		{
			echo $this->Yellow("S");
		}
	}

	protected function Red($Text)
	{
			return "\033[0;31m{$Text}\033[37m";
	}

	protected function Yellow($Text)
	{
			return "\033[0;33m{$Text}\033[37m";
	}
	
	protected function BlankLine()
	{
		return "\n\n";
	}

	protected function NewLine()
	{
		return "\n";
	}
}
