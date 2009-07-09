<?php

// The naming convention of this spec is intentionally special
// because it is used for testing specs, but shouldn't
// be run during normal runs.
class FooTest extends DescribeBehavior
{
	public function ItIsAPendingSpec()
	{
		return $this->Pending();
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


