<?php

class AssertTestSpec extends DescribeBehavior
{
	protected $_Mock;

	public function BeforeEach()
	{
		$this->_Mock = new Mock();
	}

	public function ItShouldReturnAMockWhenCallingAMethod()
	{
		return $this->Expects($this->_Mock->TestMethod())->ToBeInstanceOf('Mock');
	}

	public function ItShouldReturnAMockWhenCallingAProperty()
	{
		return $this->Expects($this->_Mock->TestProperty)->ToBeInstanceOf('Mock');
	}

	public function ItShouldAllowNestingOfMockes()
	{
		return $this->Expects($this->_Mock->TestMethod()->TestProperty)->ToBeInstanceOf('Mock');
	}

	public function ItShouldRememberTheValueSetForAProperty()
	{
		$this->_Mock->TestProperty = 'abc';

		return $this->Expects($this->_Mock->TestProperty)->ToBeEqualTo('abc');
	}

	public function ItShouldReturnTheSameMockEachTimeYouCallTheSameMethod()
	{
		$this->_Mock->FooBar()->TestProperty = 'abc';

		return $this->Expects($this->_Mock->FooBar()->TestProperty)->ToBeEqualTo('abc');
	}

	public function ItShouldReturnTheSameMockRegardlessOfArguments()
	{
		$a = $this->_Mock->FooBar('abc', 'def');
		$b = $this->_Mock->FooBar(array(1,2,3));

		return $this->Expects($a)->ToBeEqualTo($b);
	}

	public function ItShouldAllowInjectingAReturnValue()
	{
		$this->_Mock->SetReturnValue('foobar', 'abcdefg');

		return $this->Expects($this->_Mock->FooBar())->ToBeEqualTo('abcdefg');
	}

	public function ItShouldAllowInjectingAPropertyValue()
	{
		$this->_Mock->SetPropertyValue('foobar', 'abcdefg');

		return $this->Expects($this->_Mock->FooBar)->ToBeEqualTo('abcdefg');
	}

	public function ItShouldHaveAnEmptyRecorderOnInstantiation()
	{
		return $this->Expects($this->_Mock->Recorder)->ToBeEmpty();
	}

	public function ItShouldCreateANameWhenCreatingAnUnnamedMock()
	{
		return $this->Expects($this->_Mock->MockName)->ToExist();
	}

	public function ItShouldCreateANamedMock()
	{
		$this->_Mock = new Mock('something');

		return $this->Expects($this->_Mock->MockName)->ToBeEqualTo('something');
	}

	public function ItShouldRecordMethodCalls()
	{
		$this->_Mock->TestOne();
		$this->_Mock->TestTwo();

		return $this->Expects($this->_Mock->Recorder)->ToContain('TestTwo()');
	}

	public function ItShouldRecordMethodCallsWithArguments()
	{
		$this->_Mock->TestOne();
		$this->_Mock->TestTwo('abc', 4);

		return $this->Expects($this->_Mock->Recorder)->ToContain('TestTwo(abc, 4)');
	}

	public function ItShouldRecordReadingAProperty()
	{
		$this->_Mock->TestPropertyOne;
		$this->_Mock->TestPropertyTwo;

		return $this->Expects($this->_Mock->Recorder)->ToContain('TestPropertyTwo');
	}

	public function ItShouldRecordSettingAProperty()
	{
		$this->_Mock->TestPropertyOne;
		$this->_Mock->TestPropertyTwo = 'abc';

		return $this->Expects($this->_Mock->Recorder)->ToContain('TestPropertyTwo = abc');
	}
}
