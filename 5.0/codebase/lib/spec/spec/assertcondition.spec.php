<?php

include_once('dependencies.php');

class AssertConditionSpec extends DescribesBehavior
{
	public function ItShouldAssertTo()
	{
		$assert = new AssertsCondition(5, "ItShouldntMatter", $this);
		$result = $assert->ToBeEqualTo(5)->TestResult;

		return $this->Expects($result)->ToBeTrue();
	}

	public function ItShouldAssertToNot()
	{
		$assert = new AssertsCondition(5, "ItShouldntMatter", $this);
		$result = $assert->ToNotBeEqualTo(4)->TestResult;

		return $this->Expects($result)->ToBeTrue();
	}

	public function ItShouldAssertEquals()
	{
		$assert = new AssertsCondition(2, "ItShouldntMatter", $this);
		$result = $assert->ToBeEqualTo(2)->TestResult;

		return $this->Expects($result)->ToBeTrue();
	}

	public function ItShouldAssertTrue()
	{
		$assert = new AssertsCondition(true, "ItShouldntMatter", $this);

		return $this->Expects(true)->ToBeTrue();
	}

	public function ItShouldAssertExists()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter", $this);

		return $this->Expects($assert->ToExist()->TestResult)->ToBeEqualTo(true);
	}

	public function ItShouldAssertContains()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter", $this);

		return $this->Expects(array('foo','bar'))->ToContain('bar');	
	}

	public function ItShouldExplainBooleanValue()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter", $this);

		return $this->Expects($assert->ExplainValue(true))->ToBeEqualTo("(boolean) true");
	}

	public function ItShouldExplainStringValue()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter", $this);

		return $this->Expects($assert->ExplainValue("test string"))->ToBeEqualTo('(string) "test string"');
	}

	public function ItShouldExplainNullValue()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter", $this);

		return $this->Expects($assert->ExplainValue(null))->ToBeEqualTo('(null) ');
	}

	public function ItShouldExplainNumericValue()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter", $this);

		return $this->Expects($assert->ExplainValue(456))->ToBeEqualTo('(numeric) 456');
	}

	public function ItShouldExplainIndexedArrayValue()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter", $this);

		return $this->Expects($assert->ExplainValue(array(1,2,3)))->ToBeEqualTo('(array) ([0] => 1, [1] => 2, [2] => 3)');
	}

	public function ItShouldExplainAssociativeArrayValue()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter", $this);

		return $this->Expects($assert->ExplainValue(array("foo" => "123", "bar" => "456")))->ToBeEqualTo('(array) ([foo] => 123, [bar] => 456)');
	}

	public function ItShouldExplainObjectValue()
	{
		$assert = new AssertsCondition('123', "ItShouldntMatter", $this);

		return $this->Expects($assert->ExplainValue(new FooSpec()))->ToBeEqualTo('(object) FooSpec');
	}

}

