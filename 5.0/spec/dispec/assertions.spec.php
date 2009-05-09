<?php

include_once('dependencies.php');

class AssertionsSpec extends DISpecSuite
{
	public function ItShouldAssertTo()
	{
		$assert = new TestsAssertion(5, "ItShouldntMatter");

		return $this->Expects($assert->ToBeEqualTo(5))->ToBeEqualTo(true);
	}

	public function ItShouldAssertToNot()
	{
		$assert = new TestsAssertion(5, "ItShouldntMatter");

		return $this->Expects($assert->ToNotBeEqualTo(4))->ToBeEqualTo(true);
	}

	public function ItShouldAssertEquals()
	{
		$assert = new TestsAssertion(2, "ItShouldntMatter");

		return $this->Expects($assert->ToBeEqualTo(2))->ToBeEqualTo(true);
	}

	public function ItShouldAssertTrue()
	{
		$assert = new TestsAssertion(true, "ItShouldntMatter");

		return $this->Expects($assert->ToBeTrue())->ToBeEqualTo(true);
	}
}

