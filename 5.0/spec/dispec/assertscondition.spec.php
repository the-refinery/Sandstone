<?php

include_once('dependencies.php');

class AssertsConditionSpec extends DescribesBehavior
{
	public function ItShouldAssertTo()
	{
		$assert = new AssertsCondition(5, "ItShouldntMatter");
		$result = $assert->ToBeEqualTo(5)->TestResult;

		return $this->Expects($result)->ToBeTrue();
	}

	public function ItShouldAssertToNot()
	{
		$assert = new AssertsCondition(5, "ItShouldntMatter");
		$result = $assert->ToNotBeEqualTo(4)->TestResult;

		return $this->Expects($result)->ToBeTrue();
	}

	public function ItShouldAssertEquals()
	{
		$assert = new AssertsCondition(2, "ItShouldntMatter");
		$result = $assert->ToBeEqualTo(2)->TestResult;

		return $this->Expects($result)->ToBeTrue();
	}

	public function ItShouldAssertTrue()
	{
		$assert = new AssertsCondition(true, "ItShouldntMatter");

		return $this->Expects(true)->ToBeTrue();
	}

	public function ItShouldAssertExists()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter");

		return $this->Expects($assert->ToExist()->TestResult)->ToBeEqualTo(true);
	}
}

