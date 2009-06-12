<?php

class AssertConditionSpec extends DescribeBehavior
{
	public function ItShouldAssertTo()
	{
		$assert = new AssertCondition(5, $this);
		$result = $assert->ToBeEqualTo(5)->TestResult;

		return $this->Expects($result)->ToBeTrue();
	}

	public function ItShouldAssertToNot()
	{
		$assert = new AssertCondition(5, $this);
		$result = $assert->ToNotBeEqualTo(4)->TestResult;

		return $this->Expects($result)->ToBeTrue();
	}

	public function ItShouldAssertEquals()
	{
		$assert = new AssertCondition(2, $this);
		$result = $assert->ToBeEqualTo(2)->TestResult;

		return $this->Expects($result)->ToBeTrue();
	}

	public function ItShouldAssertNull()
	{
		$assert = new AssertCondition(2, $this);

		return $this->Expects(null)->ToBeNull();
	}

	public function ItShouldAssertGreaterThan()
	{
		$assert = new AssertCondition(2, $this);

		return $this->Expects(2)->ToBeGreaterThan(1);
	}

	public function ItShouldAssertLessThan()
	{
		$assert = new AssertCondition(2, $this);

		return $this->Expects(2)->ToBeLessThan(5);
	}

	public function ItShouldAssertGreaterThanOrEqualTo()
	{
		$assert = new AssertCondition(2, $this);

		return $this->Expects(2)->ToBeGreaterThanOrEqualTo(1);
	}

	public function ItShouldAssertLessThanOrEqualTo()
	{
		$assert = new AssertCondition(2, $this);

		return $this->Expects(2)->ToBeLessThanOrEqualTo(5);
	}

	public function ItShouldAssertTrue()
	{
		$assert = new AssertCondition(true, $this);

		return $this->Expects(false)->ToBeTrue();
	}

	public function ItShouldAssertExists()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects($assert->ToExist()->TestResult)->ToBeEqualTo(true);
	}

	public function ItShouldAssertEmpty()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects(array())->ToBeEmpty();
	}

	public function ItShouldAssertContains()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects(array('foo','bar'))->ToContain('bar');	
	}

	public function ItShouldAssertHasKey()
	{
		$assert = new AssertCondition('123', $this);
	
		return $this->Expects(array('foo' => 'bar'))->ToHaveKey('foo');
	}

	public function ItShouldAssertInstanceOf()
	{
		$foo = new FooSpec();

		return $this->Expects($foo)->ToBeInstanceOf('DescribeBehavior');
	}

	public function ItShouldMatchRegularExpression()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects('http://www.designinginteractive.com/')->ToMatchRegex('@^(?:http://)?([^/]+)@i');
	}

	public function ItShouldAssertException()
	{
		try
		{
			throw new Exception('Test Exception');
		}
		catch (Exception $e)
		{
			$exception = $e;
		}

		return $this->Expects($exception)->ToBeInstanceOf('exception');
	}

	public function ItShouldExplainBooleanValue()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects($assert->ExplainValue(true))->ToBeEqualTo("(boolean) true");
	}

	public function ItShouldExplainStringValue()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects($assert->ExplainValue("test string"))->ToBeEqualTo('(string) "test string"');
	}

	public function ItShouldExplainNullValue()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects($assert->ExplainValue(null))->ToBeEqualTo('(null) ');
	}

	public function ItShouldExplainNumericValue()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects($assert->ExplainValue(456))->ToBeEqualTo('(numeric) 456');
	}

	public function ItShouldExplainIndexedArrayValue()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects($assert->ExplainValue(array(1,2,3)))->ToBeEqualTo('(array) ([0] => 1, [1] => 2, [2] => 3)');
	}

	public function ItShouldExplainAssociativeArrayValue()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects($assert->ExplainValue(array("foo" => "123", "bar" => "456")))->ToBeEqualTo('(array) ([foo] => 123, [bar] => 456)');
	}

	public function ItShouldExplainObjectValue()
	{
		$assert = new AssertCondition('123', $this);

		return $this->Expects($assert->ExplainValue(new FooSpec()))->ToBeEqualTo('(object) FooSpec');
	}

}

