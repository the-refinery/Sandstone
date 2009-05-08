<?php

class ConsoleSpecRunner extends SpecRunner
{
	protected function OutputBeginRun()
	{
		echo "\n";
	}

	protected function OutputEndRun()
	{
		echo "\n";

		foreach ($this->_alerts as $alert)
		{
			$this->OutputAlert($alert);
		}

		echo "\n[[ {$this->_passCount} PASSED, {$this->_failCount} FAILED, {$this->_pendingCount} PENDING ]]\n";
	}

	protected function OutputAlert($Alert)
	{
		echo "\n\033[0;31mALERT {$Alert}\033[37m\n";
	}

	protected function OutputSpecResult($SpecSuiteName, $SpecName, $SpecResult)
	{
		if ($SpecResult === true)
		{
			echo ".";
		}
		elseif ($SpecResult === false)
		{
			echo "\033[0;31mF\033[37m";
		}
		else
		{
			echo "\033[1;33mS\033[37m";
		}
	}
}
