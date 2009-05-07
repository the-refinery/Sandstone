<?php

class FooSpec extends DISpecSuite
{
	public function ItIsAPendingSpec()
	{
		// Pending
	}

	public function ItIsAPassingSpec()
	{
		return $this->Expects(true)->ToBeEqualTo(true);
	}

	public function ItIsAFailingSpec()
	{
		return $this->Expects(true)->ToBeEqualTo(false);
	}

	public function ThisIsNotASpec() {}
}


