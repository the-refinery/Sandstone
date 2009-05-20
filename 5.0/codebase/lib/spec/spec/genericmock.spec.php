<?php

class GenericMockSpec extends DescribeBehavior
{
	protected $_GenericMock;

	public function BeforeEach()
	{
		$this->_GenericMock = new GenericMock();
	}

	public function ItShouldReturnAGenericMockWhenCallingAMethod()
	{
		return $this->Expects($this->_GenericMock->TestMethod())->ToBeInstanceOf('GenericMock');
	}

	public function ItShouldReturnAGenericMockWhenCallingAProperty()
	{
		return $this->Expects($this->_GenericMock->TestProperty)->ToBeInstanceOf('GenericMock');
	}

	public function ItShouldAllowNestingOfGenericMockes()
	{
		return $this->Expects($this->_GenericMock->TestMethod()->TestProperty)->ToBeInstanceOf('GenericMock');
	}

	public function ItShouldRememberTheValueSetForAProperty()
	{
		$this->_GenericMock->TestProperty = 'abc';

		return $this->Expects($this->_GenericMock->TestProperty)->ToBeEqualTo('abc');
	}

	public function ItShouldReturnTheSameGenericMockEachTimeYouCallTheSameMethod()
	{
		$this->_GenericMock->FooBar()->TestProperty = 'abc';

		return $this->Expects($this->_GenericMock->FooBar()->TestProperty)->ToBeEqualTo('abc');
	}

	public function ItShouldReturnTheSameGenericMockRegardlessOfArguments()
	{
		$a = $this->_GenericMock->FooBar('abc', 'def');
		$b = $this->_GenericMock->FooBar(array(1,2,3));

		return $this->Expects($a)->ToBeEqualTo($b);
	}

	public function ItShouldAllowInjectingAReturnValue()
	{
		$this->_GenericMock->SetReturnValue('foobar', 'abcdefg');

		return $this->Expects($this->_GenericMock->FooBar())->ToBeEqualTo('abcdefg');
	}

	public function ItShouldHaveAnEmptyRecorderOnInstantiation()
	{
		return $this->Expects($this->_GenericMock->Recorder)->ToBeEmpty();
	}

	public function ItShouldCreateANameWhenCreatingAnUnnamedGenericMock()
	{
		return $this->Expects($this->_GenericMock->MockName)->ToExist();
	}

	public function ItShouldCreateANamedGenericMock()
	{
		$this->_GenericMock = new GenericMock('something');

		return $this->Expects($this->_GenericMock->MockName)->ToBeEqualTo('something');
	}

	public function ItShouldRecordMethodCalls()
	{
		$this->_GenericMock->TestOne();
		$this->_GenericMock->TestTwo();

		return $this->Expects($this->_GenericMock->Recorder)->ToContain('TestTwo()');
	}

	public function ItShouldRecordMethodCallsWithArguments()
	{
		$this->_GenericMock->TestOne();
		$this->_GenericMock->TestTwo('abc', 4);

		return $this->Expects($this->_GenericMock->Recorder)->ToContain('TestTwo(abc, 4)');
	}

	public function ItShouldRecordReadingAProperty()
	{
		$this->_GenericMock->TestPropertyOne;
		$this->_GenericMock->TestPropertyTwo;

		return $this->Expects($this->_GenericMock->Recorder)->ToContain('TestPropertyTwo');
	}

	public function ItShouldRecordSettingAProperty()
	{
		$this->_GenericMock->TestPropertyOne;
		$this->_GenericMock->TestPropertyTwo = 'abc';

		return $this->Expects($this->_GenericMock->Recorder)->ToContain('TestPropertyTwo = abc');
	}
}
