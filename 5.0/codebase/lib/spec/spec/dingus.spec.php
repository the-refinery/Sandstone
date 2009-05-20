<?php

class DingusSpec extends DescribeBehavior
{
	protected $_dingus;

	public function BeforeEach()
	{
		$this->_dingus = new Dingus();
	}

	public function ItShouldReturnADingusWhenCallingAMethod()
	{
		return $this->Expects($this->_dingus->TestMethod())->ToBeInstanceOf('Dingus');
	}

	public function ItShouldReturnADingusWhenCallingAProperty()
	{
		return $this->Expects($this->_dingus->TestProperty)->ToBeInstanceOf('Dingus');
	}

	public function ItShouldAllowNestingOfDinguses()
	{
		return $this->Expects($this->_dingus->TestMethod()->TestProperty)->ToBeInstanceOf('Dingus');
	}

	public function ItShouldRememberTheValueSetForAProperty()
	{
		$this->_dingus->TestProperty = 'abc';

		return $this->Expects($this->_dingus->TestProperty)->ToBeEqualTo('abc');
	}

	public function ItShouldReturnTheSameDingusEachTimeYouCallTheSameMethod()
	{
		$this->_dingus->FooBar()->TestProperty = 'abc';

		return $this->Expects($this->_dingus->FooBar()->TestProperty)->ToBeEqualTo('abc');
	}

	public function ItShouldReturnTheSameDingusRegardlessOfArguments()
	{
		$a = $this->_dingus->FooBar('abc', 'def');
		$b = $this->_dingus->FooBar(array(1,2,3));

		return $this->Expects($a)->ToBeEqualTo($b);
	}

	public function ItShouldAllowInjectingAReturnValue()
	{
		$this->_dingus->SetReturnValue('foobar', 'abcdefg');

		return $this->Expects($this->_dingus->FooBar())->ToBeEqualTo('abcdefg');
	}

	public function ItShouldRecordMethodCalls()
	{
		$this->_dingus->TestOne();
		$this->_dingus->TestTwo();

		return $this->Expects($this->_dingus->Recorder)->ToContain('TestTwo()');
	}

	public function ItShouldRecordMethodCallsWithArguments()
	{
		$this->_dingus->TestOne();
		$this->_dingus->TestTwo('abc', 4);

		return $this->Expects($this->_dingus->Recorder)->ToContain('TestTwo(abc, 4)');
	}

	public function ItShouldRecordReadingAProperty()
	{
		$this->_dingus->TestPropertyOne;
		$this->_dingus->TestPropertyTwo;

		return $this->Expects($this->_dingus->Recorder)->ToContain('TestPropertyTwo');
	}

	public function ItShouldRecordSettingAProperty()
	{
		$this->_dingus->TestPropertyOne;
		$this->_dingus->TestPropertyTwo = 'abc';

		return $this->Expects($this->_dingus->Recorder)->ToContain('TestPropertyTwo = abc');
	}
}
