<?php

class SpecMock extends Mock
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


