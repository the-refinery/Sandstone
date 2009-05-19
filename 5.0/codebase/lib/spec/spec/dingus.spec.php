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
}
