<?php

class ConsoleSpecRunner extends SpecRunner
{
	protected function OutputBeginRun()
	{
		echo $this->BlankLine();
	}

	protected function OutputEndRun()
	{
		echo "\n";
		echo "\n[[ {$this->_passCount} PASSED, {$this->_failCount} FAILED, {$this->_pendingCount} PENDING ]]\n";
	}

	protected function OutputSpecResult($SpecSuiteName, $SpecName, $SpecResult)
	{
		if ($SpecResult->TestResult === true)
		{
			echo ".";
		}
		elseif ($SpecResult->TestResult === false)
		{
			echo $this->Red("F") . $this->BlankLine();
			echo $this->Red("{$SpecSuiteName}: {$SpecResult->Name}") . $this->BlankLine();
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
}
