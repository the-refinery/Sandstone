<?php

class DingusSpec extends SpecBase
{
	protected $_dingus;
	
	public function BeforeEach()
	{
		$this->_dingus = new Dingus();
	}
	
	public function ItShouldInstantiateABareDingus()
	{
		Check($this->_dingus)->ShouldBeInstanceOf('Dingus');		
	}
	
	public function ItShouldReturnADingusWhenCallingAProperty()
	{
		Check($this->_dingus->TestProperty)->ShouldBeInstanceOf('Dingus');
	}
	
	public function ItShouldReturnADingusWhenCallingAMethod()
	{
		Check($this->_dingus->TestMethod())->ShouldBeInstanceOf('Dingus');
	}
	
	public function ItShouldAllowNestingOfDinguses()
	{
		Check($this->_dingus->TestProperty->FooMethod()->AnotherTestProperty)->ShouldBeInstanceOf('Dingus');
	}
	
	public function ItShouldKeepALogOfCalls()
	{
		$this->_dingus->TestMethod();
		$this->_dingus->AnotherMethod();
		$this->_dingus->FooBar('a', 'b', 'c');
		
		Check($this->_dingus->Stack())->ShouldContain("foobar(a, b, c)");
	}
}

?>